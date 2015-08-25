<?php
	$fields = sts_get_create_ticket_form_fields( 'admin' );
	if( isset( $_GET['ticket-created'] ) ):
		?>
		<script>location.href="?page=sts&action=single&ticket-new=1&ID=<?php echo $_GET['ticket-id']; ?>";</script>
		<?php
		die();
	endif;
?>
<div id="sts-wrap" class="wrap">
	<h2>
		<img src="<?php echo STS_URL; ?>assetts/logo-small.svg" height="25px" />
		<?php _e( 'Create a new ticket', 'sts' ); ?>
	</h2>
	<form method="post" class="ticket create" enctype="multipart/form-data">
		<?php if( isset( $_SESSION['tickets']['error'] ) && is_wp_error( $_SESSION['tickets']['error'] ) ): ?>
		<div class="error"><p><?php echo $_SESSION['tickets']['error']->get_error_message(); ?></p></div>
		<?php endif; ?>

		<input type="hidden" name="t-action" value="ticket-create" />
		<?php wp_nonce_field( 'ticket-create', 't-nonce' ); ?>

		<?php foreach( $fields as $field )
			sts_render_form_field( $field, 'admin' );
		?>
		<section>
			<label>&ensp;</label>
			<button class="button button-primary button-large"><?php _e( 'Send', 'sts' ); ?></button>
		</section>
	</form>
</div>