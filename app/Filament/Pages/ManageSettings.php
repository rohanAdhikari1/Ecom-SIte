<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{
    protected string $view = 'filament.pages.manage-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $title = 'Shop Settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'shop_name' => Setting::get('shop_name'),
            'shop_tagline' => Setting::get('shop_tagline'),
            'contact_phone' => Setting::get('contact_phone'),
            'contact_whatsapp' => Setting::get('contact_whatsapp'),
            'contact_email' => Setting::get('contact_email'),
            'shop_address' => Setting::get('shop_address'),
            'shop_lat' => Setting::get('shop_lat'),
            'shop_lng' => Setting::get('shop_lng'),
            'shop_google_maps_url' => Setting::get('shop_google_maps_url'),
            'facebook_url' => Setting::get('facebook_url'),
            'instagram_url' => Setting::get('instagram_url'),
            'min_order_amount' => Setting::get('min_order_amount'),
            'default_delivery_fee' => Setting::get('default_delivery_fee'),
            'esewa_enabled' => (bool) Setting::get('esewa_enabled'),
            'esewa_note' => Setting::get('esewa_note'),
            'announcement_text' => Setting::get('announcement_text'),
            'hero_badge_text' => Setting::get('hero_badge_text'),
            'story_heading' => Setting::get('story_heading'),
            'story_body' => Setting::get('story_body'),
            'story_stat1_value' => Setting::get('story_stat1_value'),
            'story_stat1_label' => Setting::get('story_stat1_label'),
            'story_stat2_value' => Setting::get('story_stat2_value'),
            'story_stat2_label' => Setting::get('story_stat2_label'),
            'why_choose_us' => json_decode(Setting::get('why_choose_us', '[]'), true) ?: [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Shop Details')
                    ->columns(2)
                    ->components([
                        TextInput::make('shop_name')->required(),
                        TextInput::make('shop_tagline'),
                        TextInput::make('contact_phone'),
                        TextInput::make('contact_whatsapp'),
                        TextInput::make('contact_email')->email(),
                        TextInput::make('shop_address'),
                        TextInput::make('facebook_url')->url(),
                        TextInput::make('instagram_url')->url(),
                    ]),
                Section::make('Shop Location')
                    ->description('Used as the pickup point and the default map pin for delivery orders.')
                    ->columns(3)
                    ->components([
                        TextInput::make('shop_lat')
                            ->label('Latitude')
                            ->numeric(),
                        TextInput::make('shop_lng')
                            ->label('Longitude')
                            ->numeric(),
                        TextInput::make('shop_google_maps_url')
                            ->label('Google Maps link')
                            ->url()
                            ->columnSpan(1),
                    ]),
                Section::make('Homepage Content')
                    ->description('Everything on the storefront home page is editable here — reuse this site for a different shop without touching code.')
                    ->columns(2)
                    ->components([
                        TextInput::make('announcement_text')
                            ->label('Top announcement bar text')
                            ->columnSpanFull(),
                        TextInput::make('hero_badge_text')
                            ->label('Hero badge text'),
                        TextInput::make('story_heading')
                            ->label('Story section heading'),
                        Textarea::make('story_body')
                            ->label('Story section body')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('story_stat1_value')->label('Stat 1 value'),
                        TextInput::make('story_stat1_label')->label('Stat 1 label'),
                        TextInput::make('story_stat2_value')->label('Stat 2 value'),
                        TextInput::make('story_stat2_label')->label('Stat 2 label'),
                        Repeater::make('why_choose_us')
                            ->label('"Why Choose Us" cards')
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Emoji icon')
                                    ->required()
                                    ->maxLength(4),
                                TextInput::make('title')
                                    ->required(),
                                TextInput::make('body')
                                    ->label('Description')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add card')
                            ->reorderable()
                            ->maxItems(6),
                    ]),
                Section::make('Ordering')
                    ->columns(2)
                    ->components([
                        TextInput::make('min_order_amount')
                            ->label('Minimum order amount')
                            ->numeric()
                            ->prefix('Rs.'),
                        TextInput::make('default_delivery_fee')
                            ->numeric()
                            ->prefix('Rs.'),
                    ]),
                Section::make('Payments')
                    ->components([
                        Toggle::make('esewa_enabled')
                            ->label('Show eSewa as a payment option at checkout')
                            ->live(),
                        Textarea::make('esewa_note')
                            ->label('Note shown to customers when eSewa is selected')
                            ->rows(2)
                            ->visible(fn ($get) => $get('esewa_enabled')),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $data['esewa_enabled'] = $data['esewa_enabled'] ? '1' : '0';
        $data['why_choose_us'] = json_encode(array_values($data['why_choose_us'] ?? []));

        foreach ($data as $key => $value) {
            Setting::set($key, $value === null ? null : (string) $value);
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
