<?php
define('DIRECT_ACCESS_CHECK',false);
require_once('globals.php');
require_once CONTROLLERS_DIRECTORY.DS.'main.php';

$directoryParser = new Filesystem_Directory(
    '//172.16.50.15/d$/lotus/Domino/Data/domino/logs/','*.log',
    'read','DominoLog'
);
$directoryParser->getFileObject = true;
$filter = new LogProfile('condense.xml');

foreach ($directoryParser as $file) {
    print $file->fullpath."\n";
    print $file->path."\n";
    print $file->filename."\n";
    print $file->ext."\n\n";
    
    $counter = 0;
    foreach ($file as $line) {
        if ($filter->include_line($line)) {
            $counter++;
            if (($counte%100)==0) {
                print "$counter\n";
            }
        }
    }
}



#print $file->all();

#$file->close();
?>