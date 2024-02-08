<?php
namespace BooklyPro\Frontend\Modules\Icalendar;

use Bookly\Lib as BooklyLib;

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
     * Staff iCalendar feed
     */
    public static function staffIcalendar()
    {
        /** @var BooklyLib\Entities\Staff $staff */
        $staff = BooklyLib\Entities\Staff::query()->where( 'icalendar_token', self::parameter( 'token' ) )->findOne();

        $ics = new BooklyLib\Utils\Ics\Feed();

        if ( $staff && $staff->getICalendar() ) {
            $appointments = BooklyLib\Entities\Appointment::query()
                ->where( 'staff_id', $staff->getId() )
                ->whereGte( 'start_date', date_create()->modify( -$staff->getICalendarDaysBefore() . 'days' )->format( 'Y-m-d' ) )
                ->whereLte( 'end_date', date_create()->modify( $staff->getICalendarDaysAfter() . 'days' )->format( 'Y-m-d' ) )
                ->find();

            foreach ( $appointments as $appointment ) {
                if ( $appointment->getServiceId() === null ) {
                    $service_name = $appointment->getCustomServiceName();
                } else {
                    $service = BooklyLib\Entities\Service::find( $appointment->getServiceId() );
                    $service_name = $service->getTranslatedTitle();
                }

                $template = __( 'Service', 'bookly' ) . ": {service_name}\n";
                $template .= "{#each participants as participant}\n";
                $template .= "{participant.client_name}{#if participant.client_phone} ({participant.client_phone}){/if}{#if participant.client_email} {participant.client_email} \n{/if}";
                $template .= "{/each}\n";
                
                $description = BooklyLib\Utils\Codes::replace( $template, BooklyLib\Utils\Codes::getAppointmentCodes( $appointment ), false );
                $ics->addEvent( $appointment->getStartDate(), $appointment->getEndDate(), $service_name, $description, $appointment->getLocationId() );
            }
        }

        echo $ics->render();

        exit();
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        $excluded_actions = array(
            'staffIcalendar',
        );

        return in_array( $action, $excluded_actions ) || parent::csrfTokenValid( $action );
    }
}