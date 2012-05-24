<?php 
/*
Plugin Name: Ultimate Live Chat
Plugin URI: http://www.ultimatelivechat.com
Description: Plugin/Widget for enabling Ultimate Live Chat on your website.
Author: CMS Fruit
Version: 1.1
Author URI: http://www.cmsfruit.com
*/

class ulc_img_widget extends WP_Widget 
{
	var $hostedUri = null;
	var $scriptsAlreadyAdded = false;
	
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'ulc_img_widget', // Base ID
			'Ultimate Live Chat Online/Offline Image', // Name
			array( 'description' => __( 'Ultimate Live Chat Online/Offline Image Widget', 'text_domain' ), ) // Args
		);
		
		$instance = $this->get_livechat_settings();
		
		if(!empty($instance))
		{
			// Okay we have livechat settings
			if(!empty($instance['hosted_mode_uri_override'])) 
			{
				$hostedURI = $instance['hosted_mode_uri_override'];
			}
			elseif(!empty($instance['hosted_mode_api_key']) && !empty($instance['hosted_mode_user_id']) && !empty($instance['hosted_mode_path']))
			{
				if(strtolower(@$_SERVER['HTTPS']) == 'on') 
				{
					$hostedURI = 'https://';
				}
				else
				{
					$hostedURI = 'http://';
				}
				
				$hostedURI .= 'www.ultimatelivechat.com/sites/'.$instance['hosted_mode_user_id'].'/'.$instance['hosted_mode_path'].'/';
			}
			else
			{
				$hostedURI = false;
			}
			
			if(!empty($hostedURI))
			{
				// We have all the hosted livechat settings, inject javascript
				$this->hostedUri = rtrim($hostedURI, '/');
				
				add_action('wp_enqueue_scripts', array($this, 'add_external_scripts'));
				add_action('wp_footer', array($this, 'add_inline_scripts'));
			}
		}
	}
	
	public function add_external_scripts()
	{
		if(!empty($this->hostedUri) && !$this->scriptsAlreadyAdded)
		{
			wp_enqueue_style('jlc', $this->hostedUri.'/components/com_jlivechat/assets/css/jlivechat.min.css');
				
			wp_enqueue_script('jlc-lazyload', $this->hostedUri.'/components/com_jlivechat/js/lazyload-min.js');
			wp_enqueue_script('jlc-main', $this->hostedUri.'/components/com_jlivechat/js/jlivechat.min.js');
		}
	}
	
	public function add_inline_scripts()
	{
		if(!empty($this->hostedUri) && !$this->scriptsAlreadyAdded)
		{
			$this->scriptsAlreadyAdded = true;
			
			$trackerImgUri = $this->get_tracker_image_uri();
			
			echo <<<EOF
<script type="text/javascript">
	JLiveChat.hostedModeURI='{$this->hostedUri}';
	JLiveChat.websiteRoot='{$this->hostedUri}';

	setTimeout('JLiveChat.initialize();', 100);
</script>
<img src="{$trackerImgUri}" width="1" height="1" alt="" border="0" />	
EOF;
		}
	}
	
	public function get_livechat_settings()
	{
		$livechatSettings = get_option($this->option_name);
		
		if(!empty($livechatSettings) && is_array($livechatSettings))
		{
			foreach($livechatSettings as $key => $value)
			{
				if(isset($livechatSettings[$key]['hosted_mode_api_key'])) return $value;
			}
		}
	}
	
	public function get_tracker_image_uri()
	{
		$current_user = wp_get_current_user();
		
		$trackerUri = $this->hostedUri.'/index.php?option=com_jlivechat&amp;no_html=1&amp;tmpl=component';
		
		if(strtolower(@$_SERVER['HTTPS']) == 'on') 
		{
			$scheme = 'https://';
		}
		else
		{
			$scheme = 'http://';
		}
		
		$currentUrl = $scheme.@$_SERVER['SERVER_NAME'].@$_SERVER['REQUEST_URI'];
		
		$trackerUri .= '&amp;view=popup';
		$trackerUri .= '&amp;task=track_remote_visitor';
		if(isset($current_user->ID)) $trackerUri .= '&amp;user_id='.$current_user->ID;
		if(isset($current_user->display_name)) $trackerUri .= '&amp;full_name='.urlencode($current_user->display_name);
		if(isset($current_user->user_login)) $trackerUri .= '&amp;username='.urlencode($current_user->user_login);
		if(isset($current_user->user_email)) $trackerUri .= '&amp;email='.urlencode($current_user->user_email);
		if(isset($_SERVER['HTTP_REFERER'])) $trackerUri .= '&amp;referrer='.urlencode($_SERVER['HTTP_REFERER']);
		$trackerUri .= '&amp;last_uri='.urlencode($currentUrl);
		
		return $trackerUri;
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $hostedURI;
		
		extract( $args );
		
		if(!empty($instance['hosted_mode_uri_override'])) 
		{
			$hostedURI = $instance['hosted_mode_uri_override'];
		}
		elseif(!empty($instance['hosted_mode_api_key']) && !empty($instance['hosted_mode_user_id']) && !empty($instance['hosted_mode_path']))
		{
			if(strtolower($_SERVER['HTTPS']) == 'on') 
			{
				$hostedURI = 'https://';
			}
			else
			{
				$hostedURI = 'http://';
			}
			
			$hostedURI .= 'www.ultimatelivechat.com/sites/'.$instance['hosted_mode_user_id'].'/'.$instance['hosted_mode_path'].'/';
		}
		else
		{
			$hostedURI = false;
		}
		
		if(!empty($hostedURI))
		{
			include 'ultimatelivechat-img-widget.php';
		}
		else
		{
			echo '<a href="https://www.ultimatelivechat.com/my-account.html" target="_blank">Ultimate Live Chat hosted mode API key not defined, please define first!</a>';
		}
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		
		$instance['hosted_mode_display_api_key'] = $old_instance['hosted_mode_display_api_key'];
		$instance['hosted_mode_api_key'] = $old_instance['hosted_mode_api_key'];
		$instance['hosted_mode_user_id'] = $old_instance['hosted_mode_user_id'];
		$instance['hosted_mode_path'] = $old_instance['hosted_mode_path'];
		$instance['image_size'] = $new_instance['image_size'];
		$instance['popup_mode'] = $new_instance['popup_mode'];
		$instance['specific_operators'] = trim($new_instance['specific_operators']);
		$instance['specific_department'] = trim($new_instance['specific_department']);
		$instance['specific_route_id'] = trim($new_instance['specific_route_id']);
		$instance['online_img_override'] = trim($new_instance['online_img_override']);
		$instance['offline_img_override'] = trim($new_instance['offline_img_override']);
		$instance['hosted_mode_uri_override'] = trim($new_instance['hosted_mode_uri_override']);
		
		
		if(!empty($instance['hosted_mode_uri_override']))
		{
			// Force forward slash at end
			if(!preg_match('@(/$)@', $instance['hosted_mode_uri_override']))
			{
				$instance['hosted_mode_uri_override'] .= '/';
			}
		}
		
		$key = trim($new_instance['hosted_mode_display_api_key']);
		
		if(strpos($key, '******') !== FALSE)
		{
			// Leave unchanged
		}
		elseif(empty($key) || strlen($key) < 3)
		{
			$instance['hosted_mode_display_api_key'] = '';
			$instance['hosted_mode_api_key'] = '';
			$instance['hosted_mode_user_id'] = '';
			$instance['hosted_mode_path'] = '';
		}
		else
		{
			// Validate key
			$checkUri = 'https://www.ultimatelivechat.com/index.php?option=com_ultimatelivechat&view=api&format=raw&task=validate_api_key2&k='.urlencode($key);
			
			$keyDetails = file_get_contents($checkUri);
			$keyDetails = json_decode($keyDetails);

			if($keyDetails->success && isset($keyDetails->user_id) && isset($keyDetails->path))
			{
				// Api Key was successfully validated
				$instance['hosted_mode_display_api_key'] = '*****************************';
				$instance['hosted_mode_api_key'] = $key;
				$instance['hosted_mode_user_id'] = $keyDetails->user_id;
				$instance['hosted_mode_path'] = $keyDetails->path;
			}
		}
		
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if (isset($instance['hosted_mode_display_api_key'])) 
		{
			$apiKey = $instance[ 'hosted_mode_display_api_key' ];
		}
		else
		{
			$apiKey = '';
		}
		
		if (isset($instance['image_size'])) 
		{
			$imgSize = $instance[ 'image_size' ];
		}
		else
		{
			$imgSize = 'large';
		}
		
		if (isset($instance['popup_mode'])) 
		{
			$popupMode = $instance[ 'popup_mode' ];
		}
		else
		{
			$popupMode = 'popup';
		}
		
		if (isset($instance['specific_operators'])) 
		{
			$specificOperators = $instance[ 'specific_operators' ];
		}
		else
		{
			$specificOperators = '';
		}
		
		if (isset($instance['specific_department'])) 
		{
			$specificDepartment = $instance[ 'specific_department' ];
		}
		else
		{
			$specificDepartment = '';
		}
		
		if (isset($instance['specific_route_id'])) 
		{
			$specificRouteId = $instance[ 'specific_route_id' ];
		}
		else
		{
			$specificRouteId = '';
		}
		
		if (isset($instance['online_img_override'])) 
		{
			$onlineImgOverride = $instance[ 'online_img_override' ];
		}
		else
		{
			$onlineImgOverride = '';
		}
		
		if (isset($instance['offline_img_override'])) 
		{
			$offlineImgOverride = $instance[ 'offline_img_override' ];
		}
		else
		{
			$offlineImgOverride = '';
		}
		
		if (isset($instance['hosted_mode_uri_override'])) 
		{
			$hostedModeUriOverride = $instance[ 'hosted_mode_uri_override' ];
		}
		else
		{
			$hostedModeUriOverride = '';
		}
		
		?>
		<p>
			<span style="font-size: 1.2em; font-weight: bold; color: blue;">* Required Setting</span>
			<br />
			<label for="<?php echo $this->get_field_id( 'hosted_mode_display_api_key' ); ?>"><a href="https://www.ultimatelivechat.com/my-account.html" target="_blank"><?php _e( 'Hosted Mode API Access Key:' ); ?></a></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'hosted_mode_display_api_key' ); ?>" name="<?php echo $this->get_field_name( 'hosted_mode_display_api_key' ); ?>" type="text" value="<?php echo esc_attr( $apiKey ); ?>" />
			<br />
			<hr />
			<br />
			
			<span style="font-size: 1.2em; font-weight: bold; color: blue;">Optional Settings</span>
			<br />
			
			<label for="<?php echo $this->get_field_id( 'popup_mode' ); ?>"><?php _e( 'Popup Mode:' ); ?></label> 
			<select id="<?php echo $this->get_field_id( 'popup_mode' ); ?>" name="<?php echo $this->get_field_name( 'popup_mode' ); ?>">
				<option value="popup" <?php if($popupMode == 'popup') { ?>selected="selected"<?php } ?>>Popup Mode</option>
				<option value="iframe" <?php if($popupMode == 'iframe') { ?>selected="selected"<?php } ?>>IFrame Mode</option>
			</select>
			<br /><br />
			
			<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image Size:' ); ?></label> 
			<select id="<?php echo $this->get_field_id( 'image_size' ); ?>" name="<?php echo $this->get_field_name( 'image_size' ); ?>">
				<option value="large" <?php if($imgSize == 'large') { ?>selected="selected"<?php } ?>>Large</option>
				<option value="small" <?php if($imgSize == 'small') { ?>selected="selected"<?php } ?>>Small</option>
			</select>
			<br /><br />
			
			
			<label for="<?php echo $this->get_field_id( 'specific_operators' ); ?>"><?php _e( 'Specific Operators:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'specific_operators' ); ?>" name="<?php echo $this->get_field_name( 'specific_operators' ); ?>" type="text" value="<?php echo esc_attr( $specificOperators ); ?>" />
			<br />
			<span style="font-size: 0.85em; font-style: italic;">* Comma seperated list of operator IDs</span>
			<br /><br />
			
			<label for="<?php echo $this->get_field_id( 'specific_department' ); ?>"><?php _e( 'Specific Department:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'specific_department' ); ?>" name="<?php echo $this->get_field_name( 'specific_department' ); ?>" type="text" value="<?php echo esc_attr( $specificDepartment ); ?>" />
			<br /><br />
			
			<label for="<?php echo $this->get_field_id( 'specific_route_id' ); ?>"><?php _e( 'Specific Route ID:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'specific_route_id' ); ?>" name="<?php echo $this->get_field_name( 'specific_route_id' ); ?>" type="text" value="<?php echo esc_attr( $specificRouteId ); ?>" />
			<br />
			<br />
			
			
			<span style="font-size: 1.2em; font-weight: bold; color: blue;">Advanced Settings</span>
			<br />
			
			<label for="<?php echo $this->get_field_id( 'hosted_mode_uri_override' ); ?>"><?php _e( 'JLive! Chat Installation Path Override:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'hosted_mode_uri_override' ); ?>" name="<?php echo $this->get_field_name( 'hosted_mode_uri_override' ); ?>" type="text" value="<?php echo esc_attr( $hostedModeUriOverride ); ?>" />
			<br />
			<span style="font-size: 0.85em; font-style: italic;">Example: http://mysite.com/joomla/</span>
			<br /><br />
			
			<!--<label for="<?php echo $this->get_field_id( 'online_img_override' ); ?>"><?php _e( 'Online Image Src Override:' ); ?></label>-->
			<input class="widefat" id="<?php echo $this->get_field_id( 'online_img_override' ); ?>" name="<?php echo $this->get_field_name( 'online_img_override' ); ?>" type="hidden" value="<?php echo esc_attr( $onlineImgOverride ); ?>" />
			<!--
			<br />
			<span style="font-size: 0.85em; font-style: italic;">Example: http://mysite.com/images/online.jpg</span>
			<br /><br />
			-->
			<!--<label for="<?php echo $this->get_field_id( 'offline_img_override' ); ?>"><?php _e( 'Offline Image Src Override:' ); ?></label>--> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'offline_img_override' ); ?>" name="<?php echo $this->get_field_name( 'offline_img_override' ); ?>" type="hidden" value="<?php echo esc_attr( $offlineImgOverride ); ?>" />
			<!--
			<br />
			<span style="font-size: 0.85em; font-style: italic;">Example: http://mysite.com/images/offline.jpg</span>
			<br />
			-->
		</p>
		<?php 
	}
}

// register JLC Online/Offline Image widget
add_action('widgets_init', create_function('', 'register_widget( "ulc_img_widget" );'));
