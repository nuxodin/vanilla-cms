<?php
namespace qg;

qg::need('core');

require_once sysPATH.'cms/lib/cms.class.php';
require_once sysPATH.'cms/lib/Page.class.php';
require_once sysPATH.'cms/lib/functions.php';
require_once sysPATH.'cms/lib/events.php';

isset($_GET['qgCms_editmode']) && G()->SET['cms']['editmode']->setUser($_GET['qgCms_editmode']);

qg::on('action', function(){

	if (isset($_FILES['cmsPageFile'])) {
		if ($_FILES['cmsPageFile']['error']) {
			$error = [
				1 => 'Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe.',
				3 => 'Die Datei wurde nur teilweise hochgeladen.',
				4 => 'Es wurde keine Datei hochgeladen.',
				6 => 'Fehlender temporärer Ordner.',
				7 => 'Speichern der Datei auf die Festplatte ist fehlgeschlagen.',
				8 => 'Eine PHP Erweiterung hat den Upload der Datei gestoppt.'
			][$_FILES['cmsPageFile']['error']];
			Answer(['error'=>$error]);
		}
		$image_fix_orientation = function($filename) { // todo: preserve exif
			if (@exif_imagetype($filename) !== 2) return;
			$exif = @exif_read_data($filename);
			if (!isset($exif['Orientation'])) return;
			$ori = $exif['Orientation'];
			$rotations = [
				'3' => 180,
				'6' => -90,
				'8' => 90,
			];
			if (!isset($rotations[$ori])) return;
			$image = imagecreatefromjpeg($filename);
			$image = imagerotate($image, $rotations[$ori], 0);
			imagejpeg($image, $filename, 94);
		};

		$Page = Page($_GET['cmspid']);
		if ($Page->access() > 1) {
			$_FILES['cmsPageFile']['name'] = str_replace('%','%25',$_FILES['cmsPageFile']['name']);
			$image_fix_orientation($_FILES['cmsPageFile']['tmp_name']);

			qg::fire('page::file_upload-before');
			$File = isset($_GET['replace']) && $_GET['replace'] ? $Page->File($_GET['replace']) : $Page->FileAdd();
			$File->replaceFromUpload($_FILES['cmsPageFile']);
			qg::fire('page::file_upload-after');

			Answer(['id'=>(string)$File, 'url'=>$File->url().'/'.$File->vs['name']]);
		}
	}

	if (isset($_GET['qgCms_page_files_as_zip'])) {
		$P = Page($_GET['qgCms_page_files_as_zip']);
		if ($P->access() < 1) exit('no access');
      	if (!$P->Files())     exit('no files');

		ini_set('max_execution_time', '300');

		$Zip = new Zip;
		$tmpfname = appPATH.'cache/tmp/pri/'.randString().'.zip';
		$Zip->open($tmpfname, Zip::CREATE);
		foreach ($P->Files() as $File) $Zip->addFile($File->path, $File->name());
		$Zip->close();
		$filename = $_GET['filename'] ?? 'files_'.$P.'.zip';
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Content-Length: '.filesize($tmpfname));
		readfile($tmpfname);
		unlink($tmpfname);
		exit();
	}
});
qg::on('dbFile::access2', function($e){
	if ($e['access']) return;
	foreach (D()->query("SELECT page_id FROM ".table('page_file')." WHERE file_id = ".$e['File']) as $vs) {
		$P = Page($vs['page_id']);
		if ($P->access()) { // todo: better $P->isReadable() ?
			$e['access'] = 1;
			return;
		}
	}
});
qg::on('textpro_lang::get',function($data){
	$Obj = $data['obj'];
	if (isset($Obj->Text->edit)) {
		if (!$Obj->Text->edit) {
			$Obj->value = preg_replace_callback('/cmspid:\/\/([0-9]+)/' ,'qg\cmsModifyTexts_replaceLinks', $Obj->value);
		} else {
		}
		$Obj->value = preg_replace_callback('|/dbFile/([0-9]+)/u-([a-z0-9]+)/|' ,'qg\cmsModifyTexts_replaceFileUrls', $Obj->value);
	}
});
function cmsModifyTexts_replaceLinks($treffer) {
	$P = Page((int)$treffer[1]);
	if (!$P->is()) {
		trigger_error('dead intern link '.$treffer[0]);
		return '#';
	}
	return $P->url();
}
function cmsModifyTexts_replaceFileUrls($treffer) { // edit only
	$File = dbFile($treffer[1]);
	if (!$File->exists()) trigger_error('dbFile does not exist: '.$treffer[1]);
	$u = substr($File->vs['md5'],0,4);
	return '/dbFile/'.$treffer[1].'/u-'.$u.'/';
}
