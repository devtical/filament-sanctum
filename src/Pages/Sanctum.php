<?php

namespace Devtical\Sanctum\Pages;

use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Sanctum extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament-sanctum::pages.sanctum';

    public static function getNavigationIcon(): string
    {
        return config('filament-sanctum.navigation.icon', 'heroicon-o-finger-print');
    }

    public static function getDefaultSlug(): string
    {
        return config('filament-sanctum.navigation.slug', 'sanctum');
    }

    public function getTitle(): string
    {
        return trans('Sanctum');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-sanctum.navigation.sidebar_menu.group', null);
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-sanctum.navigation.sidebar_menu.sort', -1);
    }

    public static function getNavigationLabel(): string
    {
        return trans('Sanctum');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('filament-sanctum.navigation.sidebar_menu.enabled');
    }

    public static function canAccess(): bool
    {
        if (! config('filament-sanctum.authorization.enabled')) {
            return true;
        }

        $gate = config('filament-sanctum.authorization.gate');

        if (blank($gate)) {
            return true;
        }

        return Gate::allows($gate);
    }

    protected function getTableQuery(): Builder
    {
        return Auth::user()->tokens()->getQuery();
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'id';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(trans('Name'))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('abilities')
                ->label(trans('Abilities'))
                ->getStateUsing(function ($record): string {
                    $count = count($this->resolveAbilities(null, $record));

                    return $count === 0
                        ? trans('None')
                        : trans(':count abilities', ['count' => $count]);
                })
                ->tooltip(fn ($record): ?string => blank($this->resolveAbilities(null, $record))
                    ? null
                    : collect($this->formatAbilityLabels($this->resolveAbilities(null, $record)))->join(', '))
                ->action($this->makeTokenDetailsAction('tokenDetailsColumn'))
                ->color(fn ($record): string => count($this->resolveAbilities(null, $record)) > 0 ? 'primary' : 'gray'),

            Tables\Columns\TextColumn::make('expires_at')
                ->label(trans('Expires at'))
                ->dateTime()
                ->sortable()
                ->placeholder(trans('Never')),

            Tables\Columns\TextColumn::make('last_used_at')
                ->label(trans('Last used at'))
                ->dateTime()
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label(trans('Created at'))
                ->dateTime()
                ->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            $this->makeTokenDetailsAction()
                ->icon('heroicon-o-eye')
                ->label(trans('Details')),
            Action::make('revoke')
                ->label(trans('Revoke'))
                ->action(fn ($record) => $record->delete())
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new')
                ->label(trans('Create a new Token'))
                ->action(function (array $data) {
                    $user = Auth::user();

                    $expiresAt = $this->resolveTokenExpiration($data);

                    $token = $user->createToken(
                        $data['name'],
                        $data['abilities'] ?? [],
                        $expiresAt,
                    )->plainTextToken;

                    request()->session()->flash('sanctum-token', $token);

                    Notification::make()
                        ->title(trans('Token was created successfully'))
                        ->success()
                        ->icon('heroicon-o-finger-print')
                        ->send();

                    return redirect(static::getUrl(panel: Filament::getCurrentPanel()?->getId()));
                })
                ->form($this->getCreateTokenFormSchema()),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            BulkAction::make('revoke')
                ->label(trans('Revoke'))
                ->action(fn (Collection $records) => $records->each->delete())
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected function getCreateTokenFormSchema(): array
    {
        $nameField = Forms\Components\TextInput::make('name')
            ->label(trans('Token Name'))
            ->required();

        $expirationFields = config('filament-sanctum.abilities.allow_expiration')
            ? [
                Grid::make(2)
                    ->schema([
                        $nameField,
                        Forms\Components\Select::make('expiration')
                            ->label(trans('Expiration'))
                            ->options(fn (): array => $this->getExpirationOptions())
                            ->default(fn (): string => $this->getDefaultExpirationValue())
                            ->live()
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-o-calendar-days'),
                    ]),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label(trans('Expires at'))
                    ->minDate(now())
                    ->visible(fn (Get $get): bool => $get('expiration') === 'custom')
                    ->required(fn (Get $get): bool => $get('expiration') === 'custom'),
            ]
            : [$nameField];

        return [
            ...$expirationFields,
            Forms\Components\CheckboxList::make('abilities')
                ->label(trans('Abilities'))
                ->options(config('filament-sanctum.abilities.list'))
                ->columns(config('filament-sanctum.abilities.columns')),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveTokenExpiration(array $data): ?Carbon
    {
        if (! config('filament-sanctum.abilities.allow_expiration')) {
            return null;
        }

        return match ($data['expiration'] ?? 'never') {
            'never' => null,
            'custom' => filled($data['expires_at'] ?? null)
                ? Carbon::parse($data['expires_at'])
                : null,
            default => now()->addDays((int) $data['expiration']),
        };
    }

    /**
     * @return array<string, string>
     */
    protected function getExpirationOptions(): array
    {
        $options = [];

        foreach (config('filament-sanctum.abilities.expiration_presets', [7, 30, 60, 90]) as $days) {
            $options[(string) $days] = trans(':days days (:date)', [
                'days' => $days,
                'date' => now()->addDays((int) $days)->format('M j, Y'),
            ]);
        }

        $options['custom'] = trans('Custom');
        $options['never'] = trans('No expiration');

        return $options;
    }

    protected function getDefaultExpirationValue(): string
    {
        $defaultDays = config('filament-sanctum.abilities.default_expiration_days');

        if (blank($defaultDays)) {
            return 'never';
        }

        $presets = array_map(
            strval(...),
            config('filament-sanctum.abilities.expiration_presets', [7, 30, 60, 90]),
        );

        return in_array((string) $defaultDays, $presets, true)
            ? (string) $defaultDays
            : 'custom';
    }

    protected function makeTokenDetailsAction(string $name = 'tokenDetails'): ViewAction
    {
        return ViewAction::make($name)
            ->modalHeading(fn ($record) => $record->name)
            ->schema($this->getTokenDetailsSchema());
    }

    /**
     * @return array<int, TextEntry>
     */
    protected function getTokenDetailsSchema(): array
    {
        return [
            TextEntry::make('abilities')
                ->label(trans('Abilities'))
                ->badge()
                ->formatStateUsing(fn ($state): ?string => $this->formatAbilityLabel($state))
                ->placeholder(trans('None')),
            TextEntry::make('expires_at')
                ->label(trans('Expires at'))
                ->dateTime()
                ->placeholder(trans('Never')),
            TextEntry::make('last_used_at')
                ->label(trans('Last used at'))
                ->dateTime()
                ->placeholder(trans('Never')),
            TextEntry::make('created_at')
                ->label(trans('Created at'))
                ->dateTime(),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function resolveAbilities(mixed $state, mixed $record = null): array
    {
        if (is_array($state)) {
            return $state;
        }

        if (is_string($state) && filled($state)) {
            $decoded = json_decode($state, true);

            if (is_array($decoded)) {
                return $decoded;
            }

            return [$state];
        }

        if ($record !== null && filled($record->abilities ?? null)) {
            return is_array($record->abilities) ? $record->abilities : [];
        }

        return [];
    }

    protected function formatAbilityLabel(mixed $state): ?string
    {
        if (blank($state) || is_array($state)) {
            return null;
        }

        $list = config('filament-sanctum.abilities.list', []);

        return $list[$state] ?? (string) $state;
    }

    /**
     * @param  array<int, string>  $abilities
     * @return array<int, string>
     */
    protected function formatAbilityLabels(array $abilities): array
    {
        return collect($abilities)
            ->map(fn (string $ability): string => $this->formatAbilityLabel($ability) ?? $ability)
            ->values()
            ->all();
    }
}
