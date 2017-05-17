<?php
namespace qg;

html::addJSFile(sysURL.'core/js/jQuery.js',      'be-layout');
html::addJsFile(sysURL.'core/js/c1.js',          'be-layout');
html::addJsFile(sysURL.'core/js/c1/dom.js',  'be-layout');
html::addJsFile(sysURL.'core/js/c1/onElement.js','be-layout');
html::addJsFile(sysURL.'core/js/c1/href.js',     'be-layout');
html::addJsFile(sysURL.'core/js/c1/focusIn.js',  'be-layout');
html::addJsFile(sysURL.'core/js/c1/css/theme.js','be-layout');
html::addJSFile(sysURL.'core/js/qg.js',          'be-layout');
html::addJSFile(sysURL.'cms/pub/js/cms.js',      'be-layout');

html::addCssFile(sysURL.'core/js/c1/css/normalize.css',    'be-layout');
html::addCssFile(sysURL.'core/js/c1/css/recommend.css',    'be-layout');
html::addCssFile(sysURL.'core/js/c1/css/theme1.css',       'be-layout');
html::addCssFile(sysURL.'cms.frontend.1/pub/css/main.css', 'be-layout');

html::$meta['viewport'] = 'width=device-width, initial-scale=1, maximum-scale=1';

$Page = $Cont->Page;

!$Cont->Conts() && $Cont->Cont('main'); // create default
?>
<div class=qgCMS id=container>
	<nav id=nav>
		<ul>
			<?php foreach (Page(G()->SET['cms']['backend']->v)->Children('navi') as $C) {
				$Sub = $C->Children('navi');
				?>
				<li>
					<a class="-item <?=$Page->in($C)?'-active':''?> <?=$Sub?'-hasSub':''?>" href="<?=$C->url()?>"><?=$C->title()?></a>
					<?php if ($Page->in($C)) { ?>
						<?php foreach ($Sub as $SC) {
							$Sub = $SC->Children('navi');
							?>
							<ul>
								<li>
									<a class="-item <?=$Page->in($SC)?'-active':''?> <?=$Sub?'-hasSub':''?>" href="<?=$SC->url()?>"><?=$SC->title()?></a>
									<?php if ($Page->in($SC)) { ?>
										<?php foreach ($SC->Children('navi') as $SSC) { ?>
											<ul>
												<li><a class="-item <?=$Page->in($SSC)?'-active':''?>" href="<?=$SSC->url()?>"><?=$SSC->title()?></a>
											</ul>
										<?php } ?>
									<?php } ?>
							</ul>
						<?php } ?>
					<?php } ?>
			<?php } ?>
			<?php if (count(L::all()) > 1) { ?>
				<li>
					<span class=-item style="padding:6px 16px; text-align:right">
						<?php foreach (L::all() as $l) { if (L()===$l) continue; ?>
							<a href="<?=Url()->addParam('changeLanguage', $l)?>"><?=$l?></a>
						<?php } ?>
					</span>
			<?php } ?>
		</ul>
	</nav>
	<div id=content>
		<?php
		foreach ($Cont->Conts() as $C) {
			echo $C->get();
		}
		?>
	</div>
</div>
