<?php
/*
Contributors: deformator
Plugin Name: Replain
Plugin URI: http://wordpress.org/plugins/replain/
Description: The simplest live chat in the world. The first live chat in your messenger. Messages from the site come directly to your Telegram. Re:plain For those who need customers, not data.
Version: 1.5
Author: Re:plain
Author URI: http://replain.cc
License: GPL2
Text Domain: replain
Domain Path: /lang/
*/

/*
Copyright 2018  CETIS BRANDING AGENCY  (email : cont@cetis.ru)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


add_action( 'plugins_loaded', 'true_load_plugin_textdomain' );

function true_load_plugin_textdomain() {
	load_plugin_textdomain( 'replain', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}


add_action( 'wp_before_admin_bar_render', 'replain_admin_menu_link' );

function replain_admin_menu_link () {
	global $wp_admin_bar;

	$wp_admin_bar->add_menu( array(
		'id'     => 'replain-link',
		'parent' => null,
        'group'  => null,
		'title'  => 'Re:plain',
		'href'   => admin_url( 'options-general.php?page=replain/replain.php' ),

		'meta' => array(
			'class'    => 'replain-top-link',
			'tabindex' => PHP_INT_MAX,
		),
	) );
}


add_action( 'admin_enqueue_scripts', 'replain_wp_toolbar_css' );
add_action( 'wp_enqueue_scripts', 'replain_wp_toolbar_css' );

function replain_wp_toolbar_css() {
    if ( current_user_can( 'level_5' ) ) {
        wp_register_style( 'add_replain_wp_toolbar_css', plugin_dir_url( __FILE__ ).'/assets/css/replain-wp-toolbar-link.css', '', '', 'screen' );
        wp_enqueue_style( 'add_replain_wp_toolbar_css' );
    }
}


register_activation_hook(__FILE__, 'replain_activate');

function replain_activate () {
	if ( !current_user_can( 'activate_plugins' ) ) {
		return;
	}

	add_option( 'replain_enabled', true );
	add_option( 'replain_code', '' );
}


register_deactivation_hook( __FILE__, 'replain_deactivate' );

function replain_deactivate () {
	if ( !current_user_can( 'activate_plugins' ) ) {
		return;
	}
}


register_uninstall_hook( __FILE__, 'replain_uninstall' );

function replain_uninstall () {
	if ( !current_user_can( 'delete_plugins' ) ) {
		return;
	}

	delete_option( 'replain_code' );
	delete_option( 'replain_enabled' );
}


add_action( 'admin_menu', 'replain_menu' );

function replain_menu () {
	add_options_page( 'Re:plain', 'Re:plain', 8, __FILE__, 'replain_toplevel_page' );
}

function replain_toplevel_page () {
	$nonce_name   = 'replain_nonce';
	$nonce_action = 'replain_nonce_action';
	$enabled_name = 'replain_enabled';
	$code_name 	  = 'replain_code';
	$selected 	  = ' selected="selected"';
	$updated 	  = false;

	$options = array(
		'enabled'  => '',
		'disabled' => '',
	);

	if ( isset( $_POST[ $nonce_name ] ) ) {
        if ( wp_verify_nonce( $_POST[$nonce_name], $nonce_action ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                if ( isset( $_POST[$enabled_name] ) ) {
					update_option( $enabled_name, ( (boolean)$_POST[$enabled_name] ) );
        			$updated = true;
        		}

        		if ( isset( $_POST[$code_name] ) ) {
        			preg_match( '/REPLAIN_\s*=\s*\W(.*?)\W;/', stripslashes( $_POST[$code_name] ), $matches );

        			if ( !empty( $matches ) && isset( $matches[1] ) ) {
        				$token = sanitize_text_field( $matches[1] );
        			} else {
        				$token = '';
        			}

					update_option( $code_name, $token );
        			$updated = true;
        		}
            } else {
                echo '<div class="error"><p>'.__( 'User does not have permission to edit plugins', 'replain' ).'</p></div>';
            }
        } else {
            echo '<div class="error"><p>'.__( 'Nonce did not pass the verification', 'replain' ).'</p></div>';
        }
	}

	$enabled = (boolean)get_option( $enabled_name );
	$code 	 = get_option( $code_name );

	if ( true === $enabled ) {
		$options[ 'enabled' ] = $selected;
	} else {
		$options[ 'disabled' ] = $selected;
	}

	if ( true == $updated ) {
		echo '<div class="updated"><p>'.__( 'Data saved successfully', 'replain' ).'</p></div>';
	}

	echo '<div class="wrap"><h2><strong>'.__( 'Re:plain Settings', 'replain' ).'</strong></h2>';

	if ( $code == '' ) {
		echo '<p>'.__( 'To connect Re:plain you need to get the Re:plain code. To do this, follow the link below.', 'replain' ).'</p>';
		echo '<p><a class="replain-bot-link" href="https://replain.cc/" target="_blank">'.__( 'Get the Re:plain code', 'replain' ).'</a></p><hr>';
	}

	echo '<form name="replain_form" method="post" action="'.str_replace( '%7E', '~', $_SERVER[ 'REQUEST_URI' ] ).'">';
	wp_nonce_field( $nonce_action, $nonce_name );

	if ( $code != '' ) {
		echo '<p>
			<strong class="replain-label">'.__( 'Chat is', 'replain' ).'</strong>

			<select class="replain-enabled" name="'.$enabled_name.'" tabindex="1">
				<option value="1"'.$options[ 'enabled' ].'>'.__( 'Enabled', 'replain' ).'</option>
				<option value="0"'.$options[ 'disabled' ].'>'.__( 'Disabled', 'replain' ).'</option>
			</select>
		</p>';
	}

	echo '<p>
		<strong class="replain-label">'.__( 'Enter your Re:plain code', 'replain' ).'</strong>
		<textarea class="replain-code" name="'.$code_name.'" cols="80" rows="8" tabindex="2">'.replain_js_code( $code ).'</textarea>
	</p>

	<p class="submit replain-submit">
		<input class="replain-submit-button" type="submit" name="Submit" value="'.__( 'Update Settings', 'replain' ).'"  tabindex="3">
	</p>

	</form></div>';
}


add_action('wp_footer', 'replain_footer');

function replain_footer () {
	$enabled = (boolean)get_option( 'replain_enabled' );
	$code 	 = get_option( 'replain_code' );

	if ( $code != '' && true == $enabled ) {
		echo replain_js_code( $code );
	}
}

function replain_js_code ($code) {
	if ( empty( $code ) ) {
		return '';
	}

	$jsCode = '<script type="text/javascript" charset="utf-8">var __REPLAIN_ = \''.$code.'\';(function(u){var s=document.createElement(\'script\');s.type=\'text/javascript\';s.async=true;s.src=u;var x=document.getElementsByTagName(\'script\')[0];x.parentNode.insertBefore(s,x);})(\'https://widget.replain.cc/dist/client.js\');</script>';
	$jsCode = stripslashes( $jsCode );
	return $jsCode;
}
?>
