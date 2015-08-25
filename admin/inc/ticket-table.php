<?php
	if( ! class_exists( 'WP_List_Table' ) )
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


	/**
 	* Define the standard column for the table
	*
 	* @since 	1.0.6
 	*
 	* @param (array)	$columns
 	* @return (array)	$columns
	 **/
	function sts_standard_table_column( $column, $screen ) {
		$column = 'subject';
    	/**
 		* Filters the standard table column
 		* which was introduced in WP 4.3
 		* see https://make.wordpress.org/core/2015/08/08/list-table-changes-in-4-3/
 		*
 		* @since 1.0.5
 		*
 		* @param 	(string) 	$column 	the columns ID
 		* @return 	(string) 	$column 	the columns ID
 		*/
		$column = apply_filters( 'sts-standard-table-column', $column );
		return $column;
	}

	/**
 	* Add the standard columns
	*
 	* @since 	1.0.0
 	*
 	* @param (array)	$columns
 	* @return (array)	$columns
	 **/
	add_filter( 'sts-tickets-table-columns', 'sts_ticket_table_columns', 1, 1 );
	function sts_ticket_table_columns( $columns ){
			if( current_user_can( 'read_assigned_tickets' ) )
				$columns = array_merge( $columns, array( 'unread'		=> '' ) );

  			return array_merge( $columns, array(    			
    			'cb'			=> '<input type="checkbox" />',		
    			'subject'		=> __( 'Subject', 'sts' ),
    			'ID'			=> __( 'ID', 'sts' ),
    			'date'			=> __( 'Date', 'sts' ),
    			'from'			=> __( 'From', 'sts' ),
    			'status'		=> __( 'Status', 'sts' ),
  			) );
	}

	/**
 	* Render the standard columns
	*
 	* @since 	1.0.0
 	*
 	* @param (string)	$current 		current output
 	* @param (stdClass)	$item 	 		current post item
 	* @param (string)	$column_name 	the column ID
 	* @return (string)			 		updated output
	 **/
	add_filter( 'sts-tickets-table-column', 'sts_ticket_table_column_render', 1, 3 );
	function sts_ticket_table_column_render( $current, $item, $column_name ){
		switch( $column_name ){
			case 'unread':
				if( 'unread' == sts_get_the_ticket_read( $item->ID ) && current_user_can( 'read_assigned_tickets' ) )
					return '<a href="admin.php?page=sts&action=single&ID=' . $item->ID . '"><span title="' . __( 'Unread', 'sts' ) . '" class="ticket-unread">' . __( 'Unread', 'sts' ) . '</span></a>';
				return '';
			case 'ID':
				return '<a href="admin.php?page=sts&action=single&ID=' . $item->ID . '">' . $item->ID . '</a>';
			case 'subject':
				return '<a href="admin.php?page=sts&action=single&ID=' . $item->ID . '">' . get_the_title( $item->ID ) . '</a>';;
			case 'from':
				if( $item->post_author == 0 ) return 0;

				$user = get_userdata( $item->post_author );
				if( ! $user )
					return __( 'User not found :/', 'sts' );
				return $user->data->display_name . ' &lt;' . $user->data->user_email . '&gt;';
			case 'date':
				return get_the_time( get_option( 'date_format' ), $item->ID ) . ', ' . get_the_time( get_option( 'time_format' ), $item->ID );
			case 'status':
				return sts_get_the_status( $item->ID );
		}
	}

	/**
 	* Select only specific ticket status
	*
 	* @since 	1.0.0
 	*
 	* @param (array)	$columns
 	* @return (array)	$columns
	 **/
	add_filter( 'sts-tickets-table-postargs', 'sts_tickets_table_postargs' );
	function sts_tickets_table_postargs( $args ){
		if( !isset( $_GET['status'] ) || (int) $_GET['status'] == -1 )
			return $args;

		$status = (int) $_GET['status'];

		if( ! isset( $args['meta_query'] ) )
			$args['meta_query'] = array();

		$args['meta_query'][] = array(
			'key' => 'ticket-status',
			'value' => $status
		);

		return $args;
	}

	/**
 	* Add status filter
	*
 	* @since 	1.0.0
 	*
 	* @param (string)	$which 		'top' or 'bottom'
 	* @return (void)			 		
	 **/
	add_action( 'sts-extra-tablenav', 'sts_table_add_status_filter' );
	function sts_table_add_status_filter( $which ){

			echo '<label class="screen-reader-text" for="status-filter">' . __( 'Filter by ticket status', 'sts' ) . '</label>';
			echo '<select id="status-filter" name="status">';
			$status_array = sts_get_statusArr();
			$current_status_index = -1;
			if( isset( $_GET['status'] ) )
				$current_status_index = (int) $_GET['status'];

			echo '<option value="-1" ' . selected( -1, $current_status_index, false ) . '>' . __( 'Every status', 'sts' ) . '</option>';			
			foreach( $status_array as $status_index => $status ){
				echo '<option ' . selected( $status_index, $current_status_index, false ) . ' value="' . $status_index . '">' . $status . '</option>';

			}
			echo '</select>';
			submit_button( __( 'Filter' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
			echo '';
	}

	class STS_Tickets_Table extends WP_List_Table {
		function get_bulk_actions() {
  			$actions = array();
  			if( current_user_can( 'delete_other_tickets' ) )
    			$actions['delete'] = __( 'Delete' );
    		/**
 			* Filters the bulk actions for the ticket overview table
 			*
 			* @since 1.0.5
 			*
 			* @param 	(array) 	$actions 	the actions
 			* @return 	(array) 	$actions 	the actions
 			*/
  			return apply_filters( 'sts-tickets-table-bulk-actions', $actions );
		}

		function column_cb($item) {
       		return sprintf(
            	'<input type="checkbox" name="ticket[]" value="%s" />', $item->ID
        	);    
    	}

		function get_columns(){
			$columns = array();
			/**
			* Filter the columns of the table
			*
			* @since 1.0.0
			*
			* @param (array) 	$columns 	the status array
			* @return (array) 	$columns 	the status array
			*/
  			return apply_filters( 'sts-tickets-table-columns', $columns );
		}

		function get_data(){
			global $post;
			$data = array();

			$paged = 1;
			if( isset( $_GET['paged'] ) )
				$paged = (int) $_GET['paged'];

			$args = array(
				'post_type'			=> 'ticket',
				'post_status'		=> 'any',
				'post_parent'		=> 0,
				'paged'				=> $paged
			);

			/**
			* Filter the Query args for the table
			*
			* @since 1.0.0
			*
			* @param (array) 	$args 	the query args
			* @return (array) 	$args 	the query args
			*/
			$args = apply_filters( 'sts-tickets-table-postargs', $args );

			if( ! current_user_can( 'read_other_tickets' ) && ! current_user_can( 'read_assigned_tickets' ) )
				$args['author'] = get_current_user_id();
			elseif( ! current_user_can( 'read_other_tickets' ) )
				$args['meta_query'] = array(
					array(
						'key' => 'ticket-agent',
						'value' => get_current_user_id()
					)
				);
			#echo '<pre>';print_r( $args );echo '</pre>';
			$query = new WP_Query( $args );
			#echo '<pre>';print_r( $query );echo '</pre>';
			while( $query->have_posts() ){
				$query->the_post();
				$data[] = $post;
			}

			$this->set_pagination_args( array(
    			'total_items' => $query->found_posts,
    			'per_page'    => 10
  			) );

			return $data;
		}

		function no_items() {
 			_e( 'No tickets found.', 'sts' );
		}

		function extra_tablenav( $which ) {			
			echo '<div class="alignleft actions">';
			/**
			 * Action, to add tablenav elements
			 *
			 * @since 1.0.0
			 *
			 * @param (string) 	$which 	'top' or 'bottom'
			 **/
			do_action( 'sts-extra-tablenav', $which );
			echo '</div>';
		}

		function prepare_items() {
			$columns = $this->get_columns();
  			$hidden = array();
  			$sortable = array();
  			$this->_column_headers = array($columns, $hidden, $sortable);
  			$this->items = $this->get_data();
		}

		function single_row( $item ){
			$tr_classes = array(
				'status-' . sanitize_key( sts_get_the_status( $item->ID, 'class' ) )
			);
			if( 'unread' == sts_get_the_ticket_read( $item->ID ) && current_user_can( 'read_other_tickets' ) )
				$tr_classes[] = 'ticket-unread';

			echo '<tr class="' . implode( ' ', $tr_classes ) . '">';
			echo $this->single_row_columns( $item );
			echo '</tr>';

		}

		function display_tablenav( $which ) {
			?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">
				<?php if( 'top' == $which ): ?>
				<form method="get">
 					<input type="hidden" name="page" value="sts" />
					<?php $this->extra_tablenav( $which ); ?>
				</form>

				<form method="post">
					<?php wp_nonce_field( 'sts-bluk-action', 't-nonce' ); ?>
					<input type="hidden" name="sts-action" value="bulk-action" />
					<div class="alignleft actions bulkactions">
						<?php $this->bulk_actions( $which ); ?>
					</div>
					
				<?php endif; ?>
				<?php $this->pagination( $which ); ?>
				<br class="clear" />
			</div>
			<?php
		}

		function column_default( $item, $column_name ) {
			$rendered = '';

			/**
			* Filter the column rendering output
			* @since 1.0.0
			*
			* @param (string) 	$rendered		the rendered output
			* @param (stdClass) $item 			the post item
			* @param (string) 	$column_name 	the column name
			* @return (string) 	$rendered 		the rendered output
			*/
			$rendered = apply_filters( 'sts-tickets-table-column', $rendered, $item, $column_name );

			
			/**
			* Filter the column rendering output for a specific column
			* @since 1.0.0
			*
			* @param (string) 	$rendered		the rendered output
			* @param (stdClass) $item 			the post item
			* @return (string) 	$rendered 		the rendered output
			*/
			return apply_filters( 'sts-tickets-table-column-' . $column_name, $rendered, $item );
  		}
	}

	
?>