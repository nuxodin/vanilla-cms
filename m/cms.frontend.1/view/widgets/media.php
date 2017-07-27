<?php namespace qg ?>
<div cmsconf=contMedia_overview class=qgCmsFileManager pid=<?=$Cont?>>
	<button class=-uploadBtn><?=L('hochladen')?></button>
	<input class=-addExistingFile type=qgcms-file id=<?=$available=i()?> placeholder="<?=hee(L('bestehende Datei'))?>">
	<?php if (count($Cont->FilesAndPlaceholders()) > 1) { ?>
		<select class=-sortFilesSelect>
			<option value> <?=L('sortieren nach...')?>
			<option value=name><?=L('Name')?>
			<option value=name_reverse><?=L('Name von hinten')?>
			<option value=date><?=L('Datum')?>
			<option value=reverse><?=L('umkehren')?>
		</select>
		<select class=-deleteFilesSelect>
			<option> <?=L('löschen...')?>
			<option value=double><?=L('doppelte')?>
			<option value=all><?=L('alle')?>
		</select>
	<?php } ?>
	<br>
	<br>
	<div cmsconf=media_list id=cmsWidgetContent_media_list>
		<?php include sysPATH.'cms.frontend.1/view/widgets/media_list.php'; ?>
	</div>
</div>
