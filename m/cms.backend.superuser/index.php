<?php
namespace qg;
if (!Usr()->superuser) return;
?>
<div class=beBoxCont>

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
        		if (preg_match('/\.settings/', $search)) continue;
        		if (preg_match('/\.project/', $search)) continue;
                if (preg_match('/qg\/file\//', $search)) continue;
        		if (!is_file($datei)) continue;
                $writeTr($datei);
        	}
        	?>
          	</table>
        </div>
    </div>

    <div class=beBox>
    	<div class=-head>Cleanup Settings</div>
    	<div class=-body>
			Mehrfach vorhanden:
		</div>
        <div style="overflow:auto; max-height:80vh">
			<?php
			$all = D()->all("SELECT offset, basis, count(id) as count FROM qg_setting GROUP BY basis, offset HAVING count(id) > 1");
			?>
          	<table class="c1-style">
				<thead>
					<tr>
						<td> Basis
						<td> Offset
						<td> Count
				<tbody>
				<?php foreach ($all as $row) { ?>
				<tr>
					<td><?=$row['basis']?>
					<td><?=$row['offset']?>
					<td><?=$row['count']?>
				<?php } ?>
			</table>
        </div>

    	<div class=-body>
			Ohne basis:
		</div>
        <div style="overflow:auto; max-height:80vh">
			<?php
			$all = D()->all("SELECT s1.* FROM qg_setting s1 LEFT JOIN qg_setting s2 ON s1.basis = s2.id WHERE s2.id IS NULL AND s1.basis != 0");
			?>
          	<table class="c1-style">
				<thead>
					<tr>
						<td> ID
						<td> Basis
						<td> Offset
				<tbody>
				<?php foreach ($all as $row) { ?>
				<tr>
					<td><?=$row['id']?>
					<td><?=$row['basis']?>
					<td><?=$row['offset']?>
				<?php } ?>
			</table>
        </div>
    </div>

</div>
