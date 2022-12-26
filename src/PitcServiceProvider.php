<?php

namespace pitc\Auth;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route; 
use Illuminate\Support\ServiceProvider;

class PitcAuthServiceProvider extends ServiceProvider
{
    public function boot(PitcAuth $pitc)
    {
        $this->databasePublishing();
    }

    public function register()
    {  
    }

 
    protected function databasePublishing()
    { 

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
