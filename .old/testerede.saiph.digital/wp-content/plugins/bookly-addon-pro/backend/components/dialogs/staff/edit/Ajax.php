<?php
namespace BooklyPro\Backend\Components\Dialogs\Staff\Edit;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Dialogs\Staff\Edit\Proxy;

class Ajax extends BooklyLib\Base\Ajax
{
    /** @var BooklyLib\Entities\Staff */
    protected static $staff;

    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        $permissions = get_option( 'bookly_gen_allow_staff_edit_profile' )
            ? array( '_default' => 'staff' )
            : array();
        if ( BooklyLib\Config::staffCabinetActive() ) {
            $permissions = array( '_default' => 'staff' );
        }

        return $permissions;
    }

    /**
     * Update staff advanced settings.
     */
    public static function updateStaffAdvanced()
    {
        $parameters = self::parameters();
        self::$staff->setFields( $parameters );
        $data = array( 'alerts' => array( 'error' => array() ) );

        $data = Proxy\Shared::preUpdateStaffAdvanced( $data, self::$staff, $parameters );
        self::$staff->save();
        $data = Proxy\Shared::updateStaffAdvanced( $data, self::$staff, $parameters );

        wp_send_json_success( $data );
    }

    /**
     * Get staff advanced.
     */
    public static function getStaffAdvanced()
    {
        $data = Proxy\Shared::editStaffAdvanced(
            array( 'alert' => array( 'error' => array() ), 'tpl' => array() ),
            self::$staff
        );
        $html = self::renderTemplate( 'staff_advanced', self::$staff, $data['tpl'] );
        wp_send_json_success( compact( 'html' ) );
    }

    /**
     * Check if Google calendar already used by other staff
     */
    public static function checkGoogleCalendars()
    {
        foreach (
            BooklyLib\Entities\Staff::query( 's' )
                ->whereNot( 'id', self::$staff->getId() )
                ->whereNot( 'google_data', '' )
                ->fetchCol( 'google_data' ) as $json_data
        ) {
            try {
                $google_data = json_decode( $json_data, true );
                if ( $google_data['calendar']['id'] === self::parameter( 'calendar_id' ) ) {
                    wp_send_json_error();
                }
            } catch ( \Exception $e ) {
            }
        }

        wp_send_json_success();
    }

    /**
     * Extend parent method to control access on staff member level.
     *
     * @param string $action
     * @return bool
     */
    protected static function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            if ( ! BooklyLib\Utils\Common::isCurrentUserAdmin() ) {
                self::$staff = BooklyLib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->findOne();
                if ( ! self::$staff ) {
                    return false;
                } else switch ( $action ) {
                    case 'getStaffAdvanced':
                    case 'updateStaffAdvanced':
                    case 'checkGoogleCalendars':
                        return self::$staff->getId() == self::parameter( 'staff_id' );
                    default:
                        return false;
                }
            } elseif ( $action === 'updateStaffAdvanced' || $action === 'checkGoogleCalendars' ) {
                self::$staff = new BooklyLib\Entities\Staff();
                self::$staff->load( self::parameter( 'staff_id' ) );
            }

            return true;
        }

        return false;
    }
}