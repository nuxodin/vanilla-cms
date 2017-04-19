<?php
namespace qg;

$separator = hee($Cont->SET['breadcrumb separator']->v);
$separator = str_replace(' ', ' ', $separator) ?: ' - ';

$previewImage = $Cont->SET['preview image']->setType('bool')->v;
if ($previewImage) $previewImageQuery = $Cont->SET->make('preview image query','/w-120/h-400/max/')->v;

foreach ($res AS $id => $r) {
	$C = Page($id);
	$P = $C->Page;
	$href = $C->url();
	?>
	<div class=-item>

		<?php
		if ($previewImage) foreach ($C->Files() as $F) {
			if (!Image::able($F->path)) continue;
			echo '<a href="'.$href.'"><img src="'.$F->url().'/'.$previewImageQuery.'/'.$F->name().'"></a>'; break;
		}
		?>

		<a href="<?=$href?>" class=-title><?=(string)$C->Title() ? $C->Title() : $P->Title() ?></a>

		<?php if ($Cont->SET['breadcrumb']->setType('bool')->v) { ?>
			<div class=-breadcrumb>
				<?php
				$all = [];
				foreach (Page($id)->Path() as $PathP) {
					if ($PathP->id === 1) continue;
					if (!trim(strip_tags($PathP->Title()))) continue;
					//if ($PathP === $P) continue;
					$all[] = cms_link($PathP);
				}
				echo implode($separator, $all);
				?>
			</div>
		<?php } ?>

		<a href="<?=$href?>" class=-content>
			<?=cutSearch1($r['text'], $search, 7);?>
		</a>

		<?php if (isset($r['files'])) { ?>
			<div class=-files>
			<?php foreach ($r['files'] as $vs) { $F = dbFile($vs['file_id']); ?>
				<a style="display:block" target=_blank href="<?=$F->url().'/'.$F->name()?>"><?=$F->vs['name']?> (<?=byte_format($F->size())?>)</a>
				<?=cutSearch1($F->vs['text'], $search, 1);?>
			<?php } ?>
			</div>
		<?php } ?>

	</div>
<?php
}
if (!isset($r)) { echo L('Keine Ergebnisse gefunden'); }
