<?php
	/**
	* Plugin Activation Hook
	* Register new User role
	*
 	* @since 	1.0.0
	*/
	function sts_on_plugin_activation() {
		$role = add_role( 'ticket-user', __( 'Ticket User', 'sts' ), array( 'read' => true, 'read_own_tickets' => true ) );
		remove_role( 'ticket-agent' );
		$role = add_role( 'ticket-agent', __( 'Ticket Agent', 'sts' ), array( 
			'read' => true, 
			'read_own_tickets' => true, 
			'read_assigned_tickets' => true, 
			'read_other_tickets' => false, 
			'update_tickets' => true,
		) );

		$admin_role = get_role( 'administrator' );
		$admin_role->add_cap( 'read_own_tickets', true );
		$admin_role->add_cap( 'read_assigned_tickets', true );
		$admin_role->add_cap( 'read_other_tickets', true );
		$admin_role->add_cap( 'update_tickets', true );
		$admin_role->add_cap( 'assign_agent_to_ticket', true );
	}
	register_activation_hook( STS_FILE, 'sts_on_plugin_activation' );

	/**
	 * Add the metafields to the "create ticket"-form
	 *
	 * @since 1.0.0
	 *
	 * @param (array) 	$fields 	the existing fields
	 * @return (array) 	$fields 	the extended fields
	 **/
	add_filter( 'sts-create-ticket-formfields', 'sts_metafields' );
	function sts_metafields( $fields ){
		$add = get_option( 'sts-metafields', array() );
		foreach( $add as $field ){
			$type = '';
			if( $field['tag'] == 'input' )
				$type = 'text';
			$single = array(
				'label'		=> $field['label'],
				'id'		=> $field['id'],
				'tag'		=> $field['tag'],
				'name'		=> $field['metakey'],
				'metakey'	=> $field['metakey'],
				'type'		=> $type,
				'value'		=> '',
				'error'		=> false,
				'required'	=> false
			);

			if( $field['tag'] == 'select' ){
				$choicesARR = $field['choices'];
				$choices = array();
				foreach( $choicesARR as $key => $val )
					$choices[ $val ] = $val;
				
				$single['choices'] = $choices;
			}
			$fields[] = $single;
		}
		return $fields;
	}

	add_action( 'sts-createticket-after', 'sts_save_metafields', 10, 2 );
	function sts_save_metafields( $post_id, $post_data ){
		$metafields = get_option( 'sts-metafields', array() );
		foreach( $metafields as $field ){
			if( isset( $post_data[ $field['metakey'] ] ) )
				update_post_meta( $post_id, $field['metakey'], sanitize_text_field( $post_data[ $field['metakey'] ] ) );
		}
	}
?>