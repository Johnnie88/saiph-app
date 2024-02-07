<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly ?>
<div id="bookly-<?php echo $type ?>-popup" style="display: none">
    <form>
        <table>
            <tr>
                <th class="bookly-title-col"><?php esc_html_e( 'Appearance form name', 'bookly' ) ?></th>
                <td>
                    <select id="bookly-select-shortcode">
                        <?php foreach ( $forms as $form ) : ?>
                            <option value="<?php echo esc_attr( $form['token'] ) ?>"><?php echo esc_html( $form['name'] ) ?></option>
                        <?php endforeach ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button class="button button-primary bookly-js-insert-shortcode" type="button"><?php esc_html_e( 'Insert', 'bookly' ) ?></button>
                </td>
            </tr>
        </table>
    </form>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        let $form = $('#bookly-<?php echo $type ?>-popup form'),
            $select_shortcode = $('#bookly-select-shortcode', $form),
            $insert = $('button.bookly-js-insert-shortcode', $form)
        ;

        $insert.on('click', function (e) {
            e.preventDefault();

            let code = $select_shortcode.val();
            if (code) {
                code = ' ' + code;
            }
            window.send_to_editor('[bookly-<?php echo $type ?>' + code + ']');

            window.parent.tb_remove();
            return false;
        });
    });
</script>