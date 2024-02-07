<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Lib\Utils\DateTime;
use Bookly\Lib\Utils\Log;

?>
<div class="w-100 text-left">
    <div id="bookly-logs-table-wrap" class="mb-3">
        <div class="form-row">
            <div class="col-lg-auto col-md-12">
                <button type="button" class="btn btn-default w-100 mb-3 text-truncate text-left" id="bookly-logs-date-filter" data-date="<?php echo date( 'Y-m-d', strtotime( 'first day of' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of' ) ) ?>">
                    <i class="far fa-calendar-alt mr-1"></i>
                    <span>
                        <?php echo DateTime::formatDate( 'first day of this month' ) ?> - <?php echo DateTime::formatDate( 'last day of this month' ) ?>
                    </span>
                </button>
            </div>
            <div class="col-md-3 col-xl-2">
                <div class="form-group">
                    <ul id="bookly-filter-logs-action"
                        data-txt-select-all="<?php esc_attr_e( 'All actions', 'bookly' ) ?>"
                        data-txt-all-selected="<?php esc_attr_e( 'All actions', 'bookly' ) ?>"
                        data-txt-nothing-selected="<?php esc_attr_e( 'Nothing selected', 'bookly' ) ?>"
                        data-hide-icon
                    >
                        <?php $actions = array( Log::ACTION_CREATE, Log::ACTION_DELETE, Log::ACTION_UPDATE, Log::ACTION_ERROR ); ?>
                        <?php if ( $debug ) {
                            $actions[] = Log::ACTION_DEBUG;
                        } ?>
                        <?php foreach ( $actions as $action ): ?>
                            <li data-value="<?php echo esc_attr( $action ) ?>">
                                <?php echo ucfirst( $action ) ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-3 col-xl-2">
                <div class="form-group">
                    <input class="form-control" type="text" id="bookly-filter-logs-target-id" placeholder="<?php esc_attr_e( 'Target ID', 'bookly' ) ?>"/>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <input class="form-control" type="text" id="bookly-log-search" placeholder="<?php esc_attr_e( 'Quick search', 'bookly' ) ?>"/>
                </div>
            </div>
        </div>
        <table id="bookly-logs-table" class="table table-striped table-hover nowrap w-100 bookly-table-wrap text-left">
            <thead>
            <tr>
                <?php foreach ( $datatables['logs']['settings']['columns'] as $column => $show ) : ?>
                    <?php if ( $show ) : ?>
                        <th><?php echo esc_html( $datatables['logs']['titles'][ $column ] ) ?></th>
                    <?php endif ?>
                <?php endforeach ?>
            </tr>
            </thead>
        </table>
        <div class="text-right mt-3">
            <?php Buttons::renderDelete( 'bookly-delete-logs', 'mr-2', __( 'Clear logs', 'bookly' ) ) ?>
        </div>
    </div>
    <?php
    Selects::renderSingle( 'bookly_logs_expire', __( 'Keep logs', 'bookly' ), null, array( array( '7', sprintf( _n( '%s day', '%s days', 7, 'bookly' ), 7 ) ), array( '30', sprintf( _n( '%s day', '%s days', 30, 'bookly' ), 30 ) ), array( '90', sprintf( _n( '%s day', '%s days', 90, 'bookly' ), 90 ) ), array( '365', sprintf( _n( '%s day', '%s days', 365, 'bookly' ), 365 ) ) ) );
    ?>
</div>