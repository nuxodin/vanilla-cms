<?php
namespace qg;

class cmsBackend {
	static function checkInstalled() {
		if (!cms::PageByModule('cms.backend')) {
			$P = Page(1)->createChild(['visible'=>0, 'module'=>'cms.layout.backend', 'access'=>0, 'offline'=>0, 'sort'=>20, 'searchable'=>0]);
			$P->changeGroup(1, 0);
			$P->changeGroup(2, 0);
			$P->changeGroup(3, 0);
			$P->Cont(1)->set('module','cms.backend');
			$P->SET['childXML'] = '<page visible="1"></page>';
			G()->SET['cms']['backend']->setDefault((string)$P);
		}
		return cms::PageByModule('cms.backend')->Page;
	}
	static function install($module) {
		self::checkInstalled();
		if (!preg_match('/cms\.backend\.(.+)/', $module, $matches)) return false;
		$parts = explode( '.', $matches[1] );
		$parentModule = 'cms.backend';
		foreach ($parts as $part) {
			$m = $parentModule.'.'.$part;
			if (!cms::PageByModule($m)) {
				$Parent = cms::PageByModule($parentModule)->Page;
				$Page = $Parent->createChild([
					'module'  => 'cms.layout.backend',
					'visible' => 1,
					'access'  => 0,
					'offline' => 0
				])->Cont(1);
				$Page->set('module',$m);
			}
			$parentModule = $m;
		}
		return cms::PageByModule($module)->Page;
	}
}
