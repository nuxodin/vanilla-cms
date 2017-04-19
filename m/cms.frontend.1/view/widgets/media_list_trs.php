<?php
namespace qg;

$Cont->Files();
foreach ($Cont->_FilesAll as $name => $F) { ?>
	<tr itemid="<?=hee($name)?>">
		<td class=-preview title="<?=hee(L('Klicken um die Datei zu ersetzen'))?>">
			<?php
			$ext = $F->extension();
			switch ($ext) {
				case 'jpg' :
				case 'jpeg':
				case 'gif' :
				case 'png' :
				case 'svg' :
					echo '<img src="'.$F->url().'/w-60/h-40/dpr-0/max/'.hee($F->name()).'" '.($ext==='svg'?'height=40':'').' alt="">';
					break;
				default:
					$text = $F->exists() ? $ext : 'upload';
					?>
					<svg width=60 height=40 style="display:block">
						<rect x=0 y=0 width=60 height=40 xrx=5 xry=3 fill="var(--cms-color);"></rect>
						<text x=30 y=24 fill="#fff"><tspan text-anchor=middle><?=$text?></tspan></text>
					</svg>
					<?php
			}
			?>
		<td class=-link>
			<?php
			if (!$F->exists()) {
				echo L('Platzhalter');
			} else {
				?><a title="<?=$F->name()?>" href="<?=$F->url().'/'.hee($F->name())?>" target=_blank><?=hee($F->vs['name'])?></a><?php
			}
			if ($name[0] !== '_') {
				?><div style="font-size:11px; color:#999; font-style:italic">(<?=hee($name)?>)</div><?php
			}
			?>
		<td class=-size>
			<?= $F->exists() ? byte_format($F->size()) : '' ?>
		<td class=-handle>
		<td class=-delete>
<?php } ?>
