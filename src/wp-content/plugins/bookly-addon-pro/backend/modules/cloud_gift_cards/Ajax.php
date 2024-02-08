<?php
namespace BooklyPro\Backend\Modules\CloudGiftCards;

use BooklyPro\Lib;
use Bookly\Lib as BooklyLib;

class Ajax extends BooklyLib\Base\Ajax
{
     /**
     * Get gift card types list
     */
    public static function getGiftCardTypes()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $columns = BooklyLib\Utils\Tables::filterColumns( self::parameter( 'columns' ), BooklyLib\Utils\Tables::GIFT_CARD_TYPES );
        $order = self::parameter( 'order', array() );
        $filter = self::parameter( 'filter' );

        $query = Lib\Entities\GiftCardType::query( 'g' )
            ->leftJoin( 'GiftCardTypeService', 'gs', 'gs.gift_card_type_id = g.id' )
            ->leftJoin( 'GiftCardTypeStaff', 'gst', 'gst.gift_card_type_id = g.id' )
        ;

        // Filters.
        if ( $filter['title'] != '' ) {
            $query->whereLike( 'g.title', '%' . BooklyLib\Query::escape( $filter['title'] ) . '%' );
        }
        if ( $filter['service'] != '' ) {
            $query->where( 'gs.service_id', $filter['service'] );
        }
        if ( $filter['staff'] != '' ) {
            $query->where( 'gst.staff_id', $filter['staff'] );
        }
        if ( $filter['active'] ) {
            $today = BooklyLib\Slots\DatePoint::now()->format( 'Y-m-d' );
            $query->whereRaw(
                '(g.start_date IS NULL OR %s >= g.start_date) AND (g.end_date IS NULL OR %s <= g.end_date)',
                array( $today, $today )
            );
        }

        $ids = $query->fetchCol( 'DISTINCT g.id' );

        $query = Lib\Entities\GiftCardType::query( 'g' )
            ->select( 'SQL_CALC_FOUND_ROWS g.*,
                gs.service_id,
                gst.staff_id,
                COUNT(DISTINCT gs.service_id) AS services,
                COUNT(DISTINCT gst.staff_id) AS staff'
            )
            ->leftJoin( 'GiftCardTypeService', 'gs', 'gs.gift_card_type_id = g.id' )
            ->leftJoin( 'GiftCardTypeStaff', 'gst', 'gst.gift_card_type_id = g.id' )
            ->whereIn( 'g.id', $ids )
            ->groupBy( 'g.id' );

