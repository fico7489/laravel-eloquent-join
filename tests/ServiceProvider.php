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
        $_ENV['type'] = 'mysql';  //sqllite, mysql, postgresql

        if ($_ENV['type'] = 'mysql') {
            \Artisan::call('migrate', ['--database' => 'mysql']);
        } elseif ($_ENV['type'] = 'sqllite') {
            \Artisan::call('migrate', ['--database' => 'sqllite']);
        }

        $migrator = $this->app->make('migrator');
        $migrator->run($path);
    }
}
