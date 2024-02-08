<?php
namespace BooklyPro\Frontend\Modules\ModernBookingForm;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib as ProLib;
use Bookly\Lib\Entities;
use Bookly\Lib\Base\Gateway;
use BooklyPro\Frontend\Modules\ModernBookingForm\Lib\Request;
use BooklyPro\Lib\Utils\Common;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    public static function modernBookingFormGetServices()
    {
        $list = array();
        $filters = self::parameter( 'filters' );
        $date = date_create( self::parameter( 'date' ) );
        if ( ! isset( $filters['locations'] ) ) {
            $filters['locations'] = array( 0 );
        }
        foreach ( $filters['services'] as $service_id ) {
            $service = BooklyLib\Entities\Service::find( $service_id );
            foreach ( $filters['staff'] as $staff_id ) {
                foreach ( $filters['locations'] as $location_id ) {
                    $chain_item = new BooklyLib\ChainItem();
                    $chain_item
                        ->setStaffIds( array( $staff_id ) )
                        ->setServiceId( $service_id )
                        ->setNumberOfPersons( $service->getCapacityMin() )
                        ->setQuantity( 1 )
                        ->setLocationId( $location_id )
                        ->setUnits( $service->getUnitsMin() )
                        ->setExtras( array() );

                    $chain = new BooklyLib\Chain();
                    $chain->add( $chain_item );

                    $scheduler = new BooklyLib\Scheduler( $chain, $date->format( 'Y-m-d 00:00' ), $date->format( 'Y-m-d' ), 'daily', array( 'every' => 1 ), array(), false );
                    $schedule = $scheduler->scheduleForFrontend( 1 );
                    if ( isset( $schedule[0]['options'] ) && count( $schedule[0]['options'] ) ) {
                        $list[] = compact( 'service_id', 'staff_id', 'location_id' );
                    }
                }
            }
        }
        wp_send_json_success( $list );
    }

    public static function modernBookingFormGetSlots()
    {
        $date = self::parameter( 'date' );

        $chain = new BooklyLib\Chain();
        foreach ( self::parameter( 'chain' ) as $item ) {
            $service_id = $item['service_id'];
            $staff_id = $item['staff_id'];
            $location_id = $item['location_id'];
            $nop = isset( $item['nop'] ) ? $item['nop'] : 1;
            $units = isset( $item['units'] ) ? $item['units'] : 1;
            $extras = isset( $item['extras'] ) ? $item['extras'] : array();
            foreach ( array_keys( $extras, 0, false ) as $key ) {
                unset( $extras[ $key ] );
            }

            $chain_item = new BooklyLib\ChainItem();
            $chain_item
                ->setStaffIds( array( $staff_id ) )
                ->setServiceId( $service_id )
                ->setNumberOfPersons( $nop )
                ->setQuantity( 1 )
                ->setLocationId( $location_id )
                ->setUnits( $units )
                ->setExtras( $extras );


            $chain->add( $chain_item );
        }

        $params = array(
            'every' => 1,
        );

        $customer = self::parameter( 'customer' );
        $use_time_zone = false;
        if ( isset( $customer['time_zone'], $customer['time_zone_offset'] ) && $customer['time_zone_offset'] === '' && $customer['time_zone'] !== '' ) {
            $time_zone = $customer['time_zone'];
            $time_zone_offset = null;
            if ( preg_match( '/^UTC[+-]/', $time_zone ) ) {
                $offset = preg_replace( '/UTC\+?/', '', $time_zone );
                $time_zone = null;
                $time_zone_offset = -$offset * 60;
            }
            $use_time_zone = true;
            $params['time_zone'] = $time_zone;
            $params['time_zone_offset'] = $time_zone_offset;
        } elseif ( BooklyLib\Config::useClientTimeZone() && ( isset( $customer['time_zone_offset'] ) || isset( $customer['time_zone'] ) ) ) {
            $time_zone_offset = isset( $customer['time_zone_offset'] ) && $customer['time_zone_offset'] !== '' ? $customer['time_zone_offset'] : null;
            $time_zone = isset( $customer['time_zone'] ) ? $customer['time_zone'] : null;
            $use_time_zone = true;
            $params['time_zone'] = $time_zone;
            $params['time_zone_offset'] = $time_zone_offset;
        }

        $exclude = array();
        if ( $cart = self::parameter( 'cart' ) ) {
            foreach ( $cart as $item ) {
                if ( isset( $item['slot'] ) && $item['slot'] !== '' ) {
                    $exclude[] = json_encode( $item['slot']['slot'] );
                }
            }
        }

        $scheduler = new BooklyLib\Scheduler( $chain, date_create( $date )->format( 'Y-m-d 00:00' ), date_create( $date )->format( 'Y-m-d' ), 'daily', $params, $exclude, BooklyLib\Config::waitingListActive() );
        $schedule = $scheduler->scheduleForFrontend( 1 );

        if ( isset( $schedule[0]['options'] ) ) {
            foreach ( $schedule[0]['options'] as &$option ) {
                $slots = json_decode( $option['value'], false );
                $option['slots'] = array();
                $slot_index = 0;
                foreach ( self::parameter( 'chain' ) as $item ) {
                    $service_id = $item['service_id'];
                    $service = Entities\Service::find( $service_id );
                    $slots_list = array();
                    $slot = $slots[ $slot_index ];
                    if ( $service->withSubServices() ) {
                        $price = $service->getPrice();
                        $services_count = $service->withSubServices() ? count( $service->getSubServices() ) : 1;
                        for ( $i = 0; $i < $services_count; $i++ ) {
                            $slots_list[] = $slots[ $slot_index + $i ];
                        }
                    } else {
                        $services_count = 1;
                        list( $service_id, $staff_id, $date, $location_id ) = $slot;
                        $time = substr( $slot[2], 11 );
                        $staff_service = new Entities\StaffService();
                        $location_id = BooklyLib\Proxy\Locations::prepareStaffLocationId( $location_id, $staff_id ) ?: null;
                        $staff_service->loadBy( compact( 'staff_id', 'service_id', 'location_id' ) );
                        $slots_list[] = $slot;
                        $price = $service->withSubServices() ? $service->getPrice() : BooklyLib\Proxy\SpecialHours::adjustPrice( $staff_service->getPrice(), $staff_id, $service_id, $location_id, $time, date( 'w', strtotime( $date ) ) + 1 );
                    }

                    $option['slots'][] = array(
                        'price' => $price,
                        'datetime' => $use_time_zone ? BooklyLib\Utils\DateTime::formatDateTime( BooklyLib\Utils\DateTime::applyTimeZone( $slot[2], $time_zone, $time_zone_offset ) ) : BooklyLib\Utils\DateTime::formatDateTime( $slot[2] ),
                        'slot' => $slots_list
                    );
                    $slot_index += $services_count;
                }
            }
        }

        wp_send_json_success( $schedule );
    }

    public static function modernBookingFormSave()
    {
        $request = new Lib\Request();
        if ( $request->isValid() ) {
            try {
                wp_send_json_success( $request->processPayment() );
            } catch ( \Exception $e ) {
            }
        }

        wp_send_json_error( $request->getError() );
    }

    /**
     * When payment window closed by client
     * @return void
     */
    public static function retrieveOrderStatus()
    {
        wp_send_json_success( self::retrieveOrderResult() );
    }

    /**
     * Endpoint for payment systems window.
     *
     * @return void
     */
    public static function checkoutResponse()
    {
        $request = \Bookly\Frontend\Modules\Payment\Request::getInstance();
        $gateway = $request->getGateway();
        if ( $gateway instanceof BooklyLib\Payment\NullGateway ) {
            // Get here after closing payment modal window
            // NullGateway means that until then the webhook
            // that caused $gateway->failed() worked and there is no trace of the payment system left
            // hence we send status = Gateway::STATUS_FAILED
            $status = Gateway::STATUS_FAILED;
            $data = array(
                'status' => $status,
                'data' => Lib\PaymentFlow::getBookingResultFromOrder( $status, $request->get( 'bookly_order' ) ),
            );
        } else {
            $data = self::retrieveOrderResult();
        }

        print '<script>window.opener.BooklyModernBookingForm.setBookingResult( \'' . $data['status'] . '\', ' . json_encode( $data['data'] ) . ' );</script>';
        exit;
    }

    /**
     * Get staff schedule for modern form calendar
     *
     * @return void
     */
    public static function modernBookingFormGetCalendarSchedule()
    {
        $holidays = array();
        $filters = self::parameter( 'filters' );

        $month = self::parameter( 'month' );
        $year = self::parameter( 'year' );

        // Minimum time prior booking
        $service_ids = isset( $filters['services'] ) ? $filters['services'] : array();
        $min_time_prior_booking = null;
        if ( $service_ids ) {
            foreach ( $service_ids as $service_id ) {
                $service_min_time = ProLib\Config::getMinimumTimePriorBooking( $service_id );
                $min_time_prior_booking = $min_time_prior_booking === null ? $service_min_time : min( $min_time_prior_booking, $service_min_time );
            }
        } else {
            $min_time_prior_booking = ProLib\Config::getMinimumTimePriorBooking();
        }

        $start_date = date_create( $year . '-' . $month . '-1' )->modify( '-7 days' );
        $end_date = date_create( $year . '-' . $month . '-1' )->modify( '1 month 7 days' );

        if ( $min_time_prior_booking ) {
            $end_date->modify( $min_time_prior_booking . ' seconds' );
        }

        $staff_ids = isset( $filters['staff'] ) ? $filters['staff'] : array();

        if ( $staff_ids ) {
            // Holidays.
            $holidays = Entities\Holiday::query( 'h' )
                ->select( 'DISTINCT(DATE_FORMAT(h.date, "%%m-%%d")) AS short_date' )
                ->whereIn( 'h.staff_id', $staff_ids )
                ->whereRaw( 'h.repeat_event = 1 OR (h.date >= %s AND h.date <= %s)', array( $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) ) )
                ->groupBy( 'short_date' )
                ->havingRaw( 'COUNT(id) >= %d', array( count( $staff_ids ) ) )
                ->fetchArray();

            $holidays = array_map( function ( $h ) {
                return $h['short_date'];
            }, $holidays );

            $staff_timezones = array();
            foreach ( Entities\Staff::query( 'st' )->select( 'id, time_zone' )->whereIn( 'id', $staff_ids )->fetchArray() as $staff ) {
                $staff_timezones[ $staff['id'] ] = $staff['time_zone'];
            }
            // Calculate weekly schedule
            $weekly_schedule = array();
            $res = Entities\StaffScheduleItem::query()
                ->select( 'r.day_index, r.start_time, r.end_time, r.staff_id' )
                ->whereIn( 'r.staff_id', $staff_ids )
                ->whereNot( 'r.start_time', null )
                ->groupBy( 'r.staff_id' )
                ->groupBy( 'day_index' )
                ->fetchArray();

            foreach ( $res as $row ) {
                $weekly_schedule[ $row['day_index'] ]['start_time'] = $row['start_time'];
                $weekly_schedule[ $row['day_index'] ]['end_time'] = $row['end_time'];
                $weekly_schedule[ $row['day_index'] ]['time_zone'] = isset( $staff_timezones[ $row['staff_id'] ] ) ? $staff_timezones[ $row['staff_id'] ] : null;
            }

            $special_days = array();
            foreach ( BooklyLib\Proxy\SpecialDays::getSchedule( $staff_ids, $start_date, $end_date ) ?: array() as $special_day ) {
                $special_days[ $special_day['date'] ] = array( 'start_time' => $special_day['start_time'], 'end_time' => $special_day['end_time'], 'time_zone' => isset( $staff_timezones[ $row['staff_id'] ] ) ? $staff_timezones[ $row['staff_id'] ] : null );
            }

            $current_date = clone( $start_date );
            $working_days = array();
            do {
                $month_day = $current_date->format( 'm-d' );
                $day = $current_date->format( 'Y-m-d' );
                if ( ! in_array( $month_day, $holidays, true ) ) {
                    $weekday = 1 + (int)$current_date->format( 'w' );
                    if ( isset( $weekly_schedule[ $weekday ] ) ) {
                        $working_days = array_merge( $working_days, self::_prepareDates( $current_date, $weekly_schedule[ $weekday ]['start_time'], $weekly_schedule[ $weekday ]['end_time'], $weekly_schedule[ $weekday ]['time_zone'] ) );
                    }
                }
                if ( isset( $special_days[ $day ] ) ) {
                    $working_days = array_merge( $working_days, self::_prepareDates( $current_date, $special_days[ $day ]['start_time'], $special_days[ $day ]['end_time'], $special_days[ $day ]['time_zone'] ) );
                }
                $current_date->modify( '1 day' );
            } while ( $current_date < $end_date );

            $current_date = clone( $start_date );
            do {
                $formatted_date = $current_date->format( 'Y-m-d' );
                if ( ! in_array( $formatted_date, $working_days, true ) ) {
                    $holidays[] = $formatted_date;
                }
                $current_date->modify( '1 day' );
            } while ( $current_date < $end_date );
        }

        if ( $min_time_prior_booking ) {
            $min_date = BooklyLib\Slots\DatePoint::now()->toTz( 'UTC' )->modify( $min_time_prior_booking )->modify( 'midnight' )->value();
            $current_date = clone( $start_date );
            do {
                if ( $current_date < $min_date ) {
                    $formatted_date = $current_date->format( 'Y-m-d' );
                    if ( ! in_array( $formatted_date, $holidays, true ) ) {
                        $holidays[] = $formatted_date;
                    }
                }
                $current_date->modify( '1 day' );
            } while ( $current_date < $end_date );
        }

        wp_send_json_success( $holidays );
    }

    public static function modernBookingFormVerifyGiftCard()
    {
        $request = new Request();

        $user_data = $request->getUserData();

        try {
            Common::validateGiftCard( self::parameter( 'gift_card' ), $user_data );
        } catch ( \LogicException $e ) {
            wp_send_json_error( array( 'error' => $e->getMessage() ) );
        }

        wp_send_json( array(
            'success' => true,
            'gift_card' => array(
                'discount' => $user_data->getGiftCard()->getBalance(),
                'service_id' => ProLib\Entities\GiftCardTypeService::query()->where( 'gift_card_type_id', $user_data->getGiftCard()->getGiftCardTypeId() )->fetchCol( 'service_id' ),
                'staff_id' => ProLib\Entities\GiftCardTypeStaff::query()->where( 'gift_card_type_id', $user_data->getGiftCard()->getGiftCardTypeId() )->fetchCol( 'staff_id' ),
            ),
        ) );
    }

    /**
     * @param \DateTime $date
     * @param string $start_time
     * @param string $end_time
     * @param string|null $timezone
     * @return array
     */
    protected static function _prepareDates( $date, $start_time, $end_time, $timezone )
    {
        $start = BooklyLib\Slots\TimePoint::fromStr( $start_time );
        $end = BooklyLib\Slots\TimePoint::fromStr( $end_time );
        $wp_tz_offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

        if ( $timezone ) {
            $staff_tz_offset = BooklyLib\Utils\DateTime::timeZoneOffset( $timezone );
            $start = $start->toTz( $staff_tz_offset, $wp_tz_offset );
            $end = $end->toTz( $staff_tz_offset, $wp_tz_offset );
        }

        // Convert to client time zone.
        $start = $start->toClientTz();
        $end = $end->toClientTz();

        $result = array();
        if ( $start->value() < 0 ) {
            $clone_date = clone( $date );
            $result[] = $clone_date->modify( '-1 day' )->format( 'Y-m-d' );
        }
        if ( $start->value() < HOUR_IN_SECONDS * 24 && $end->value() > 0 ) {
            $result[] = $date->format( 'Y-m-d' );
        }
        if ( $end->value() > HOUR_IN_SECONDS * 24 ) {
            $clone_date = clone( $date );
            $result[] = $clone_date->modify( '1 day' )->format( 'Y-m-d' );
        }

        return $result;
    }

    /**
     * @return array
     */
    protected static function retrieveOrderResult()
    {
        $request = \Bookly\Frontend\Modules\Payment\Request::getInstance();
        $gateway = $request->getGateway();
        if ( $request->get( 'bookly_event' ) === Gateway::EVENT_CANCEL ) {
            $status = Gateway::STATUS_FAILED;
            $gateway->fail();
        } else {
            $status = $gateway->retrieve();
        }
        return array(
            'status' => $status,
            'data' => Lib\PaymentFlow::getBookingResultFromOrder( $status, $request->get( 'bookly_order' ) ),
        );
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        return $action === 'checkoutResponse' || parent::csrfTokenValid( $action );
    }
}