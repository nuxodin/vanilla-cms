<?php
namespace qg;

class cmsLayouter3 {
	function __construct($module) {
		$this->mod = $module;
	}
	function getLayoutPage() {
        if (!isset($this->LPage)) {
            $Tmp = Page(5)->Children(['module' => $this->mod]);
    		$this->LPage = array_shift($Tmp);
    		if (!$this->LPage) {
    			$this->LPage = Page(5)->createChild(['module'=>$this->mod, 'access'=>1, 'offline'=>0]);
    			$this->LPage->Title(L(),$this->mod);
    		}
        }
		return $this->LPage;
	}
	function customPath() {
		return appPATH.'qg/'.$this->mod.'/pub/';
	}
	function customCssPath() {
		return $this->customPath().'custom.css';
	}
	function googleFonts() {
		$gFont = $this->getLayoutPage()->SET['google-font']->v;
		$gFonts = [];
		if ($gFont) {
			$gFonts = urldecode( preg_replace('/.*family=/','',$gFont) );
			$gFonts = explode('|',$gFonts);
			foreach ($gFonts as $key => $gF) {
				$gFonts[$key] = preg_replace('/:.*/','',$gF);
			}
		}
		return $gFonts;
	}
	function init() {
		if (is_file(appPATH.'qg/'.$this->mod.'/pub/main.js')) {
			html::addJsFile(appURL.'qg/'.$this->mod.'/pub/main.js');
		}
		html::addCssFile(appURL.'qg/'.$this->mod.'/pub/base.css',   'dr8og', false);
		html::addCssFile(appURL.'qg/'.$this->mod.'/pub/custom.css', 'dr8og', false);
		if ($gFont = $this->getLayoutPage()->SET['google-font']->v) {
			$gFont = str_replace('|', '%7C', $gFont); // rawurlencode ?
			html::$head .= '<link rel=stylesheet href="'.hee($gFont).'">';
			G()->csp['style-src']['https://fonts.googleapis.com'] = 1;
		}
		if ($this->LPage->edit) {
			html::addJsFile( sysURL.'cms.layouter3/pub/qgElSty/qgCssProps.js','43l5k');
			html::addJsFile( sysURL.'cms.layouter3/pub/qgElSty/q1CssText.js','43l5k');
			html::addJsFile( sysURL.'cms.layouter3/pub/qgElSty/qgStyleEditor.js','43l5k');
			html::addJsFile( sysURL.'cms.layouter3/pub/qgElSty/qgStyleSheetEditor.js','43l5k');
			html::addJsFile( sysURL.'cms.layouter3/pub/qgElSty/spectrum.js','43l5k');
			html::addCssFile(sysURL.'cms.layouter3/pub/qgElSty/spectrum.css','43l5k',0);
			html::addCssFile(sysURL.'cms.layouter3/pub/qgElSty/main.css','43l5k');
			html::addJsFile( sysURL.'cms.layouter3/pub/plupload/js/plupload.full.js','43l5k');
			html::addJsFile( sysURL.'cms.layouter3/pub/edit.js','43l5k');
		}
	}
	function getImages() {
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

qg::on('action', function() { // deprecated?
	if (isset($_GET['mCmsLayouter3_uploadPid'])) {
		$pid = (int)$_GET['mCmsLayouter3_uploadPid'];
		$Cont = Page($pid);
		$Layouter = new cmsLayouter3($Cont->vs['module']);
		$Layouter->init();
		$LPage = $Layouter->getLayoutPage();
		if ($LPage->access() < 2) exit(0);

		$File = $_FILES['file'];
		move_uploaded_file($File['tmp_name'], $Layouter->customPath().'img/'.$File['name']);
		exit(1);
	}
});
