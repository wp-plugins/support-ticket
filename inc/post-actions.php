<?php
	if( $_POST['t-action'] == 'ticket-create' )
		sts_action_ticket_create();

	/**
	 * Create a new ticket
	 * This function creates a new ticket
	 *
	 * @since 1.0.0
	 **/
	function sts_action_ticket_create(){
		if( ! session_id() )
			session_start();
		unset( $_SESSION['tickets']['error'] );

		if( ! wp_verify_nonce( $_POST['t-nonce'], 'ticket-create' ) )
			die( __( 'Something went wrong :/', 'sts' ) );

		/**
		* Filter for the post data.
		*
		* @since 1.0.0
		*
		* @param (array) 	$_POST['t'] 	post data
		* @param (array) 	$post_data 		post data
		*/
		$post_data = apply_filters( 'sts-create-new-ticket-post', $_POST['t'] );

		foreach( $post_data as $key => $val ){
			if( is_string( $val ) )
				$post_data[ $key ] = trim( sanitize_text_field( $val ) );
		}
		
		if( isset( $post_data['user'] ) && empty( $post_data['user'] ) ){
			$_SESSION['tickets']['error'] = new WP_Error( 'ticket-user', __( 'Please enter your name.', 'sts' ) );
			return false;
		}
		
		if( isset( $post_data['email'] ) && empty( $post_data['email'] ) ){
			$_SESSION['tickets']['error'] = new WP_Error( 'ticket-email', __( 'Please enter your email address.', 'sts' ) );
			return false;
		}
		
		if( isset( $post_data['subject'] ) && empty( $post_data['subject'] ) ){
			$_SESSION['tickets']['error'] = new WP_Error( 'ticket-subject', __( 'Please enter a subject.', 'sts' ) );
			return false;
		}
		
		if( isset( $post_data['message'] ) && empty( $post_data['message'] ) ){
			$_SESSION['tickets']['error'] = new WP_Error( 'ticket-message', __( 'Please enter a message.', 'sts' ) );
			return false;
		}


		/**
		* Filter if an error occured during the process.
		*
		* @since 1.0.0
		*
		* @param (bool) 	error = false
		* @param (array) 	post data
		* @return (mixed) 	false or wp_error object
		*/
		$check = apply_filters( 'sts-create-new-ticket-errors', false, $post_data );
		if( is_wp_error( $check ) ){
			$_SESSION['tickets']['error'] = $check;
			return false;
		}

		//If the user is not logged in
		//we need to check, if the user has already an account
		//or we have to create a new account
		if( ! is_user_logged_in() ){
			$user = get_user_by( 'email', $post_data['email'] );

			if( false === $user ){
				//We create a new user, if the email is not registered yet.
				$random_password = wp_generate_password();
				/**
				* Filter the username
				*
				* @since 1.0.0
				*
				* @param (string) 	user name
				* @return (string) 	user name
				*/
				$username = apply_filters( 'sts-createuser-username', $post_data['email'] );
				/**
				* Filter the users password
				*
				* @since 1.0.0
				*
				* @param (string) 	user password
				* @return (string) 	user password
				*/
				$random_password = apply_filters( 'sts-createuser-password', $random_password );
				$user_id = wp_create_user( $username , $random_password, $post_data['email'] );

  				$usermeta =	array(
    							'ID'			=> $user_id,
    							'user_nicename'	=> $post_data['user'],
    							'display_name'	=> $post_data['user']
  				);

				/**
				* Filter the user meta data
				*
				* @since 1.0.0
				*
				* @param (array) 	meta data
				* @return (array) 	meta data
				*/
				$usermeta = apply_filters( 'sts-createuser-usermeta', $usermeta );
				wp_update_user( $usermeta );


				/**
				* Action after the user has been created
				*
				* @since 1.0.0
				*
				* @param (int) 		user ID
				* @param (array) 	usermeta
				* @param (array) 	post data
				*/
				do_action( 'sts-createuser-after', $user_id, $usermeta, $post_data );

				$credentials = array(
					'user_login'	=> $username,
					'user_password'	=> $random_password,
					'remember'		=> false
				);

				/**
				* Filter the securecookie for wp_singon
				*
				* @since 1.0.0
				*
				* @param (boolean) 	use secure cookie = false
				* @return (boolean) 	use secure cookie
				*/
				$secure_cookie = apply_filters( 'sts-loginuser-securecookie', false );
				$user = wp_signon( $credentials, $secure_cookie );


				$welcome_mail_subject = sprintf( __( 'Welcome to %s', 'sts' ), get_bloginfo( 'name' ) );
				$welcome_mail_body = sprintf( __( 'Hello %s,', 'sts' ), $usermeta['display_name'] ) . PHP_EOL;
				$welcome_mail_body .= sprintf( __( "you've just registered on %s and submitted a ticket.", 'sts' ), get_bloginfo( 'name' ) ) . PHP_EOL;
				$welcome_mail_body .= __( 'You can login using the following informations:', 'sts' ) . PHP_EOL;
				$welcome_mail_body .= sprintf( __( 'Loginurl: %s', 'sts' ), wp_login_url() ) . PHP_EOL;
				$welcome_mail_body .= sprintf( __( 'Username: %s', 'sts' ), $credentials['user_login'] ) . PHP_EOL;
				$welcome_mail_body .= sprintf( __( 'Password: %s', 'sts' ), $credentials['user_password'] ) . PHP_EOL . PHP_EOL;
				$welcome_mail_body .= __( 'Thank you.', 'sts' );

				/**
				* Filter the welcome email text
				*
				* @since 1.0.0
				*
				* @param (string) 	$welcome_mail_body 	the email text
				* @param (array) 	$credentials 		the login credentials
				* @param (int) 		$user_id 			the user id
				* @param (array) 	$usermeta 			the user meta
				* @param (array) 	$post_data 			the post data
				* @return (string) 	$welcome_mail_body 	the email text
				*/
				$welcome_mail_body = apply_filters( 'sts-userregister-welcome-email-body', $welcome_mail_body, $credentials, $user_id, $usermeta, $post_data );

				/**
				* Filter the welcome email subject
				*
				* @since 1.0.0
				*
				* @param (string) 	$welcome_mail_body 	the email subject
				* @param (array) 	$credentials 		the login credentials
				* @param (int) 		$user_id 			the user id
				* @param (array) 	$usermeta 			the user meta
				* @param (array) 	$post_data 			the post data
				* @return (string) 	$welcome_mail_body 	the email subject
				*/
				$welcome_mail_subject = apply_filters( 'sts-userregister-welcome-email-subject', $welcome_mail_subject, $credentials, $user_id, $usermeta, $post_data );

				sts_mail( $post_data['email'], $welcome_mail_subject, $welcome_mail_body, array(), array() );
			} else {
				//If the email is registered, we ask the user to login before he proceeds.

				//Save all entered data in the session, so we can recall them later.
				$_SESSION['ticket']['ticket-create'] = $_POST['t'];
				$_SESSION['ticket']['action'] = 'ask-login';
				return;
			}
		}

		if( ! isset( $user ) )
			$user = wp_get_current_user();
		
		//Make sure, the user can read his own tickets
		if( ! user_can( $user, 'read_own_tickets' ) )
			$user->add_role( 'ticket-user' );

		$args = array(
			'post_title' => sanitize_text_field( $post_data['subject'] ),
			'post_content' => wp_strip_all_tags( $post_data['message'] ),
			'post_type' => 'ticket',
			'post_author' => $user->ID
		);

		/**
		* Filter the ticket args
		*
		* @since 1.0.0
		*
		* @param (array) 	args
		* @param (array) 	post data
		* @return (array) 	args
		*/
		$args = apply_filters( 'sts-createticket-args', $args, $post_data );		
		$post_id = wp_insert_post( $args );
		add_post_meta( $post_id, 'ticket-status', '0' );

		$settings = get_option( 'sts-core-settings' );
		$ticket_agent = 1;
		if( isset( $settings['user']['standard-agent'] ) )
			$ticket_agent = $settings['user']['standard-agent'];
		add_post_meta( $post_id, 'ticket-agent', $ticket_agent );
		
		/**
		* Action after the ticket has been created
		*
		* @since 1.0.0
		*
		* @param (int) 		post ID
		* @param (array) 	post data
		*/
		do_action( 'sts-createticket-after', $post_id, $post_data );

		if( isset( $settings['email']['notifiy-agent'] ) && $settings['email']['notifiy-agent'] == 1 ){
			$agent = get_user_by( 'id', $ticket_agent );
			$subject = $args['post_title'];
			$text = $args['post_content'] . PHP_EOL . PHP_EOL;
			$text .= sprintf( __( 'Please click the link to reply:', 'sts' ) ) . ' ';
			$view_ticket = admin_url( "admin.php?page=sts&action=single&ID=" . $post_id );
			/**
 			* Filters the URL where the ticket can be read
 			*
 			* @since 1.0.0
 			*
 			* @param 	(string) 	$view_ticket 	the URL
 			* @return 	(string) 	$view_ticket 	the URL
 			*/
			$view_ticket = apply_filters( 'sts-view-ticket-url', $view_ticket, $post_id );

			$text .= $view_ticket;

			$headers = array();
			$attachments = array();

			if( ! sts_mail( $agent->data->user_email, $subject, $text, $headers, $attachments ) )
				die( 'Nicht verschickt :/');
		}

		$redirect_url = add_query_arg( array( 'ticket-created' => 1 ) );
		$redirect_url = add_query_arg( array( 'ticket-id' => $post_id ), $redirect_url );
		wp_redirect( $redirect_url );
		die();
	}
?>