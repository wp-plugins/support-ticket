<?php
	
	/**
 	 * Add the core settings for the plugin
	 *
 	 * @since 	1.0.0
 	 *
 	 * @param (array) 	$sts_setting_content 	The setting sections
 	 * @return (array) 	$sts_setting_content 	The setting sections
	 **/
	add_filter( 'sts-settings-content', 'sts_setting_core_content' );
	function sts_setting_core_content( $sts_setting_content ){
		$sts_setting_content[] = array(
			'id' => 'welcome',
			'title' => __( 'Welcome', 'sts' ),
			'before_form' => '<p style="text-align:center"><img width="50%" src="' . STS_URL . 'assetts/logo-large.svg" alt="" /></p><hr /><p style="text-align:center"><strong>' . __( 'Welcome to WP Support Ticket!', 'sts' ) . '</strong><br />' . __( 'On this page, you can configure the plugin, so it fits your needs.', 'sts' ) . '</p>' ,
			'after_form' => '',
		);
		$sts_setting_content[] = array(
			'id' => 'email',
			'title' => __( 'Email settings', 'sts' ),
			'before_form' => '',
			'after_form' => '',
		);
		$sts_setting_content[] = array(
			'id' => 'user',
			'title' => __( 'User settings', 'sts' ),
			'before_form' => '',
			'after_form' => '',
		);
		$sts_setting_content[] = array(
			'id' => 'ticket',
			'title' => __( 'Ticket settings', 'sts' ),
			'before_form' => '',
			'after_form' => '',
		);


		return $sts_setting_content;
	}

	class Sts_Settings{
		private $nav = array();
		private $content = array();
		private $errors = array();
		private $updates = array();

		public function __construct(){
			/**
			 * Filter the settings tabs
			 *
			 * @param 	(array) 	the current tabs
			 * @return 	(array) 	the altered tabs
			 **/
			$sts_setting_content = apply_filters( 'sts-settings-content', array() );
			foreach( $sts_setting_content as $args ){

				$nav = array(
					'id'	=> $args['id'],
					'title'	=> $args['title'],
				);

				$content = array(
					'id'		=> $args['id'],
					'title'		=> $args['title'],
					'before_form' => $args['before_form'],
					'after_form' => $args['after_form'],
				);

				$this->add_nav( $nav );
				$this->add_content( $content );
			}	
		}

		public function add_nav( $nav ){
			$this->nav[ $nav['id'] ] = $nav;
		}

		public function add_content( $content ){
			$this->content[ $content['id'] ] = $content;
		}


		/**
 		 * Renders the navigation tabs of the settings
		 *
 		 * @since 	1.0.0
 		 *
 		 * @return (void)
	 	 **/
		public function render_tabs(){
			foreach( $this->nav as $n ):
				?>
				<li><a href="#<?php echo $n['id']; ?>"><?php echo $n['title']; ?></a></li>
				<?php
			endforeach;
		}

			
		/**
 		 * Renders the single settings sections
	 	 *
 		 * @since 	1.0.0
 		 *
 		 * @return (void)
	 	 **/
		public function render_content(){
			foreach( $this->content as $c ):
				?>
				<div id="<?php echo $c['id']; ?>">
					<h3><?php echo $c['title']; ?></h3>
					<div class="inner">
						<?php echo $c['before_form']; ?>
						<form method="post" action="#<?php echo $c['id']; ?>">
							<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
							<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
							<input type="hidden" name="t-action" value="settings" />
							<input type="hidden" name="t-subaction" value="<?php echo $c['id']; ?>" />
							<?php wp_nonce_field( 'ticket-settings-' . $c['id'], 't-nonce' ); ?>
							<?php do_meta_boxes( 'ticket-settings-' . $c['id'], 'normal', new stdClass() );  ?>
							<?php if( sts_has_meta_boxes( 'ticket-settings-' . $c['id'], 'normal' ) ): ?>
							<button class="button button-primary button-large"><?php _e( 'Update', 'sts' ); ?></button>
							<?php endif; ?>
						</form>
						<?php echo $c['after_form']; ?>
					</div>
				</div>
				<?php
			endforeach;
		}

		public function update(){
			if( ! wp_verify_nonce( $_POST['t-nonce'], 'ticket-settings-' . $_POST['t-subaction'] ) )
				wp_die( __( 'Something went wrong :/', 'sts' ) );
		
			/**
			 * Filter, if errors occured during update.
			 *
			 * With this filter you can hook into the settings update process
			 * and perform the update.
			 *
			 * The filter name is 'sts-settings-update-{$sectionid}'
			 *
			 * If an error occurs, return a WP ERROR object, which describes the problem.
			 * Otherwise return true
			 *
			 * @since 1.0.0
			 *
			 * @param (boolean) 	true 			No error
			 * @return (mixed) 		$return 		true or WP Error
			*/
			$return = apply_filters( 'sts-settings-update-' . $_POST['t-subaction'], true );
			
			if( is_wp_error( $return ) ){
				$this->errors[] = $return;
			} else {
				$redirect = 'admin.php?page=sts-settings&updated=1#' . $_POST['t-subaction'];
				?>
				<script>location.href='<?php echo $redirect; ?>';</script>
				<?php
				die();
			}
			
		}

		public function render_error(){
			if( count( $this->errors ) == 0 )
				return;
			foreach( $this->errors as $e )
			?>
			<div class="error"><p><?php echo $e->get_error_message(); ?></p></div>
			<?php
		}

	}
?>