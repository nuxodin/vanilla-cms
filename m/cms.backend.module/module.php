<?php
namespace qg;

$name = $_GET['module'];
$Module = D()->module->Entry(D()->row("SELECT * FROM module WHERE name = ".D()->quote($name)));

if (isset($vars['addModuleFile'])) {
    $file = sysPATH.$name.'/'.$vars['addModuleFile'];
    !file_exists(dirname($file)) && mkdir(dirname($file), 0777, true);
    touch($file);
}
if (isset($vars['delModuleFile'])) {
    $file = sysPATH.$name.'/'.$vars['delModuleFile'];
	unlink($file);
}

$mTime         = dir_mtime(sysPATH.$name)-1;
$tmp           = qg::Store()->indexAll();
$storeData     = $tmp[$name] ?? false;
//$storeData     = qg::Store()->indexGet($name);
$changed       = $mTime > $Module->local_updated;
$localAge      = time() - $mTime;
$hasNewVersion = $storeData && $storeData['version'] > $Module->local_version;
?>
<div class=beBoxCont>

	<h1 class=beBox style="flex:100%; padding:10px">Modul "<?=$name?>"</h1>

	<?php if (G()->SET->has($name)) { ?>
		<div class=beBox>
			<div class=-head>Einstellungen</div>
			<div style="max-height:60vh; overflow:auto">
				<?php
				$form = new SettingsEditor(G()->SET[$name]);
				echo $form->show();
				?>
			</div>
		</div>
	<?php } ?>

	<div class=beBox style="flex:320px">
		<div class=-head>Manage</div>
		<table class=c1-style>
			<tr> <td> Lokale Version : <td> <?=$Module->local_version?>
			<tr> <td> Server Version : <td> <?=$storeData['version']?>
			<tr> <td> Geupdated : <td> <?=strftime('%x %H:%M',$Module->local_updated)?>
			<?php
			$color = '';
			if ($changed) {
				if ($localAge > 0)          $color = '#f00';
				if ($localAge > 60*60*24)   $color = '#a00';
				if ($localAge > 60*60*24*4) $color = '#500';
			}
			?>
			<tr style="color:<?=$color?>">
				<td> Lokal geändert :
				<td> <?= $changed ? 'vor '.round($localAge/60).'min' : '-' ?>
		</table>
		<div class=-body>
			<button onclick="this.c1Find('>svg').style.color='black'; $fn('page::api')(<?=$Cont?>,{init:'<?=$name?>'}).then(()=>{this.c1Find('>svg').style.color=''})">
				initialisieren
				<svg style="width:1.5em; height:1.5em; fill:currentColor; vertical-align:middle; display:inline-block; margin:-.5em 0" viewBox="0 0 24 24">
					<use xlink:href="<?=sysURL?>cms.frontend.1/pub/img/reload.svg#main" />
				</svg>
			</button>
			<form id=updateForm style="display:inline; <?=$hasNewVersion?'':'opacity:.4'?>">
				<button>Update</button>
			</form>
			<script>
			{
				let form = document.getElementById('updateForm');
				form.addEventListener('submit',e=>{
					e.preventDefault();
                    confirm('wirklich?') && $fn('page::api')(<?=$Cont?>, {update:'<?=$name?>'}).run(done=>{
						location.href = location.href.replace(/#.*$/,'');
					});
				});
			}
			</script>
			<hr>
			<form id=releaseForm <?=!$changed||$hasNewVersion?'style="opacity:.4"':''?>>
				<textarea name=notes placeholder="Release-Notes" style="width:100%; height:130px"></textarea><br>
				<?php
				list($v1,$v2,$v3) = explode('.', $storeData['version'].'.0.0.0');
				$v1 = (int)$v1;
				?>
				<select name=inc>
					<option value=1>          <?=$v1+1?>.0.0
					<option value=2 selected> <?=$v1?>.<?=$v2+1?>.0
					<option value=3>          <?=$v1?>.<?=$v2?>.<?=$v3+1?>
				</select>
				<button>Release</button>
			</form>
			<script>
			{
				let form = document.getElementById('releaseForm');
				form.addEventListener('submit',function(e){
					e.preventDefault();
			        if (!confirm('Achtung! Module wird auf dem Server überschrieben!\nwirklich hochladen?')) return false;
			        let notes = form.querySelector('[name=notes]').value;
					let select = form.querySelector('[name=inc]');
					let inc = select.options[select.selectedIndex].value;
                    $fn('page::api')(<?=$Cont?>, {upload:'<?=$name?>', incVersion:inc, notes}).run(done=>{
			            !done && alert('hat nicht funktioniert!');
						location.href = location.href.replace(/#.*$/,'');
			        });
				});
			}
			</script>
		</div>
	</div>

	<div class=beBox style="flex:320px">
    	<div class=-head>Dateien</div>
    	<style>.files a { display:block;} </style>
		<div class=-body>
			<input placeholder="neue Datei" onkeyup="event.which===13&&$fn('page::reload')(<?=$Cont?>,{addModuleFile:this.value})" >
		</div>
        <div style="overflow:auto; max-height:60vh">
          	<table class="c1-style files" id=files_table style="white-space:nowrap">
				<colgroup>
					<col>
					<col style="width:20px">
					<col style="width:10px">
				<tbody>
				<?php
				$verzeichnis = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator(sysPATH.$name), true );
				$startOffset = strlen( realpath( sysPATH.$name ) )+1;
				foreach ($verzeichnis as $datei) {
					$show = substr($datei, $startOffset);
					$search = str_replace('\\', '/', $datei);
					if (preg_match('/\.svn/', $search)) continue;
					if (preg_match('/\.settings/', $search)) continue;
					if (preg_match('/\.project/', $search)) continue;
					if (preg_match('/org\.eclipse/', $search)) continue;
					if (preg_match('/qg\/file\//', $search)) continue;
					if (preg_match('/\/Zend\//', $search)) continue;
					if (preg_match('/\/cache\//', $search)) continue;
					if (!is_file($datei)) continue;

					$file = realpath($datei);
					$_SESSION['fileEditor']['allow'][$file] = 1;
					$src = appURL.'editor?file='.urlencode($file);

					$fMtime   = filemtime($file);
					$fChanged = $fMtime > $Module->local_updated;
					$fAge     = time() - $fMtime;
					?><tr><?php
						?><td><a href="<?=hee($src)?>" target=<?=md5($file)?>><?=$show?></a><?php
						?><td><?=$fChanged?'geändert vor '.round($fAge/60).'min':''
						?><td><img class=-del onclick="" src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg" style="cursor:pointer; height:20px" alt=löschen><?php
				}
				?>
          	</table>
			<script>
			{
				let table = document.getElementById('files_table');
				table.addEventListener('click',e=>{
					if (!e.target.classList.contains('-del')) return;
					let file = e.target.closest('tr').querySelector('a').innerHTML;
					confirm('Datei "'+file+'" löschen?') && $fn('page::reload')(<?=$Cont?>,{delModuleFile:file})
				})
			}
			</script>
        </div>
    </div>

	<div class=beBox style="flex:320px">
		<div class=-head>als Seiten / Inhalte Vorhanden</div>
		<div style="max-height:60vh; overflow:auto">
			<table class=c1-style style="width:auto">
				<?php foreach (D()->query("SELECT * FROM page WHERE module = ".D()->quote($name)) as $row) {
					$P = Page($row['id'], $row);
					$Path = $P->Path();
					array_shift($Path);
					?>
					<tr>
						<td> <?php foreach ($Path as $Next) echo cms_link($Next).' | ' ?>
						<td> <a style="font-size:2em" href="<?=hee($P->url())?>"><?=$P->Title()?> id <?=$P?></a>
				<?php } ?>
			</table>
		</div>
	</div>

	<div class=beBox style="flex:320px">
		<div class=-head>Changelog</div>
		<div style="max-height:60vh; overflow:auto">
			<table class=c1-style style="white-space:nowrap;">
				<?php
				$changes = @qg::Store()->changelog($name);
				$changes = array_reverse($changes);
				?>
				<?php foreach ($changes as $change) {
					$installed = @$change['version'] === $Module->local_version;
					?>
					<tr style="vertical-align:top; <?=$installed?'color:var(--cms-color);':''?>">
						<td> <b><?=@$change['version']?></b>   <?=isset($change['time']) ? strftime('%x %H:%M',$change['time']) : ''?> <br><?=@$change['user']?>
						<td>
							<ul style="white-space:normal; margin:0; padding:0">
							<?php
							foreach (explode("\n", $change['notes']) as $line) echo '<li>'.hee($line).'</li>';
							?>
							</ul>
				<?php } ?>
			</table>
		</div>
	</div>

	<?php if ($Module->server_time && $Module->local_version) { ?>
		<div class=beBox style="flex:100%">
			<div class=-head>Mit Server vergleichen</div>
			<div class=-body style="max-height:90vh; overflow:auto;" data-part="diff">
				<button onclick="$fn('page::loadPart')(<?=$Cont?>,'diff')">laden</button>
			</div>
		</div>
	<?php } ?>
</div>
