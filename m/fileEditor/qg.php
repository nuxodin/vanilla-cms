<?php
namespace qg;

qg::on('action', function() {
	if (!isset($_GET['file']) || strpos(appRequestUri,'editor') !== 0) return;
	$file = $_GET['file'];
	!isset($_SESSION['fileEditor']['allow'][$file]) && !Usr()->superuser && exit('no access');
	!is_file($file) && exit('file does not exist');
	if ($ask = G()->ASK) {
		$done = 0;
		if (isset($ask['save']) && is_file($file)) {
			copy($file, appPATH.'cache/tmp/pri/fileEditorBackup_'.urlencode($file).'_'.date('dmYhi'));
			if (file_put_contents($file, $ask['save']) && is_writable($file)) {
				$done = 1;
			}
		}
		Answer($done);
	}
	$T = new template(['file'=>$file]);
	echo $T->get(sysPATH.'fileEditor/view/codemirror.php');
	exit();
});
