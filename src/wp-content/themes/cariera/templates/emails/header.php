<?php
/**
 * Email Template - Header
 *
 * This template can be overridden by copying it to cariera-child/templates/emails/header.php.
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


<!-- <!DOCTYPE html> -->
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width">

	<style>
		html { background: #f1f2f6; }
		body { 
			font-family: sans-serif; 
			-webkit-font-smoothing: antialiased; 
			font-size: 16px;
			line-height: 1.4; 
			-ms-text-size-adjust: 100%; 
			-webkit-text-size-adjust: 100%; 
			margin: 0; 
			padding: 0; 
			min-width: 100% !important; 
		}
		img { border: none; -ms-interpolation-mode: bicubic; max-width: 100%; }
		.content { width: 100%; max-width: 600px; border: 1px solid #e3e3e3; border-radius: 3px; overflow: hidden; }
		.main { padding: 80px 0; color: #666; font-family: sans-serif; }
		.main a { text-decoration: none; }
		.header { padding: 20px; }
		.innerpadding { padding: 30px; }
		.borderbottom { border-bottom: 1px solid #e3e3e3; }
		.title { text-align: center; text-transform: uppercase; }
		.title a { font-size: 32px; line-height: 40px; color: #fff; }
		.subhead { text-align: center; font-size: 12px; color: #fff; }
		.h1 { text-align: center; font-size: 30px; color: #fff; }
		.h2 { padding: 0 0 15px 0; font-size: 16px; line-height: 28px; font-weight: bold; }
		.h3 { font-size: 15px; text-decoration: underline; }
		.bodycopy { font-size: 14px; line-height: 22px; }
		.mssg { font-size: 12px; text-align: center; }
		.footer { padding: 20px 30px 15px 30px; border-top: 1px solid #f5f5f5; }
		.footer a { color: <?php echo esc_attr( $main_color ); ?> }
		.footercopy { font-size: 15px; color: #777777; }
		.footercopy a {}
		.social a { font-size: 14px; }
		@media screen and (max-width: 600px) { .main { padding: 0; } }
	</style>
</head>

<!-- Body -->
<body yahoo bgcolor="#f5eddb">
	<table width="100%" bgcolor="#f0f2f6" class="main" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<table bgcolor="#ffffff" class="content" align="center" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td bgcolor="<?php echo esc_attr( $main_color ); ?>" class="header">
							<table class="col425" align="left" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
								<tr>
									<td height="70">
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td class="title" style="padding: 5px 0 0 0;">
													<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $site_title ); ?></a>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>

					<tr>
						<td class="innerpadding borderbottom">
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
