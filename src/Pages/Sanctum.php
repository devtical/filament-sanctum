<?php

namespace Devtical\Sanctum\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Sanctum Page Class
 * 
 * This class represents the main page for managing Laravel Sanctum API tokens
 * within the Filament admin panel. It provides functionality to create, view,
 * and revoke personal access tokens for authenticated users.
 */
class Sanctum extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    /**
     * Navigation icon for the page
     * Set to null to use default icon from config
     */
    protected static string|BackedEnum|null $navigationIcon = null;

    /**
     * Get the navigation icon for this page
     * 
     * @return string The icon name from config or default fingerprint icon
     */
    public static function getNavigationIcon(): string
    {
        return config('filament-sanctum.navigation.icon', 'heroicon-o-finger-print');
    }

    /**
     * The view file that will be used to render this page
     */
    protected string $view = 'filament-sanctum::pages.sanctum';

    /**
     * The URL slug for this page
     */
    protected static ?string $slug = 'sanctum';

    /**
     * Get the page title
     * 
     * @return string Translated page title
     */
    public function getTitle(): string
    {
        return trans('Sanctum');
    }

    /**
     * Get the navigation group this page belongs to
     * 
     * @return string|null The navigation group name from config
     */
    public static function getNavigationGroup(): ?string
    {
        return config('filament-sanctum.navigation.sidebar_menu.group', null);
    }

    /**
     * Get the sort order for navigation menu
     * 
     * @return int|null Sort order from config, defaults to -1
     */
    public static function getNavigationSort(): ?int
    {
        return config('filament-sanctum.navigation.sidebar_menu.sort', -1);
    }

    /**
     * Get the navigation label for this page
     * 
     * @return string Translated navigation label
     */
    public static function getNavigationLabel(): string
    {
        return trans('Sanctum');
    }

    /**
     * Determine if this page should be registered in navigation
     * 
     * @return bool Whether to show this page in navigation menu
     */
    public static function shouldRegisterNavigation(): bool
    {
        return config('filament-sanctum.navigation.sidebar_menu.enabled');
    }

    /**
     * Get the query builder for the tokens table
     * 
     * @return Builder Query builder for authenticated user's tokens
     */
    protected function getTableQuery(): Builder
    {
        return Auth::user()->tokens()->getQuery();
    }

    /**
     * Get the default column to sort by
     * 
     * @return string|null Default sort column (id)
     */
    protected function getDefaultTableSortColumn(): ?string
    {
        return 'id';
    }

    /**
     * Get the default sort direction
     * 
     * @return string|null Default sort direction (descending)
     */
    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    /**
     * Define the columns for the tokens table
     * 
     * @return array Array of table column definitions
     */
    protected function getTableColumns(): array
    {
        return [
            // Token name column - sortable and searchable
            Tables\Columns\TextColumn::make('name')
                ->label(trans('Name'))
                ->sortable()
                ->searchable(),
            
            // Token abilities column - displayed as tags
            Tables\Columns\TagsColumn::make('abilities')
                ->label(trans('Abilities')),
            
            // Last used timestamp column - sortable datetime
            Tables\Columns\TextColumn::make('last_used_at')
                ->label(trans('Last used at'))
                ->dateTime()
                ->sortable(),
            
            // Created timestamp column - sortable datetime
            Tables\Columns\TextColumn::make('created_at')
                ->label(trans('Created at'))
                ->dateTime()
                ->sortable(),
        ];
    }

    /**
     * Define the header actions available on this page
     * 
     * @return array Array of header action definitions
     */
    protected function getHeaderActions(): array
    {
        return [
            // Action to create a new personal access token
            Action::make('new')
                ->label(trans('Create a new Token'))
                ->action(function (array $data) {
                    // Get the authenticated user
                    $user = Auth::user();
                    
                    // Create a new token with specified name and abilities
                    $token = $user->createToken($data['name'], $data['abilities'])->plainTextToken;
                    
                    // Flash the token to session for display
                    request()->session()->flash('sanctum-token', $token);
                    
                    // Show success notification
                    Notification::make()
                        ->title(trans('Saved successfully'))
                        ->success()
                        ->icon('heroicon-o-finger-print')
                        ->title(trans('Token was created successfully'))
                        ->send();

                    // Redirect back to the sanctum page
                    return redirect(route('filament.admin.pages.'.config('filament-sanctum.navigation.slug')));
                })
                ->form([
                    // Token name input field
                    Forms\Components\TextInput::make('name')
                        ->label(trans('Token Name'))
                        ->required(),
                    
                    // Token abilities selection - checkbox list
                    Forms\Components\CheckboxList::make('abilities')
                        ->label(trans('Abilities'))
                        ->options(config('filament-sanctum.abilities.list'))
                        ->columns(config('filament-sanctum.abilities.columns')),
                ]),
        ];
    }

    /**
     * Define bulk actions available for the tokens table
     * 
     * @return array Array of bulk action definitions
     */
    protected function getTableBulkActions(): array
    {
        return [
            // Bulk action to revoke multiple tokens at once
            BulkAction::make('revoke')
                ->label(trans('Revoke'))
                ->action(fn (Collection $records) => $records->each->delete())
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }
}
