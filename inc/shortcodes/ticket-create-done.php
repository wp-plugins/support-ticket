<?php $user = wp_get_current_user(); 
$view_ticket = admin_url( "admin.php?page=sts&action=single&ID=" . $_GET['ticket-id'] );
/**
 * Filters the URL where the ticket can be read
 *
 * @since 1.0.0
 *
 * @param 	(string) 	$view_ticket 	the URL
 * @return 	(string) 	$view_ticket 	the URL
 */
$view_ticket = apply_filters( 'sts-view-ticket-url', $view_ticket, $_GET['ticket-id'] );
?>
<p><?php printf( __( 'Hello %s', 'sts' ), $user->data->display_name ); ?>,</p>
<p>
	<?php _e( 'We have received your ticket and will contact you as soon as possible.', 'sts' ); ?>
	<?php printf( __( 'Your ticket is filed as #%d.', 'sts' ), (int) $_GET['ticket-id'] ); ?>
	<a href="<?php echo $view_ticket; ?>">
		<?php _e( 'Click here to see your ticket.', 'sts' ); ?>
	</a>
</p>
<p><?php _e( 'Thank you.', 'sts' ); ?></p>
