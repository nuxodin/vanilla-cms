<?php
namespace qg;

if (G()->SET['cms']['frontend']->v !== 'cms.frontend.1') return;

function cmsFrontend1Widget($widget, $open, $class='-content') {
	global $cmsWidgetCont;
	$Cont = $cmsWidgetCont;
	echo '<div class="'.$class.'" widget='.$widget.'>';
	if ($open) include sysPATH.'cms.frontend.1/view/widgets/'.$widget.'.php';
	echo '</div>';
}
function cmsFrontend1WidgetAccordion($widget, $title) {
	$SET = G()->SET['cms.frontend.1']['custom']['widget'];
	$open = $SET->has($widget) && $SET[$widget]->v;
	$class = '-widgetHead '.($open?'-open':'');
	if (is_file(sysPATH.'cms.frontend.1/view/widgets/'.$widget.'.head.php')) {
		cmsFrontend1Widget($widget.'.head', true, $class);
	} else {
		echo '<div class="'.$class.'"><span class=-title>'.$title.'</span></div>';
	}
	cmsFrontend1Widget($widget, $open, '-content');
}
function cmsFrontend1WidgetSidebar($widget, $title, $tooltip='') {
	$open = G()->SET['cms.frontend.1']['custom']['sidebar']->v === $widget;
	?>
	<div class="-item <?=$open?'-open':''?>" itemid="<?=$widget?>">
		<?php cmsFrontend1Widget($widget, $open) ?>
		<div class=-title>
			<div class=-text title="<?=hee($tooltip)?>" c1-tooltip><?=$title?></div>
		</div>
	</div>
	<?php
}

class serverInterface_cms_frontend_1 {
	static function widget($widget, $params=[]) {
		Api::checkToken();
		$P = Page($params['pid']);
		if ($P->access() < 2) return false;
		if (strpos($widget,'/'))  return null;
		global $cmsWidgetCont;
		$cmsWidgetCont = $P;
		L::nsStart('cms');
		$T = new Template(['Cont'=>$P ,'param'=>$params]);
		$html = $T->get(sysPATH.'cms.frontend.1/view/widgets/'.$widget.'.php');
		L::nsStop();
		return $html;
	}
}

qg::on('cms-ready', function(){
	if (isset($_GET['qgCmsNoFrontend'])) return;
	$Cont = cms::$MainPage;
	$access = $Cont->access();
	$inBackend = $Cont->vs['module'] === 'cms.layout.backend';
	//toggle backend
	if ($access > 1 || $inBackend) {
		if (G()->SET['cms']['pageNotFound']->v != $Cont->id) {
			G()->SET['cms'][$inBackend?'last_backend_page':'last_frontend_page']->setUser($_SERVER['REQUEST_URI']);
			$backendTogglelUrl = G()->SET['cms'][$inBackend?'last_frontend_page':'last_backend_page']->v;
			G()->js_data['cmsBackendUrl'] = $backendTogglelUrl;
			html::addJSFile( sysURL.'cms.frontend.1/pub/js/init.js',    'cms/noEdit');
		}
	}
	if ($access > 1) {
		G()->csp['img-src']['blob:'] = true; // drag&drop images
		//G()->csp['default-src']["'self'"] = true; // as of firefox 50, svgs loaded via "use" are blocked
		G()->js_data['Page'] = $Cont->id;
		G()->js_data['qgCmsRequestedPage'] = cms::$RequestedPage->id;
		G()->js_data['qgDebugmode'] = Usr()->superuser ? debug : null;
		G()->js_data['qgCmsEditmode'] = (int)G()->SET['cms']['editmode']->v;
		// show frontend
		if (G()->SET['cms']['editmode']->v) {
			G()->js_data['cmsClipboard'] = (int)G()->SET['cms']['clipboard']->v;
			ob_start();
			include sysPATH.'cms.frontend.1/view/panel.php';
			html::$content = ob_get_clean().html::$content; // frontend first
		}
	}
});
qg::on('deliverHtml', function(){
	if (isset($_GET['qgCmsNoFrontend'])) return;
	if (Page()->access() < 2) return;
	html::addJSFile(sysURL.'core/js/c1.js',                     'cms/noEdit');
	html::addJsFile(sysURL.'core/js/c1/dom.js',                 'cms/noEdit');
	html::addCSSFile(sysURL.'cms.frontend.1/pub/css/off.css',   'cms/noEdit');
	html::addJSFile( sysURL.'cms.frontend.1/pub/js/init.js',    'cms/noEdit');
	if (Page()->edit) {
		html::addJSFile( sysURL.'cms.frontend.1/pub/js/browserCheck.js', 'cms/edit');
		html::addJSFile( sysURL.'core/js/qg.js',                         'cms/edit');
		html::addJSFile( sysURL.'core/js/c1/onElement.js',               'cms/edit');
		html::addJSFile( sysURL.'cms/pub/js/cms.js',                     'cms/edit', true, '');
		// jQuery neede for "sortable" and "dynatree"
		html::addJSFile(sysURL.'core/js/jQuery.js',                      'cms/edit');
		html::addJSFile(sysURL.'core/js/jQuery/ui.js',                   'cms/edit');
		html::addJsFile(sysURL.'core/js/jQuery/fn/dynatree.js',          'cms/edit');
		// js modules
		html::addJSM( sysURL.'cms.frontend.1/pub/js/frontend.mjs');
		html::addJSM( sysURL.'cms.frontend.1/pub/js/panel.mjs');
		//html::addJSM( sysURL.'cms.frontend.1/pub/js/c1Intro.mjs');
		//html::addJSM( sysURL.'core/js/c1/tooltip.mjs');
		// css
		html::addCSSFile(sysURL.'core/js/Rte/main.css',                  'cms/edit');
		html::addCSSFile(sysURL.'core/js/jQuery/fn/dynatree/skin-vista/ui.dynatree.css', 'cms/edit');
		html::addCSSFile(sysURL.'core/js/qgCalendar/calendar.css',       'cms/edit');
		html::addCSSFile(sysURL.'core/css/q1Rst.css',                    'cms/edit');
		html::addCSSFile(sysURL.'core/css/c1/box.css',                   'cms/edit');
		html::addCSSFile(sysURL.'cms.frontend.1/pub/css/main.css',       'cms/edit');
		html::addCSSFile(sysURL.'cms.frontend.1/pub/css/panel.css',      'cms/edit');
		html::addCSSFile(sysURL.'cms.frontend.1/pub/css/tree.css',       'cms/edit');
	}
});
