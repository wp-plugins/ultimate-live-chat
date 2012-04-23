<?php
header('Content-type: text/javascript');
?>
JLiveChat.hostedModeURI='<?php echo rtrim(urldecode($_GET['hosted_mode_path']), '/'); ?>';
JLiveChat.websiteRoot='<?php echo rtrim(urldecode($_GET['hosted_mode_path']), '/'); ?>';

setTimeout('JLiveChat.initialize();', 100);
