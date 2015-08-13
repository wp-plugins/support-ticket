<?php
add_action( 'add_meta_boxes', 'sts_add_meta_boxes' );
function sts_add_meta_boxes(){
	//Single Ticket Metaboxes
	if( ! isset( $_GET['page'] ) || ! in_array( $_GET['page'], array( 'sts', 'sts-settings' ) ) )
		return;

	if( isset( $_GET['action'] ) && $_GET['action'] == 'single' ){
		add_meta_box( 'ticket-message', sprintf( __( 'Ticket #%d', 'sts' ), (int) $_GET['ID'] ), 'sts_metabox_message_render', 'ticket-boxes', 'normal' );
		
		$metafields = get_option( 'sts-metafields', array() );
		if( count( $metafields ) > 0 )
			add_meta_box( 'ticket-metafields', __( 'Metafields', 'sts' ), 'sts_metabox_metafields_render', 'ticket-boxes', 'normal' );
		
		add_meta_box( 'ticket-answer', __( 'Answer', 'sts' ), 'sts_metabox_answer_render', 'ticket-boxes', 'normal' );
		add_meta_box( 'ticket-status', __( 'Status', 'sts' ), 'sts_metabox_status_render', 'ticket-boxes', 'side' );
		
		if( current_user_can( 'update_tickets' ) )
			add_meta_box( 'ticket-privatenote', __( 'Private Note', 'sts' ), 'sts_metabox_privatenote_render', 'ticket-boxes', 'side' );
	
	} elseif( $_GET['page'] == 'sts-settings' ){
		//Settings Metaboxes
		add_meta_box( 'ticket-setting-email-notification', __( 'Email notification', 'sts' ), 'sts_settings_metabox_email_notification_render', 'ticket-settings-email', 'normal' );
		add_meta_box( 'ticket-setting-user-agent', __( 'Ticket agents', 'sts' ), 'sts_settings_metabox_user_agent_render', 'ticket-settings-user', 'normal' );
		add_meta_box( 'ticket-setting-email-sender', __( 'Email Sender', 'sts' ), 'sts_settings_metabox_email_sender_render', 'ticket-settings-email', 'normal' );
		add_meta_box( 'ticket-setting-email-wrapper', __( 'Email wrapper', 'sts' ), 'sts_settings_metabox_email_wrapper_render', 'ticket-settings-email', 'normal' );
		add_meta_box( 'ticket-setting-ticket-wrapper', __( 'Additional ticket fields', 'sts' ), 'sts_settings_metabox_metafields_render', 'ticket-settings-ticket', 'normal' );
	
	}
}


/**
 * Renders the metafields metabox
 *
 * @since 1.0.0
 *
 * @param (stdClass) 	$args 	empty
 * @return (void)
 **/
function sts_metabox_metafields_render( $post ){
	$metafields = get_option( 'sts-metafields', array() );
	?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr><th><?php _e( 'Name', 'sts' ); ?></th><th><?php _e( 'Value', 'sts' ); ?></th></tr>
		</thead>
		<tbody>
			<?php foreach( $metafields as $field ):	?>
			<tr>
				<td><?php echo $field['label']; ?></td>
				<td>
					<?php echo get_post_meta( $post->ID, $field['metakey'], true ); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}


/**
 * Update the Ticket field settings
 * 
 * Explanations on this filter can be found in admin/inc/functions-settings.php
 * 
 *
 * @since 1.0.0
 *
 * @param (mixed)		$return 	
 * @return (mixed)		$return
 **/
add_filter( 'sts-settings-update-ticket', 'sts_settings_ticket_fields' );
function sts_settings_ticket_fields( $return ){
	if( ! isset( $_POST['ticket']['fields']['id'] ) ){
		update_option( 'sts-metafields', array() );
		return $return;
	}

	$field_ids = $_POST['ticket']['fields']['id'];
	$field_settings = $_POST['ticket']['fields']['fields'];

	$fields = array();
	if( is_array( $field_ids) ){
		foreach( $field_ids as $key => $val ){
			$single = json_decode( stripslashes( $field_settings[ $key ] ) );
			$single->id = $val;

			if( $single->tag == 'input' )
				$single->type = 'text';

			$single = (array) $single;
			foreach( $single as $key => $val ){
				if( ! is_object( $val ) && ! is_array( $val ) ){
					$single[ $key ] = sanitize_text_field( $val );
				} else {
					$val = ( array ) $val;
					foreach( $val as $sub_key => $sub_val )
						$val[ $sub_key ] = sanitize_text_field( $sub_val );
					$single[ $key ] = $val;
				}
			}

			$fields[] = $single;
		}
	}
	update_option( 'sts-metafields', $fields );
	return  $return;
}

