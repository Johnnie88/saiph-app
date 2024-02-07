<?php if( ! $return ): ?><div id="<?php echo $slug; ?>" class="smylesv2-card card ui segment"><?php endif; ?>
	<div id="<?php echo "{$slug}_dimmer"; ?>" class="ui dimmer">
		<div class="ui text loader"><?php _e( 'Deactivating ...', 'wp-job-manager-field-editor' ); ?></div>
	</div>
	<div class="content smylesv2-card-top">
		<?php if( ! $plugin_installed ) echo "<a class=\"ui mini red top fluid attached label\">" . __( 'Plugin Not Found', 'wp-job-manager-field-editor' ) . "</a>"; ?>
		<a href="https://plugins.smyl.es/<?php echo $slug; ?>/" target="_blank" class="header" style="text-decoration: none;"><?php echo $plugin['title']; ?>
			<i class="mini external icon"></i>
		</a>

<?php if($plugin_image): ?>
	</div>

	<div class="blurring dimmable image">
				<div class="ui dimmer">
					<div class="content">
						<div class="center">
							<a href="https://plugins.smyl.es/<?php echo $slug; ?>" target="_blank" class="ui icon inverted button smylesv2-button" style="height: auto;">
								<i class="icon world"></i>
								<?php _e( 'Details', 'wp-job-manager-field-editor' ); ?>
							</a>
						</div>
					</div>
				</div>
				<img src="<?php echo $plugin_image; ?>">
			</div>

	<div class="content">
<?php endif; ?>

		<div class="meta">
			<?php if( ! empty($version) ): ?>
				<a class="ui mini basic icon label">
					<i class="icon history"></i>
					<?php _e( 'Installed Version:', 'wp-job-manager-field-editor' ); ?>
					<div class="detail"><?php echo $version; ?></div>
				</a>
			<?php endif; ?>
			<?php if( ! empty($hr_activation_date) ): ?>
				<div class="ui divider"></div>
				<a class="ui olive fluid icon label">
					<i class="icon calendar"></i>
					<?php _e( 'Activated:', 'wp-job-manager-field-editor' ); ?>
					<div class="detail"><?php echo $hr_activation_date; ?></div>
				</a>
			<?php endif; ?>
		</div>
		<div class="description">
			<?php // _e( 'To receive updates, and support, enter your license details below and click <strong>Activate License.</strong>' ); ?>
			<div id="<?php echo "{$slug}_license_key_wrap"; ?>" class="ui fluid large left icon input disabled" style="margin-top: 10px;">
				<input id="<?php echo "{$slug}_license_key"; ?>" type="text" name="<?php echo "{$slug}_license_key"; ?>" placeholder="XXXX-XXXX-XXXX-XXXX" value="<?php echo substr_replace( $api_key, "****-*****", 0, 9 ); ?>">
				<i class="privacy icon"></i>
			</div>
			<div id="<?php echo "{$slug}_email_wrap"; ?>" class="ui fluid large left icon input disabled" style="margin-top: 10px;">
				<input id="<?php echo "{$slug}_email"; ?>" type="text" name="<?php echo "{$slug}_email"; ?>" placeholder="API License Email Address" value="<?php echo $activation_email; ?>">
				<i class="mail icon"></i>
			</div>
		</div>
	</div>
	<button id="<?php echo $slug; ?>" class="deactivate-license ui bottom attached button orange" style="height: auto;" type="submit">
		<i class="large icons">
			<i class="unlink icon"></i>
		</i>
		<?php _e( 'Deactivate License', 'wp-job-manager-field-editor' ); ?>
	</button>
<?php if( ! $return ): ?></div><?php endif; ?>