<?php

namespace App\Filament\Resources\Team;

use App\Filament\Resources\Team\Pages\CreateTeamMember;
use App\Filament\Resources\Team\Pages\EditTeamMember;
use App\Filament\Resources\Team\Pages\ListTeam;
use App\Filament\Resources\Team\Schemas\TeamMemberForm;
use App\Filament\Resources\Team\Tables\TeamTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends \Filament\Resources\Resource<User>
 */
class TeamResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Team';

    protected static ?string $modelLabel = 'team member';

    protected static ?string $slug = 'team';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage users') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role(['admin', 'staff']);
    }

    public static function form(Schema $schema): Schema
    {
        return TeamMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeam::route('/'),
            'create' => CreateTeamMember::route('/create'),
            'edit' => EditTeamMember::route('/{record}/edit'),
        ];
    }
}
