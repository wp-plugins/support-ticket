<?php

	/**
	* Returns ticket status
	*
 	* @since 	1.0.0
 	* @param	(int) $post_id	Post ID
 	* @return   (string) Status
	*/	
	function sts_get_the_status( $post_id = null ){
		if( null == $post_id )
			$post_id = get_the_ID();

		return sts_translate_status( get_post_meta( $post_id, 'ticket-status', true ) );
	}
		function sts_the_status( $post_id = null ){
			if( null == $post_id )
				$post_id = get_the_ID();

			echo sts_get_the_status( $post_id );
		}


	/**
	* Returns if a ticket has been read by an agent
	*
 	* @since 	1.0.0
 	* @param	(int) $post_id	Post ID
 	* @return   (string) read/unread
	*/	
	function sts_get_the_ticket_read( $post_id = null ){
		if( null == $post_id )
			$post_id = get_the_ID();

		$read = get_post_meta( $post_id, 'ticket-read', true );
		if( ! empty( $read ) )
			return 'read';

		return 'unread';		
	}
		function sts_the_ticket_read( $post_id = null ){
			if( null == $post_id )
				$post_id = get_the_ID();

			echo sts_get_the_ticket_read( $post_id );
		}
?>