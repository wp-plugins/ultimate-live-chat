<?php
if(!class_exists('mod_ulc_helper')) 
{
	class mod_ulc_helper
	{		
		function get_popup_uri($popupMode=null, $specificOperators=null, $specificDepartment=null, $specificRouteId=null)
		{
			global $hostedURI;
			
			$popupUri = rtrim($hostedURI, '/');
			$popupUri .= '/index.php?option=com_jlivechat&amp;view=popup&amp;tmpl=component&amp;popup_mode='.$popupMode;

			$activeLanguage = get_bloginfo('language');
			
			if(!empty($activeLanguage)) $popupUri .= '&amp;lang='.$activeLanguage;
			if(!empty($specificOperators)) $popupUri .= '&amp;operators='.$specificOperators;
			if(!empty($specificDepartment)) $popupUri .= '&amp;department='.urlencode($specificDepartment);
			if(!empty($specificRouteId)) $popupUri .= '&amp;routeid='.(int)$specificRouteId;
			
			return $popupUri;
		}

		function get_dynamic_image_uri($imgSize=null, $specificOperators=null, $specificDepartment=null, $specificRouteId=null)
		{
			global $hostedURI;
			
			$imgUri = rtrim($hostedURI, '/');
			
			if(empty($imgSize)) $imgSize = 'large';

			$imgUri .= '/index.php?option=com_jlivechat&amp;view=popup&amp;task=display_status_img';
			$imgUri .= '&amp;no_html=1&amp;do_not_log=true&amp;size='.$imgSize;
			$imgUri .= '&amp;t='.time(); // Prevent caching

			if(!empty($specificOperators)) $imgUri .= '&amp;operators='.$specificOperators;
			if(!empty($specificDepartment)) $imgUri .= '&amp;department='.urlencode($specificDepartment);
			if(!empty($specificRouteId)) $imgUri .= '&amp;routeid='.(int)$specificRouteId;
			
			return $imgUri;
		}
	}
}
?>
<a class="ulc_livechat_img" href="javascript:void(0);" onclick="requestLiveChat('<?php echo mod_ulc_helper::get_popup_uri($instance['popup_mode'], $instance['specific_operators'], $instance['specific_department'], $instance['specific_route_id']); ?>', '<?php echo $instance['popup_mode']; ?>');"><img src="<?php echo mod_ulc_helper::get_dynamic_image_uri($instance['image_size'], $instance['specific_operators'], $instance['specific_department'], $instance['specific_route_id']); ?>" alt="" border="0" /></a>
