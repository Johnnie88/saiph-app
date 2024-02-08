<?php
namespace BooklyPro\Backend\Components\Gutenberg\Shortcodes;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Entities\Form;

class Block extends BooklyLib\Base\Block
{
    /**
     * @inheritDoc
     */
    public static function registerBlockType()
    {
        self::enqueueScripts( array(
            'module' => array(
                'js/shortcodes-block.js' => array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-editor' ),
            ),
        ) );

        $exists_appearance = array();
        foreach ( Form::getTypes() as $type ) {
            $exists_appearance[ $type ] = false;
        }
        unset( $exists_appearance[ Form::TYPE_BOOKLY_FORM ] );

        /** @var array $forms */
        $forms = Form::query()
            ->select( 'type, name, token, settings' )
            ->sortBy( 'type, name' )
            ->whereNot( 'type', Form::TYPE_BOOKLY_FORM )
            ->fetchArray();
        foreach ( $forms as &$form ) {
            $exists_appearance[ $form['type'] ] = true;
            $settings = json_decode( $form['settings'], true );
            $form['color'] = $settings['main_color'];
            unset( $form['settings'] );
        }

        $name = __( 'Default', 'bookly' );
        $token = '';
        $color = get_option( 'bookly_app_color', '#f4662f' );
        $block = array();
        foreach ( $exists_appearance as $type => $exists ) {
            if ( ! $exists ) {
                $forms[] = compact( 'type', 'name', 'token', 'color' );
            }
            $block[ $type ] = array(
                'title' => 'Bookly - ' . Form::getTitle( $type ),
                'description' => Form::getDescription( $type ),
            );
        }

        wp_localize_script( 'bookly-shortcodes-block.js', 'BooklyShortcodesL10n',
            compact( 'block', 'forms' )
        );
    }
}