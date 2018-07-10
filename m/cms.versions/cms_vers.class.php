<?php
namespace qg;

class cms_vers {
    static $space = 0;
    static $log   = 0;

	static function page_load_runtime_cache($Cont){
		$Cont->Files();
		$Cont->Classes();
		foreach (L::$all as $l) {
            $Cont->Title($l);
            $Cont->urlSeo($l);
			foreach ($Cont->Texts() as $Text) $Text->get($l)->get();
		}
		// if (G()->SET['cms']['pages']->has($Cont->id)) {
		// 	$Cont->SET->get(); // not needed in space only?
		// } else {
		// 	//$Cont->_SET = []; // bad
		// }
	}
	static function publishCont($pid, $fromSpace, $fromLog, $toSpace, $subPages=false) {
		$generate = null;
		$generate = function ($pid) use(&$generate, $fromSpace, $fromLog, $toSpace, $subPages) {
			if (Page($pid)->access() > 1) {
                vers::tableEntriesCopyTo('page',       ['id'=>$pid],                  $fromSpace, $fromLog, $toSpace);
                $filter = $subPages ? ['basis'=>$pid] : ['basis'=>$pid, 'type'=>'c'];
                vers::tableEntriesCopyTo('page',       $filter,  $fromSpace, $fromLog, $toSpace); // remove sub contents not in this version!
    			vers::tableEntriesCopyTo('page_file',  ['page_id'=>$pid],             $fromSpace, $fromLog, $toSpace);
    			vers::tableEntriesCopyTo('page_class', ['page_id'=>$pid],             $fromSpace, $fromLog, $toSpace);
    			vers::tableEntriesCopyTo('page_text',  ['page_id'=>$pid],             $fromSpace, $fromLog, $toSpace);
    			vers::tableEntriesCopyTo('page_url',   ['page_id'=>$pid],             $fromSpace, $fromLog, $toSpace);

                $title_id = D()->one("SELECT title_id FROM ".vers::view('page',$toSpace)." WHERE id = ".$pid);
    			vers::tableEntriesCopyTo('text',       ['id'=>$title_id], $fromSpace, $fromLog, $toSpace); // todo: cached!

                foreach (D()->col("SELECT text_id FROM ".vers::view('page_text',$toSpace)." WHERE page_id = ".$pid) as $text_id) {
    				vers::tableEntriesCopyTo('text',   ['id'=>$text_id],  $fromSpace, $fromLog, $toSpace);
    			}
    			foreach (D()->col("SELECT file_id FROM ".vers::view('page_file',$toSpace)." WHERE page_id = ".$pid) as $file_id) {
    				vers::tableEntriesCopyTo('file',   ['id'=>$file_id],  $fromSpace, $fromLog, $toSpace);
    			}
    			$basis = G()->SET['cms']['pages']->i;
    			vers::qgSettingsCopyTo($basis, $pid, $fromSpace, $fromLog, $toSpace);
            }
            foreach (D()->col("SELECT id FROM ".vers::view('page',$toSpace)." WHERE basis = ".$pid." ".($subPages?'':" AND type = 'c' ")) as $id) {
                $generate($id);
			}
		};

        $oldVers = vers::setVers($fromSpace,$fromLog); // in from-space all pages should exist => calling access on it
		settingArray::$All = cms::$_Pages = dbFile::$All = [];
		$generate($pid);

		/* generate urls */
		settingArray::$All = cms::$_Pages = dbFile::$All = [];
        vers::setVers($toSpace,0);

        // urls, needed? => triggers page::modify
        $P = Page($pid);
        foreach (L::$all as $l) if ($P->urlSeoGenerated($l) !== $P->urlSeo($l)) $P->urlSeoGen($l); // nicht ganz sauber
		//Page($pid)->urlsSeoGen();


        vers::setVers($oldVers);
		settingArray::$All = cms::$_Pages = dbFile::$All = [];
	}
	static function preventDbManipulations(){ // prevent every? manipulations
		$prevent = function($e) {
			extract($e, EXTR_REFS);   // $Table, $id, $data, $return
            $table = (string)$Table;
			if (isset(vers::$db[$table]) || substr($table,0,6) === '_vers_') {
				$return = false;
			}
		};
		qg::on('dbTable::insert-before', $prevent);
		qg::on('dbTable::update-before', $prevent);
		qg::on('dbTable::delete-before', $prevent);
	}
    static function SettingToPage($setting_id){
        $S = settingArray::getSetting($setting_id);
        if (isset($S->CmsPage)) return $S->CmsPage;
        return false;
    }
	static function cacheHeaders() {
        $expires = time()+60*60*24*180;
 		header('Expires: ' . gmdate('D, d M Y H:i:s', $expires) .' GMT');
		header('Cache-Control: store, cache, max-age='.$expires.', private');
		header('Pragma: private'); // needed or els it will not cache
	}
}
