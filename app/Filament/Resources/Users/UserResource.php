<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Instructor;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

/** Staff logins: admins (/admin) and instructors (/trainer). */
class UserResource extends Resource
{
    use \App\Filament\Concerns\RestrictedToArea;

    protected static string $accessArea = 'staff';

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Staff accounts';

    protected static ?string $modelLabel = 'staff account';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')->columnSpanFull()
                ->description(new HtmlString('Instructors sign in at <strong>'.e(url('/trainer')).'</strong> and see only the classes assigned to them (Training → Classes → Instructor). All other roles sign in at <strong>'.e(url('/admin')).'</strong> and see only the sections their role allows.'))
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')->required()->maxLength(120),
                        TextInput::make('email')->email()->required()->maxLength(160)->unique(ignoreRecord: true),
                        TextInput::make('phone')->tel()->maxLength(30),
                        Select::make('role')->options(User::ROLES)->default('instructor')->required()->native(false)->live()
                            ->disabled(fn (?User $record) => $record?->is(auth()->user()))
                            ->helperText(fn (?User $record) => $record?->is(auth()->user()) ? 'You cannot change your own role.' : null),
                        Select::make('instructor_id')->label('Public instructor profile (optional)')->native(false)
                            ->options(fn () => Instructor::orderBy('name')->pluck('name', 'id'))
                            ->visible(fn (Get $get) => $get('role') === 'instructor'),
                        Toggle::make('is_active')->label('Can sign in')->default(true)->inline(false)
                            ->disabled(fn (?User $record) => $record?->is(auth()->user())),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('password')->password()->revealable()->rule('min:10')
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->label(fn (string $operation) => $operation === 'create' ? 'Password' : 'New password (leave empty to keep)')
                            ->helperText('At least 10 characters. Share it privately; they can change it under their profile after signing in.'),
                        TextInput::make('password_confirmation')->password()->revealable()->same('password')->dehydrated(false)
                            ->required(fn (Get $get) => filled($get('password')))->label('Repeat password'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->modifyQueryUsing(fn ($query) => $query->withCount('classes'))
            ->columns([
                TextColumn::make('name')->searchable()->description(fn (User $r) => $r->email),
                TextColumn::make('role')->badge()->formatStateUsing(fn ($state) => ucfirst((string) $state))
                    ->color(fn ($state) => match ($state) { 'admin' => 'primary', 'instructor' => 'warning', default => 'info' }),
                TextColumn::make('classes_count')->label('Classes')->numeric()->alignCenter(),
                ToggleColumn::make('is_active')->label('Can sign in')->disabled(fn (User $r) => $r->is(auth()->user())),
                TextColumn::make('created_at')->label('Created')->date('j M Y')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([SelectFilter::make('role')->options(collect(User::ROLES)->map(fn ($l) => strtok($l, ':'))->all())])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
