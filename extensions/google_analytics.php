<?php
/*
Extension Name:
Extension URI:
Description:
Version:
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL
*/

/*
<google_analytics_code>
  <placement>predisplay</placement>
  <code>raw code</code>
  <!-- xpath trumps code -->
  <xpath>//o_google_analytics_code</xpath>
  <priority>100</priority>
  <source>&includepath;extensions/google_analytics.php</source>
</google_analytics_code>
*/

$analytics_code  = Nexista_Config::get('./extensions/google_analytics_code/code');
$analytics_xpath = Nexista_Config::get('./extensions/google_analytics_code/xpath');
if($analytics_xpath) {
    $analytics_code = Nexista_Flow::getByPath($analytics_xpath);
}
if(!$priority = Nexista_Config::get('./extensions/google_analytics_code/priority')) {
    $priority = 10;
}


if ($analytics_code) {
$google_analytics_code = <<<EOS
<script src="http://www.google-analytics.com/ga.js" type="text/javascript"></script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("$analytics_code");
pageTracker._initData();
pageTracker._trackPageview();
</script>
EOS;

$flow = Nexista_Flow::singleton('Nexista_Flow');

$f = new DOMDocument('1.0', 'UTF-8');
$f->loadXML('<post_body_content><priority>11</priority><nodes>jhi'.$google_analytics_code.'</nodes></post_body_content>');
$n = $f->getElementsByTagName('post_body_content')->item(0);
$g = $flow->flowDocument->importNode($n, true);
$flow->root->appendChild($g);

}


?>
