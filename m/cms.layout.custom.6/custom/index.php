<?php
namespace qg;

html::addJsFile(sysURL.'core/js/c1.js');
html::addJsFile(sysURL.'core/js/c1/fix/dom.js');
html::addJsFile(sysURL.'core/js/c1/onElement.js');
html::addJsFile(sysURL.'core/js/c1/img-ratio.js');
//html::addJsFile(sysURL.'core/js/jQuery.js');
//html::addJsFile(sysURL.'core/js/qg.js');
//html::addJsFile(sysURL.'cms/pub/js/cms.js');

//html::addJsFile (appURL.'qg/'.$Cont->vs['module'].'/pub/main.js');
//html::addJsFile (appURL.'qg/'.$Cont->vs['module'].'/pub/mob_nav/mob_nav.js');
//html::addCssFile(appURL.'qg/'.$Cont->vs['module'].'/pub/mob_nav/mob_nav.css');

// preload fonts
//html::$head .= '<link rel=preload href="https://fonts.gstatic.com/s/lato/v13/1YwB1sO8YE1Lyjf12WNiUA.woff2" as=font type=font/woff2 crossorigin>';

$Logo = $LPage->File('logo');
?>
<div id=container>
	<header id=head>
		<div class="l1_width l1_cols">
			<a id=logo href="<?=Page(2)->url()?>">
				<img src="<?=$Logo->url().'/h-60/'.$Logo->name()?>" data-dbfile-editable>
			</a>
			<div id=nav style="flex:1">
				<?=$LPage->Cont('head')->get()?>
			</div>
			<a href="#nav">---</a>
		</div>
	</header>
	<div id=main>
		<div class="l1_width l1_cols">
			<div  id=left>    <?=$LPage->Cont('left')->get()?>  </div>
			<main id=content> <?=$Cont->Cont('main')->get()?>   </main>
			<div  id=right>   <?=$LPage->Cont('right')->get()?> </div>
		</div>
	</div>
    <footer id=foot>
		<div class=l1_width>
	    	<?=$LPage->Cont('foot')->get()?>
	    </div>
   	</footer>
</div>
