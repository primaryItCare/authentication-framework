<?php

namespace YM\Userform;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class UserformServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('YM\Userform\Controllers\OrganizationController');
        $this->loadViewsFrom(__DIR__.'/views','organization');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->databasePublishing();
        $this->formDataPublic();
        include __DIR__.'/routes.php';
    }

    protected function databasePublishing(){ 
        $this->publishes([
            __DIR__.'/database/migrations/create_organizations_table.php.stub' => $this->getMigrationFileName('create_organizations_table.php'),
        ], 'migrations');
        $this->publishes([
            __DIR__.'/database/migrations/create_countrys_table.php.stub' => $this->getMigrationFileName('create_countrys_table.php'),
        ], 'migrations');
        $this->publishes([
            __DIR__.'/database/migrations/create_states_table.php.stub' => $this->getMigrationFileName('create_states_table.php'),
        ], 'migrations');
    }
    protected function formDataPublic(){
        \File::copyDirectory(__DIR__.'/public', public_path());
    }
    protected function getMigrationFileName($migrationFileName): string{
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
