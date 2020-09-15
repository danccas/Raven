<?php

namespace Raven\Contracts\Broadcasting;

interface ShouldBroadcast
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Raven\Broadcasting\Channel|\Raven\Broadcasting\Channel[]
     */
    public function broadcastOn();
}
