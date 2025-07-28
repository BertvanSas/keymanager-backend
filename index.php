<?php
// Automatisch redirecten naar Control Panel login pagina
header("Location: /Control%20Panel.php");
exit();

// Powered by Site.pro
if (function_exists('ini_set')) @ini_set('opcache.enable', '0');
include dirname(__FILE__).'/sitepro/index.php';
?>