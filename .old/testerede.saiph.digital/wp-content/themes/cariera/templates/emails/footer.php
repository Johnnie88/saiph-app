<?php
/**
 * Email Template - Footer
 *
 * This template can be overridden by copying it to cariera-child/templates/emails/footer.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.4.4
 * @version     1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_title = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
$main_color = cariera_get_option( 'cariera_main_color' );
?>

							</table>
						</td>
					</tr>
					<tr>
						<td class="footer" bgcolor="#f6f6f6">
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
								<tr>
									<td align="center" class="footercopy">
										<?php echo date( 'Y' ); ?> &#169; <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color: <?php echo esc_attr( $main_color ); ?>"><?php echo esc_html( $site_title ); ?></a> <?php esc_html_e( 'All Rights Reserved.', 'cariera' ); ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>

			</td>
		</tr>
	</table>
</body>
</html>
