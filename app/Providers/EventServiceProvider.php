<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\TaskCreated; 
use App\Listeners\TaskCreatedListener;

class EventServiceProvider extends ServiceProvider
{
    
    protected $listen = [
            TaskCreated::class , 
                TaskCreatedListener::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}