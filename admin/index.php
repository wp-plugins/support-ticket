<div id="sts-wrap" class="wrap <?php if( isset( $_GET['action'] ) ) echo 'ticket-' . $_GET['action']; ?>">
	<?php 
	$sts_actions = array( 'single' );
	if( isset( $_GET['action'] ) && in_array( $_GET['action'], $sts_actions ) ):
		if( 'single' == $_GET['action'] ):
			require_once( 'ticket-single.php' );
		endif;
	else: 
		require_once( dirname( __FILE__ ) . '/inc/ticket-table.php' );?>

	<h2>
		<img src="<?php echo STS_URL; ?>assetts/logo-small.svg" height="25px" />
		<?php _e( 'Tickets', 'sts' ); ?>
	</h2>
	<?php
		if( isset( $_GET['updated'] ) ):
	?>
		<div id="message" class="updated notice is-dismissible"><p><?php _e( 'Updated.', 'sts' ); ?></p></div>
	<?php endif; ?>
	<?php

		add_filter( 'list_table_primary_column', 'sts_standard_table_column', 10, 2 );
		$table = new STS_Tickets_Table();
 		$table->prepare_items(); ?>
 		<?php $table->display(); ?>
		</form>
	<?php endif; ?>
</div>