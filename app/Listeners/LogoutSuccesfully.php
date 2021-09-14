<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Activitylog\Contracts\Activity;

class LogoutSuccesfully
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        if ($event->user->sso_id) {
            activity()
            ->tap(function(Activity $activity) {
                $activity->causer_id = auth()->user()->sso_id;
            })
            ->log('logout successfully');
        }
    }
}
