<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;


class TaskCreatedListener
{
    /**
     * Create the event listener.
     */
    public $task ; 
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskCreated $event): void
    {
        $task = $event->task ; 

        Log::info('the user is register in Task' .$task->name) ; 
        Session()->flash('the user is Confrined to do');
    }
}