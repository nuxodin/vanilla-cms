<?php
namespace qg;

class serverInterface_cmslayouter3 {
	static $Layouter = null;
	static function saveCustomCss($pid, $css) {
		return file_put_contents(self::$Layouter->customCssPath(), $css);
	}
	static function deleteImg($pid, $name) {
		unlink( self::$Layouter->customPath().'img/'.$name );
	}
	static function getGoogleFonts($pid) {
		return self::$Layouter->googleFonts();
	}
	static function getImages($pid) {
		return self::$Layouter->getImages();
	}
	static function onBefore($fn, $pid) {
		$Cont = Page($pid);
		self::$Layouter = new cmsLayouter3($Cont->vs['module']);
		$LPage = self::$Layouter->getLayoutPage();
		if ($LPage->access() < 2) return false;
	}
}
