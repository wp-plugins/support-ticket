<div id="sts-wrap" class="wrap <?php if( isset( $_GET['action'] ) ) echo 'ticket-' . $_GET['action']; ?>">
	<?php 
	if( isset( $_GET['action'] ) ):
		if( 'single' == $_GET['action'] ):
			require_once( 'ticket-single.php' );
		endif;
	else: 
		require_once( dirname( __FILE__ ) . '/inc/ticket-table.php' );?>

	<h1>
		<img src="<?php echo STS_URL; ?>assetts/logo-small.svg" height="25px" />
		<?php _e( 'Tickets', 'sts' ); ?>
	</h1>
	<?php
		$table = new STS_Tickets_Table();
 		 $table->prepare_items(); 
  		$table->display(); 
	endif; ?>
</div>