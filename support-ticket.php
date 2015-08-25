<?php
	/**
	 * Plugin Name: Support Ticket
	 * Author: David Remer
	 * Plugin URI: http://wpsupportticket.com/
	 * Author URI: http://websupporter.net/
	 * Version: 1.0.6
	 * Description: Easy to use support ticket system for WordPress.
	 * License: GPLv2 or later
	 * Text Domain: sts
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

	//Register Constansts
	define( 'STS_FILE', __FILE__ );
	define( 'STS_ROOT', dirname( STS_FILE ) . '/' );
	define( 'STS_URL', plugins_url( '/', __FILE__ ) );

	//Include Files
	require_once( STS_ROOT . 'inc/functions.php' );
	require_once( STS_ROOT . 'inc/hooks.php' );
	require_once( STS_ROOT . 'inc/template-tags.php' );
	require_once( STS_ROOT . 'inc/shortcodes.php' );

	if( is_admin() )
		require_once( STS_ROOT . 'admin/admin.php' );



	/**
	* Init the system
	* Register ticket post type
	*
 	* @since 	1.0.0
	*/
	add_action( 'init', 'sts_init' );
	function sts_init(){
		if( ! session_id() )
			session_start();
		
		$args = array(
			'label' => __( 'Ticket', 'sts' ),
			'hierarchical' => true,
		);

		/**
 		* Filters the custom post type args
 		* see https://codex.wordpress.org/Function_Reference/register_post_type
 		*
 		* @since 1.0.5
 		*
 		* @param 	(array) 	$args 	the argument array
 		* @return 	(array) 	$args 	the argument array
 		*/
		$args = apply_filters( 'sfs-custom-posttype-args', $args );
		register_post_type( 'ticket', $args );

		//POST actions
		if( isset( $_POST['t-action'] ) )
			require_once( STS_ROOT . 'inc/post-actions.php' );

	}


	/**
	* Register the text domain
	*
 	* @since 	1.0.0
	*/
	add_action('plugins_loaded', 'sts_textdomain');
	function sts_textdomain() {
		$plugin_dir = basename( STS_ROOT ) . '/assetts/language/';
		load_plugin_textdomain( 'sts', false, $plugin_dir );
	}

	/**
	* Register Scripts & Styles
	* Adds the scripts and styles for the front end
	*
 	* @since 	1.0.0
	*/
	add_action( 'wp_enqueue_scripts', 'sts_scripts' );
	function sts_scripts(){

	}
?>