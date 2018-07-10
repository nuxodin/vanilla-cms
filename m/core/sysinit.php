<?php
namespace qg;

require_once sysPATH.'core/lib/path.php';

//if (defined('appPATH')) trigger_error('dont define appPATH in your index.php');
!defined('appPATH')  && define('appPATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');
!defined('sysPATH')  && define('sysPATH', appPATH.'m/');
!defined('sysURL')   && define('sysURL', path2uri(sysPATH));
!defined('appURL')   && define('appURL', path2uri(appPATH));
!defined('QG_HTTPS') && define('QG_HTTPS', false);

error_reporting(E_ALL);
session_name('qg'.substr(md5(appPATH), 0, 4));
session_set_cookie_params(0, appURL, '', QG_HTTPS, true);
session_start();

if (!isset($_SESSION['qg'])) $_SESSION['qg'] = ['debug'=>false];
if (isset($_SESSION['liveUser']) && isset($_GET['debugmode'])) {
	$_SESSION['qg']['debug'] = (int)$_GET['debugmode']; // todo: every logedin-user can be in debugmode!
}
define('debug', $debug??false || $_SESSION['qg']['debug']);
ini_set('display_errors', debug);
$skip_stacks = 0; // error_report

set_include_path(sysPATH); // zend
require_once sysPATH.'core/lib/cache.php';
require_once sysPATH.'core/lib/qg.class.php';
require_once sysPATH.'core/lib/db.class.php';
require_once sysPATH.'core/lib/dbTable.class.php';
require_once sysPATH.'core/lib/dbField.class.php';
require_once sysPATH.'core/lib/dbEntry.class.php';
require_once sysPATH.'core/lib/init.php';
require_once sysPATH.'core/lib/lang.php';
require_once sysPATH.'core/lib/settingArray.class.php';
require_once sysPATH.'core/lib/html.class.php';
require_once sysPATH.'core/lib/TextPro.class.php';
require_once sysPATH.'core/lib/qgEntries.php';
require_once sysPATH.'core/lib/Auth.class.php';
require_once sysPATH.'core/lib/divers.php';
require_once sysPATH.'core/lib/File.class.php';
require_once sysPATH.'core/lib/module.class.php';
// files should contain only classes:
require_once sysPATH.'core/lib/dbEntry_mail.class.php';
require_once sysPATH.'core/lib/dbFile.class.php';

spl_autoload_register(function($name){
	$name = substr($name, 3); // remove the namespace;
	$file = sysPATH.'core/lib/'.$name.'.class.php';
	file_exists($file) && require_once($file);
});

ini_set('max_execution_time', '7');
ini_set('memory_limit', '512M');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Paris'); // todo

$_SERVER['DOCUMENT_ROOT'] = realpath($_SERVER['DOCUMENT_ROOT']);
$_SERVER['SCHEME']        = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
if (!isset($_SERVER['HTTP_REFERER']))    $_SERVER['HTTP_REFERER']    = '';
if (!isset($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT'] = '';
if (!isset($_SERVER['HTTP_HOST']))       $_SERVER['HTTP_HOST']       = '';
if (!isset($_SERVER['REQUEST_URI']))     $_SERVER['REQUEST_URI']     = '/';
if (!isset($_SERVER['REMOTE_ADDR']))     $_SERVER['REMOTE_ADDR']     = '';

!defined('qg_dbhost') && define('qg_dbhost', 'localhost');
!defined('qg_dbname') && define('qg_dbname', qg_dbuser);
!defined('qg_dbuser') && define('qg_dbuser', qg_dbname);

$x = substr($_SERVER['REQUEST_URI'], strlen(appURL));
$x = preg_replace('/\?.*/', '', $x);
$x = preg_replace('/\/$/', '', $x);
define('appRequestUri', urldecode($x));

!D()->qg_setting && qg::initialize('core'); // todo, better solution?

G()->SET = new settingArray();

G()->ASK = isset($_POST['askJSON']) ? json_decode($_POST['askJSON'], true) : null;

foreach (explode(',', G()->SET['qg']['langs']->v) as $l) {
	$l = trim($l);
	if ($l) L::$all[$l] = $l;
}
L::$def = reset(L::$all);

//header('X-Content-Type-Options: nosniff'); // use for scripts and css https://sonarwhal.com/docs/user-guide/rules/rule-x-content-type-options/
header('X-Xss-Protection: 1; mode=block');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer-when-downgrade');

qg::on('output-before',function(){
	$enable = G()->SET['qg']['csp']['enable']->v;
	if (!$enable) return;
	if (isset(G()->csp['default-src']["'none'"]) && count(G()->csp['default-src']) > 1) unset(G()->csp['default-src']["'none'"]);
	$str = '';
	foreach (G()->csp as $type => $allowed) {
		$str .= $type.' '.implode(' ', array_keys($allowed)).'; ';
	}
	if (G()->csp_report_uri) $str .= ' report-uri '.G()->csp_report_uri;
	header('Content-Security-Policy'.($enable==='report only'?'-Report-Only':'').': '.$str);
});

// ok?: report-sample - https://www.chromestatus.com/feature/5792234276388864
G()->csp = [
	'default-src' => ["'self'"=>1], // if none, favicon and serviceworker blocked?
	'font-src'    => ['*'=>1, 'data:'=>1], /* firefox makes it intern to data-links and so blocks fonts? */
	'img-src'     => ["'self'"=>1, 'data:'=>1],
	'script-src'  => ["'self'"=>1, "'unsafe-inline'"=>1, "'report-sample'"=>1,], // report-sample ok?
	'style-src'   => ["'self'"=>1, "'unsafe-inline'"=>1, "'report-sample'"=>1,], // report-sample ok?
	'connect-src' => ["'self'"=>1],
	'frame-src'   => ["'self'"=>1],
];
//unset(G()->csp['script-src']["'unsafe-inline'"]);
G()->csp_report_uri = false;

if (Usr()->id !== 0 && !Usr()->is()) $_SESSION['liveUser'] = null; // logout if user not exists
