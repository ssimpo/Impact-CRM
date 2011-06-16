<?php
define('DIRECT_ACCESS_CHECK',false);
require_once('globals.php');
require_once CONTROLLERS_DIRECTORY.DS.'main.php';

$parser = new LogParser('Domino');
$dir = SITE_FOLDER.'logs'.DS;
$parser->parse($dir,'condense');

?>