/**
 * Renders the ticket fields metabox
 *
 * @since 1.0.0
 *
 * @param (stdClass) 	$args 	empty
 * @return (void)
 **/
function sts_settings_metabox_metafields_render( $args ){
	$fields = sts_get_create_ticket_form_fields( 'edit' );
	$core_fields = array( 'ticket-user', 'ticket-email', 'ticket-subject', 'ticket-message' );
	$metafields = get_option( 'sts-metafields', array() );
	$edited_fields = array();
	foreach( $metafields as $field )
		$edited_fields[] = $field['id'];
	?>

	<ul class="ticket-field-list">
		<?php
		foreach( $fields as $field ):
			if( in_array( $field['id'], $edited_fields ) ): ?>
			<li class="editable">
				<input name="ticket[fields][id][]" value="<?php echo $field['id']; ?>" type="hidden"/><textarea name="ticket[fields][fields][]" style="display:none;"><?php echo json_encode( $field ); ?></textarea>
				<?php echo $field['label']; ?>
				<div title="<?php _e( 'Trash', 'sts' ); ?>" class="sts-delete dashicons dashicons-trash"></div>
				<div title="<?php _e( 'Edit', 'sts' ); ?>" class="sts-edit-field dashicons dashicons-edit"></div>
			</li>
			<?php endif; 
		endforeach;
		?>
	</ul>
	<hr />
	<a id="btn-sts-create-new-ticket-field" title="<?php _e( 'Add a new form field', 'sts' ); ?>" href="#TB_inline?width=250&height=auto&inlineId=sts-create-new-ticket-field" class="thickbox button button-large"><div class="dashicons dashicons-plus"></div> <?php _e( 'Create new field', 'sts' ); ?></a>
	<a id="btn-sts-edit-ticket-field" href="#TB_inline?width=250&height=auto&inlineId=sts-edit-ticket-field" class="thickbox"></a>
	<div id="sts-edit-ticket-field" style="display:none;">
			<input type="hidden" id="edit_metakey" value="" />
			<input type="hidden" id="edit_tag" value="" />
			<input type="hidden" id="edit_li_index" value="" />
			<p>
				<label for="edit_label"><?php _e( 'Label', 'sts' ); ?>:</label>
				<br />
				<input type="text" id="edit_label" value="" />
			</p>
			<p>
				<label for="edit_metakey_display"><?php _e( 'Meta key', 'sts' ); ?>:</label>
				<br />
				<span id="edit_metakey_display"></span>
			</p>
			<p>
				<label for="edit_tag_display"><?php _e( 'Type', 'sts' ); ?>:</label>
				<br />
				<span id="edit_tag_display"></span>
			</p>
			<div id="sts-formfield-choices-edit-wrapper" style="display:none;">
				<p>
					<label for="edit_choices"><?php _e( 'Choices', 'sts' ); ?>:</label>
					<br />
					<textarea id="edit_choices"></textarea>
					<br />
					<small><?php _e( 'Use a new line for each choice.', 'sts' ); ?></small>
				</p>
			</div>
			<button class="button default" id="do-sts-edit-ticket-field"><?php _e( 'Edit', 'ri' ); ?></button>
	</div>

	<div id="sts-create-new-ticket-field" style="display:none;">
			<p>
				<label for="label"><?php _e( 'Label', 'sts' ); ?>:</label>
				<br />
				<input type="text" id="label" value="" />
			</p>
			<p>
				<label for="metakey"><?php _e( 'Meta key', 'sts' ); ?>:</label>
				<br />
				<input type="text" id="metakey" value="" />
			</p>
			<p>
				<label for="type"><?php _e( 'Type', 'sts' ); ?>:</label>
				<br />
				<select type="text" id="tag">
					<option value="input"><?php _e( 'Input field', 'sts' ); ?></option>
					<option value="select"><?php _e( 'Selectbox', 'sts' ); ?></option>
				</select>
			</p>
			<div id="sts-formfield-choices-wrapper" style="display:none;">
				<p>
					<label for="choices"><?php _e( 'Choices', 'sts' ); ?>:</label>
					<br />
					<textarea id="choices"></textarea>
					<br />
					<small><?php _e( 'Use a new line for each choice.', 'sts' ); ?></small>
				</p>
			</div>
			<button class="button default" id="do-sts-create-new-ticket-field"><?php _e( 'Add', 'ri' ); ?></button>
	</div>
	<?php
}

