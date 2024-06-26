<?php
namespace BooklyPro\Backend\Components\Dialogs\Appointment\AttachPayment\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Appointment\AttachPayment\Proxy;
use BooklyPro\Backend\Components\Dialogs\Appointment\AttachPayment\Dialog;

class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderAttachPaymentDialog()
    {
        Dialog::render();
    }
}