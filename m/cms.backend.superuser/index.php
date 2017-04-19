<?php
namespace qg;

if (!Usr()->superuser) return;

if (isset($_GET['export'])) {
	//time_limit(0);

  	$tmpFile = appPATH.'cache/tmp/pri/modExport.zip';
	is_file($tmpFile) && unlink($tmpFile);
	$zip = new Zip;
	$zip->open($tmpFile, Zip::CREATE);
	$zip->addDir(appPATH.'m' , null, '/(\.svn)/');
	$zip->addDir(appPATH.'qg', null, '/(\.svn)/');
  	foreach (scandir(appPATH) as $file) {
      if (!is_file( appPATH.$file )) continue;
      if ($file === 'error_log') continue;
      $zip->addFile( appPATH.$file, $file );
  	}

	/* only custom module:
	foreach (module::all() as $M) {
		If ($M->server_time) continue;
		$zip->addDir(sysPATH.$name, 'm/'.$M->name, '/(\.svn)/');
	}

	/* add mysql export */
	$structExport = '';
	@mkdir('/tmp/');
	@mkdir('/tmp/qgdbexport1/');
	chmod('/tmp/qgdbexport1/', 0777);
	foreach (D()->Tables() as $T) {
		$file = realpath('/tmp/qgdbexport1/').'/'.$T.'.csv';
		@unlink($file);
		D()->query("SELECT * INTO OUTFILE ".D()->quote($file)." FROM ".$T);
		$zip->addFile($file, 'mysql/'.$T);

		$tmp = D()->row("SHOW CREATE TABLE ".$T);
		$structExport .= $tmp['Create Table'].";\n\n";
	}
	$zip->addFromString('mysql.struct.sql', $structExport);
	$zip->close();

	/* send */
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false);
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=\"export.zip\";" );
	header("Content-Transfer-Encoding: binary");

	while (ob_get_level()) ob_end_clean();
	readfile($tmpFile);
	exit();
	/**/
}

?>
<div class=beBoxCont>

    <div class=beBox style="flex:0 0 auto">
    	<div class=-head>Backup / Export</div>
    	<div class=-body>
    		<a href="?export=1" >export</a>
    	</div>
    </div>

    <div class=beBox>
    	<div class=-head>Dateien</div>
    	<style>.files a { display:block;} </style>
        <div style="overflow:auto; max-height:80vh">
          	<table class="c1-style files">
        	<?php
            $startOffset = strlen( realpath(appPATH) )+1;
            $writeTr = function($file) use($startOffset) {
                $file = realpath($file);
                $show = substr($file, $startOffset);
                $_SESSION['fileEditor']['allow'][$file] = 1;
                $src = appURL.'editor?file='.urlencode($file);
                ?><tr><td><a href="<?=hee($src)?>" target=<?=md5($file)?>><?=$show?></a><td><?=strftime('%x %X',filemtime($file))?><?php
            };
            $writeTr(appPATH.'index.php');
            $writeTr(appPATH.'.htaccess');
        	$verzeichnis = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator(appPATH.'qg/'), true );
        	foreach ($verzeichnis as $datei) {
                $search = str_replace('\\', '/', $datei);
        		if (preg_match('/\.svn/', $search)) continue;
        		if (preg_match('/\.settings/', $search)) continue;
        		if (preg_match('/\.project/', $search)) continue;
        		if (preg_match('/org\.eclipse/', $search)) continue;
                if (preg_match('/qg\/file\//', $search)) continue;
        		if (preg_match('/\/Zend\//', $search)) continue;
        		if (preg_match('/\/cache\//', $search)) continue;
        		if (!is_file($datei)) continue;
                $writeTr($datei);
        	}
        	?>
          	</table>
        </div>
    </div>

</div>
