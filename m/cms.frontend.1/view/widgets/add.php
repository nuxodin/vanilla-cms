<?php namespace qg ?>
<div class="-standalone qgCmsFront1ModuleManager">
	<div class=-h1>
		<span><?=L('Module')?></span>
		<input placeholder=suche style="width:50%">
	</div>

	<div class="cmsAddModule -module-boxes">
		<?php foreach (cms::getModules() as $name => $path) {
				$M = D()->module->Entry($name);
				if (!$M->access && !Usr()->superuser) continue;
				if ($name==='cms.cont.flexible') continue;
				$desc = is_file($path.'description.txt') ? file_get_contents($path.'description.txt') : '';
				$title = ucfirst(str_replace('cms.cont.','',$M->Title()));
				$title = str_replace('.',' ',$title);
			?>
			<div itemid="<?=hee($name)?>" title="<?=hee($desc)?>" todo_c1-tooltip style="--c1-tooltip-delay:.5">
				<div class=-title title="<?=hee($M->name)?>"><?=$title?></div>
				<svg class=-img xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#fff">
					<?php if (is_file($path.'pub/module.svg')) { ?>
							<use xlink:href="<?=path2uri($path)?>pub/module.svg#main" />
					<?php } else { ?>
							<use xlink:href="<?=sysURL?>cms.frontend.1/pub/img/module_default.svg#main" />
					<?php } ?>
				</svg>
			</div>
		<?php } ?>
	</div>

	<?php
	$Models = [];
	$all = explode(',',G()->SET['cms']['models']->v.','.G()->SET['cms']['models']->getDefault());
	foreach ($all as $v) {
		$all[trim($v)] = 1;
	}
	foreach ($all as $id => $egal) {
      	if (!$id) continue;
		$P = Page(trim($id));
		if ($P->vs['type'] !== 'c') continue;
		if ($P->access() < 2) continue;
		$Models[] = $P;
	}
	?>
	<?php if ($Models) { ?>
		<div class=-standalone>
			<br><br>
			<div class=-h1>
				<span>Vorlagen</span>
			</div>
		</div>
		<div class="cmsAddModels -module-boxes">
			<?php foreach ($Models as $P) {
					if ($P->access() < 2) continue;
					$M = D()->module->Entry($P->vs['module']);
					if (!$M->access && !Usr()->superuser) continue;
					if ($name==='cms.cont.flexible') continue;
					$path = sysPATH.$M->name.'/';
					$desc = is_file($path.'description.txt') ? file_get_contents($path.'description.txt') : '';
					$title = ucfirst($P->Title());
				?>
				<div itemid="<?=$P->id?>" title="<?=hee($desc)?>">
					<svg class=-img xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#fff">
						<?php if (is_file($path.'pub/module.svg')) { ?>
								<use xlink:href="<?=path2uri($path)?>pub/module.svg#main" />
						<?php } else { ?>
								<use xlink:href="<?=sysURL?>cms.frontend.1/pub/img/module_default.svg#main" />
						<?php } ?>
					</svg>
					<div class=-title title="<?=hee($P->id)?>"><?=$title?></div>
				</div>
			<?php } ?>
		</div>
	<?php } ?>

</div>
