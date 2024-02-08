<?php
namespace BooklyPro\Backend\Modules\Notifications;

use BooklyPro\Lib;
use Bookly\Lib as BooklyLib;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'supervisor' );
    }

    /**
     * Get email logs.
     */
    public static function getEmailLogs()
    {
        /** @global \wpdb $wpdb*/
        global $wpdb;

        $order = self::parameter( 'order', array() );
        $columns = self::parameter( 'columns' );
        $filter = self::parameter( 'filter' );

        $query = Lib\Entities\EmailLog::query( 'e' );

        // Filters.
        list ( $start, $end ) = explode( ' - ', $filter['range'], 2 );
        $end = date( 'Y-m-d', strtotime( '+1 day', strtotime( $end ) ) );

        $query->whereBetween( 'e.created_at', $start, $end );
        if ( isset( $filter['search'] ) && $filter['search'] !== '' ) {
            $query->whereRaw( 'e.to LIKE "%%%s%" OR e.subject LIKE "%%%s%" OR e.body LIKE "%%%s%"', array_fill( 0, 3, $wpdb->esc_like( $filter['search'] ) ) );
        }

        $total = $query->count();

        $query->limit( self::parameter( 'length' ) )->offset( self::parameter( 'start' ) );

        foreach ( $order as $sort_by ) {
            $query->sortBy( '`' . str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) . '`' )
                ->order( $sort_by['dir'] == 'desc' ? BooklyLib\Query::ORDER_DESCENDING : BooklyLib\Query::ORDER_ASCENDING );
        }

        $data = $query->select( 'e.id, e.to, e.subject, e.body, e.headers, e.attach, e.created_at' )->fetchArray();

        foreach ( $data as &$record ) {
            $record['headers'] = json_decode( $record['headers'] );
            $record['attach'] = json_decode( $record['attach'] );
            $record['created_at'] = BooklyLib\Utils\DateTime::formatDateTime( $record['created_at'] );
        }

        unset( $filter['range'], $filter['search'] );
        BooklyLib\Utils\Tables::updateSettings( BooklyLib\Utils\Tables::EMAIL_LOGS, $columns, $order, $filter );

        wp_send_json( array(
            'draw' => ( int ) self::parameter( 'draw' ),
            'recordsTotal' => count( $data ),
            'recordsFiltered' => $total,
            'data' => $data,
        ) );
    }

    /**
     * Get email logs.
     */
    public static function deleteEmailLogs()
    {
        Lib\Entities\EmailLog::query()->delete()->whereIn( 'id', array_map( 'intval', self::parameter( 'data', array() ) ) )->execute();

        wp_send_json_success();
    }

    /**
     * Set email logs expire
     */
    public static function setEmailLogsExpire()
    {
        update_option( 'bookly_email_logs_expire', self::parameter( 'expire', 30 ) );

        wp_send_json_success();
    }

}