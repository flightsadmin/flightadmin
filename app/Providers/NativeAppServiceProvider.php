<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Menu::create(
            Menu::app(),
            Menu::edit(),
            Menu::view(),
            Menu::make(
                Menu::link('https://docs.flightadmin.info', 'Documentation')->openInBrowser(),
                Menu::separator(),
                Menu::link('https://docs.flightadmin.info', 'Learn More ...')->openInBrowser(),
                Menu::separator(),
                Menu::checkbox('Accept Terms')->checked(),
            )->label('Help'),
            Menu::window(),
        );

        Window::open()
            ->width(1500)
            ->height(800)
            ->minWidth(1400)
            ->minHeight(800)
            ->showDevTools(false)
            ->rememberState();

        // Check if the migrations table exists and if users table is empty
        if (Schema::hasTable('users') && DB::table('users')->count() === 0) {
            $this->runMigrations();
            $this->runDatabaseSeeding();
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }

    protected function runMigrations(): void
    {
        if (Artisan::call('migrate:status') !== 0) {
            Artisan::call('migrate', ['--force' => true]);
        }
    }

    protected function runDatabaseSeeding(): void
    {
        Artisan::call('db:seed', ['--force' => true]);
    }
}