/**
 * Update the User agent settings
 * 
 * Explanations on this filter can be found in admin/inc/functions-settings.php
 * 
 *
 * @since 1.0.0
 *
 * @param (mixed)		$return 	
 * @return (mixed)		$return
 **/
add_filter( 'sts-settings-update-user', 'sts_settings_user_standard_agent' );
function sts_settings_user_standard_agent( $return ){
	$settings = get_option( 'sts-core-settings' );
	$standard_agent = 1;
	if( isset( $_POST['user']['standard-agent'] ) )
		$standard_agent = (int) $_POST['user']['standard-agent'];
	$settings['user']['standard-agent'] = $standard_agent;


	update_option( 'sts-core-settings', $settings );
	return $return;
}

/**
 * Renders the standard agent metabox
 *
 * @since 1.0.0
 *
 * @param (stdClass) 	$args 	empty
 * @return (void)
 **/
function sts_settings_metabox_user_agent_render( $args ){
	$standard_agent = 1;
	$settings = get_option( 'sts-core-settings' );
	if( isset( $settings['user']['standard-agent'] ) )
		$standard_agent = $settings['user']['standard-agent'];

	//Get Admins
	$agents = sts_get_possible_agents();

	?>
	<section>
		<label for="standard-ticket-agent"><?php _e( 'New tickets are assigned to', 'sts' ); ?></label>
		<select id="standard-ticket-agent" type="checkbox" name="user[standard-agent]">
			<?php foreach( $agents as $s ):
			if( is_string( $s ) ): ?>
			<option disabled>---------------------</option>
			<?php else: ?>
			<option value="<?php echo $s->ID; ?>" <?php selected( $s->ID, $standard_agent ); ?>><?php echo $s->data->display_name; ?></option>
			<?php 
			endif;
			endforeach; ?>
		</select>
	</section>
	<?php
}

/**
 * Update the Email sender settings
 * 
 * Explanations on this filter can be found in admin/inc/functions-settings.php
 * 
 *
 * @since 1.0.5
 *
 * @param (mixed)		$return 	
 * @return (mixed)		$return
 **/
add_filter( 'sts-settings-update-email', 'sts_settings_update_email_sender' );
function sts_settings_update_email_sender( $return ){
	$settings = get_option( 'sts-core-settings' );
	if( isset( $_POST['email']['from_name'] ) )
		$settings['email']['from_name'] = sanitize_text_field( $_POST['email']['from_name'] );
	if( isset( $_POST['email']['from_email'] ) )
		$settings['email']['from_email'] = sanitize_text_field( $_POST['email']['from_email'] );


	update_option( 'sts-core-settings', $settings );
	return $return;
}


/**
 * Renders the email sender metabox in the settings
 *
 * @since 1.0.5
 * 
 * @param (stdClass) 	$args 	empty
 * @return (void)
 **/
function sts_settings_metabox_email_sender_render( $args ){
	$settings = get_option( 'sts-core-settings' );

	$from_name =  get_bloginfo( 'name' );
	$from_email = get_bloginfo( 'admin_email' );
	if( isset( $settings['email']['from_name'] ) )
		$from_name = $settings['email']['from_name'];
	if( isset( $settings['email']['from_email'] ) )
		$from_email = $settings['email']['from_email'];
	

	?>
	<section>
		<label for="standard-email-from-name"><?php _e( 'Sender Name', 'sts' ); ?></label>
		<input type="text" id="standard-email-from-name" name="email[from_name]" value="<?php echo sanitize_text_field( $from_name ); ?>" />
	</section>
	<section>
		<label for="standard-email-from-email"><?php _e( 'Sender Email', 'sts' ); ?></label>
		<input type="email" id="standard-email-from-email" name="email[from_email]" value="<?php echo sanitize_text_field( $from_email ); ?>" />
	</section>
	<?php
}

/**
 * Renders the email wrapper metabox
 *
 * @since 1.0.0
 *
 * @param (stdClass) 	$args 	empty
 * @return (void)
 **/
