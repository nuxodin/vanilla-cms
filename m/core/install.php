<?php
namespace qg;

qg::need('Zend');

copy(sysPATH.'core/util/app.htaccess',appPATH.'.htaccess');
copy(sysPATH.'core/util/app.user.ini',appPATH.'.user.ini');

is_dir(appPATH.'cache') || mkdir(appPATH.'cache');
is_dir(appPATH.'cache/pri') || mkdir(appPATH.'cache/pri');
file_put_contents(appPATH.'cache/pri/.htaccess', 'deny from all');

is_dir(appPATH.'cache/tmp') || mkdir(appPATH.'cache/tmp');
is_dir(appPATH.'cache/tmp/pri') || mkdir(appPATH.'cache/tmp/pri');
file_put_contents(appPATH.'cache/tmp/pri/.htaccess', 'deny from all');

is_dir(appPATH.'qg') || mkdir(appPATH.'qg');
is_dir(appPATH.'qg/file') || mkdir(appPATH.'qg/file');
file_put_contents(appPATH.'qg/.htaccess', 'deny from all');

if (isset(G()->SET)) {
	G()->SET['qg']->make('dbFile_dpr_dependent',1)->setType('bool');
	G()->SET['qg']['mail']['smtp']['host'];
	G()->SET['qg']['mail']['smtp']['username'];
	G()->SET['qg']['mail']['smtp']['password'];
	G()->SET['qg']['mail']['smtp']['port'];
	G()->SET['qg']['smalltexts_counter']->setType('bool');
	G()->SET['qg']['csp']->make('enable','report only')->setHandler('select')->setOptions('','report only','enable');
	G()->SET['qg']['HSTS']->make('max-age', 60*60);
	G()->SET['qg']['HSTS']->make('includeSubDomains', true)->setType('bool');
	G()->SET['qg']['HSTS']->make('preload', false)->setType('bool');
}
