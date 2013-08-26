<?php

class B5F_SE_Metabox
{
	private $plugin_path;
	private $plugin_url;
	public function __construct( $path, $url ) 
	{
		$this->plugin_path = $path;
		$this->plugin_url = $url;
		add_action( 'add_meta_boxes', array( $this, 'add_custom_box' ) );
		add_action( 'save_post', array( $this, 'save_postdata' ) );
		add_action( 'admin_head-post.php', array( $this, 'script_enqueuer' ) );
	}



	/* Adds a box to the main column on the Post and Page edit screens */
	public function add_custom_box() 
	{
		add_meta_box(
			'b5f_se_metabox_section_id',
			__( 'Stack Exchange - All my posts', 'wpse' ), 
			array( $this, 'inner_custom_box' ),
			'page',
			'side'
		);
	}


	/* Prints the box content */
	public function inner_custom_box($post)
	{
		wp_nonce_field( plugin_basename( __FILE__ ), 'b5f_se_metabox_nonce' );
		
		# https://github.com/marghoobsuleman/ms-Dropdown
		# combobox_output.php : added this to GetHTML()
		# $meta_icon = ' data-image="'. $item['favicon_url'] .'"';
		wp_enqueue_script( 'dd_js', $this->plugin_url . 'js/jquery.dd.min.js', array('jquery'));
		wp_enqueue_script( 'dd_fire', $this->plugin_url . 'js/dd-fire.js', array('jquery'));
		wp_enqueue_style( 'dd_style', $this->plugin_url . 'css/dd.css' );

		require_once $this->plugin_path.'includes/config.php';
		require_once $this->plugin_path.'includes/stackphp/output_helper.php';
		

		# Sites list
		$se_site_saved = get_post_meta( $post->ID, 'se_site', true);
		if( !$se_site_saved )
			$se_site_saved = 'stackoverflow';
		$combo = OutputHelper::CreateCombobox( API::Sites(), 'se_site' );
		$site_html = $combo->FetchMultiple()->SetIndices('name', 'api_site_parameter')->SetCurrentSelection( $se_site_saved )->GetHTML();
		echo "<p><strong>Select site</strong><br />$site_html</p>";
		
		# Post types
		$se_post_types = array(
			'answers'       => __('Answers', 'wpse'),
			'questions'     => __('Questions', 'wpse')
		);
		$se_post_type_saved = get_post_meta( $post->ID, 'se_post_type', true);
		if( !$se_post_type_saved )
			$se_post_type_saved = 'questions';
		echo '<p><label for="se_post_type" class="mbox-label"><strong>Type of posts</strong></label> <select name="se_post_type" id="se_post_type">';
		foreach ( $se_post_types as $key => $label ) 
		{
			printf(
				'<option value="%s" %s> %s</option>',
				esc_attr($key),
				selected( $se_post_type_saved, $key, false),
				esc_html($label)
			);
		}
		echo '</select></p>';
		#
		
		# User ID
		$se_user_id_saved = get_post_meta( $post->ID, 'se_user_id', true);
		if( !$se_user_id_saved )
			$se_user_id_saved = '';
		echo "<p><label for='se_user_id' class='mbox-label'><strong>User ID</strong></label> <input type='text' size='6' name='se_user_id' id='se_user_id' value='" . esc_attr( $se_user_id_saved ) . "' /></p>";
		#
		
		# Posts per page
		$se_per_page = get_post_meta( $post->ID, 'se_per_page', true);
		echo "<p><label for='se_per_page' class='mbox-label'><strong>Posts per page</strong></label><input type='text' size='6' name='se_per_page' id='se_per_page' value='" . esc_attr( $se_per_page ) . "' /></p>";
		#
		
		# Cache
		$se_cached = get_post_meta( $post->ID, 'se_cached', true);
		printf(
			'<p><label for="se_cached" class="mbox-label"><strong>Cache results</strong></label> <input name="se_cached" id="se_cached" type="checkbox" %s />',
			checked( $se_cached, 'on', false)
		);
	}

	/* When the post is saved, saves our custom data */
	public function save_postdata( $post_id ) 
	{
		  if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				|| ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) 
			  return;

		  if ( !isset( $_POST['b5f_se_metabox_nonce'] ) ||  !wp_verify_nonce( $_POST['b5f_se_metabox_nonce'], plugin_basename( __FILE__ ) ) )
			  return;

		  if ( isset($_POST['se_site']) )
				update_post_meta( 
						$post_id, 
						'se_site', 
						$_POST['se_site'] 
				);
		  
		  if ( isset($_POST['se_post_type']) )
				update_post_meta( 
						$post_id, 
						'se_post_type', 
						$_POST['se_post_type'] 
				);
		  
		  if ( isset($_POST['se_user_id']) && $_POST['se_user_id'] != "" )
				update_post_meta( 
						$post_id, 
						'se_user_id', 
						intval( stripslashes( strip_tags( $_POST['se_user_id'] ) ) ) 
				);
		  if ( isset($_POST['se_cached']) && $_POST['se_cached'] != "" )
				update_post_meta( 
						$post_id, 
						'se_cached', 
						$_POST['se_cached'] 
				);
		  else
			  delete_post_meta( $post_id, 'se_cached' );
		  
		  if ( isset($_POST['se_per_page']) && $_POST['se_per_page'] != "" )
		  {
			  $total = intval( stripslashes( strip_tags( $_POST['se_per_page'] ) ) );
			  if( $total > 100 )
				  $total = 100;
				update_post_meta( 
						$post_id, 
						'se_per_page', 
						 $total
				);
		  }
	}

	public function script_enqueuer() 
	{
		global $typenow;

		if( 'page' != $typenow ) 
			return;

		echo <<<HTML
<script type="text/javascript">
jQuery(document).ready( function($) {

	/**
	 * Adjust visibility of the meta box at startup
	*/
	if($('#page_template').val() == 'template-stackapp.php') {
		// show the meta box
		$('#b5f_se_metabox_section_id').show();
		$("form#adv-settings label[for='b5f_se_metabox_section_id-hide']").show();
	} else {
		// hide your meta box
		$('#b5f_se_metabox_section_id').hide();
		$("form#adv-settings label[for='b5f_se_metabox_section_id-hide']").hide();
	}

	/**
	 * Live adjustment of the meta box visibility
	*/
	$('#page_template').live('change', function(){
			if($(this).val() == 'template-stackapp.php') {
			// show the meta box
			$('#b5f_se_metabox_section_id').show();
			$("form#adv-settings label[for='b5f_se_metabox_section_id-hide']").show();
		} else {
			// hide your meta box
			$('#b5f_se_metabox_section_id').hide();
			$("form#adv-settings label[for='b5f_se_metabox_section_id-hide']").hide();
		}
	});					
});    
</script>
HTML;
	}

	/**
	 * Zero, one or more votes
	 * @param string $score
	 * @return string
	 */
	public function get_score( $score, $prefix='', $suffix='' )
	{
		switch( $score )
		{
			case '0':
			null:
				$score = '';
			break;
			case '1':
				$score = $prefix.'1 vote'.$suffix;
			break;
			default:
				$score = $prefix . $score . ' votes'.$suffix;
			break;
		}
		return $score;
	}
	
}