<?php
/**
 * New Default template stylesheet.
 * @version 3.0.1
 * @return string style.
 */
function free_minimalist_presets() {

  	ob_start();
  	if ( version_compare( $GLOBALS['wp_version'], '5.3', '>=' ) ) : ?>
    	<style media="screen"  id="loginpress-style-wp-5-3">
			html body.login div.wishlistmember-loginform{
				padding: 0;
				margin: auto;
				transform: none;
				top: 0;
				width: 100%;
			}
			.login form .input, .login input[type=text],.login form input[type=checkbox]{
				border-radius: 0;
			}
			input[type=checkbox]:checked:before {
				content: url('data:image/svg+xml;utf8,<svg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27><path%20d%3D%27M14.83%204.89l1.34.94-5.81%208.38H9.02L5.78%209.67l1.34-1.25%202.57%202.4z%27%20fill%3D%27%23F6366A%27%2F><%2Fsvg>');
				margin: -0.25rem 0 0 -0.275rem !important;
			}
			body .language-switcher{
				margin-top: 10px;
				clear: both;
				z-index: 1;
				position: absolute;
				top: 100%;
				width: 100%;
			}
			body #login{
				position: relative;
	
			}
			.wp-core-ui #login .button-primary{
				margin-left: 0;
			}
			.admin-email__actions-primary .button:first-child {
				font: 400 15px Segoe UI,Frutiger,Frutiger Linotype,Dejavu Sans,Helvetica Neue,Arial,sans-serif;
				color: #fff;
				height: auto;
				line-height: 20px !important;
				padding: 13px;
				padding-top: 13px;
				padding-bottom: 13px;
				width: 100%;
				text-align: center;
				background-color: #008ec2;
				border: 0;
				margin-bottom: 8px;
				border-radius: 5px;
			}
			.admin-email__actions-primary .button:first-child:hover {
				background-color: #008ec2;
			}
			.login form.admin-email-confirm-form {
				max-width: 360px;
				margin: 0 auto;
			}
			.login-action-confirm_admin_email #backtoblog a {
				color: #008ec2 !important;
			}
			input[type=checkbox]:focus, input[type=checkbox]:checked{
				border-color: #F6366A;
			}
			
    	</style>
  	<?php else: ?>
		<style>
			input[type=checkbox]:checked:before {
				content: '\f147';
				margin: -5px 0 0 -6px;
				color: #1e8cbe;
			}
		</style>
  	<?php endif; ?>
  	<style media="screen" id="loginpress-style">

		/*************************************************************
		* Plugin:      LoginPress - Customizing the WordPress Login.*
		* Description: LoginPress is the best Login Page Customizer *
		*              in WordPress which allows you to completely  *
		*              change the layout of login, register and     *
		*              forgot password forms.                       *
		* Author:      WPBrigade                                    *
		* Visit:       https://wordpress.org/plugins/loginpress/    *
		*************************************************************/
		body.login {
			background-image: url(<?php echo esc_url( apply_filters( 'loginpress_default_bg', plugins_url( 'img/minimalist.jpg', LOGINPRESS_PLUGIN_BASENAME ) ) ); ?>);
			/*background-color: #ddd5c3;*/
			background-repeat: no-repeat;
			background-position: center;
			background-size: cover;
			background-attachment: fixed;
			display: table;
			min-height: 100vh;
			width: 100%;
			padding: 0;
		}
		.header-wrapper{
			/* display: table-row; */
		}
		#login{
			margin-top: 2%;
			background: #fff;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			-ms-border-radius: 5px;
			-o-border-radius: 5px;
			border-radius: 5px;
			-webkit-box-shadow: 0 0 15px rgba(0,0,0,.8);
			box-shadow: 0 0 15px rgba(0,0,0,.8);
			max-width: 350px;
			padding: 26px 26px 6px 26px;
			width: 100%;
		}
		.login form {
			margin-top: 20px;
			margin-left: 0;
			background: #fff;
			-webkit-box-shadow: none;
			box-shadow: none;
		}
		.login form .input, .login input[type="text"]{
			height: 48px;
			background: #fff;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			-ms-border-radius: 5px;
			border-radius: 5px;
			margin-bottom: 18px;
			font: normal 15px Segoe UI,Frutiger,Frutiger Linotype,Dejavu Sans,Helvetica Neue,Arial,sans-serif;
			color: #7f7f7f;
			border-color: #bdbdbd;
			padding-left: 27px;
			font-weight: normal;
		}
		#jetpack_protect_answer{
			padding-left: 0;
			text-align : center;
		}
		.login input[type="submit"],
		body.wp-core-ui.login .two-factor-email-resend .button,
		.wp-core-ui #login .button-primary {
			margin: 7px 0 7px;
			height: 46px;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			-ms-border-radius: 5px;
			border-radius: 5px;
			font: normal 15px Segoe UI,Frutiger,Frutiger Linotype,Dejavu Sans,Helvetica Neue,Arial,sans-serif;
			color: #fff;
			width: 100%;
		}
		.wp-core-ui.login .button-group.button-large .button,
		.wp-core-ui.login .button.button-large, .wp-core-ui.login .button-primary{
			height: 46px;
			line-height: 0;
			background: #008ec2;
		}
		.wp-core-ui.login  .two-factor-email-resend .button{
			color: #444;
		}
		.login #nav {
			text-align: center;
		}
		#login_error { 
			display:block;
		}
		/*.message { display:none; }*/
		/*.custom-message {
			-moz-border-radius:3px 3px 3px 3px;
			border-style:solid;
			border-width:1px;
			margin:0 0 16px 8px;
			padding:12px;
		}*/

		#backtoblog{
			text-align: center;
		}
		.header-cell{
			/* display: table-cell; */
			height: 100px;
		}
		.loginHeaderMenu{
			text-align: center;
			position: relative;
			z-index: 10;
			list-style: none;
			background: #333;

		}
		.loginHeaderMenu>ul>li{
			display: inline-block;
			vertical-align: top;
			position: relative;
			list-style: none;
		}
		.loginHeaderMenu>ul>li>a{
			color: #fff;
			text-transform: uppercase;
			text-decoration: none;
			font-size: 16px;
			padding: 17px 20px;
			display: inline-block;
		}
		.loginHeaderMenu>ul>li:hover>a{
			background: #4CAF50;
			color: #fff;
		}
		.loginHeaderMenu>ul>li>ul{
			position: absolute;
			width: 200px;
			padding: 0;
			top: 100%;
			left: 0;
			background: #fff;
			list-style: none;
			text-align: left;
			border-radius: 0 0 5px 5px;
			-webkit-box-shadow: 0px 5px 10px -1px rgba(0,0,0,0.31);
			-moz-box-shadow: 0px 5px 10px -1px rgba(0,0,0,0.31);
			box-shadow: 0px 5px 10px -1px rgba(0,0,0,0.31);
			overflow: hidden;
			opacity: 0;
			visibility: hidden;
		}
		.loginHeaderMenu>ul>li:hover>ul{
			opacity: 1;
			visibility: visible;
		}
		.loginHeaderMenu>ul>li>ul>li{
			font-size: 15px;
			color: #333;
		}
		.loginHeaderMenu>ul>li>ul>li>a{
			color: #333;
			padding: 10px;
			display: block;
			text-decoration: none;
		}
		.loginHeaderMenu>ul>li>ul>li>a:hover {
			background: rgba(51, 51, 51, 0.35);
			color: #fff;
		}
		.login-wrapper{
			display: table-row;
		}
		.login-cell{
			display: table-cell;
			vertical-align: middle;
		}
		.clear{
			display: none;
		}
		.footer-wrapper{
			width: 100%;
			/* display: table-footer-group; */
			position: -webkit-sticky;
			position: sticky;
			top: 100vh;
			margin-top: 30px;
		}
		.footer-cont{
			right: 0;
			bottom: 0;
			left: 0;
			text-align: center;
			/* display: table-cell; */
			vertical-align: bottom;
			/* height: 100px; */
		}
		.loginFooterMenu{
			text-align: center;
			background-color: rgba(0,0,0,.7);
		}
		.loginFooterMenu>ul{
			display: inline-flex;
		}

		.loginFooterMenu>ul>li{
			display: inline-block;
			padding: 18px;
		}
		/* style two factor plugin */
		.login .backup-methods-wrap a, #login form p:not([class]){
			color: #555d66;
		}
		.login .backup-methods-wrap a:hover{
			color: #00a0d2;
		}
		/*End style two factor plugin */
		.loginFooterMenu>ul>li:focus{
			outline: none;
			border: 0;
		}
		.loginFooterMenu>ul>li>a:focus{
			outline: none;
			border: 0;
		}
		.loginFooterMenu>ul>li>a{
			color: #fff;
			text-transform: uppercase;
			text-decoration: none;
			font-size: 14px;
		}
		.loginHead {
			width:100%;
			text-align: center;
			margin-top: 0;
			margin-bottom: 20px;
			padding: 13px 0 0;
			position: relative;
			margin: 0;
		}
		.loginHead p a {
		text-decoration: none;
		}
		.loginFooter {
			display: table-footer-group;
		}
		.loginFooter p a {
			display: inline-block;
			margin: 0 5px;
		}
		.loginInner {
			margin: 0 auto;
			text-align: center;
			margin-top: 20px;
			margin-bottom: 0;
			padding: 13px 0;
			position: relative;
		}
		.copyRight{
			text-align: center;
			padding: 12px;
			background-color: #efefef;
		}
		.loginpress-show-love{
			color: #222;
		}
		.loginpress-show-love a{
			color: #263466;
		}
		.loginpress-show-love a:hover{
			color: #4d5d95;
		}
		body.login #loginpress_video-background-wrapper {
			position: fixed !important;
			top: 0 !important;
			left: 0 !important;
			width: 100% !important;
			height: 100% !important;
			background-size: cover !important;
			background-position: center !important;
			z-index: -999 !important;
			transform: translate(0);
			pointer-events: none;
		}
		body.login{
			height: auto;
			align-items: flex-start !important;
			justify-content: flex-start !important;
			padding: 40px 0  0 !important;
		}
		#login{
			border-radius: 0;
			margin: 40px auto;
			background: none;
			box-shadow: none;
			padding: 0;
		}
		body #login{
			margin-bottom: auto !important;
			margin-top: auto !important;
		}
		.login form{
			padding: 30px 25px;
		}
		.login label{
			color: #2C3338;
			font-size: 14px;
			line-height: 19px;
			margin: 0 0 10px;
		}
		.login input[type="text"], .login input[type="password"]{
			box-shadow: none;
			border: 1px solid #C3C4C7;
		background: #FFF;
			border-radius: 0;
			padding-left: 15px;
			margin-bottom: 20px;
			margin-right: 0;
		}
		.login .button.wp-hide-pw .dashicons{
			color: #363636;
		}
		.social-sep{
			font-size: 14px;
			line-height: 19px;
			text-transform: uppercase;
		}
		.login .button.wp-hide-pw{
			height: 48px;
		}
		.login input[type="submit"], .wp-core-ui #login .button-primary{
			padding: 12px 15px;
			background: #F6366A;
			border-color: #F6366A;
			margin: 20px 0 0;
		}
		.login form{
			background: #FFF;
			padding: 30px 25px;
			box-shadow: 0px 12px 30px 0px rgba(0, 0, 0, 0.10);
		}
		.social-sep:after,
		.social-sep:before{
			border-width: 2px;
			border-color: #CECECE;
		}
		.social-networks.block{
			padding-bottom: 0;
		}
		.social-networks.block a > div{
			background: none;
			padding: 12px 10px 12px 10px;
			border: 1px solid #C3C4C7;
			background: #FFF;
			font-size: 14px;
			color: #000;
			height: 44px;
			display: flex;
			align-content: center;
			line-height: 19px;
			justify-content: center;
			gap: 10px;
		}
		#loginform .user-pass-fields{
			margin: 0;
		}
		.social-networks a svg{
			position: static;
			order: -1;
			width: auto;
			height: auto;
		}
		.login #nav{
			padding: 0;
			display: flex;
			justify-content: space-between;
			color: #2C3338;

			text-align: center;
			font-family: Segoe UI,Frutiger,Frutiger Linotype,Dejavu Sans,Helvetica Neue,Arial,sans-serif; 
		}
		.loginpress-seprator{
			display: none;
		}
		.social-networks.block a span{
			font-weight: 400;
			float: none;
		}
		body .language-switcher{
			position: static;
		}
		body .footer-wrapper {
			margin-top: 30px;
			position: static;
		}
		#language-switcher {
			margin-top: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 5px;
		}
		#language-switcher label{
			margin-bottom: 0;
		}
		@media screen and (max-width: 767px) {
			#login{
				width: 300px;
			}
			.login .loginpress-show-love{
				position: static;
				text-align: center;
				float: none;
				background: rgba(255,255,255, .5);
				margin-top: 11px;
				padding-bottom: 0;
				padding: 3px;
			}
		}
    </style>
	
	
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			// Find the element with the ID "nav"
			var navElement = document.getElementById('nav');

			// Check if the element exists
			if (navElement) {
				// Replace the "|" with "<span>|</span>"
				navElement.innerHTML = navElement.innerHTML.replace(/\|/g, '<span class="loginpress-seprator">|</span>');
			}
			if(document.querySelector('.footer-cont').innerHTML == ''){
				document.querySelector('.footer-wrapper').style.display = "none";
			}
		});
	</script>
  	<?php
  	$content = ob_get_clean();
  	return $content;
}
