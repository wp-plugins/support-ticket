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
		global $wpdb;

		//Find no of unread tickets of user
		$unread = 0;
		if( current_user_can( 'read_assigned_tickets' ) ){
			$sql = "
				select 
					count( a.post_id )  as alltickets
				from
					" . $wpdb->prefix . "postmeta as a
				where (
					a.meta_key = 'ticket-agent' &&
					a.meta_value = '" . get_current_user_id() . "'
				)";

			$all = $wpdb->get_results( $sql );

			$sql = "
				select 
					count( r.post_id )  as readtickets
				from
					" . $wpdb->prefix . "postmeta as r,
					" . $wpdb->prefix . "postmeta as a
				where (
					a.meta_key = 'ticket-agent' &&
					a.meta_value = '" . get_current_user_id() . "' &&
					a.post_id = r.post_id &&
					r.meta_key = 'ticket-read' &&
					r.meta_value = 1
				)";
			$res = $wpdb->get_results( $sql );
			$unread = $all[0]->alltickets - $res[0]->readtickets;
		}
		$tickets_title = __( 'Tickets', 'sts' );
		if( $unread > 0 )
			$tickets_title = __( 'Tickets', 'sts' ) . ' (' . $unread . ')';
		add_menu_page( $tickets_title, $tickets_title, 'read_own_tickets', 'sts', 'sts_admin_outpout_index' );
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



	/**
	* Admin init
	*
	* Do the bulk actions here
	*
 	* @since 	1.0.0
	*/
	add_action( 'admin_init', 'sts_admin_init' );
	function sts_admin_init(){
		if( 
			! isset( $_POST['sts-action'] ) || 
			! isset( $_POST['action'] ) || 
			! isset( $_POST['ticket'] ) || 
			! is_array( $_POST['ticket'] ) 
		)
			return;

		if( 
			$_POST['sts-action'] == 'bulk-action' &&
			! wp_verify_nonce( $_POST['t-nonce'], 'sts-bluk-action')
		)
			wp_die( __( 'Something went wrong :/', 'sts' ) );

		if( $_POST['action'] == 'delete' && ! current_user_can( 'delete_other_tickets' ) )
			wp_die( __( 'Something went wrong :/', 'sts' ) );

		foreach( $_POST['ticket'] as $ticket ){
			$ticket = (int) $ticket;

			$args = array(
				'post_type' => 'ticket',
				'post_parent' => $ticket,
				'posts_per_page' 	=> -1,
			);

			$query = new WP_Query( $args );
			if( $query->have_posts() ){
				while( $query->have_posts() ){
					$query->the_post();
					wp_delete_post( get_the_ID(), true );
				}
			}
			wp_reset_query();

			wp_delete_post( $ticket, true );
		}

		$url = add_query_arg( array( 'updated' => 1 ) );
		wp_redirect( $url );
	}
?>