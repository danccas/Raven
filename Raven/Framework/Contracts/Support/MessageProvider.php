<?php

namespace Raven\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \Raven\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}
