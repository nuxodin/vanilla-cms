<?php
namespace qg;

$debug = 1; // not production
define('QG_HTTPS', false);

define('qg_dbname', 'vanilla_cms');
define('qg_dbuser', 'root');
define('qg_dbpass', '');

define('appPATH', __DIR__.'/');
define('sysPATH', appPATH.'m/');

define('qg_host', 'v5.shwups-cms.ch');

$initFile = sysPATH.'core/sysinit.php';
if (!( is_file($initFile) && include($initFile) )) {
    !is_writable(appPATH) && die('Failed, '.appPATH.' is not writable!');
    copy('http://'.qg_host.'/install', 'tmp') ? include('tmp') : die('Failed');
}

qg::need('shwups');
qg::init();
echo cms::render();
