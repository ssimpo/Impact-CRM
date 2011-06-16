<?php
define('DIRECT_ACCESS_CHECK',false);
require_once('globals.php');
require_once CONTROLLERS_DIRECTORY.DS.'main.php';

$directoryParser = new LogDirectoryParser();
$directoryParser->add_directory(SITE_FOLDER.'logs'.DS);
$logParser = new LogParser('Domino','condense');

$filename = $directoryParser->start();
do {
    echo "$filename\n";
    
    $logParser->file = $filename;
    do {
        $line = $logParser->next();
        print $line."\n";
    } while ($line !== false);
    
    $filename = $directoryParser->next();
} while ($filename !== false);

?>