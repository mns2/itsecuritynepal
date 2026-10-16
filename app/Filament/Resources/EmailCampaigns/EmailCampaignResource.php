<?php

namespace App\Filament\Resources\EmailCampaigns;

use App\Filament\Resources\EmailCampaigns\Pages\CreateEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Pages\EditEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Mail\NewsletterEmail;
use App\Models\Campaign;
use App\Models\EmailCampaign;
use App\Models\Subscriber;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class EmailCampaignResource extends Resource
{
    use \App\Filament\Concerns\RestrictedToArea;

    protected static string $accessArea = 'marketing';

    protected static ?string $model = EmailCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static string|\UnitEnum|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Email campaigns';

    protected static ?string $modelLabel = 'email campaign';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Email')->columnSpanFull()
                ->description(fn () => 'Goes to '.number_format(Subscriber::subscribed()->count()).' subscribed people. Every email includes an unsubscribe link and your address.')
                ->disabled(fn (?EmailCampaign $record) => $record && ! $record->isEditable())
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('subject')->required()->maxLength(120)->helperText('Short and specific, e.g. "New RHCSA batch starts 3 November".'),
                        TextInput::make('preheader')->label('Preview text')->maxLength(140)->helperText('Shown after the subject in most inboxes.'),
                    ]),
                    Select::make('campaign_id')->label('Related offer (optional)')->native(false)
                        ->options(fn () => Campaign::orderByDesc('id')->pluck('name', 'id'))->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($c = Campaign::find($state)) {
                                $set('cta_label', $c->cta_label ?: 'See the offer');
                                $set('cta_url', $c->has_landing_page ? url($c->landingUrl()) : $c->cta_url);
                            }
                        })
                        ->helperText('Fills the button with the offer page link.'),
                    TextInput::make('heading')->maxLength(120)->helperText('Large title at the top of the email (optional).'),
                    FileUpload::make('image')->label('Image (optional)')->hintIcon('heroicon-m-photo')->hint('1200 × 600 px (2:1), under 300 KB')->image()->disk('public')->directory('emails')->maxSize(2048)
                        ->helperText('600 px wide or more. Keep important text out of the image: many inboxes block images.'),
                    MarkdownEditor::make('body')->required()
                        ->toolbarButtons([['bold', 'italic', 'link'], ['heading', 'bulletList', 'orderedList'], ['undo', 'redo']]),
                    Grid::make(2)->schema([
                        TextInput::make('cta_label')->label('Button text')->maxLength(40)->placeholder('e.g. Book your seat'),
                        TextInput::make('cta_url')->label('Button link')->url()->maxLength(500)->requiredWith('cta_label'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('10s')
            ->columns([
                TextColumn::make('subject')->searchable()->wrap()->description(fn (EmailCampaign $r) => $r->campaign?->name),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => EmailCampaign::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'sent' => 'success', 'sending' => 'info', 'scheduled' => 'warning', default => 'gray' })
                    ->description(fn (EmailCampaign $r) => match ($r->status) {
                        'scheduled' => 'for '.$r->scheduled_at?->format('j M, g:i A'),
                        'sent' => $r->finished_at?->format('j M Y'),
                        default => null,
                    }),
                TextColumn::make('progress')->label('Delivered')
                    ->state(fn (EmailCampaign $r) => $r->recipients_count ? "{$r->sent_count} / {$r->recipients_count}" : '—')
                    ->description(fn (EmailCampaign $r) => $r->failed_count ? "{$r->failed_count} failed" : null),
                TextColumn::make('clicks')->numeric()->alignEnd()
                    ->description(fn (EmailCampaign $r) => $r->sent_count ? round($r->clicks / $r->sent_count * 100, 1).'% clicked' : null),
            ])
            ->recordActions([
                self::previewAction(),
                self::testAction(),
                Action::make('send')->label('Send')->icon('heroicon-o-paper-airplane')->color('primary')
                    ->visible(fn (EmailCampaign $r) => in_array($r->status, ['draft', 'cancelled'], true))
                    ->modalHeading('Send this email')
                    ->modalDescription(fn () => 'It will go to '.number_format(Subscriber::subscribed()->count()).' subscribed people, about '.\App\Support\NewsletterSender::perMinute().' per minute. Keep the Email campaigns page open while it sends.')
                    ->schema([DateTimePicker::make('at')->label('Send at (leave empty to send now)')->native(false)->seconds(false)->minDate(now())])
                    ->action(function (EmailCampaign $r, array $data) {
                        $n = $r->queueForSending(filled($data['at'] ?? null) ? \Illuminate\Support\Carbon::parse($data['at']) : null);
                        Notification::make()->success()->title($r->status === 'scheduled' ? 'Scheduled' : 'Sending started')
                            ->body("{$n} recipients. Sending runs from the Email campaigns page: keep it open until it says Sent.")->send();
                    }),
                Action::make('cancel')->label('Stop')->icon('heroicon-o-stop')->color('danger')->requiresConfirmation()
                    ->visible(fn (EmailCampaign $r) => in_array($r->status, ['scheduled', 'sending'], true))
                    ->action(fn (EmailCampaign $r) => $r->update(['status' => 'cancelled'])),
                ReplicateAction::make()->label('Copy')
                    ->excludeAttributes(['status', 'scheduled_at', 'started_at', 'finished_at', 'recipients_count', 'sent_count', 'failed_count', 'clicks'])
                    ->mutateRecordDataUsing(fn (array $data) => array_merge($data, ['subject' => $data['subject'].' (copy)'])),
                EditAction::make()->visible(fn (EmailCampaign $r) => $r->isEditable()),
            ])
            ->emptyStateHeading('No email campaigns yet')
            ->emptyStateDescription('Write an email about a new batch or offer and send it to your subscribers.');
    }

    public static function previewAction(): Action
    {
        return Action::make('preview')->label('Preview')->icon('heroicon-o-eye')->color('gray')
            ->modalSubmitAction(false)->modalCancelActionLabel('Close')->modalWidth('3xl')
            ->modalContent(function (EmailCampaign $record) {
                $html = (new NewsletterEmail($record, self::sampleSubscriber()))->render();

                return new HtmlString('<iframe title="Email preview" sandbox="" style="width:100%;height:70vh;border:1px solid #DCE4F0;border-radius:8px;background:#fff" srcdoc="'.e($html).'"></iframe>');
            });
    }

    public static function testAction(): Action
    {
        return Action::make('test')->label('Send test')->icon('heroicon-o-beaker')->color('gray')
            ->schema([TextInput::make('to')->label('Send a test to')->email()->required()->default(fn () => auth()->user()?->email)])
            ->action(function (EmailCampaign $record, array $data) {
                try {
                    Mail::to($data['to'])->send(new NewsletterEmail($record, self::sampleSubscriber($data['to']), isTest: true));
                    Notification::make()->success()->title('Test sent to '.$data['to'])->send();
                } catch (\Throwable $e) {
                    Notification::make()->danger()->title('Could not send the test')->body('Check the email (SMTP) settings in .env. '.$e->getMessage())->send();
                }
            });
    }

    private static function sampleSubscriber(string $email = 'preview@example.com'): Subscriber
    {
        return new Subscriber(['email' => $email, 'name' => 'Preview', 'token' => 'preview-only', 'status' => 'subscribed']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailCampaigns::route('/'),
            'create' => CreateEmailCampaign::route('/create'),
            'edit' => EditEmailCampaign::route('/{record}/edit'),
        ];
    }
}
