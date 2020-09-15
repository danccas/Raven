<?php

namespace Raven\Support\Facades;

use Raven\Support\Testing\Fakes\MailFake;

/**
 * @method static \Raven\Mail\PendingMail to($users)
 * @method static \Raven\Mail\PendingMail bcc($users)
 * @method static void raw(string $text, $callback)
 * @method static void send(\Raven\Contracts\Mail\Mailable|string|array $view, array $data = [], \Closure|string $callback = null)
 * @method static array failures()
 * @method static mixed queue(\Raven\Contracts\Mail\Mailable|string|array $view, string $queue = null)
 * @method static mixed later(\DateTimeInterface|\DateInterval|int $delay, \Raven\Contracts\Mail\Mailable|string|array $view, string $queue = null)
 * @method static void assertSent(string $mailable, callable|int $callback = null)
 * @method static void assertNotSent(string $mailable, callable|int $callback = null)
 * @method static void assertNothingSent()
 * @method static void assertQueued(string $mailable, callable|int $callback = null)
 * @method static void assertNotQueued(string $mailable, callable $callback = null)
 * @method static void assertNothingQueued()
 * @method static \Raven\Support\Collection sent(string $mailable, \Closure|string $callback = null)
 * @method static bool hasSent(string $mailable)
 * @method static \Raven\Support\Collection queued(string $mailable, \Closure|string $callback = null)
 * @method static bool hasQueued(string $mailable)
 *
 * @see \Raven\Mail\Mailer
 * @see \Raven\Support\Testing\Fakes\MailFake
 */
class Mail extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return \Raven\Support\Testing\Fakes\MailFake
     */
    public static function fake()
    {
        static::swap($fake = new MailFake);

        return $fake;
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mailer';
    }
}