function sts_settings_metabox_email_wrapper_render( $args ){
	$settings = get_option( 'sts-core-settings' );

	if( isset( $settings['email']['wrapper'] ) )
		$wrapper = $settings['email']['wrapper'];
	else
		$wrapper = preg_replace( '^#logo#^', STS_URL . 'assetts/logo.png', file_get_contents( STS_ROOT . 'assetts/email-wrapper.html' ) );

	?>
	<section>
		<label for="standard-email-wrapper"><?php _e( 'Email Wrapper', 'sts' ); ?></label>
		<textarea id="standard-email-wrapper" name="email[wrapper]"><?php echo $wrapper; ?></textarea>
		<p>
			<small>
				Use some HTML to style your emails. The template tag <code>#content#</code>
				will be replaced by the actual email.
			</small>
		</p>
	</section>
	<?php
}

/**
 * Update the Settings Email section
 * 
 * Explanations on this filter can be found in admin/inc/functions-settings.php
 * 
 *
 * @since 1.0.0
 *
 * @param (mixed)		$return 	
 * @return (mixed)		$return
 **/
add_filter( 'sts-settings-update-email', 'sts_settings_email_update' );
function sts_settings_email_update( $return ){
	$settings = get_option( 'sts-core-settings' );
	$notification = 0;
	if( isset( $_POST['email']['notifiy-ticketowner'] ) && $_POST['email']['notifiy-ticketowner'] == '1' )
		$notification = 1;
	$settings['email']['notifiy-ticketowner'] = $notification;


	$notification = 0;
	if( isset( $_POST['email']['notifiy-agent'] ) && $_POST['email']['notifiy-agent'] == '1' )
		$notification = 1;
	$settings['email']['notifiy-agent'] = $notification;

	$wrapper = '';
	if( isset( $_POST['email']['wrapper'] ) ){
		$wrapper = stripslashes( $_POST['email']['wrapper'] );
		$settings['email']['wrapper'] = $wrapper;
	}

	update_option( 'sts-core-settings', $settings );
	return $return;
}

/**
 * Render the Email notification box for settings
 *
 * @since 1.0.0
 *
 * @param (stdClass)	$args 	is empty
 * @return (void)
 **/
function sts_settings_metabox_email_notification_render( $args ){
	$settings = get_option( 'sts-core-settings' );
	$owner_checked = '';
	if( isset( $settings['email']['notifiy-ticketowner'] ) && $settings['email']['notifiy-ticketowner'] == 1 )
		$owner_checked = 'checked="checked"';
	$agent_checked = '';
	if( isset( $settings['email']['notifiy-agent'] ) && $settings['email']['notifiy-agent'] == 1 )
		$agent_checked = 'checked="checked"';
?>
<section>
	<label for="notify-ticketowner-on-answer"><?php _e( 'Notifiy ticket owner on answer', 'sts' ); ?></label>
	<input id="notify-ticketowner-on-answer" type="checkbox" name="email[notifiy-ticketowner]" value="1" <?php echo $owner_checked; ?> />
</section>
<section>
	<label for="notify-agent-on-answer"><?php _e( 'Notifiy agent owner on answer', 'sts' ); ?></label>
	<input id="notify-agent-on-answer" type="checkbox" name="email[notifiy-agent]" value="1" <?php echo $agent_checked; ?> />
</section>
<?php
}

/**
 * Update the ticket status
 *
 * @since 1.0.0
 *
 * @param (array)	$post_data 	the POST data
 * @param (int) 	$post_id 	the post ID
 * @return (void)
 **/
add_action( 'ticket-admin-update', 'sts_admin_update_status', 10, 2 );
function sts_admin_update_status( $post_data, $post_id ){	
	if( ! current_user_can( 'update_tickets' ) && ! current_user_can( 'assign_agent_to_ticket' )  )
		return;

	if( 
		current_user_can( 'update_tickets' ) &&
		isset( $post_data['ticket-status'] ) && 
		is_numeric( $post_data['ticket-status'] ) 
	)
		$status_update = update_post_meta( $post_id, 'ticket-status', $post_data['ticket-status'] );

	if( 
		current_user_can( 'assign_agent_to_ticket' ) &&
		isset( $post_data['ticket-agent'] ) && 
		is_numeric( $post_data['ticket-agent'] ) 
	)
		$agent_update = update_post_meta( $post_id, 'ticket-agent', $post_data['ticket-agent'] );

	/**
	* Status has been updated
	*
	* @since 1.0.0
	*
	* @param (int) 		Ticket ID
	* @param (array) 	POST data
	*/
	if( isset( $status_update ) && false !== $status_update )
		do_action( 'sts-ticket-status-updated', $post_id, $post_data );

	/**
	* Assigned agent changed
	*
	* @since 1.0.0
	*
	* @param (int) 		Ticket ID
	* @param (array) 	POST data
	*/
	if( isset( $agent_update ) && false !== $agent_update )
		do_action( 'sts-ticket-agent-updated', $post_id, $post_data );
}

