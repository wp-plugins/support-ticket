<?php
require_once( dirname( __FILE__ ) . '/inc/metaboxes.php' );
require_once( dirname( __FILE__ ) . '/inc/functions-settings.php' );
add_thickbox(); 
do_action( 'add_meta_boxes' );
$sts_settings = new Sts_Settings();

if( isset( $_POST['t-action'] ) && $_POST['t-action'] == 'settings' )
	$sts_settings->update();

wp_enqueue_script('common');
wp_enqueue_script('wp-lists');
wp_enqueue_script('postbox');

?><div id="sts-wrap" class="wrap">
	<h2><?php _e( 'Settings', 'sts' ); ?></h2>
	<?php
		$sts_settings->render_error();
	?>
	<?php
		if( isset( $_GET['updated'] ) ):
	?>
	<div id="message" class="updated notice is-dismissible"><p><?php _e( 'Settings updated.', 'sts' ); ?></p></div>
<?php endif; ?>

	<div id="poststuff">
		<div id="sts-tabs">
			<ul>
				<?php $sts_settings->render_tabs(); ?>
			</ul>
				<?php $sts_settings->render_content(); ?>
		</div>
	</div>
</div>

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