        foreach ( $order as $sort_by ) {
            $query
                ->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? BooklyLib\Query::ORDER_DESCENDING : BooklyLib\Query::ORDER_ASCENDING );
        }

        $filtered = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );

        $card_types = $query
            ->limit( self::parameter( 'length' ) )
            ->offset( self::parameter( 'start' ) )
            ->fetchArray();

        foreach ( $card_types as &$type ) {
            $type['start_date_formatted'] = is_null( $type['start_date'] ) ? '' : BooklyLib\Utils\DateTime::formatDate( $type['start_date'] );
            $type['end_date_formatted'] = is_null( $type['end_date'] ) ? '' : BooklyLib\Utils\DateTime::formatDate( $type['end_date'] );
        }

        unset( $filter['title'] );
        BooklyLib\Utils\Tables::updateSettings( BooklyLib\Utils\Tables::GIFT_CARD_TYPES, $columns, $order, $filter );

        wp_send_json( array(
            'draw' => (int) self::parameter( 'draw' ),
            'recordsFiltered' => $filtered,
            'data' => $card_types,
        ) );
    }

    /**
     * Get gift cards list
     */
    public static function getGiftCards()
    {
        $columns = BooklyLib\Utils\Tables::filterColumns( self::parameter( 'columns' ), BooklyLib\Utils\Tables::GIFT_CARDS );
        $order = self::parameter( 'order', array() );
        $filter = self::parameter( 'filter' );

        $query = Lib\Entities\GiftCard::query( 'g' )
            ->select( '
                g.id,
                g.code,
                g.balance,
                g.notes,
                gt.title AS type,
                c.full_name AS customer,
                g.payment_id,
                p.paid AS payment,
                p.total AS payment_total,
                p.type AS payment_type,
                p.status AS payment_status'
            )
            ->leftJoin( 'GiftCardType', 'gt', 'gt.id = g.gift_card_type_id' )
            ->leftJoin( 'Customer', 'c', 'c.id = g.customer_id', '\Bookly\Lib\Entities' )
            ->leftJoin( 'Payment', 'p', 'p.id = g.payment_id', '\Bookly\Lib\Entities' )
        ;

        // Filters.
        if ( $filter['code'] != '' ) {
            $query->whereLike( 'g.code', '%' . BooklyLib\Query::escape( $filter['code'] ) . '%' );
        }
        if ( $filter['type'] != '' ) {
            $query->whereLike( 'g.gift_card_type_id', $filter['type'] );
        }
        if ( $filter['customer'] != '' ) {
            $query->where( 'g.customer_id', $filter['customer'] );
        }

        if ( $filter['active'] ) {
            $today = BooklyLib\Slots\DatePoint::now()->format( 'Y-m-d' );
            $query->whereRaw(
                'g.balance > 0 AND (gt.start_date IS NULL OR %s >= gt.start_date) AND (gt.end_date IS NULL OR %s <= gt.end_date)',
                array( $today, $today )
            );
        }

        $filtered = $query->count();
        foreach ( $order as $sort_by ) {
            $query
                ->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? BooklyLib\Query::ORDER_DESCENDING : BooklyLib\Query::ORDER_ASCENDING );
        }

        $cards = $query
            ->limit( self::parameter( 'length' ) )
            ->offset( self::parameter( 'start' ) )
            ->fetchArray();

        foreach ( $cards as &$card ) {
            $payment_title = '';
            if ( $card['payment'] !== null ) {
                $payment_title = BooklyLib\Utils\Price::format( $card['payment'] );
                if ( $card['payment'] != $card['payment_total'] ) {
                    $payment_title = sprintf( __( '%s of %s', 'bookly' ), $payment_title, BooklyLib\Utils\Price::format( $card['payment_total'] ) );
                }

                $payment_title .= sprintf(
                    ' %s <span%s>%s</span>',
                    BooklyLib\Entities\Payment::typeToString( $card['payment_type'] ),
                    $card['payment_status'] == BooklyLib\Entities\Payment::STATUS_PENDING ? ' class="text-danger"' : '',
                    BooklyLib\Entities\Payment::statusToString( $card['payment_status'] )
                );
            }
            $card['payment'] = $payment_title;
            unset( $cards['payment_total'], $cards['payment_type'], $cards['payment_status'] );
        }

        unset( $filter['code'] );
        BooklyLib\Utils\Tables::updateSettings( BooklyLib\Utils\Tables::GIFT_CARDS, $columns, $order, $filter );

        wp_send_json( array(
            'draw' => (int) self::parameter( 'draw' ),
            'recordsFiltered' => $filtered,
            'data' => $cards,
        ) );
    }

    /**
     * Delete gift cards.
     */
    public static function deleteGiftCards()
    {
        $gift_ids = array_map( 'intval', self::parameter( 'data', array() ) );
        Lib\Entities\GiftCard::query()->delete()->whereIn( 'id', $gift_ids )->execute();

        wp_send_json_success();
    }

    /**
     * Delete gift card types.
     */
    public static function deleteGiftCardTypes()
    {
        $gift_ids = array_map( 'intval', self::parameter( 'data', array() ) );
        Lib\Entities\GiftCardType::query()->delete()->whereIn( 'id', $gift_ids )->execute();

        wp_send_json_success();
    }

    /**
     * Export gift cards
     */
    public static function exportGiftCards()
    {
        $delimiter = self::parameter( 'export_customers_delimiter', ',' );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=GiftCards.csv' );

        $datatables = BooklyLib\Utils\Tables::getSettings( BooklyLib\Utils\Tables::GIFT_CARDS );

        $header = array();
        $column = array();
        foreach ( self::parameter( 'exp', array() ) as $key => $value ) {
            $header[] = $datatables[ BooklyLib\Utils\Tables::GIFT_CARDS ]['titles'][ $key ];
            $column[] = $key;
        }

        $output = fopen( 'php://output', 'w' );
        fwrite( $output, pack( 'CCC', 0xef, 0xbb, 0xbf ) );
        fputcsv( $output, $header, $delimiter );

        $query = Lib\Entities\GiftCard::query( 'g' )
            ->select( 'g.id, g.code, gt.title AS type, gt.amount, g.balance, gt.start_date, gt.end_date, gt.min_appointments, gt.max_appointments, p.paid AS payment, p.total AS payment_total, p.type AS payment_type, p.status AS payment_status' )
            ->addSelect( 'GROUP_CONCAT(DISTINCT s.title) AS services,
                GROUP_CONCAT(DISTINCT st.full_name) AS staff,
                c.full_name AS customer' )
            ->leftJoin( 'GiftCardType', 'gt', 'gt.id = g.gift_card_type_id' )
            ->leftJoin( 'GiftCardTypeService', 'gs', 'gs.gift_card_type_id = gt.id' )
            ->leftJoin( 'Service', 's', 's.id = gs.service_id', '\Bookly\Lib\Entities' )
            ->leftJoin( 'GiftCardTypeStaff', 'gst', 'gst.gift_card_type_id = gt.id' )
            ->leftJoin( 'Staff', 'st', 'st.id = gst.staff_id', '\Bookly\Lib\Entities' )
            ->leftJoin( 'Customer', 'c', 'c.id = g.customer_id', '\Bookly\Lib\Entities' )
            ->leftJoin( 'Payment', 'p', 'p.id = g.payment_id', '\Bookly\Lib\Entities' )
            ->groupBy( 'g.id' )
        ;

        if ( self::parameter( 'active' ) ) {
            $today = BooklyLib\Slots\DatePoint::now()->format( 'Y-m-d' );
            $query->whereRaw(
                'g.balance > 0 AND (gt.start_date IS NULL OR %s >= gt.start_date) AND (gt.end_end IS NULL OR %s <= gt.end_end)',
                array( $today, $today )
            );
        }

        foreach ( $query->fetchArray() as $row ) {
            if ( $row['payment'] !== null ) {
                $payment_title = BooklyLib\Utils\Price::format( $row['payment'] );
                if ( $row['payment'] != $row['payment_total'] ) {
                    $payment_title = sprintf( __( '%s of %s', 'bookly' ), $payment_title, BooklyLib\Utils\Price::format( $row['payment_total'] ) );
                }
                $row['payment'] = implode( ' ', array(
                    $payment_title,
                    BooklyLib\Entities\Payment::typeToString( $row['payment_type'] ),
                    BooklyLib\Entities\Payment::statusToString( $row['payment_status'] )
                ) );
            }

            $row_data = array_fill( 0, count( $column ), '' );
            foreach ( $row as $key => $value ) {
                $pos = array_search( $key, $column );
                if ( $pos !== false ) {
                    if ( ( $key == 'customer' ) && $value === null ) {
                        $value = __( 'All', 'bookly' );
                    }
                    $row_data[ $pos ] = $value;
                }
            }

            fputcsv( $output, $row_data, $delimiter );
        }

        fclose( $output );

        exit;
    }

    public static function dismissGiftCardLocalPaymentNotice()
    {
        update_user_meta( get_current_user_id(), 'bookly_dismiss_gift_card_local_payment_notice', 1 );

        wp_send_json_success();
    }
}