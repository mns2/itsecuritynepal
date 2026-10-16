<?php

namespace App\Filament\Resources\Subscribers;

use App\Filament\Resources\Subscribers\Pages\CreateSubscriber;
use App\Filament\Resources\Subscribers\Pages\EditSubscriber;
use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Models\Subscriber;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class SubscriberResource extends Resource
{
    use \App\Filament\Concerns\RestrictedToArea;

    protected static string $accessArea = 'marketing';

    protected static ?string $model = Subscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Subscribers';

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Subscriber')->columnSpanFull()->schema([
                Grid::make(3)->schema([
                    TextInput::make('email')->email()->required()->maxLength(160)->unique(ignoreRecord: true),
                    TextInput::make('name')->maxLength(120),
                    Select::make('status')->options(Subscriber::STATUSES)->default('subscribed')->required()->native(false),
                ]),
                Checkbox::make('consent_confirmed')->label('This person agreed to receive marketing emails from IT Security Nepal')
                    ->accepted()->dehydrated(false)->visibleOn('create')
                    ->helperText('Only add people who asked for updates (e.g. on a paper form or at an event). Never add bought or scraped lists.'),
                Grid::make(3)->schema([
                    TextEntry::make('source')->formatStateUsing(fn ($state) => Subscriber::SOURCES[$state] ?? $state),
                    TextEntry::make('consent_at')->label('Consent given')->dateTime('j M Y, g:i A'),
                    TextEntry::make('confirmed_at')->label('Confirmed')->dateTime('j M Y, g:i A')->placeholder('Not yet'),
                ])->visibleOn('edit'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable()->description(fn (Subscriber $r) => $r->name),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => Subscriber::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'subscribed' => 'success', 'pending' => 'warning', default => 'gray' }),
                TextColumn::make('source')->formatStateUsing(fn ($state) => Subscriber::SOURCES[$state] ?? $state)->toggleable(),
                TextColumn::make('created_at')->label('Signed up')->date('j M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Subscriber::STATUSES),
                SelectFilter::make('source')->options(Subscriber::SOURCES),
            ])
            ->recordActions([
                Action::make('confirm')->label('Confirm')->icon('heroicon-o-check')->color('success')->requiresConfirmation()
                    ->modalDescription('Only confirm if you know this person asked for emails.')
                    ->visible(fn (Subscriber $r) => $r->status === 'pending')->action(fn (Subscriber $r) => $r->confirm()),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('unsubscribe')->label('Unsubscribe')->icon('heroicon-o-no-symbol')->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->unsubscribe()),
                DeleteBulkAction::make(),
            ])])
            ->emptyStateHeading('No subscribers yet')
            ->emptyStateDescription('People who sign up in the pop-up, the footer or the enquiry form appear here.');
    }

    public static function getNavigationBadge(): ?string
    {
        $n = Subscriber::subscribed()->count();

        return $n ? number_format($n) : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscribers::route('/'),
            'create' => CreateSubscriber::route('/create'),
            'edit' => EditSubscriber::route('/{record}/edit'),
        ];
    }
}
