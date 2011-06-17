<?php
define('DIRECT_ACCESS_CHECK',false);
require_once('globals.php');
require_once CONTROLLERS_DIRECTORY.DS.'main.php';

$directoryParser = new LogDirectoryParser();
#$directoryParser->add_directory('\\\\172.16.50.15\\d$\\lotus\\Domino\\Data\\domino\\logs\\');
$directoryParser->add_directory('logs/');
$logParser = new LogParser('Domino','condense');

$filename = $directoryParser->start();
do {
    echo "$filename\n";
    
    $counter = 0;
    $logParser->file = $filename;
    do {
        $line = $logParser->next();
        #print $line."\n";
        $counter++;
    } while ($line !== false);
    print "$counter entries found\n";
    
    $filename = $directoryParser->next();
} while ($filename !== false);

?>