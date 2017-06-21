<?php
namespace qg;

$module = $_GET['module'];

time_limit(600);
// download to temp folder
$vs = qg::Store()->indexGet($module);
$zipPath   = appPATH.'cache/tmp/pri/remoteModule.zip';


qg::Store()->Ftp()->get($zipPath, '/module/'.$module.'/'.$vs['version'].'.zip');


//$from = 'https://'.qg_host.'/module/'.$module.'/'.$vs['version'].'.zip';
//$arrContextOptions=["ssl" => ["verify_peer"=>false,"verify_peer_name"=>false]];
//$string = file_get_contents($from, false, stream_context_create($arrContextOptions));
//file_put_contents($zipPath, $string);


$zip = new \ZipArchive;
$go = $zip->open($zipPath);
if (!$go) return;
$tmpDir = appPATH.'cache/tmp/pri/module_to_compare/';
!is_dir($tmpDir) && mkdir($tmpDir);
rrmdir($tmpDir.$module);
$zip->extractTo($tmpDir);
$zip->close();

// compare
$files = [];

$dir = $tmpDir.$module;
$verzeichnis = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST );
$startOffset = strlen( realpath($dir) )+1;
foreach ($verzeichnis as $datei) {
    if (!is_file($datei)) continue;
    $datei = realpath($datei);
    $show = substr($datei, $startOffset);
    $files[$show]['remote'] = $datei;
}

$dir = sysPATH.$module;
$verzeichnis = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST );
$startOffset = strlen( realpath($dir) )+1;
foreach ($verzeichnis as $datei) {
    if (!is_file($datei)) continue;
    $datei = realpath($datei);
    $show = substr($datei, $startOffset);
    $files[$show]['local'] = $datei;
}

foreach ($files as $show => $both) {
    echo '<div></div>'.$show.' ';
    if (!isset($both['local']))  { echo '<span style="color:red">fehlt lokal</span>';  continue; }
    if (!isset($both['remote'])) { echo '<span style="color:red">fehlt remote</span>'; continue; }
    $remote = file_get_contents($both['remote']);
    $local  = file_get_contents($both['local']);
    if ($remote === $local) { echo '<span style="color:green">gleich</span>'; continue; }
    echo '<span style="color:red">different!</span>';
    require_once($Cont->modPath.'/lib/finediff.php');
    $opcodes = \FineDiff::getDiffOpcodes($remote, $local);
    // store opcodes for later use...
    //Later, $to_text can be re-created from $from_text using $opcodes as follow:
    echo '<pre style="box-shadow:0 0 8px rgba(0,0,0,.7); padding:6px; margin:6px 0; font-size:12px">';
    echo \FineDiff::renderDiffToHTMLFromOpcodes($remote, $opcodes);
    echo '</pre>';
}
rrmdir($tmpDir.$module);
?>
<style>
del { color: red;   background: #fdd; text-decoration: none; }
ins { color: green; background: #dfd; text-decoration: none; }
</style>
