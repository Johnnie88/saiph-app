<?php
namespace BooklyPro\Lib\Notifications\Test\ProxyProviders;

use Bookly\Lib\Entities;
use Bookly\Lib\Notifications\Test\Proxy;
use BooklyPro\Lib\Notifications\Test\Sender;

abstract class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function send( $to_email, Entities\Notification $notification, $codes, $attachments, $reply_to, $send_as, $from )
    {
        Sender::send( $to_email, $notification, $codes, $attachments, $reply_to, $send_as, $from );
    }
}