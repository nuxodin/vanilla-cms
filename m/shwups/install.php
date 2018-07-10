<?php
namespace qg;

qg::need('cms.installation.default');

is_file(appPATH.'qg/html-template.php') && unlink(appPATH.'qg/html-template.php'); // cleanup old, zzz

// Benutzer
if (!D()->one("SELECT id FROM usr WHERE superuser = '1'")) {
	D()->query("INSERT INTO usr SET email = 'su',          pw = '$2y$10\$CfeMgTdPi26our51Q06E4u.Hf/H5p2UFJcDc0uFS/TM6Ar7KLiCL2', superuser=1, active=1, firstname='Superuser', lastname='Superuser' ");
}
if (!D()->one("SELECT id FROM usr WHERE email = 't@shwups.ch'")) {
	D()->query("INSERT INTO usr SET email = 't@shwups.ch', pw = '$2y$10$.L1ZjvctQ2wJNpY6wNu/lOEhMrGY5aIrjBEfMr7DrmAc8VBZZo01q', superuser=1, active=1, firstname='Tobias', lastname='Buschor' ");
}
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
G()->SET['cms.backend.webmaster']['google.api.key'] = 'AIzaSyDBewkr4_EDLmQCZOulBlPtdrVPKq3F1Yw';
