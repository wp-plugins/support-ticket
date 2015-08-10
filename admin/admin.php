<?php
	
	
	/**
	* Register Scripts & Styles
	* Adds the scripts and styles for the backend end
	*
 	* @since 	1.0.0
	*/
	add_action( 'admin_enqueue_scripts', 'sts_adminscripts' );
	function sts_adminscripts( $hook ){
		$hooks = array( 'toplevel_page_sts', 'tickets_page_sts-new', 'tickets_page_sts-settings' );
		if( ! in_array( $hook, $hooks ) )
			return;

		wp_enqueue_style( 'sts-admin-style', STS_URL . 'admin/style.css' );
		wp_enqueue_script( 'sts-admin-script', STS_URL . 'admin/script.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-sortable' ) );
		
		$stsLocalize = array(
			'trash' => __( 'Trash', 'sts' ),
			'edit'	=> __( 'Edit', 'sts' ),
			'inputfield'	=> __( 'Input field', 'sts' ),
			'selectbox'	=> __( 'Selectbox', 'sts' ),
		);
		wp_localize_script( 'sts-admin-script', 'stsLocalize', $stsLocalize );
		add_action( 'admin_footer_text', 'sts_admin_thankyou' );
	}


	/**
	* Extend Admin Menu
	*
 	* @since 	1.0.0
	*/
	add_action( 'admin_menu', 'wp_sf_adminpage' );
	function wp_sf_adminpage() {
		add_menu_page( __( 'Tickets', 'sts' ), 'Tickets', 'read_own_tickets', 'sts', 'sts_admin_outpout_index' );
		add_submenu_page( 'sts', __( 'New Ticket', 'sts' ), __( 'New Ticket', 'sts' ), 'read_own_tickets', 'sts-new', 'sts_admin_outpout_new_ticket' );		
		add_submenu_page( 'sts', __( 'Settings', 'sts' ), __( 'Settings', 'sts' ), 'manage_options', 'sts-settings', 'sts_admin_outpout_settings' );
	}
	
	function sts_admin_outpout_index(){
		require_once( dirname( __FILE__ ) . "/index.php");
	}

	function sts_admin_outpout_new_ticket(){
		require_once( dirname( __FILE__ ) . "/ticket-new.php");
	}

	function sts_admin_outpout_settings(){
		require_once( dirname( __FILE__ ) . "/settings.php");
	}

	function sts_admin_thankyou(){
		return '<span id="footer-thankyou">' . sprintf( __( 'Thank you for using %s.' ), '<a href="http://wpsupportticket.com">Support Ticket Plugin</a>' ) . '</span>';
	}
?>