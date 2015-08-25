<?php
if( ! isset( $_GET['ID'] ) || ! is_numeric( $_GET['ID'] ) || ! sts_current_user_can_read_ticket( $_GET['ID'] ) )
	wp_die( __( 'Something went wrong :/', 'sts' ) );



$_GET['ID'] = (int) $_GET['ID'];



//Add metaboxes functionality
require_once( dirname( __FILE__ ) . '/inc/metaboxes.php' );

do_action( 'add_meta_boxes' );
wp_enqueue_script('common');
wp_enqueue_script('wp-lists');
wp_enqueue_script('postbox');

if( isset( $_POST["t-action"] ) && $_POST["t-action"] == 'ticket-admin-update' ){
	if( wp_verify_nonce( $_POST['t-nonce'], 'ticket-admin-update-' . get_current_user_id() ) && isset( $_POST['t'] ) ){

		/**
		* Filter the post data before the update
		*
		* @since 1.0.0
		*
		* @param (array) 	$_POST['t'] 	The post data
		* @param (int) 		$_GET['ID'] 	The ticket ID
		* @return (array) 	$post_data 		The post data
		*/
		$post_data = apply_filters( 'sts-ticket-admin-update-postdata', $_POST['t'], $_GET['ID'] );
		do_action( 'ticket-admin-update', $post_data, $_GET['ID'] );
		?><script>location.href='?page=sts&action=single&ID=<?php echo $_GET['ID']; ?>&updated=1'</script>'<?php
	}
	die();
}

?>
<?php if( isset( $_GET['updated'] ) ): ?>
<div id="message" class="updated notice is-dismissible"><p><?php _e( 'Ticket updated.', 'sts' ); ?></p></div>
<?php endif; ?>
<?php if( isset( $_GET['ticket-new'] ) ): ?>
<div id="message" class="updated notice is-dismissible"><p><?php _e( 'Ticket created.', 'sts' ); ?></p></div>
<?php endif; ?>
<form method="post">
	<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
	<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
	<input type="hidden" name="t-action" value="ticket-admin-update" />
	<?php wp_nonce_field( 'ticket-admin-update-' . get_current_user_id(), 't-nonce' ); ?>
	
	<?php
	$args = array(
		'post_type'			=> 'ticket',
		'post_status'		=> array( 'draft' ),
		'p'					=> (int) $_GET['ID'],
	);
	
	if( ! current_user_can( 'read_other_tickets' ) && ! current_user_can( 'read_assigned_tickets' ) )
		$args['author'] = get_current_user_id();
	elseif( ! current_user_can( 'read_other_tickets' ) )
		$args['meta_query'] = array(
			'meta_key' => 'ticket-agent',
			'meta_value' => get_current_user_id()
		);

	$query = new WP_Query( $args );
	if( !$query->have_posts() ):
	?>
	<h1>
		<img src="<?php echo STS_URL; ?>assetts/logo-small.svg" height="25px" />
		<?php _e( 'Ticket not found :/', 'sts' ); ?>
	</h1>
	<?php else: 
		$query->the_post();

		//If the assigned ticket agent reads this ticket,
		//The postmeta information the ticket has been read will be set.
		if( get_current_user_id() == (int) get_post_meta( get_the_ID(), 'ticket-agent', true ) )
			update_post_meta( get_the_ID(), 'ticket-read', 1 );
	?>
	<h2>
		<img src="<?php echo STS_URL; ?>assetts/logo-small.svg" height="25px" />
		<?php the_title(); ?>
	</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content" style="position: relative;">

			<?php 
				global $post;
				do_meta_boxes( 'ticket-boxes', 'normal', $post ); 
			?>
			</div>
			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( 'ticket-boxes', 'side', $post ); ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</form>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('ticket-boxes');
	});
	//]]>
</script>