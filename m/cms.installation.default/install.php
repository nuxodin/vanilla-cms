<?php
namespace qg;

qg::need('cms');

$freshInstallation = false;
if (!G()->SET['qg']['langs']->v) {
	G()->SET['qg']['langs'] = L::$def = L::$now = 'de';
	L::$all = ['de'];
	$freshInstallation = true;
}

// Benutzer
if (!D()->one("SELECT id FROM usr WHERE active AND !superuser")) {
	D()->query("INSERT INTO grp SET name = 'admin', page_access = '1'");
	$adminGrp = D()->lastInsertId();
	D()->query("INSERT INTO usr SET email = 'admin', pw = '', active=1, firstname='Client', lastname='Client' "); //
	$usr = D()->lastInsertId();
	D()->query("INSERT INTO usr_grp SET usr_id = '".$usr."', grp_id = '".$adminGrp."'");
	Page(1)->changeGroup($adminGrp, 2); // edit recht auf root
}

// Home
if (!D()->one("SELECT id FROM page WHERE id = 2")) {
	$Page = Page(1)->createChild(['id'=>2,'access'=>1,'visible'=>1,'offline'=>0,'searchable'=>1,'sort'=>1]);
	$Page->changeGroup($adminGrp, 2);
	D()->query("REPLACE INTO page_redirect SET request = '', redirect = '2'");
	$Page->Title('de','Home');
	$Page->Title('en','Home');
}
// Service
if (!D()->one("SELECT id FROM page WHERE id = 10")) {
	$Page = Page(1)->createChild(['id'=>10, 'access'=>1, 'visible'=>0, 'searchable'=>1, 'sort'=>4]);
	$Page->changeGroup($adminGrp, 1);
	$Page->Title('de','Service');
	$Page->Title('en','Service');
}
// search
if (!D()->one("SELECT id FROM page WHERE id = 20")) {
	$Page = Page(10)->createChild(['id'=>20, 'visible'=>1, 'searchable'=>0]);
	$Page->changeGroup($adminGrp, 2);
	$Page->Cont('main')->Cont(1,'cms.cont.search1');
	$Page->Title('de','Suche');
	$Page->Title('en','Search');
}

// System
if (!D()->one("SELECT id FROM page WHERE id = 40")) {
	$Page = Page(1)->createChild(['id'=>40, 'access'=>0, 'visible'=>0, 'searchable'=>0, 'sort'=>8]);
	$Page->changeGroup($adminGrp, 1);
	$Page->Title('de','System');
	$Page->Title('en','System');
}
// Layout
if (!D()->one("SELECT id FROM page WHERE id = 5")) {
	$Page = Page(40)->createChild(['id'=>5, 'access'=>1, 'offline'=>0, 'visible'=>0]);
	$Page->changeGroup($adminGrp, 1);
	$Page->Title('de','Layout');
	$Page->Title('en','Layout');
}

// Papierkorb
if (!D()->one("SELECT id FROM page WHERE id = 50")) {
	qg::need('cms.cont.trash');
	$Page = Page(40)->createChild(['id'=>50, 'access'=>0, 'offline'=>0, 'visible'=>0]);
	$Page->changeGroup($adminGrp, 1);
	$Page->Cont('main','cms.cont.trash');
	$Page->Title('de','Papierkorb');
	$Page->Title('en','Trash');
	G()->SET['cms']['pageTrash'] = 50;
}
Page(50)->set('module', 'cms.layout.login');
Page(50)->Cont('main')->set('module','cms.cont.trash');

// Kein Recht
if (!D()->one("SELECT id FROM page WHERE id = 60")) {
	$Page = Page(40)->createChild(['id'=>60, 'access'=>1, 'offline'=>0, 'visible'=>0]);
	$Page->changeGroup($adminGrp, 1);
	$C = $Page->Cont('main')->Cont(1,'cms.cont.login4');
	$Page->Title('de','kein Recht');
	$Page->Title('en','No access');
	G()->SET['cms']['pageNoAccess'] = 60;
}
Page(60)->set('module', 'cms.layout.login');
Page(60)->Cont('main')->Cont(1)->set('module','cms.cont.login4');
// Login
if (!D()->one("SELECT id FROM page WHERE id = 80")) {
	$Page = Page(40)->createChild(['id'=>80, 'access'=>1, 'offline'=>0]);
	$Page->changeGroup($adminGrp, 1);
	$C = $Page->Cont('main')->Cont(1,'cms.cont.login4');
	$C->SET['redirect'] = 2;
	$Page->Title('de','Login');
	$Page->Title('en','Login');
	D()->query("REPLACE INTO page_redirect SET request = 'login', redirect = '80'");
}
Page(80)->set('module', 'cms.layout.login');
Page(80)->Cont('main')->Cont(1)->set('module','cms.cont.login4');

// nicht gefunden und Offline
if (!D()->one("SELECT id FROM page WHERE id = 70")) {
	$Page = Page(40)->createChild(['id'=>70, 'access'=>1, 'offline'=>0, 'visible'=>0]);
	$Page->changeGroup($adminGrp, 2);
	$Page->Cont('main')->Cont(1,'cms.cont.notFound1');
	$Page->Title('de','nicht gefunden');
	$Page->Title('en','not Found');
	G()->SET['cms']['pageNotFound'] = 70;
	G()->SET['cms']['pageOffline']  = 60;
}


/* install the frontend */
G()->SET['cms']->make('frontend','cms.frontend.1');
qg::need('cms.frontend.1');
qg::need('cms.versions');
qg::need('cms.backend');


if ($freshInstallation) {
	qg::need('cms.cont.phpfile');
	qg::need('cms.cont.notFound1');
	qg::need('cms.backend.superuser');
	qg::need('cms.backend.superuser.db');
	qg::need('cms.backend.superuser.cd-clean');
	qg::need('cms.backend.superuser.vers');
	qg::need('cms.backend.superuser.error_report');
	qg::need('cms.backend.module');
	qg::need('cms.backend.settings');
	qg::need('cms.backend.system');
	qg::need('cms.backend.mails');
	qg::need('cms.backend.groups');
	qg::need('cms.backend.users');
	qg::need('cms.backend.struct');
	qg::need('cms.backend.struct.grpaccess');
	qg::need('cms.backend.webmaster');
	qg::need('cms.backend.app1');
	qg::need('cms.cont.flexible');
	qg::need('cms.cont.login4');
	qg::need('cms.cont.nav2');
	qg::need('cms.cont.search1');
	qg::need('cms.cont.table1');
	qg::need('cms.cont.text');
	qg::need('cms.cont.redirect');
	qg::need('cms.cont.impressum2');
	qg::need('cms.layout.backend');
	qg::need('cms.layout.login');
	qg::need('cms.layout.custom.6');
	qg::need('cms.encrypt_emails');
	qg::need('cms.image_editor');
	qg::need('error_report');
	qg::need('reporting');
}
