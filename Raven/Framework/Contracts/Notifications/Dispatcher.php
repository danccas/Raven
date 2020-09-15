<?php

namespace Raven\Contracts\Notifications;

interface Dispatcher
{
    /**
     * Send the given notification to the given notifiable entities.
     *
     * @param  \Raven\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function send($notifiables, $notification);

    /**
     * Send the given notification immediately.
     *
     * @param  \Raven\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function sendNow($notifiables, $notification);
}
