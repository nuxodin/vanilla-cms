<?php
namespace qg;

copy(sysPATH.'shwups/util/html-template.php', appPATH.'qg/html-template.php');

// Benutzer
if (!D()->one("SELECT id FROM usr WHERE superuser = '1'")) {
	D()->query("INSERT INTO usr SET email = 'su',          pw = '$2y$10\$CfeMgTdPi26our51Q06E4u.Hf/H5p2UFJcDc0uFS/TM6Ar7KLiCL2', superuser=1, active=1, firstname='Superuser', lastname='Superuser' ");
}
if (!D()->one("SELECT id FROM usr WHERE email = 't@shwups.ch'")) {
	D()->query("INSERT INTO usr SET email = 't@shwups.ch', pw = '$2y$10$.L1ZjvctQ2wJNpY6wNu/lOEhMrGY5aIrjBEfMr7DrmAc8VBZZo01q', superuser=1, active=1, firstname='Tobias', lastname='Buschor' ");
}
if (!D()->one("SELECT id FROM usr WHERE email = 'bolligab@gmail.com'")) {
	D()->query("INSERT INTO usr SET email = 'bolligab@gmail.com', pw = '$2y$10$.L1ZjvctQ2wJNpY6wNu/lOEhMrGY5aIrjBEfMr7DrmAc8VBZZo01q', superuser=1, active=1, firstname='Gabriel', lastname='Bolliger' ");
}

/* email */
$host = preg_replace('/\.shwups-cms\.ch/','',$_SERVER['HTTP_HOST']);
if (preg_match('/\./',$host)) {
	$email = 'info@'.$host;
} else {
	$email = 'info@shwups-cms.ch';
}
G()->SET['qg']['mail']->make('defSender',     $email);
G()->SET['qg']['mail']->make('defSendername', $email);
G()->SET['qg']['mail']->make('replay',        $email);
G()->SET['cms']['feedback']['email'] = 'office@shwups.ch';

qg::need('reporting');
qg::need('cms.image_editor');

if (0) { // todo?
	qg::need('cms.cont.cols2');
	qg::need('cms.cont.map.google1');
	qg::need('cms.cont.gallery.fancybox3');
	qg::need('cms.cont.form1');
	qg::need('cms.cont.form1.fields2');
	qg::need('cms.cont.filelist');
}
