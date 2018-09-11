<?php
namespace qg;

// todo:
// https://gist.github.com/thomd/9220049
// https://github.com/iandevlin/html5bones/blob/master/main-template.html

html::addJsFile(sysURL.'core/js/c1.js');
html::addJsFile(sysURL.'core/js/c1/dom.js');
html::addJsFile(sysURL.'core/js/c1/onElement.js');
html::addJsFile(sysURL.'core/js/c1/img-ratio.js');
//html::addJsFile(sysURL.'core/js/qg.js');
//html::addJsFile(sysURL.'cms/pub/js/cms.js');
//html::addJsFile(sysURL.'core/js/jQuery.js');

//html::addJsFile (appURL.'qg/'.$Cont->vs['module'].'/pub/main.js');
html::addJsFile (appURL.'qg/'.$Cont->vs['module'].'/pub/mob_nav/mob_nav.js');
html::addCssFile(appURL.'qg/'.$Cont->vs['module'].'/pub/mob_nav/mob_nav.css');
html::addJsFile (appURL.'qg/'.$Cont->vs['module'].'/pub/scrolling.js');
//html::addCssFile(appURL.'qg/'.$Cont->vs['module'].'/pub/dropdown_nav.css');

// preload fonts
//html::$head .= '<link rel=preload href="https://fonts.gstatic.com/s/lato/v13/1YwB1sO8YE1Lyjf12WNiUA.woff2" as=font type=font/woff2 crossorigin>';

$Logo = $LPage->File('logo');
?>
<div id=container>
	<style>
	:root {
		--l1-theme-color:<?=G()->SET['app1']['theme_color']->v?>;
		--l1-background-color:<?=G()->SET['app1']['background_color']->v?>;
		--l1-black: #000;
		--l1-white: #fff;
	}
	</style>
	<header id=head>
		<div class="l1_width l1_cols">
			<a id=logo href="<?=Page(2)->url()?>">
				<img src="<?=$Logo->url().'/h-60/'.$Logo->name()?>" dbfile-editable>
			</a>
			<div id=nav style="flex:1">
				<?=$LPage->Cont('head')->get()?>
			</div>
			<svg class=mob_nav_btn xmlns="http://www.w3.org/2000/svg" viewbox="0 0 30 30">
			    <g class=-menu>
			      <line x1="5" y1="7"  x2="25" y2="7" ></line>
			      <line x1="5" y1="15" x2="25" y2="15"></line>
			      <line x1="5" y1="23" x2="25" y2="23"></line>
			    </g>
			    <g class=-close>
			      <line x1="7" y1="7"  x2="23" y2="23"></line>
			      <line x1="7" y1="23" x2="23" y2="7" ></line>
			    </g>
			</svg>
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
