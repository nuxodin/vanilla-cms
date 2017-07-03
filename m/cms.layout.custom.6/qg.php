<?php
namespace qg;

class layoutCustom6 {
	static function layoutPage(){
		static $Page = null;
		if ($Page === null) {
			$Tmp = Page(5)->Children(['module' => 'cms.layout.custom.6']);
			$Page = array_shift($Tmp);
			if (!$Page) {
				$Page = Page(5)->createChild(['module'=>'cms.layout.custom.6', 'access'=>1, 'offline'=>0]);
				$Page->Title(L(), 'cms.layout.custom.6');
			}
		}
		return $Page;
	}
	function getImages() {
		trigger_error('used?');
		$path = $this->customPath().'img/';
		!is_dir($path) && mkdir($path);
		$url = path2uri($path);
		$ret = [];
		foreach (scanDir($path) as $f) {
			if (!is_file($path.$f)) continue;
			$ret[] = $url.$f;
		}
		return $ret;
	}
}

qg::on('action', function() {
	if (isset($_GET['mLayoutCustom6_upload'])) {
		$LPage = layoutCustom6::layoutPage();
		if ($LPage->access() < 2) exit(0);
		$File = $_FILES['file'];
		move_uploaded_file($File['tmp_name'], appPATH.'qg/cms.layout.custom.6/pub/img/'.$File['name']);
		exit(1);
	}
});
