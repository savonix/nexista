<?php
/*
Extension Name:
Extension URI:
Description:
Version:
Copyright: Nexista
Author: Albert Lash
License: LGPL
*/

/* TODO - Get this from the database. */
$google_code = Nexista_Config::get("./extensions/google_analytics_code/code");
if(!$priority = Nexista_Config::get("./extensions/google_analytics_code/priority")) {
    $priority = 10;
}

$google_analytics_code = <<<EOS
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write("\<script src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'>\<\/script>" );
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker(" $google_code ");
pageTracker._initData();
pageTracker._trackPageview();
</script>
EOS;

$footer[] = array('string' => $google_analytics_code, 'priority' => $priority);

Nexista_Flow::add("footer",$footer,false);
?>
