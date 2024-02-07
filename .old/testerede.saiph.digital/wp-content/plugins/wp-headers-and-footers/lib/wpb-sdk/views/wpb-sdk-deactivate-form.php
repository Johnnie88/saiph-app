<style>
.wpb-sdk_deactivation-frm-hidden {
	overflow: hidden;
}

.wpb-sdk_deactivation-frm-popup-overlay .wpb-sdk_deactivation-frm-internal-message {
	margin: 3px 0 3px 22px;
	display: none;
}

.wpb-sdk_deactivation-frm-reason-input {
	margin: 3px 0 3px 22px;
	display: none;
}

.wpb-sdk_deactivation-frm-pro-message {
	margin: 3px 0 3px 22px;
	display: none;
	color: #ed1515;
	font-size: 14px;
	font-weight: 600;
}

.wpb-sdk_deactivation-frm-reason-input input[type="text"] {
	width: 100%;
	display: block;
}

.wpb-sdk_deactivation-frm-popup-overlay {
	background: rgba(0, 0, 0, .8);
	position: fixed;
	top: 0;
	left: 0;
	height: 100%;
	width: 100%;
	z-index: 1000;
	overflow: auto;
	visibility: hidden;
	opacity: 0;
	transition: opacity 0.3s ease-in-out:
}

.wpb-sdk_deactivation-frm-popup-overlay.wpb-sdk_deactivation-frm-active {
	opacity: 1;
	visibility: visible;
}

.wpb-sdk_deactivation-frm-serveypanel {
	width: 600px;
	background: #fff;
	margin: 65px auto 0;
}

.wpb-sdk_deactivation-frm-popup-header {
	background: #f1f1f1;
	padding: 20px;
	border-bottom: 1px solid #ccc;
}

.wpb-sdk_deactivation-frm-popup-header h2 {
	margin: 0;
}

.wpb-sdk_deactivation-frm-popup-body {
	padding: 10px 20px;
}

.wpb-sdk_deactivation-frm-popup-footer {
	background: #f9f3f3;
	padding: 10px 20px;
	border-top: 1px solid #ccc;
}

.wpb-sdk_deactivation-frm-popup-footer:after {
	content: "";
	display: table;
	clear: both;
}

.action-btns {
	float: right;
}

.wpb-sdk_deactivation-frm-anonymous {
	display: none;
}

.attention,
.error-message {
	color: red;
	font-weight: 600;
	display: none;
}

.wpb-sdk_deactivation-frm-spinner {
	display: none;
}

