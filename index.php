<?php
namespace qg;

$debug = 1; // not production
define('QG_HTTPS', false);

define('qg_dbname', 'vanilla_cms');
define('qg_dbuser', 'root');
define('qg_dbpass', '');

define('sysPATH', __DIR__.'/m/');

define('qg_host', 'v6.vanilla-cms.org');

$initFile = sysPATH.'core/sysinit.php';
if (!( is_file($initFile) && include($initFile) )) {
    !is_writable(__DIR__) && die('Failed, '.__DIR__.' is not writable!');
    copy('http://'.qg_host.'/install', 'tmp') ? include('tmp') : die('Failed');
}

qg::need('shwups');
qg::init();
echo cms::render();
