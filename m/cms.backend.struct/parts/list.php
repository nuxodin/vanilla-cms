<?php
namespace qg;

global $aCont;
$aCont = $Cont;

//$filter = ['type'=>'*'];
$filter = [];

$rec_createStruct = null;
$rec_createStruct = function($Page) use (&$rec_createStruct, $filter) {
	global $aCont;
	static $level = -1;
	global $openPageNodes;

	foreach ($Page->Children($filter) AS $id => $SubPage) {
		$level++;
		$open = isset($openPageNodes[$SubPage->id]);
		echo '<tr '.($SubPage->vs['type']=='c'?'class=-isCont':'').'>';
			echo '<td style="text-align:right; font-weigth:bold">';
				echo '<a title="als Startpunkt setzen" href="'.Url()->addParam('rp',$id).'">'.$SubPage.'</a>';
			echo '<td style="padding-left:'.($level*15).'px; white-space:nowrap">';
				if ($SubPage->Children($filter)) {
					echo '<a class="-toggle '.($open?'-minus':'-plus').'" href="javascript:$fn(\'page::loadPart\')('.$aCont.',\'list\',{toggleOpen:'.$SubPage.',value:'.($open?0:1).'}).run()"></a>';
				} else {
					echo '<span class=-toggle></span>';
				}
				echo '<a style="vertical-align:middle" href="'.$SubPage->url().'" title="'.(string)$SubPage->title().'">'.((string)$SubPage->title()?cutText($SubPage->title(),50):'(kein Text)').'</a>';
			echo '<td>';
				$ok = !$SubPage->vs['online_start'] || $SubPage->vs['online_start'] < time();
				$datetime = $SubPage->vs['online_start'] ? strftime('%d.%m.%Y %H:%M', $SubPage->vs['online_start']) : '';
				$date = $SubPage->vs['online_start'] ? strftime('%d.%m.%Y', $SubPage->vs['online_start']) : '---';
				switch ($SubPage->access()) {
					case '0':
						echo '---';
						break;
					case '1':
						echo '<span style="color:'.($ok?'#8a8':'#a88').'">'.$date.'</span>';
						break;
					default:
						echo '<div style="position:relative"><span style="color:'.($ok?'green':'red').'">'.$date.'</span></div>';
				}
			echo '<td>';
				$max = 60*60*24*7;
				$diff = min($SubPage->vs['online_end']-time(), $max);
				$diff = max($diff, 0);
				$r = 256 - floor(256 * $diff / $max);
				$r = $SubPage->vs['online_end'] ? $r : 0;
				$g = floor((256 * $diff / $max)) - 128;
				$g = $SubPage->vs['online_end'] ? $g : 128;

				$datetime = $SubPage->vs['online_end'] ? strftime('%d.%m.%Y %H:%M', $SubPage->vs['online_end']) : '';
				$date = $SubPage->vs['online_end'] ? strftime('%d.%m.%Y', $SubPage->vs['online_end']) : '---';

				switch ($SubPage->access()) {
					case '0':
						echo '---';
						break;
					case '1':
						echo '<span style="color:'.($ok?'#8a8':'#a88').'">'.$date.'</span>';
						break;
					default:
						echo '<div style="position:relative"><span style="color:rgb('.$r.','.$g.',0)">'.$date.'</span></div>';
				}

			echo '<td>';
				$v = $SubPage->vs['access'];
				switch ($SubPage->access()) {
					case '0':
						echo '---';
						break;
					case '1':
					case '2':
						echo '<span style="color:'.($v?'#666':'#666').'" href="">'.( $v===null ? 'vererbt' : $v ? 'public' : 'private' ).'</span>';
						break;
					default:
						echo '<a onclick="return toggleAccess(this, '.$SubPage.')" style="color:'.($v===null?'#aaa':($v ? 'green':'red') ).'" href="">'.($v===null?'vererbt':($v?'public':'private')).'</a>';
				}
			echo '<td>';
				$v = $SubPage->vs['visible'];
				switch ($SubPage->access()) {
					case '0':
						echo '---';
						break;
					case '1':
						echo '<span style="color:'.($v?'#666':'#666').'" href="">'.($v?'sichtbar':'unsichtbar').'</span>';
						break;
					default:
						echo '<a onclick="return toggleVisible(this, '.$SubPage.')" style="color:'.($v?'green':'red').'" href="">'.($v?'sichtbar':'unsichtbar').'</a>';
				}
			echo '<td>';
				$v = $SubPage->vs['searchable'];
				switch ($SubPage->access()) {
					case '0':
						echo '---';
						break;
					case '1':
						echo '<span style="color:'.($v?'#666':'#666').'" href="">'.($v?'durchsuchbar':'nicht durchsuchbar').'</span>';
						break;
					default:
						echo '<a onclick="return toggleSearchable(this, '.$SubPage.')" style="color:'.($v?'green':'red').'" href="">'.($v?'durchsuchbar':'nicht durchsuchbar').'</a>';
				}
			echo '<td>';
				echo '<span>'.$SubPage->vs['module'].'</span>';

		if ($open) $rec_createStruct($SubPage);
		$level--;
	}
};

$rec_createStruct(Page($rootPageNode));
