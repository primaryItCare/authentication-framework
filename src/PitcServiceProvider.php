<?php

namespace pitc\Auth;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route; 
use Illuminate\Support\ServiceProvider;

class PitcServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->offerPublishing();
        $this->registerCommands();

    }

    public function register()
    {  
    }


    protected function registerCommands()
    {
        dd(__DIR__);
        $this->commands([
            Commands\CacheReset::class,
            Commands\CreateRole::class,
            Commands\CreatePermission::class,
            Commands\Show::class,
            Commands\UpgradeForTeams::class,
        ]);
    }
    protected function offerPublishing()
    {
        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        } 

        $this->publishes([
            __DIR__.'/../database/migrations/create_organizations_table.php.stub' => $this->getMigrationFileName('create_organizations_table.php'),
        ], 'migrations');
        $this->publishes([
            __DIR__.'/../database/migrations/create_countrys_table.php.stub' => $this->getMigrationFileName('create_countrys_table.php'),
        ], 'migrations');
        $this->publishes([
            __DIR__.'/../database/migrations/create_states_table.php.stub' => $this->getMigrationFileName('create_states_table.php'),
        ], 'migrations');
    }

    protected function getMigrationFileName($migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path.'*_'.$migrationFileName);
            })
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
