<?php

namespace Fico7489\Laravel\EloquentJoin\Tests;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        //register
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
    }

    protected function loadMigrationsFrom($path)
    {
        $_ENV['type'] = 'sqlite';  //sqlite, mysql, pgsql

        \Artisan::call('migrate', ['--database' => $_ENV['type']]);

        $migrator = $this->app->make('migrator');
        $migrator->run($path);
    }
}
