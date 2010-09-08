<?php
$config = simplexml_load_file('settings.xml');
$tparser = new templater($application);

foreach ($config->scroller as $scroller) {
	if (isEqual($scroller[id],$scrollerID)) {
		foreach ($scroller->item as $item) {
			array_push($slides,rawurlencode($tparser->parse('<template:feature name="'.$item[id].'" />')));
		}
	}
}

$JSON = '{items:["'.implode('","',$slides).'"]}';
?>