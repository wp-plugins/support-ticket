<?php

	/**
	* Register ticket create shortcode
	* Outputs the shortcode [ticket_create]
	*
 	* @since 	1.0.0
	*/
	add_shortcode( 'ticket_create', 'sts_ticket_create' );
	function sts_ticket_create( $args ){
		//The default settings
		$default = array( 'type' => 'section' );
		$args = wp_parse_args( $args, $default );

		if( isset( $_SESSION['ticket']['ticket-create'] ) )
			$_POST['t'] = $_SESSION['ticket']['ticket-create'];
		ob_start();
		if( ! isset( $_GET['ticket-created'] ) ){
			$fields = sts_get_create_ticket_form_fields( 'shortcode' );

			$shortcode_file = dirname( __FILE__ ) . '/shortcodes/ticket-create.php';
			/**
			* Filter the file path to the shortcode
			*
			* @since 1.0.0
			*
			* @param (string) 	$shortcode_file 	filename of the shortcode file
			* @return (string) 	$shortcode_file 	filename of the shortcode file
			*/
			$shortcode_file = apply_filters( 'sts-create-ticket-shortcodefile', $shortcode_file );
			
			if( is_file( $shortcode_file ) )
				require_once( $shortcode_file );
			else
				return __( 'Shortcode file not found :/', 'sts' );
		}else{
			$shortcode_file = dirname( __FILE__ ) . '/shortcodes/ticket-create-done.php';
			/**
			* Filter the file path to the shortcode for a submitted ticket.
			*
			* @since 1.0.0
			*
			* @param (string) 	$shortcode_file 	filename of the shortcode file
			* @return (string) 	$shortcode_file 	filename of the shortcode file
			*/
			$shortcode_file = apply_filters( 'sts-create-ticket-done-shortcodefile', $shortcode_file );

			if( is_file( $shortcode_file ) )
				require_once( $shortcode_file );
			else
				return __( 'Shortcode file not found :/', 'sts' );
		}
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
?>