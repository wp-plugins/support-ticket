<form method="post" class="ticket create" enctype="multipart/form-data">


	<?php if( isset( $_SESSION['tickets']['error'] ) && is_wp_error( $_SESSION['tickets']['error'] ) ): ?>
	<div class="error"><?php echo $_SESSION['tickets']['error']->get_error_message(); ?></div>
	<?php endif; ?>


	<input type="hidden" name="t-action" value="ticket-create" />
	<?php wp_nonce_field( 'ticket-create', 't-nonce' ); ?>

	<?php if( is_user_logged_in() ): 
		$user = wp_get_current_user();
	?>
	<p><?php printf( __( 'Hello %s', 'sts' ), $user->data->display_name ); ?></p>

	<?php elseif( isset( $_SESSION['ticket']['action'] ) && $_SESSION['ticket']['action'] == 'ask-login' ): ?>

	<p><?php
		printf( __( 'It seems, you have already an account registered with your mail adress %s', 'sts' ), $_SESSION['ticket']['ticket-create']['email'] ); 
		?>
	</p>
	<p><a href="<?php echo wp_login_url( get_permalink() ); ?>">
		<?php _e( 'Please log in before you proceed.', 'sts' ); ?>
		</a>
	</p>
	
	<?php endif; 

	if( 
		is_user_logged_in() || 
			( 
				! isset( $_SESSION['ticket']['action'] ) ||
				$_SESSION['ticket']['action'] != 'ask-login' 
			) 
		):?>
		<?php if( $args['type'] == 'table' ): ?>
		<table>
			<tbody>
		<?php endif; ?>
		<?php foreach( $fields as $field ):
			sts_render_form_field( $field, 'shortcode', true, $args );
		?>
		<?php endforeach; ?>
		<?php if( $args['type'] == 'table' ): ?>
			</tbody>
		</table>
		<?php endif; ?>
		<p><button><?php _e( 'Send', 'sts' ); ?></button></p>
	<?php endif; ?>
</form>