.wpb-sdk_deactivation-frm-spinner img {
	margin-top: 3px;
}
</style>
<div class="<?php echo $product_slug ?>-deactivate-wrapper">
	<div class="wpb-sdk_deactivation-frm-popup-overlay">
		<div class="wpb-sdk_deactivation-frm-serveypanel">
			<form action="#" method="post" class="wpb-sdk_deactivation-frm-deactivate-form">

				<div class="wpb-sdk_deactivation-frm-popup-header">
					<h2><?php _e( 'Quick feedback about ' . $product_name, 'ucmm-wpbrigade' ); ?></h2>
				</div>

				<div class="wpb-sdk_deactivation-frm-popup-body">

					<h3><?php _e( 'If you have a moment, please let us know why you are deactivating:', 'ucmm-wpbrigade' ); ?></h3>

					<ul id="wpb-sdk_deactivation-frm-reason-list">
						<?php if ( $has_pro_version ) { ?>
						<li class="wpb-sdk_deactivation-frm-reason wpb-sdk_deactivation-frm-reason-pro" data-input-type="" data-input-placeholder="">
							<label>
								<span><input type="radio" name="wpb-sdk_deactivation-frm-selected-reason" value="pro">
								</span><span><?php _e( 'I upgraded to ' . $product_name . ' Pro', 'ucmm-wpbrigade' ); ?></span>
							</label>
							<div class="wpb-sdk_deactivation-frm-pro-message">
								<?php _e( 'No need to deactivate this ' . $product_name . ' Core version. Pro version works as an add-on with Core version.', 'ucmm-wpbrigade' ); ?>
							</div>
						</li>
						<?php } ?>
						<li class="wpb-sdk_deactivation-frm-reason" data-input-type="" data-input-placeholder="">
							<label>
								<span><input type="radio" name="wpb-sdk_deactivation-frm-selected-reason" value="1"></span>
								<span><?php _e( 'I only needed the plugin for a short period', 'ucmm-wpbrigade' ); ?></span>
							</label>
							<div class="wpb-sdk_deactivation-frm-internal-message"></div>
						</li>
						<li class="wpb-sdk_deactivation-frm-reason has-input" data-input-type="textfield">
							<label>
								<span><input type="radio" name="wpb-sdk_deactivation-frm-selected-reason" value="2"></span>
								<span><?php _e( 'I found a better plugin', 'ucmm-wpbrigade' ); ?></span>
							</label>
							<div class="wpb-sdk_deactivation-frm-internal-message"></div>
							<div class="wpb-sdk_deactivation-frm-reason-input">
								<span class="message error-message"><?php _e( 'Kindly tell us the name of plugin', 'ucmm-wpbrigade' ); ?></span>
								<input type="text" name="better_plugin" placeholder="<?php _e( "What's the plugin's name?", 'ucmm-wpbrigade' ); ?>">
							</div>
						</li>
						<li class="wpb-sdk_deactivation-frm-reason" data-input-type="" data-input-placeholder="">
							<label>
								<span><input type="radio" name="wpb-sdk_deactivation-frm-selected-reason" value="3"></span>
								<span><?php _e( 'The plugin broke my site', 'ucmm-wpbrigade' ); ?></span>
							</label>
							<div class="wpb-sdk_deactivation-frm-internal-message"></div>
						</li>
						<li class="wpb-sdk_deactivation-frm-reason" data-input-type="" data-input-placeholder="">
							<label>
								<span><input type="radio" name="wpb-sdk_deactivation-frm-selected-reason" value="4"></span>
								<span><?php _e( 'The plugin suddenly stopped working', 'ucmm-wpbrigade' ); ?></span>
							</label>
							<div class="wpb-sdk_deactivation-frm-internal-message"></div>
						</li>
						<li class="wpb-sdk_deactivation-frm-reason" data-input-type="" data-input-placeholder="">
							<label>
								<span><input type="radio" name="wpb-sdk_deactivation-frm-selected-reason" value="5"></span>
								<span><?php _e( 'I no longer need the plugin', 'ucmm-wpbrigade' ); ?></span>
							</label>
							<div class="wpb-sdk_deactivation-frm-internal-message"></div>
						</li>
						<li class="wpb-sdk_deactivation-frm-reason" data-input-type="" data-input-placeholder="">
							<label>
								<span><input type="radio" name="wpb-sdk_deactivation-frm-selected-reason" value="6"></span>
								<span><?php _e( "It's a temporary deactivation. I'm just debugging an issue.", 'ucmm-wpbrigade' ); ?></span>
							</label>
							<div class="wpb-sdk_deactivation-frm-internal-message"></div>
						</li>
						<li class="wpb-sdk_deactivation-frm-reason has-input" data-input-type="textfield">
							<label>
								<span><input type="radio" name="wpb-sdk_deactivation-frm-selected-reason" value="7"></span>
								<span><?php _e( 'Other', 'ucmm-wpbrigade' ); ?></span>
							</label>
							<div class="wpb-sdk_deactivation-frm-internal-message"></div>
							<div class="wpb-sdk_deactivation-frm-reason-input"><span class="message error-message "><?php _e( 'Kindly tell us the reason so we can improve.', 'ucmm-wpbrigade' ); ?></span>
								<input type="text" name="other_reason" placeholder="<?php _e( "Would you like to share what's other reason ?", 'ucmm-wpbrigade' ); ?>">
							</div>
						</li>
					</ul>
				</div>

				<div class="wpb-sdk_deactivation-frm-popup-footer">
					<label class="wpb-sdk_deactivation-frm-anonymous">
						<input type="checkbox" />
						<?php _e( 'Anonymous feedback', 'ucmm-wpbrigade' ); ?>
					</label>
					<input type="button" class="button button-secondary button-skip wpb-sdk_deactivation-frm-popup-skip-feedback" value="Skip &amp; Deactivate">
					<div class="action-btns">
						<span class="wpb-sdk_deactivation-frm-spinner"><img src="<?php echo admin_url( '/images/spinner.gif' ); ?>" alt=""></span>
						<input type="submit" class="button button-secondary button-deactivate wpb-sdk_deactivation-frm-popup-allow-deactivate" value="Submit &amp; Deactivate" disabled="disabled">
						<a href="#" class="button button-primary wpb-sdk_deactivation-frm-popup-button-close">
							<?php _e( 'Cancel', 'ucmm-wpbrigade' ); ?>
						</a>
					</div>
				</div>

			</form>
		</div>
	</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
	(function($) {
		$(function() {

			var pluginSlug = "<?php echo $product_slug; ?>";
			var pluginName = "<?php echo $product_name; ?>";

			var loggerDeactiveNonce;

			$(document).on('click', 'tr[data-slug="' + pluginSlug + '"] .deactivate', function(e) {
				e.preventDefault();
				loggerDeactiveNonce = $(this).find('a').attr('href').split("wpnonce=")[1];
				$('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-overlay').addClass('wpb-sdk_deactivation-frm-active');
				$('body').addClass('wpb-sdk_deactivation-frm-hidden');
			});

			$(document).on('click', '.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-button-close', function() {
				close_popup();
			});

			$(document).on('click', ".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-serveypanel,tr[data-slug='" + pluginSlug + "'] .deactivate", function(e) {
				e.stopPropagation();
			});

			$(document).on('click', function() {
				close_popup();
			});

			$('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-reason label').on('click', function() {
				if($(this).find('input[type="radio"]').is(':checked')) {
					//$('.wpb-sdk_deactivation-frm-anonymous').show();
					$(this).next().next('.wpb-sdk_deactivation-frm-reason-input').show().end().end().parent().siblings().find('.wpb-sdk_deactivation-frm-reason-input').hide();
				}
				$('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-pro-message').hide();
			});

			$('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-reason-pro label').on('click', function() {
				if($(this).find('input[type="radio"]').is(':checked')) {
					$(this).next('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-pro-message').show().end().end().parent().siblings().find('.wpb-sdk_deactivation-frm-reason-input').hide();
					$('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-allow-deactivate').attr('disabled', 'disabled');
					$('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-skip-feedback').attr('disabled', 'disabled');
				}
			});

			$('.<?php echo $product_slug; ?>-deactivate-wrapper input[type="radio"][name="wpb-sdk_deactivation-frm-selected-reason"]').on('click', function(event) {
				$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-allow-deactivate").removeAttr('disabled');
				$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-skip-feedback").removeAttr('disabled');
			});

			$(document).on('submit', '.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-deactivate-form', function(event) {
				event.preventDefault();
				var reason = $(this).find('input[type="radio"][name="wpb-sdk_deactivation-frm-selected-reason"]:checked').val();
				var reasonDetails = '';
				if(reason == '2') {
					reasonDetails = $(this).find("input[type='text'][name='better_plugin']").val();
				} else if(reason == '7') {
					reasonDetails = $(this).find("input[type='text'][name='other_reason']").val();
				}
				if((reason == '7' || reason == '2') && reasonDetails == '') {
					$('.message.error-message').show();
					return;
				}

				let returnURL = $("tr[data-slug='" + pluginSlug + "'] .deactivate a").attr('href');

				send_log(
					'wpb_sdk_' + pluginSlug + '_deactivation',
					reason,
					reasonDetails,
					loggerDeactiveNonce,
					returnURL
				);

			});

			$('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-skip-feedback').on('click', function(e) {
				send_log(
					'wpb_sdk_' + pluginSlug + '_deactivation',
					9,
					'',
					loggerDeactiveNonce,
					$("tr[data-slug='" + pluginSlug + "'] .deactivate a").attr('href')
				);
			});

			function close_popup() {
				$('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-overlay').removeClass('wpb-sdk_deactivation-frm-active');
				$('.<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-deactivate-form').trigger("reset");
				$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-allow-deactivate").attr('disabled', 'disabled');
				$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-reason-input").hide();
				$('body').removeClass('wpb-sdk_deactivation-frm-hidden');
				$('.message.error-message').hide();
			}

			function send_log( _action, _reason, _reasonDetails, _nonce, returnURL ) {
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: _action,
						reason: _reason,
						reason_detail: _reasonDetails,
						nonce: _nonce
					},
					beforeSend: function() {
						$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-spinner").show();
						$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-allow-deactivate").attr("disabled", "disabled");
						$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-skip-feedback").attr("disabled", "disabled");
					}
				}).done(function(res) {
					$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-spinner").hide();
					$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-allow-deactivate").removeAttr("disabled");
					$(".<?php echo $product_slug; ?>-deactivate-wrapper .wpb-sdk_deactivation-frm-popup-skip-feedback").removeAttr("disabled");
					window.location.href = returnURL;
				});
			}

		});
	})(jQuery);
});
</script>