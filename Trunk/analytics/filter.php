<?php
define('DIRECT_ACCESS_CHECK',false);
require_once('globals.php');
require_once CONTROLLERS_DIRECTORY.DS.'main.php';

$directoryParser = new Filesystem_Directory(
    '//172.16.50.15/d$/lotus/Domino/Data/domino/logs/','*.log',
    'read','DominoLog'
);
$directoryParser->getFileObject = true;

foreach ($directoryParser as $file) {
    print $file->fullpath."\n";
}

foreach ($directoryParser as $file) {
    print $file->fullpath."\n";
    $oFile = new Filesystem_File(SITE_FOLDER.'logs2',$file->filename);
    $oFile->open('write','DominoLog');
    
    $start = microtime(true);
    $counter = 0;
    $found = 0;
    foreach ($file as $line) {
        $counter++;
        if ($oFile->write($line,'condense')) { // 1st Pass
            $found++;
        }
        if (($counter%5000)==0) {
            $period = get_elapsed($start);
            $rate = round(100-(($found/$counter)*100),2);
            $speed = round($counter/(microtime(true)-$start),2);
            print "$found\tout of\t$counter\tin $period\t(Filter-rate: $rate%)\t(Speed: $speed lines/sec)\n";
        }
    }
    
    $rate = round(100-(($found/$counter)*100),2);
    $speed = round($counter/(microtime(true)-$start),2);
    $period = get_elapsed($start);
    print "Found: $found in Total out of $counter ($period - Filter-rate: $rate%)\t(Speed: $speed lines/sec)\n";
}

function get_elapsed($start) {
    $period = (microtime(true)-$start);
    return get_hours($period).':'.get_minutes($period).':'.get_seconds($period).' hours';
}

function get_hours($time) {
    $hours = 0;
    if ($time >= 60*60) {
        $hours = ($time/(60*60));
    }
    return get_two_digits($hours);
}

function get_minutes($time) {
    $hours = (int) get_hours($time);
    $minutes = 0;
    if ($time >= 60) {
        $minutes = (($time-($hours*60*60))/60);
    }
    return get_two_digits($minutes);
}

function get_seconds($time) {
    $hours = (int) get_hours($time);
    $minutes = (int) get_minutes($time);
    $seconds = $time-($hours*60*60)-($minutes*60);
    return get_two_digits($seconds);
}

function get_two_digits($digits) {
    $digits = (int) $digits;
    if ($digits < 10) {
        return '0'.$digits;
    } else {
        return $digits;
    }
}



#print $file->all();

#$file->close();
?>