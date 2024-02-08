<?php
namespace BooklyPro\Backend\Modules\Appearance;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * Get list of form appearances
     */
    public static function getFormsList()
    {
        $forms = Lib\Entities\Form::query()
            ->where( 'type', self::parameter( 'form_type' ) )
            ->fetchArray();

        foreach ( $forms as &$form ) {
            $form['settings'] = ProxyProviders\Local::prepareAppearanceSettings( $form['type'], $form );
        }
        unset ( $form );

        wp_send_json_success( compact( 'forms' ) );
    }

    public static function saveAppearance()
    {
        $id = self::parameter( 'id' );
        $type = self::parameter( 'type' );
        $settings = self::parameter( 'settings' );
        $name = self::parameter( 'name' );
        $custom_css = html_entity_decode( self::parameter( 'custom_css' ) );
        $token = self::parameter( 'token' );
        $form = new Lib\Entities\Form();
        if ( $id ) {
            $form->load( $id );
        }

        self::registerWpmlString( $settings['l10n'] );

        if ( $settings['categories_any'] ) {
            if ( $settings['categories_any'] === true ) {
                $settings['categories_list'] = null;
            }
            unset( $settings['categories_any'] );
        }
        if ( $settings['services_any'] ) {
            if ( $settings['services_any'] === true ) {
                $settings['services_list'] = null;
            }
            unset( $settings['services_any'] );
        }
        unset( $settings['custom_css'], $settings['token'] );
        $form
            ->setToken( $token )
            ->setType( $type )
            ->setName( $name )
            ->setCustomCss( $custom_css )
            ->setSettings( json_encode( $settings ) );

        if ( $form->save() ) {
            wp_send_json_success( array( 'id' => $form->getId(), 'token' => $form->getToken() ) );
        } else {
            wp_send_json_error();
        }
    }

    public static function deleteAppearance()
    {
        Lib\Entities\Form::query()->delete()->where( 'id', self::parameter( 'id' ) )->execute();

        wp_send_json_success();
    }

    public static function cloneAppearance()
    {
        $id = self::parameter( 'id' );
        $form = Lib\Entities\Form::find( $id );
        if ( $form ) {
            $new_name = $form->getName() . ' clone';
            $form
                ->setId( null )
                ->setName( $new_name )
                ->setToken( null )
                ->save();
        }

        wp_send_json_success();
    }

    public static function dismissAppearanceNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_modern_appearance_notice', 1 );

        wp_send_json_success();
    }

    /**
     * @param array $strings
     * @return void
     */
    protected static function registerWpmlString( $strings )
    {
        foreach ( $strings as $text ) {
            if ( is_array( $text ) ) {
                self::registerWpmlString( $text );
            } else {
                do_action( 'wpml_register_single_string', 'bookly', 'appearance_string_' . md5( $text ), $text );
            }
        }
    }
}