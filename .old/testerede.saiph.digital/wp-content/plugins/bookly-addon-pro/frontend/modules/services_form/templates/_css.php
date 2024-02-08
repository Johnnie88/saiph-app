<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
use Bookly\Lib\Utils\Common;

$color = isset( $appearance['main_color'] ) ? $appearance['main_color'] : get_option( 'bookly_app_color', '#f4662f' );
/** @var string $form_id */
?>
<style>
    .<?php echo esc_attr( $form_id ) ?> .bookly-card-title {
        right: .5em;
        bottom: .5em;
        width: max-content;
        max-width: calc(100% - 1em);
        max-height: calc(100% - 1em);
    }

    .<?php echo esc_attr( $form_id ) ?> .bookly-card-title > div {
        overflow: hidden;
    }

    .<?php echo esc_attr( $form_id ) ?> .text-bookly:not(:hover) {
        color: <?php echo esc_attr( $color ) ?>;
    }

    .<?php echo esc_attr( $form_id ) ?> .fill-bookly {
        fill: <?php echo esc_attr( $color ) ?>;
    }

    .<?php echo esc_attr( $form_id ) ?> .hover\:text-bookly:hover {
        color: <?php echo esc_attr( $color ) ?>;
    }

    .<?php echo esc_attr( $form_id ) ?> .hover\:bg-bookly:hover {
        background-color: <?php echo esc_attr( $color ) ?> !important;
    }

    .<?php echo esc_attr( $form_id ) ?> .card:hover {
        background-color: #FDFDFD !important;
    }

    .<?php echo esc_attr( $form_id ) ?> .border-bookly {
        border-color: <?php echo esc_attr( $color ) ?>;
    }

    .<?php echo esc_attr( $form_id ) ?> .btn-check:focus + .btn-outline-bookly, .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly:focus {
        background-color: <?php echo esc_attr( $color ) ?>;
        border-color: <?php echo esc_attr( $color ) ?>;
        color: #000000;
        box-shadow: 0 0 0 0.25rem rgba(128, 128, 128, 0.5);
    }

    .<?php echo esc_attr( $form_id ) ?> .btn-check:checked + .btn-outline-bookly, .<?php echo esc_attr( $form_id ) ?> .btn-check:active + .btn-outline-bookly, .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly:active, .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly.active, .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly.dropdown-toggle.show {
        background-color: <?php echo esc_attr( $color ) ?>;
        border-color: <?php echo esc_attr( $color ) ?>;
    }

    .<?php echo esc_attr( $form_id ) ?> .btn-check:checked + .btn-outline-bookly:focus, .<?php echo esc_attr( $form_id ) ?> .btn-check:active + .btn-outline-bookly:focus, .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly:active:focus, .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly.active:focus, .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly.dropdown-toggle.show:focus {
        background-color: <?php echo esc_attr( $color ) ?>;
        border-color: <?php echo esc_attr( $color ) ?>;
        box-shadow: 0 0 0 0.25rem rgba(128, 128, 128, 0.5);
    }

    .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly:disabled, .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly.disabled {
        color: <?php echo esc_attr( $color ) ?> !important;
        background-color: transparent;
    }

    .<?php echo esc_attr( $form_id ) ?> .bg-bookly {
        background-color: <?php echo esc_attr( $color ) ?> !important;
    }

    .<?php echo esc_attr( $form_id ) ?> .grid a.selected {
        background-color: <?php echo esc_attr( $color ) ?> !important;
    }

    @media (hover) {
        .<?php echo esc_attr( $form_id ) ?> .btn-outline-bookly:hover {
            background-color: <?php echo esc_attr( $color ) ?>;
            border-color: <?php echo esc_attr( $color ) ?>;
        }
    }

    /* intlTelInput.js */
    .iti {
        display: block;
    }

    .iti__flag {
        background-image: url("<?php echo plugins_url( 'frontend/resources/images/flags.png', BooklyLib\Plugin::getMainFile() ) ?>");
    }

    @media only screen and (min-resolution: 2dppx) {
        .iti__flag {
            background-image: url("<?php echo plugins_url( 'frontend/resources/images/flags@2x.png', BooklyLib\Plugin::getMainFile() ) ?>")
        }
    }
</style>
<?php if ( isset( $appearance['custom_css'] ) && $appearance['custom_css'] != '' ) : ?>
    <style>
        <?php echo Common::css( $appearance['custom_css'] ) ?>
    </style>
<?php endif ?>
