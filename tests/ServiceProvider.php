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
        \Artisan::call('migrate', ['--database' => 'sqllite']);
        //\Artisan::call('migrate', ['--database' => 'mysql']);

        $migrator = $this->app->make('migrator');
        $migrator->run($path);
    }
}
