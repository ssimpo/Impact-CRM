<?php
class Plugin_series_sample {

	public function run ($attributes) {
		
		$HTML = '';
		
		if (array_key_exists('type',$attributes)) {
			switch ($attributes['type']) {
				case 'recent_sermons':
					$HTML = $this->_recent_sermons($attributes);
					break;
				case 'theme_table':
					$HTML = $this->_theme_table($attributes);
					break;
			}
		}
		
		return $HTML;
	}
	
	private function _recent_sermons ($attributes) {
		
		$HTML = '';
		$nr = $attributes['rows'];
		if (!is_numeric($nr)) {
			$nr = 6;
		}
		
		$sermons = db_recordset('SELECT ID,mimetype,title,name FROM view_podcastsermon ORDER BY Date DESC LIMIT '.$nr,'ID',', ');
		if ($sermons) {
			foreach ($sermons as $sermon) {
				if (--$nr < 0) {
					break;
				}
				if ($nr == 0) {
					$class1 = 'r';$class2 = '';
				} else {
					$class1 = 'r b';$class2 = 'b';
				}
				
				$mediatype = $sermon['mimetype'];
				$medialinktitle = '[Listen]';
				if (contains($mediatype,'video')) {
					$medialinktitle = '[Watch]';
				}
			
				$HTML .= '<tr><td class="'.$class1.'">'.strip_and_encode($sermon['title']).'</td><td class="'.$class2.'"><a href="/sermons?id='.$sermon['ID'].'&amp;play=yes" title="Sermon by '.$sermon['author'].'">'.$medialinktitle.'</a></td></tr>';
			}
			
			if (trim($HTML) != '') {
				if (array_key_exists('title',$attributes)) {
					if (trim($attributes['title']) != '') {
						$HTML = '<caption>'.$attributes['title'].'</caption>'.$HTML;
					}
				}
				$HTML = '<table summary="A 3-column, '.count($sermons).'-row table.  Table shows recent sermons with links to download or listen." style="clear:both">'.$HTML.'</table>';
			}
		}
		
		
		return $HTML;
	}
	
	private function _theme_table ($attributes) {
		$HTML = '';
		
		if (array_key_exists('title',$attributes)) {
		
			$sermons = db_recordset('
				SELECT title,date,series,CONCAT(path,filename) AS path,publish
				FROM podcast
				INNER JOIN objects ON podcast.objectID = objects.ID
				WHERE series="'.$attributes['title'].'"
			');
			if ($sermons) {
				$HTML .= '<table summary="A 3-column, '.count($sermons).'-row table. First row is a header.  First column contains the date and the second contains the subject." cellspacing="0">';
				$HTML .= '<caption>'.$attributes['title'];
				if (array_key_exists('subtitle',$attributes)) {
					$HTML .= '<br /><br /><span class="sml" style="font-weight:normal">'.$attributes['subtitle'].'</span>';
				}
				if (array_key_exists('sermonlink',$attributes)) {
					$HTML .= '<br /><a href="'.$attributes['sermonlink'].'" class="sml" style="font-weight:normal">...more sermons</a><br /><br />';
				}
				$HTML .= '</caption><thead><tr><th class="b r" style="width:33%">Date</th><th colspan="2" class="b">Subject</th></tr></thead><tbody><tr>';
				
				$sHighted = 1;$i =0;
				foreach ($sermons as $sermon) {
					
					$sDate = substr($sermon['date'],0,10);
					$sermon_date = mktime(
						0,0,0,
						substr($sermon['date'], 5, 2),
						substr($sermon['date'], 8, 2),
						substr($sermon['date'], 0, 4)
					);
					$current_date = getdate(time());
					$current_date = mktime(0,0,0,$current_date['mon'],$current_date['mday'],$current_date['year']);
					
					if (($sermon_date >= $current_date) && ($sHighted)) { 
						$HTML .= '<tr class="highlight">';
						$sHighted = 0;
					} else {
						$HTML .= '<tr>';
					}
					
					$HTML .= '<td class="r">'.date('D jS M Y',$sermon_date).'</td><td>'.$sermon['title'].'</td>';
					
					if ($i==0) {
						$HTML .= '<td style="width:16%" class="sml">';
					} else {
						$HTML .= '<td class="sml">';
					}
					if ((bool) $sermon['publish']) {
						$HTML .= '<a type="audio/mpeg" href="'.$sermon['path'].'">[Listen - MP3]</a></td>';
					} else {
						$HTML .= '&nbsp;</td>';
					}
					
					$i++;
					$HTML .= '</tr>';
				}
				
				$HTML .= '</table>';
			}
		}
		
		return $HTML;
	}
	
}
?>