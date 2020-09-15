<?php

namespace Raven\Support\Facades;

use Raven\Notifications\AnonymousNotifiable;
use Raven\Notifications\ChannelManager;
use Raven\Support\Testing\Fakes\NotificationFake;

/**
 * @method static void send(\Raven\Support\Collection|array|mixed $notifiables, $notification)
 * @method static void sendNow(\Raven\Support\Collection|array|mixed $notifiables, $notification)
 * @method static mixed channel(string|null $name = null)
 * @method static \Raven\Notifications\ChannelManager locale(string|null $locale)
 * @method static void assertSentTo(mixed $notifiable, string $notification, callable $callback = null)
 * @method static void assertSentToTimes(mixed $notifiable, string $notification, int $times = 1)
 * @method static void assertNotSentTo(mixed $notifiable, string $notification, callable $callback = null)
 * @method static void assertNothingSent()
 * @method static void assertTimesSent(int $expectedCount, string $notification)
 * @method static \Raven\Support\Collection sent(mixed $notifiable, string $notification, callable $callback = null)
 * @method static bool hasSent(mixed $notifiable, string $notification)
 *
 * @see \Raven\Notifications\ChannelManager
 */
class Notification extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return \Raven\Support\Testing\Fakes\NotificationFake
     */
    public static function fake()
    {
        static::swap($fake = new NotificationFake);

        return $fake;
    }

    /**
     * Begin sending a notification to an anonymous notifiable.
     *
     * @param  string  $channel
     * @param  mixed  $route
     * @return \Raven\Notifications\AnonymousNotifiable
     */
    public static function route($channel, $route)
    {
        return (new AnonymousNotifiable)->route($channel, $route);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ChannelManager::class;
    }
}