/**
 * Renders the status metabox
 *
 * @since 1.0.0
 *
 * @param (object)	$post
 * @return (void)
 **/
function sts_metabox_status_render( $post ){
	$current_status_index = (int) get_post_meta( get_the_ID(), 'ticket-status', true );
	$current_status = sts_translate_status( $current_status_index );
	$status_array = sts_get_statusArr();

	$standard_agent = 1;
	$settings = get_option( 'sts-core-settings' );
	if( isset( $settings['user']['standard-agent'] ) )
		$standard_agent = $settings['user']['standard-agent'];
	$current_agent = (int) get_post_meta( get_the_ID(), 'ticket-agent', true );

	if( ! $current_agent ){
		$current_agent = $standard_agent;
	}
	$agents = sts_get_possible_agents();
	foreach( $agents as $agent ){
		if( ! is_string( $agent ) && $agent->ID == $current_agent ){
			$current_agent = $agent;
			break;
		}
	}


	if( ! current_user_can( 'update_tickets' ) && ! current_user_can( 'assign_agent_to_ticket' ) ):
	?>
	<p><?php printf( __( 'Current status: %s', 'sts' ), $current_status ); ?></p>
	<p><?php printf( __( 'Current agent: %s', 'sts' ), $current_agent->display_name ); ?></p>
	<?php
	else:
	?>
	<?php if( current_user_can( 'update_tickets' ) ): ?>
	<p>
		<label for="ticket-status"><?php _e( 'Update status', 'sts'); ?>:</label>
		<select id="ticket-status" name="t[ticket-status]">
			<?php foreach( $status_array as $status_index => $status ): ?>
			<option <?php selected( $status_index, $current_status_index ); ?> value="<?php echo $status_index; ?>">
				<?php echo $status; ?>
			</option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php 
	endif;
	if( current_user_can( 'assign_agent_to_ticket' ) ): ?>
		<p>
			<label for="ticket-agent"><?php _e( 'Current agent', 'sts'); ?>:</label>
			<select id="ticket-agent" name="t[ticket-agent]">
			<?php foreach( $agents as $agent ): ?>
			<?php if( is_string( $agent ) ): ?>
				<option disabled>----</option>
			<?php else: ?>
				<option <?php selected( $current_agent->ID, $agent->ID ); ?> value="<?php echo $agent->ID; ?>">
					<?php echo $agent->display_name; ?>
				</option>
			<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</p>
	<?php endif; ?>
	<button class="button button-primary button-large"><?php _e( 'Update', 'sts' ); ?></button>
	<?php
	endif;
}


/**
 * Notifiy the ticket owner in case of an answer
 *
 * @since 1.0.0
 *
 * @param (int) 	$ticket_id 	the ticket ID
 * @param (int) 	$answer_id 	the answer ID
 * @param (array) 	$post_data 	the post data
 * @return (void)
 **/
