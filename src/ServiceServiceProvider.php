<?php

namespace Interpro\Service;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function boot(Dispatcher $dispatcher)
    {
        //Log::info('Загрузка ServiceServiceProvider');
    }

    /**
     * @return void
     */
    public function register()
    {
        //Log::info('Регистрация ServiceServiceProvider');

        $this->app->singleton(
            'Interpro\Service\Contracts\CleanMediator',
            'Interpro\Service\CleanMediator'
        );

        $this->app->singleton(
            'sync.command',
            'Interpro\Service\Command\Artisan\Synchronize'
        );

        $this->app->singleton(
            'clean:db.command',
            'Interpro\Service\Command\Artisan\CleanDb'
        );

        $this->app->singleton(
            'clean:file.command',
            'Interpro\Service\Command\Artisan\CleanFile'
        );

        $this->commands([
            'sync.command',
            'clean:db.command',
            'clean:file.command'
        ]);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            'sync.command',
            'clean:db.command',
            'clean:file.command'
        ];
    }

}
