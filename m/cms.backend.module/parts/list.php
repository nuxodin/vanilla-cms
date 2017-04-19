<?php
namespace qg;

$MUpdates = $MReleases = $MOthers = [];
foreach (module::all() as $M) {
	$hasUpdate = $M->server_time && versionIsSmaller($M->local_version, $M->server_version, 3);
	$hasRelease = $M->local_time && $M->local_time > $M->server_time && $M->local_time > $M->local_updated;
	if ($M->local_time && $hasUpdate) {
		$MUpdates[] = $M;
	} elseif ($M->local_time && $hasRelease) {
		$MReleases[] = $M;
	} else {
		$MOthers[] = $M;
	}
}
$All = array_merge($MUpdates, $MReleases, $MOthers);

foreach ($All as $M) {
	$search = $vars['search'] ?? '';
	if (!preg_match('/(^|\.)'.preg_quote($search,'/').'/',$M->name)) continue;

	if (!isset($vars['installed'])) $vars['installed'] = true;
	if ($vars['installed']  && !$M->local_time) continue;
	if (!$vars['installed'] &&  $M->local_time) continue;
	?>
	<tr itemid="<?=hee($M->name)?>" data-c1-href="<?=hee(Url()->addParam('module', $M->name))?>">
		<td>
			<a href="<?=hee(Url()->addParam('module', $M->name))?>">
				<?=$M->name?>
				<?php if (G()->SET->has($M->name)) { ?>
					<img src="<?=sysURL?>cms.frontend.1/pub/img/settings.svg" title="<?=hee(L('Einstellungen'))?>" style="vertical-align:middle; height:20px" alt="<?=hee(L('Einstellungen'))?>">
				<?php } ?>
			</a>
		<!--td>
			<input value="<?=hee($M->Title())?>" onchange="moduleSetTitle(this,'<?=$M?>')"-->
		<td>
			<?php
			if (preg_match('/^cms\.(backend|cont|layout)\./',$M->name)) {
				echo D()->one("SELECT count(*) FROM page WHERE module = ".D()->quote($M->name));
			}
			?>
		<td style="text-align:center">
			<input class=-access <?=$M->access?'checked':''?> type=checkbox>
		<td>
			<?php if ($M->server_time && versionIsSmaller($M->local_version,$M->server_version, 3)) { ?>
				<?php /* if ($M->checkRemoteFolder()) { */ ?>
					<a class=updateBtn href="#"> <?=$M->local_time?'update':'installieren'?> </a>
				<?php /* } else { echo 'not found!'; } */ ?>
			<?php } ?>
		<td>
			<?php if ($M->local_time) { ?>
				<img class=-init src="<?=sysURL?>cms.frontend.1/pub/img/reload.svg" title="<?=hee(L('neu initialisieren'))?>" style="cursor:pointer; height:20px" alt=initialisieren>
			<?php } ?>
		<td>
			<?php if ($M->local_time) { ?>
				<img class=-uninstall src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg" title="<?=hee(L('Löschen'))?>" style="cursor:pointer; height:20px" alt=delete>
			<?php } ?>
		<td class=localVersion  title="<?=$M->local_time  ? date('d.m.Y H:i',$M->local_time)  : ''?>"><?=$M->local_version?>
		<td> <?=$M->server?>
		<td class=serverVersion title="<?=$M->server_time ? date('d.m.Y H:i',$M->server_time) : ''?>"><?=$M->server_version?>
		<?php if (Usr()->superuser) { ?>
			<?php
			$color = $M->local_time > time()-60*60*24 ? 'rgba(0, 120, 159, 1)' : 'rgba(0, 120, 159, 0.4)';
			$color = $M->server_version ? $color : '#fbb';
			?>
			<td style="color:<?=$color?>">
				<?php if ($M->local_time && $M->local_time > $M->server_time /* neuer als auf dem Server? */
                       && $M->local_time > $M->local_updated) { /* lokal geändert? */ ?>
						<a style="color:inherit" class=-upload>release</a>
                <?php } /* elseif (!$M->checkRemoteFolder() && $M->local_time) { ?>
						<a style="color:inherit" onclick="return moduleUpload(this,'<?=$M?>',2);" href="#" >release (not realy on server!)</a>
                <?php } */ ?>
			<td>
				<?php if ($M->server_time) { ?>
					<img class=-remoteDelete src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg" title="auf dem Server Löschen" style="cursor:pointer; height:20px" alt=löschen>
				<?php } ?>
		<?php } ?>
		<td> <?=byte_format($M->server_size)?>
<?php
}
