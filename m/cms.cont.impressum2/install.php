<?php
namespace qg;

if (!D()->one("SELECT name FROM page WHERE name = 'cms.cont.impressum'")) {
	$Page = Page(10)->createChild(['name'=>'cms.cont.impressum', 'visible'=>1,'searchable'=>1, 'online_end'=>time()]);
	$Page->Cont('main')->Cont(1)->set(['module'=>'cms.cont.impressum2']);
	$Page->Title('de', 'Impressum');
	$Page->Title('en', 'Imprint');
}
