<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
/** @var array $appearance */
/** @var array $form_id */
/** @var string $ajaxurl */
?>
<script type="text/javascript">
    (function (win, fn) {
        var done = false, top = true,
            doc = win.document,
            root = doc.documentElement,
            modern = doc.addEventListener,
            add = modern ? 'addEventListener' : 'attachEvent',
            rem = modern ? 'removeEventListener' : 'detachEvent',
            pre = modern ? '' : 'on',
            init = function (e) {
                if (e.type == 'readystatechange') if (doc.readyState != 'complete') return;
                (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                if (!done) {
                    done = true;
                    fn.call(win, e.type || e);
                }
            },
            poll = function () {
                try {
                    root.doScroll('left');
                } catch (e) {
                    setTimeout(poll, 50);
                    return;
                }
                init('poll');
            };
        if (doc.readyState == 'complete') fn.call(win, 'lazy');
        else {
            if (!modern) if (root.doScroll) {
                try {
                    top = !win.frameElement;
                } catch (e) {
                }
                if (top) poll();
            }
            doc[add](pre + 'DOMContentLoaded', init, false);
            doc[add](pre + 'readystatechange', init, false);
            win[add](pre + 'load', init, false);
        }
    })(window, function () {
        window.BooklyStaffForm.showForm(<?php echo json_encode( $form_type ) ?>, <?php echo json_encode( $form_id ) ?>, <?php echo json_encode( $appearance ) ?>);
    });
    var ajaxurl = "<?php echo esc_attr( $ajaxurl ) ?>";
</script>
<?php include '_css.php' ?>
<div id="<?php echo esc_attr( $form_id ) ?>" class="bookly-staff-form-container <?php echo esc_attr( $form_id ) ?>"></div>