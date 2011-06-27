<?php
define('DIRECT_ACCESS_CHECK',false);
require_once('globals.php');
require_once CONTROLLERS_DIRECTORY.DS.'main.php';

$directoryParser = new Filesystem_Directory(
    '//172.16.50.15/d$/lotus/Domino/Data/domino/logs/','*.log',
    'read','DominoLog'
);



$file = $directoryParser->next(true);

print $file->fullpath."\n";
print $file->path."\n";
print $file->filename."\n";
print $file->ext."\n\n";
print $file->next();

#print $file->all();

#$file->close();
?>