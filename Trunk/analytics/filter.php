<?php
define('DIRECT_ACCESS_CHECK',false);
require_once('globals.php');
require_once CONTROLLERS_DIRECTORY.DS.'main.php';

$directoryParser = new LogDirectoryParser();
$directoryParser->add_directory('\\\\172.16.50.15\\d$\\lotus\\Domino\\Data\\domino\\logs\\');
#$directoryParser->add_directory('logs/');
$logParser = new LogParser('Domino','condense','rcbc_search_and_replace');

$filename = $directoryParser->start();
do {
    echo "$filename\n";
    
    preg_match('/([a-zA-Z0-9_]+\.log)/i',$filename,$matches);
    $oFilename = $matches[1];
    
    $ofh = fopen('D:\\www\\htdocs\\Impact\\analytics\\logs\\'.$oFilename, 'w');
    if (!is_null($ofh)) {
        $counter = 0;
        $logParser->file = $filename;
        do {
            $line = $logParser->next();
            $line = preg_replace('/[\n\r\f\t]/','',$line);
            fwrite($ofh,$line."\n\r");
            
            #print $counter."\n";
            #print $line."\n";
            $counter++;
        } while ($line !== false);
        print "$counter entries found\n";
        fclose($ofh);
    } else {
        print "Cannot open: $oFilename\n";
    }
   
    
    
    
    
    $filename = $directoryParser->next();
} while ($filename !== false);

?>