add_action( 'sts-after-ticket-answer-save', 'sts_send_notification_email_to_ticket_owner', 10, 3 );
function sts_send_notification_email_to_ticket_owner( $ticket_id, $answer_id, $post_data ){
	global $post;

	//Check if we want to notify the ticket owner
	$settings = get_option( 'sts-core-settings' );
	if( ! isset( $settings['email']['notifiy-ticketowner'] ) || 1 != $settings['email']['notifiy-ticketowner'] )
		return;

	//Get the ticket
	$args = array(
		'post_type' => 'ticket',
		'post_status' => array( 'draft' ),
		'p' => $ticket_id
	);

	$query = new WP_Query( $args );
	if( ! $query->have_posts() )
		return;

	$query->the_post();
	$ticket = $post;

	//Get the answer
	$args = array(
		'post_type' => 'ticket',
		'post_status' => array( 'draft' ),
		'p' => $answer_id
	);

	$query = new WP_Query( $args );
	if( ! $query->have_posts() )
		return;

	$query->the_post();
	$answer = $post;

	if( $answer->post_author == $ticket->post_author )
		return;


	$ticket_owner = get_user_by( 'id', $ticket->post_author );
	$subject = $answer->post_title;
	$text = $answer->post_content . PHP_EOL . PHP_EOL;
	$text .= sprintf( __( 'Please click the link to reply:', 'sts' ) ) . ' ';
	$view_ticket = admin_url( "admin.php?page=sts&action=single&ID=" . $ticket->ID . '#answer-' . $answer->ID );
	/**
 	* Filters the URL where the ticket can be read
 	*
 	* @since 1.0.0
 	*
 	* @param 	(string) 	$view_ticket 	the URL
 	* @return 	(string) 	$view_ticket 	the URL
 	*/
	$view_ticket = apply_filters( 'sts-view-ticket-url', $view_ticket, $ticket->ID );

	$text .= '<a href="' . $view_ticket . '">' . $view_ticket . '</a>';

	$headers = array();
	$attachments = array();

	if( ! sts_mail( $ticket_owner->data->user_email, $subject, $text, $headers, $attachments ) )
		die( 'Nicht verschickt :/');
	wp_reset_query();
}


/**
 * Notifiy the agent in case the ticket owner writes an answer
 *
 * @since 1.0.0
 *
 * @param (int) 	$ticket_id 	the ticket ID
 * @param (int) 	$answer_id 	the answer ID
 * @param (array) 	$post_data 	the post data
 * @return (void)
 **/
add_action( 'sts-after-ticket-answer-save', 'sts_send_notification_email_to_agent', 10, 3 );
function sts_send_notification_email_to_agent( $ticket_id, $answer_id, $post_data ){
	global $post;

	//Check if we want to notify the ticket owner
	$settings = get_option( 'sts-core-settings' );
	if( ! isset( $settings['email']['notifiy-agent'] ) || 1 != $settings['email']['notifiy-agent'] )
		return;

	//Get the answer
	$args = array(
		'post_type' => 'ticket',
		'post_status' => array( 'draft' ),
		'p' => $answer_id
	);

	$query = new WP_Query( $args );
	if( ! $query->have_posts() )
		return;

	$query->the_post();
	$answer = $post;
	
	$agent = get_user_by( 'id', get_post_meta( $ticket_id, 'ticket-agent', true ) );

	if( $answer->post_author == get_current_user_id() )
		return;

	$subject = $answer->post_title;
	$text = $answer->post_content . PHP_EOL . PHP_EOL;
	$text .= sprintf( __( 'Please click the link to reply:', 'sts' ) ) . ' ';
	$view_ticket = admin_url( "admin.php?page=sts&action=single&ID=" . $ticket_id . '#answer-' . $answer->ID );
	/**
 	* Filters the URL where the ticket can be read
 	*
 	* @since 1.0.0
 	*
 	* @param 	(string) 	$view_ticket 	the URL
 	* @return 	(string) 	$view_ticket 	the URL
 	*/
	$view_ticket = apply_filters( 'sts-view-ticket-url', $view_ticket, $ticket_id );

	$text .= '<a href="' . $view_ticket . '">' . $view_ticket . '</a>';

	$headers = array();
	$attachments = array();

	if( ! sts_mail( $agent->data->user_email, $subject, $text, $headers, $attachments ) )
		die( 'Nicht verschickt :/');
	wp_reset_query();
}

/**
 * Answer a ticket
 *
 * @since 1.0.0
 *
 * @param (array)	$post_data 	the POST data
 * @param (int) 	$post_id 	the post ID
 * @return (void)
 **/
add_action( 'ticket-admin-update', 'sts_admin_send_ticket_answer', 10, 2 );
function sts_admin_send_ticket_answer( $post_data, $post_id ){
	if( ! isset( $post_data['answer'] ) )
		return;
	$answer = trim( $post_data['answer'] );
	$subject = sprintf( __( 'Re: [Ticket #%d]', 'sts' ), $_GET['ID'] ) . ' ' . trim( $post_data['subject'] );

	if( empty( $answer ) )
		return;

	$args = array(
		'post_title'	=> $subject,
		'post_type'		=> 'ticket',
		'post_content'	=> $answer,
		'post_parent'	=> $_GET['ID']
	);
	$post_id = wp_insert_post( $args );

	/**
	* After the ticket has been saved
	*
	* @since 1.0.0
	*
	* @param (int) 		Ticket ID
	* @param (int) 		Answer ID
	* @param (array) 	POST data
	*/
	do_action( 'sts-after-ticket-answer-save', $_GET['ID'], $post_id, $post_data );
}

