<?php
namespace BooklyPro\Frontend\Modules\Calendar;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\DateTime;
use Bookly\Backend\Modules\Calendar\Ajax as CalendarAjax;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    /**
     * Get appointments for frontend calendar
     */
    public static function getCalendarAppointments()
    {
        $result = array();
        $one_day = new \DateInterval( 'P1D' );
        $start_date = new \DateTime( self::parameter( 'start' ) );
        $end_date = new \DateTime( self::parameter( 'end' ) );
        $location_id = self::parameter( 'location_id' );
        $staff_id = self::parameter( 'staff_id' );
        $service_id = self::parameter( 'service_id' );

        // Determine display time zone
        $display_tz = Common::getCurrentUserTimeZone();

        // Due to possibly different time zones of staff members expand start and end dates
        // to provide 100% coverage of the requested date range
        $start_date->sub( $one_day );
        $end_date->add( $one_day );

        // Load staff members
        $query = Staff::query()->whereNot( 'visibility', 'archive' );
        if ( $staff_id ) {
            $query->where( 'id', $staff_id );
        }
        /** @var Staff[] $staff_members */
        $staff_members = $query->find();

        if ( ! empty ( $staff_members ) ) {
            $location_ids = $location_id ? array( $location_id ) : null;
            $query = CalendarAjax::getAppointmentsQueryForCalendar( $staff_members, $start_date, $end_date, $location_ids );
            if ( $service_id ) {
                $query->where( 'a.service_id', $service_id );
            }
            $appointments = self::buildAppointmentsForCalendar( $query, $display_tz );
            $result = array_merge( $result, $appointments );

            $schedule = CalendarAjax::buildStaffSchedule( $staff_members, $start_date, $end_date, $location_ids );
            $result = array_merge( $result, $schedule );
        }

        wp_send_json( $result );
    }

    /**
     * Build appointments for Event Calendar.
     *
     * @param BooklyLib\Query $query
     * @param string $display_tz
     * @return array
     */
    private static function buildAppointmentsForCalendar( BooklyLib\Query $query, $display_tz )
    {
        $coloring_mode = get_option( 'bookly_cal_coloring_mode' );
        $query
            ->select( 'a.id, a.start_date, DATE_ADD(a.end_date, INTERVAL a.extras_duration SECOND) AS end_date, COALESCE(s.color,"silver") AS service_color, COALESCE(s.title,a.custom_service_name) AS service_name, st.color AS staff_color, ca.status' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
            ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' );

        $appointments = array();
        $wp_tz = Config::getWPTimeZone();
        $convert_tz = $display_tz !== $wp_tz;

        foreach ( $query->fetchArray() as $appointment ) {
            if ( ! isset ( $appointments[ $appointment['id'] ] ) ) {
                if ( $convert_tz ) {
                    $appointment['start_date'] = DateTime::convertTimeZone( $appointment['start_date'], $wp_tz, $display_tz );
                    $appointment['end_date']   = DateTime::convertTimeZone( $appointment['end_date'], $wp_tz, $display_tz );
                }
                $appointments[ $appointment['id'] ] = $appointment;
            }
            $appointments[ $appointment['id'] ]['customers'][] = array(
                'status' => $appointment['status'],
            );
        }
        $colors = array();
        if ( $coloring_mode == 'status' ) {
            $colors = BooklyLib\Proxy\Shared::prepareColorsStatuses( array(
                CustomerAppointment::STATUS_PENDING => get_option( 'bookly_appointment_status_pending_color' ),
                CustomerAppointment::STATUS_APPROVED => get_option( 'bookly_appointment_status_approved_color' ),
                CustomerAppointment::STATUS_CANCELLED => get_option( 'bookly_appointment_status_cancelled_color' ),
                CustomerAppointment::STATUS_REJECTED => get_option( 'bookly_appointment_status_rejected_color' ),
            ) );
            $colors['mixed'] = get_option( 'bookly_appointment_status_mixed_color' );
        }
        foreach ( $appointments as $key => $appointment ) {
            $event_status = null;
            foreach ( $appointment['customers'] as $customer ) {
                if ( $coloring_mode === 'status' ) {
                    if ( $event_status === null ) {
                        $event_status = $customer['status'];
                    } elseif ( $event_status != $customer['status'] ) {
                        $event_status = 'mixed';
                    }
                }
            }

            switch ( $coloring_mode ) {
                case 'status';
                    $color = $colors[ $event_status ];
                    break;
                case 'staff':
                    $color = $appointment['staff_color'];
                    break;
                case 'service':
                default:
                    $color = $appointment['service_color'];
            }

            $appointments[ $key ] = array(
                'id' => $appointment['id'],
                'start' => $appointment['start_date'],
                'end' => $appointment['end_date'],
                'title' => $appointment['service_name'],
                'color' => $color,
            );
        }

        return array_values( $appointments );
    }
}