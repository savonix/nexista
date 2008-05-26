<?php
/*
Plugin Name: google_adsense_plugin.php
Plugin URI: http://www.nexista.org/
Description: Ads Google Adsense Code
Version:
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL
*/

echo "Foundry plugins are deprecated. Please fix your config file";

/* TODO - Get this from the database. */
$adsense_account = Nexista_Config::get("./plugins/google_adsense_account/code");
if(!$priority = Nexista_Config::get("./plugins/google_adsense_account/priority")) {
    $priority = 10;
}

$google_adsense_code = <<<EOS
<div style="position: absolute; top: 18px; right: 25px;">
<script type="text/javascript"><!--
google_ad_client = "$adsense_account";
google_alternate_color = "FFFFFF"
google_ad_width = 234;
google_ad_height = 60;
google_ad_format = "234x60_as";
google_ad_type = "text";
//2007-10-29: Technology
google_ad_channel = "6222844825";
google_ad_channel = "6222844825";
google_color_border = "0066CC";
google_color_bg = "FFFFFF";
google_color_link = "0066CC";
google_color_text = "000000";
google_color_url = "1B703A";
google_ui_features = "rc:0";
//-->
</script>

<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</div>

EOS;

$footer[] = array('string' => $google_adsense_code, 'priority' => $priority);

Nexista_Flow::add("footer",$footer,false);
?>
