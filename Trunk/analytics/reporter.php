<?php
define('DIRECT_ACCESS_CHECK',false);
require_once('globals.php');
require_once CONTROLLERS_DIRECTORY.DS.'main.php';

$report = array();
$sessions = array();

$directoryParser = new Filesystem_Directory(
    SITE_FOLDER.'logs','*.log',
    'read','DominoLog'
);
$directoryParser->getFileObject = true;

$breakout = 0;
foreach ($directoryParser as $file) {
    print $file->fullpath."\n";
    
    $start = microtime(true);
    $counter = 0;
    foreach ($file as $line) {
        $url = get_path($line);
        inc_counter($line,$url);
        $counter++;
    }
    
    $period = get_elapsed($start);
    $speed = round($counter/(microtime(true)-$start),2);
    print "Parsed: $counter ($period)\t(Speed: $speed lines/sec)\n";
    
    $breakout++;
    if ($breakout > 1) {
        break;
    }
}

$oFile = new Filesystem_File(SITE_FOLDER.'logs','report.csv');
$oFile->open('write','csv');
$oFile->write(array('URL','Sessions','Users','Hits'));

uasort($report['page'], 'hits_sort');
foreach ($report['page'] as $page) {
    $row = array(
        $page['value'],count($page['users']),
        count($page['sessions']),$page['hits']
    );
    
    $oFile->write($row);
}




function hits_sort($a, $b) {
    if ($a['hits'] == $b['hits']) {
        if (count($a['sessions']) == count($b['sessions'])) {
            if (count($a['users']) == count($b['users'])) {
                return 0;
            }
            return (count($a['users']) > count($b['users'])) ? -1 : 1;
        }
        return (count($a['sessions']) > count($b['sessions'])) ? -1 : 1;
    }
    return ($a['hits'] > $b['hits']) ? -1 : 1;
}

function inc_counter($line,$url) {
    global $report;
    
    if (!isset($report['page'])) {
        $report['page'] = array();
    }
    
    $ref = md5($url);
    if (!isset($report['page'][$ref])) {
        $report['page'][$ref] = array(
            'value' => $url, 'hits' => 1,
            'users' => array(), 'sessions' => array()
        );
    } else {
        $report['page'][$ref]['hits']++;
    }
    
    $unid = get_user_unid($line);
    if (!isset($report['page'][$ref]['users'][$unid])) {
        $report['page'][$ref]['users'][$unid] = 1;
    } else {
        $report['page'][$ref]['users'][$unid]++;
    }
    
    $now = $line['datetime']->epoc;
    if (!isset($sessions[$unid])) {
        $sessions[$unid] = array(
            'id'=>md5(microtime()),
            'time'=>$now
        );
    } else {
        if (($sessions[$unid]['time']+(30*60)) < $now) {
            $sessions[$unid]['id'] = md5(microtime());
            $sessions[$unid]['time'] = $now;
        } else {
            $sessions[$unid]['time'] = $now;
        }
    }
    
    $sessionID = $sessions[$unid]['id'];
    if (!isset($report['page'][$ref]['sessions'][$sessionID])) {
        $report['page'][$ref]['sessions'][$sessionID] = 1;
    } else {
        $report['page'][$ref]['sessions'][$sessionID]++;
    }
}

function get_user_unid($line) {
    $unid = '';
    
    if ($line['cookie']) {
        if (isset($line['cookie']['__utma'])) {
            $unid = md5($line['cookie']['__utma']);
        }
    } else {
        $unid = md5($line['ip'].$line['agent']);
    }
    
    return $unid;
}

function get_path($data) {
    $url = $data['domain'].$data['request'];
    $count = preg_match('/\A(.*?)(?:\/|\Z)/',$data['protocol'],$matches);
    if ($count > 0) {
       $url = $matches[1].'://'.$url;
    }
    $url = new Filesystem_Path(strtolower($url));
    return $url;
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




/*$csv = new Filesystem_File(
    SITE_FOLDER.'util'.DS.'test'.DS,
    'external_user_hits_pt1.csv'
);
$csv->open('read','csv');
$csv->firstLineHeaders = true;
$csv->set_data_type('Users','int');
$csv->set_data_type('Hits','int');

foreach ($csv as $line) {
    print_r($line);
}*/
?>