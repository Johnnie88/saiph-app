<?php
namespace BooklyPro\Backend\Components\TinyMce\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\TinyMce\Proxy;
use BooklyPro\Lib\Entities\Form;

class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function renderMediaButtons( $version )
    {
        if ( $version < 3.5 ) {
            // show button for v 3.4 and below
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-appointment-popup&amp;height=300" id="add-ap-appointment" title="' . esc_attr__( 'Add Bookly appointments list', 'bookly' ) . '">' . __( 'Add Bookly appointments list', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-calendar&height=300" id="add-bookly-calendar" title="' . esc_attr__( 'Add Bookly calendar', 'bookly' ) . '">' . __( 'Add Bookly calendar', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-' . Form::TYPE_SEARCH_FORM . '-popup&height=300" id="add-' . Form::TYPE_SEARCH_FORM . '-form" title="' . esc_attr__( 'Add Bookly search form', 'bookly' ) . '">' . __( 'Add Bookly search form', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-' . Form::TYPE_SERVICES_FORM . '-popup&height=300" id="add-' . Form::TYPE_SERVICES_FORM . '-form" title="' . esc_attr__( 'Add Bookly services form', 'bookly' ) . '">' . __( 'Add Bookly services form', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-' . Form::TYPE_STAFF_FORM . '-popup&height=300" id="add-' . Form::TYPE_STAFF_FORM . '-form" title="' . esc_attr__( 'Add Bookly staff form', 'bookly' ) . '">' . __( 'Add Bookly staff form', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-' . Form::TYPE_CANCELLATION_FORM . '-popup&height=300" id="add-' . Form::TYPE_CANCELLATION_FORM . '-form" title="' . esc_attr__( 'Add appointment cancellation confirmation', 'bookly' ) . '">' . __( 'Add appointment cancellation confirmation', 'bookly' ) . '</a>';
        } else {
            // display button matching new UI
            $img = '<span class="bookly-media-icon"></span> ';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-appointment-popup&amp;height=300" id="add-ap-appointment" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add Bookly appointments list', 'bookly' ) . '">' . $img . __( 'Add Bookly appointments list', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-calendar&height=300" id="add-bookly-calendar" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add Bookly calendar', 'bookly' ) . '">' . $img . __( 'Add Bookly calendar', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-' . Form::TYPE_SEARCH_FORM . '-popup&height=300" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add Bookly search form', 'bookly' ) . '">' . $img . __( 'Add Bookly search form', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-' . Form::TYPE_SERVICES_FORM . '-popup&height=300" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add Bookly services form', 'bookly' ) . '">' . $img . __( 'Add Bookly services form', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-' . Form::TYPE_STAFF_FORM . '-popup&height=300" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add Bookly staff form', 'bookly' ) . '">' . $img . __( 'Add Bookly staff form', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-' . Form::TYPE_CANCELLATION_FORM . '-popup&height=300" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add appointment cancellation confirmation', 'bookly' ) . '">' . $img . __( 'Add appointment cancellation confirmation', 'bookly' ) . '</a>';
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderPopup()
    {
        $casest = BooklyLib\Config::getCaSeSt();
        $custom_fields = BooklyLib\Proxy\CustomFields::getWhichHaveData() ?: array();
        self::renderTemplate( 'appointment_list', compact( 'custom_fields' ) );
        self::renderTemplate( 'calendar', compact( 'casest' ) );
        foreach ( Form::getTypes() as $type ) {
            if ( $type === Form::TYPE_BOOKLY_FORM ) {
                continue;
            }
            $forms = Form::query()->select( 'name, token' )->where( 'type', $type )->fetchArray();
            if ( ! $forms ) {
                $forms = array(
                    array(
                        'name' => __( 'Default', 'bookly' ),
                        'token' => '',
                    ),
                );
            }
            self::renderTemplate( 'modern_form', compact( 'type', 'forms' ) );
        }
    }
}