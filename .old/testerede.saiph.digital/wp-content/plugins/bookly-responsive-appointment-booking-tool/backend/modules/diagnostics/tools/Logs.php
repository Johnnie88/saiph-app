<?php
namespace Bookly\Backend\Modules\Diagnostics\Tools;

use Bookly\Lib;

class Logs extends Tool
{
    protected $slug = 'logs';
    protected $hidden = false;
    protected $title = 'Logs';

    public function render()
    {
        $datatables = Lib\Utils\Tables::getSettings( Lib\Utils\Tables::LOGS );
        $debug = self::hasParameter( 'debug' );

        return self::renderTemplate( '_logs', compact( 'datatables', 'debug' ), false );
    }

    public function hasError()
    {
        $errors = Lib\Entities\Log::query( 'l' )
            ->where( 'action', Lib\Utils\Log::ACTION_ERROR )
            ->whereGt( 'created_at', date_create( current_time( 'mysql' ) )->modify( '-1 day' )->format( 'Y-m-d H:i:s' ) )
            ->whereLike( 'target', '%bookly-%' )
            ->count();

        return $errors > 0;
    }
}