<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Filament\Pages\Settings;

use Dashed\DashedCore\Classes\Sites;
use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedEcommerceEfulfillmentshop\Classes\EfulfillmentShop;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EfulfillmentshopSettingsPage extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'E-fulfillment shop';

    protected static string $view = 'dashed-core::settings.pages.default-settings';
    public array $data = [];

    public function mount(): void
    {
        $formData = [];
        $sites = Sites::getSites();
        foreach ($sites as $site) {
            $formData["efulfillment_shop_connected_{$site['id']}"] = Customsetting::get('efulfillment_shop_connected', $site['id'], 0) ? true : false;
            $formData["efulfillment_shop_sandbox_{$site['id']}"] = Customsetting::get('efulfillment_shop_sandbox', $site['id'], 0) ? true : false;
            $formData["efulfillment_shop_username_{$site['id']}"] = Customsetting::get('efulfillment_shop_username', $site['id']);
            $formData["efulfillment_shop_password_{$site['id']}"] = Customsetting::get('efulfillment_shop_password', $site['id']);
            $formData["efulfillment_shop_channel_id_{$site['id']}"] = Customsetting::get('efulfillment_shop_channel_id', $site['id']);
        }

        $this->form->fill($formData);
    }

    protected function getFormSchema(): array
    {
        $sites = Sites::getSites();
        $tabGroups = [];

        $tabs = [];
        foreach ($sites as $site) {
            $schema = [
                Placeholder::make('label')
                    ->label("E-fulfillment shop voor {$site['name']}")
                    ->content('Activeer E-fulfillmentshop om de bestellingen te versturen.')
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                Placeholder::make('label')
                    ->label("E-fulfillment shop is " . (! Customsetting::get('efulfillment_shop_connected', $site['id'], 0) ? 'niet' : '') . ' geconnect')
                    ->content(Customsetting::get('efulfillment_connection_error', $site['id'], ''))
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                TextInput::make("efulfillment_shop_username_{$site['id']}")
                    ->label('Gebruikersnaam')
                    ->maxLength(255),
                TextInput::make("efulfillment_shop_password_{$site['id']}")
                    ->label('Wachtwoord')
                    ->type('password')
                    ->maxLength(255),
                TextInput::make("efulfillment_shop_channel_id_{$site['id']}")
                    ->label('Channel ID')
                    ->maxLength(255),
                Toggle::make("efulfillment_shop_sandbox_{$site['id']}")
                    ->label('Sandbox mode'),
            ];

            $tabs[] = Tab::make($site['id'])
                ->label(ucfirst($site['name']))
                ->schema($schema)
                ->columns([
                    'default' => 1,
                    'lg' => 2,
                ]);
        }
        $tabGroups[] = Tabs::make('Sites')
            ->tabs($tabs);

        return $tabGroups;
    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function submit()
    {
        $sites = Sites::getSites();

        foreach ($sites as $site) {
            Customsetting::set('efulfillment_shop_sandbox', $this->form->getState()["efulfillment_shop_sandbox_{$site['id']}"], $site['id']);
            Customsetting::set('efulfillment_shop_username', $this->form->getState()["efulfillment_shop_username_{$site['id']}"], $site['id']);
            Customsetting::set('efulfillment_shop_password', $this->form->getState()["efulfillment_shop_password_{$site['id']}"], $site['id']);
            Customsetting::set('efulfillment_shop_channel_id', $this->form->getState()["efulfillment_shop_channel_id_{$site['id']}"], $site['id']);
            Customsetting::set('efulfillment_shop_connected', EfulfillmentShop::isConnected($site['id']), $site['id']);
        }

        Notification::make()
            ->title('De Efulfillment shop instellingen zijn opgeslagen')
            ->success()
            ->send();

        return redirect(EfulfillmentshopSettingsPage::getUrl());
    }
}
