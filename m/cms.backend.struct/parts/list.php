<?php
namespace qg;

global $aCont;
$aCont = $Cont;


$loop_conts = null;
$loop_conts = function($Page) use (&$loop_conts) { // todo: check access?
	$data = [
		'num_online_start_notInherit' => 0,
		'num_online_end_notInherit'   => 0,
		'num_access_notInherit'       => 0,
	];
	foreach($Page->Conts() as $C) {
		$childData = $loop_conts($C);
		$data['num_online_start_notInherit'] += $childData['num_online_start_notInherit'];
		$data['num_online_end_notInherit']   += $childData['num_online_end_notInherit'];
		$data['num_access_notInherit']       += $childData['num_access_notInherit'];

		if ($C->vs['online_start'] !== null) ++$data['num_online_start_notInherit'];
		if ($C->vs['online_end']   !== null) ++$data['num_online_end_notInherit'];
		if ($C->vs['access']       !== null) ++$data['num_access_notInherit'];
	}
	return $data;
};


$rec_createStruct = null;
$rec_createStruct = function($Page, $filter) use (&$rec_createStruct, $loop_conts) {
	global $aCont;
	static $level = -1;
	global $openPageNodes;


	foreach ($Page->Children($filter) AS $id => $SubPage) {
		$level++;

		$nextFilter = $level === -1 || $SubPage->vs['type'] === 'c' ? ['type'=>'*'] : [];

		$contsData = $loop_conts($SubPage);
		$open = isset($openPageNodes[$SubPage->id]);
		echo '<tr '.($SubPage->vs['type']=='c'?'class=-isCont':'').'>';
			echo '<td style="text-align:right; font-weigth:bold">';
				echo '<a title="als Startpunkt setzen" href="'.Url()->addParam('rp',$id).'">'.$SubPage.'</a>';
			echo '<td style="padding-left:'.($level*15).'px">';
				echo '<div style="display:flex; align-items:center">';
				if ($SubPage->Children($nextFilter)) {
					echo '<a class="-toggle '.($open?'-minus':'-plus').'" onclick="$fn(\'page::loadPart\')('.$aCont.',\'list\',{toggleOpen:'.$SubPage.',value:'.($open?0:1).'}).run()"></a>';
				} else {
					echo '<span class=-toggle></span>';
				}
				if ($SubPage->access() < 1) {
					echo '<span style="flex:1; color:#bbb">(kein Zugriff)</span>';
				} else {
					echo '<input value="'.hee($SubPage->Title()).'" style="flex:1; background:transparent; border:none; margin:0 10px 0 0; padding:0" '.($SubPage->access()<2?'disabled':'cmstxt="'.$SubPage->Title()->id.'"').' >';
				}
				echo '<a style="vertical-align:middle" href="'.hee($SubPage->url()).'" title="open"><img alt="open" src="'.sysURL.'cms.frontend.1/pub/img/open-link.svg" style="display:block; width:18px; height:18px"></a>';
				echo '</div>';

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
				$b = 0;
				if ($SubPage->vs['online_end'] === null) {
					$data = 'vererbt';
					$r = 200;
					$g = 200;
					$b = 200;
				}
				if ($SubPage->vs['online_end'] === '0') {
					$data = 'unbeschrenkt';
					$r = 0;
					$g = 0;
					$b = 255;
				}
				$date = $SubPage->vs['online_end'] === null ? 'vererbt' : ($SubPage->vs['online_end'] ? strftime('%d.%m.%Y', $SubPage->vs['online_end']) : 'immer');

				switch ($SubPage->access()) {
					case '0':
						echo '---';
						break;
					case '1':
						echo '<span style="color:'.($ok?'#8a8':'#a88').'">'.$date.'</span>';
						break;
					default:
						echo '<span style="position:relative"><span style="color:rgb('.$r.','.$g.','.$b.')">'.$date.'</span></span>';

						if ($contsData['num_online_end_notInherit']) {
							echo ' <span title="'.hee('Inhalte bei denen "Online bis" nicht vererbt wird!').'" style="display:inline-block; background:yellow; border-radius:50%; padding:0 3px">'.$contsData['num_online_end_notInherit'].'</span>';
						}
				}

			echo '<td>';
				$v = $SubPage->vs['access'];
				switch ($SubPage->access()) {
					case '0':
						echo '---';
						break;
					case '1':
					case '2':
						echo '<span style="color:'.($v?'#666':'#666').'" href="">'.( $v===null ? 'vererbt' : $v ? 'ja' : 'nein' ).'</span>';
						break;
					default:
						echo '<a onclick="return toggleAccess(this, '.$SubPage.')" style="color:'.($v===null?'#aaa':($v ? 'green':'red') ).'" href="">'.($v===null?'vererbt':($v?'ja':'nein')).'</a>';

						if ($contsData['num_access_notInherit']) {
							echo ' <span title="Inhalte bei denen der Zugriff nicht vererbt wird!" style="display:inline-block; background:yellow; border-radius:50%; padding:0 3px">'.$contsData['num_access_notInherit'].'</span>';
						}
				}
			echo '<td>';
				$v = $SubPage->vs['visible'];
				switch ($SubPage->access()) {
					case '0':
						echo '---';
						break;
					case '1':
						echo '<span style="color:'.($v?'#666':'#666').'" href="">'.($v?'ja':'nein').'</span>';
						break;
					default:
						echo '<a onclick="return toggleVisible(this, '.$SubPage.')" style="color:'.($v?'green':'red').'" href="">'.($v?'ja':'nein').'</a>';
				}
			echo '<td>';
				$v = $SubPage->vs['searchable'];
				switch ($SubPage->access()) {
					case '0':
						echo '---';
						break;
					case '1':
						echo '<span style="color:'.($v?'#666':'#666').'" href="">'.($v?'ja':'nein').'</span>';
						break;
					default:
						echo '<a onclick="return toggleSearchable(this, '.$SubPage.')" style="color:'.($v?'green':'red').'" href="">'.($v?'ja':'nein').'</a>';
				}
			echo '<td>';
				echo '<span>'.$SubPage->vs['module'].'</span>';

		if ($open) $rec_createStruct($SubPage, $nextFilter);
		$level--;
	}
};


$rec_createStruct(Page($rootPageNode), ['type'=>'*']);