/**
 * Renders the message metabox
 *
 * @since 1.0.0
 *
 * @param (object)	$post
 * @return (void)
 **/
function sts_metabox_message_render( $post ){
	$ticketauthor_id = $post->post_author;
	?>
			<div class="ticket-content">
				<p class="date">
					<?php printf( __( 'by %s', 'sts' ), get_the_author() ); ?>,
					<?php the_date(); ?>, <?php the_time(); ?>
				</p>
				<?php 

				$pattern = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`\!()\[\]{};:\'".,<>?«»“”‘’]))';     
				echo nl2br( preg_replace("!$pattern!i", "<a href=\"\\0\" rel=\"nofollow\" target=\"_blank\">\\0</a>", get_the_content() ) ); ?>
			</div>
			<ul class="ticket-history">
				<?php
					$args = array(
						'post_type'		=> 'ticket',
						'post_status'	=> 'any',
						'post_parent'	=> get_the_ID(),
					);

					$subquery = new WP_Query( $args );
					foreach( $subquery->posts as $post ):
						$user = get_userdata( $post->post_author );
						?>
						<li class="history-item <?php if( $ticketauthor_id == $post->post_author ) echo 'by-ticketowner'; ?>" id="answer-<?php echo $post->ID; ?>">
							<h3>
								<span>
									<?php printf( __( 'by %s', 'sts' ), $user->data->display_name ); ?>,
									<?php echo get_the_time( get_option( 'date_format' ), $post->ID ) . ', ' . get_the_time( get_option( 'time_format' ), $post->ID ); ?>
								</span>
								<?php echo get_the_title( $post->ID ); ?>
							</h3>
							<div class="entry">
								<?php
								$pattern = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`\!()\[\]{};:\'".,<>?«»“”‘’]))';     
								$post->post_content = preg_replace("!$pattern!i", "<a href=\"\\0\" rel=\"nofollow\" target=\"_blank\">\\0</a>", $post->post_content );
								echo apply_filters( 'the_content', $post->post_content ); ?>
							</div>
						</li>
					<?php 
					endforeach;
				?>
			</ul>
			<?php
}

/**
 * Renders the answer metabox
 *
 * @since 1.0.0
 *
 * @param (object)	$post
 * @return (void)
 **/
function sts_metabox_answer_render( $post ){
	?>
		<div class="ticket-answer">
			<section>
				<label for="answer-subject"><?php _e( 'Subject', 'sts' ); ?></label>
				<div class="input-pre">
					<span>Re: [Ticket #<?php echo $_GET['ID']; ?>]</span>
					<input id="answer-subject" name="t[subject]" type="text" />
				</div>
			</section>
			<section>
				<label for="answer-answer"><?php _e( 'Message', 'sts' ); ?></label>
				<div>
					<textarea id="answer-answer" name="t[answer]"></textarea>
				</div>
			</section>
			<section>
				<button class="button button-primary button-large"><?php _e( 'Answer', 'sts' ); ?></button>
			</section>
		</div>
	<?php
}

/**
 * Renders the privatenote metabox
 *
 * @since 1.0.0
 *
 * @param (object)	$post
 * @return (void)
 **/
function sts_metabox_privatenote_render( $post ){
	?>
		<div class="ticket-privatenote">
			<textarea name="t[privatenote]"><?php echo get_post_meta( $post->ID, 'ticket-privatenote', true ); ?></textarea>
			<small><?php _e( 'Private notes can only be seen by other agents and admins. Not by the ticket owner himself.', 'sts' ); ?></small>
			<button class="button button-primary button-large"><?php _e( 'Update', 'sts' ); ?></button>
		</div>
	<?php
}

/**
 * Update the private note
 *
 * @since 1.0.0
 *
 * @param (array)	$post_data 	the POST data
 * @param (int) 	$post_id 	the post ID
 * @return (void)
 **/
add_action( 'ticket-admin-update', 'sts_admin_update_privatenote', 10, 2 );
function sts_admin_update_privatenote( $post_data, $post_id ){	
	if( isset( $post_data['privatenote'] ) )
		update_post_meta( $post_id, 'ticket-privatenote', $post_data['privatenote'] );
}

?>