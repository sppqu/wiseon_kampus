<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $settingsPath = storage_path('app/settings_presensi.json');
            if (file_exists($settingsPath)) {
                $settings = json_decode(file_get_contents($settingsPath), true);
                if (!empty($settings['timezone']) && in_array($settings['timezone'], timezone_identifiers_list(), true)) {
                    config(['app.timezone' => $settings['timezone']]);
                    date_default_timezone_set($settings['timezone']);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika gagal membaca pengaturan saat instalasi awal
        }
    }
}
