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
function cmsFrontend1WidgetSidebar($widget, $titel) {
	$open = G()->SET['cms.frontend.1']['custom']['sidebar']->v === $widget;
	?>
	<div class="-item <?=$open?'-open':''?>" itemid="<?=$widget?>">
		<?php cmsFrontend1Widget($widget, $open) ?>
		<div class=-title>
			<div class=-text><?=$titel?></div>
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
	if (cms::$MainPage->access() < 2) return;

	//if (isset($_GET['qgCmsNoFrontend'])) return; zzz
	G()->csp['img-src']['blob:'] = true;
	G()->csp['default-src']["'self'"] = true; /* as of firefox 50, svgs loaded via "use" are blocked */
	$Cont = Page();
	$cmsSET = G()->SET['cms'];
	$cmsSET->make('last_backend_page',$cmsSET['backend']->v)->custom();
	$cmsSET->make('last_frontend_page',2)->custom();
	$edit = $cmsSET['editmode']->v;
	G()->js_data['Page'] = $Cont->id;
	G()->js_data['cmsToggleEditUrl']  = (string)Url()->addParam('qgCms_editmode', $cmsSET['editmode']->v ? 0 : 1, false)->addParam('cmspid', cms::$RequestedPage->id, false);
	if (Usr()->superuser) G()->js_data['cmsToggleDebugUrl'] = (string)Url()->addParam('debugmode',debug ? 0 : 1 ,false);
	$inBackend = $Cont->in($cmsSET['backend']->v);
	$cmsSET[$inBackend?'last_backend_page':'last_frontend_page']->setUser($Cont);
	$BPage = Page($cmsSET[$inBackend?'last_frontend_page':'last_backend_page']->v);
	if ($BPage->access()) G()->js_data['cmsBackendUrl'] = (string)Url($BPage->url());
	G()->js_data['qgCmsEditmode'] = (int)$edit;
	if (!$edit) return;
	G()->js_data['cmsClipboard'] = (int)$cmsSET['clipboard']->v;
	html::addBodyFile(sysPATH.'cms.frontend.1/view/panel.php');
	//html::addBodyFile(sysPATH.'cms.frontend.1/view/frontend.php');
});
qg::on('deliverHtml', function(){
	if (isset($_GET['qgCmsNoFrontend'])) return;
	if (Page()->access() < 2) return;
	html::addJSFile(sysURL.'core/js/c1.js',                     'cms/noEdit');
	html::addCSSFile(sysURL.'cms.frontend.1/pub/css/off.css',   'cms/noEdit');
	html::addJSFile( sysURL.'cms.frontend.1/pub/js/init.js',    'cms/noEdit');
	if (Page()->edit) {
		html::addJSFile( sysURL.'cms.frontend.1/pub/js/browserCheck.js', 'cms/edit');
		html::addJSFile( sysURL.'core/js/qg.js',                         'cms/edit');
		html::addJsFile( sysURL.'core/js/c1/fix/dom.js',                 'cms/edit');
		html::addJSFile( sysURL.'core/js/c1/onElement.js',               'cms/edit');
		html::addJSFile( sysURL.'cms/pub/js/cms.js',                     'cms/edit', true, '');
		html::addJSFile( sysURL.'core/js/c1/NodeCleaner.js',             'cms/edit');
		html::addJSFile( sysURL.'core/js/qg/fakeSelect.js',              'cms/edit');
		html::addJSFile(sysURL.'core/js/jQuery.js',                      'cms/edit');
		html::addJSFile( sysURL.'core/js/jQuery/ui.js',                  'cms/edit');
		html::addJSFile( sysURL.'core/js/qg/fileHelpers.js',             'cms/edit');
		html::addJSFile( sysURL.'core/js/qgRte/crossbrowser.js',         'cms/edit');
		html::addJSFile( sysURL.'core/js/qgRte/htmlparser.js',           'cms/edit');
		html::addJSFile( sysURL.'core/js/qgRte/helpers.js',              'cms/edit');
		html::addCSSFile(sysURL.'core/js/qgRte/main.css',                'cms/edit');
		html::addJSFile( sysURL.'core/js/qgRte/Rte.js',                  'cms/edit');
		html::addJSFile( sysURL.'core/js/qgRte/Rte.ui.js',               'cms/edit');
		html::addJSFile( sysURL.'core/js/qgRte/Rte.ui.items.js',         'cms/edit');
		html::addJsFile(sysURL.'core/js/jQuery/fn/dynatree.js',          'cms/edit');
		html::addCSSFile(sysURL.'core/js/jQuery/fn/dynatree/skin-vista/ui.dynatree.css', 'cms/edit');
		html::addJSFile( sysURL.'core/js/c1/Placer.js',                  'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'core/js/qgCalendar/date_extend.js',     'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'core/js/qgCalendar/qgCalendar.js',      'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'core/js/qgCalendar/qgDateInput.js',     'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'core/js/qgCalendar/timepicker/nogray_time_picker.js',   'cms/edit', true, 'defer');
		html::addCSSFile(sysURL.'core/js/qgCalendar/calendar.css',       'cms/edit');
		html::addJSFile( sysURL.'cms/pub/js/rte.js',                     'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'cms/pub/js/dropPaste.js',               'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'cms/pub/js/dropPasteHelper.js',         'cms/edit', true, 'defer');
		//html::addJSFile( sysURL.'cms.frontend.1/pub/js/c1Intro.js',      'cms/edit'); // todo
		html::addJSFile( sysURL.'cms.frontend.1/pub/js/frontend1.js',    'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'cms.frontend.1/pub/js/ddConts.js',      'cms/edit', true, 'defer');

		html::addJSFile( sysURL.'cms.frontend.1/pub/js/tree.js',         'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'cms.frontend.1/pub/js/panel.js',        'cms/edit', true, 'defer');

		html::addJSFile( sysURL.'core/js/c1/fix/contextMenu.js',         'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'core/js/c1/contextMenu.js',             'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'cms.frontend.1/pub/js/contextMenu.js',  'cms/edit', true, 'defer');
		html::addJSFile( sysURL.'core/js/SettingsEditor.js',             'cms/edit', true, 'async');

		html::addCSSFile(sysURL.'core/css/q1Rst.css',                    'cms/edit');
		html::addCSSFile(sysURL.'cms.frontend.1/pub/css/main.css',       'cms/edit');
		html::addCSSFile(sysURL.'cms.frontend.1/pub/css/panel.css',      'cms/edit');
		html::addCSSFile(sysURL.'cms.frontend.1/pub/css/tree.css',       'cms/edit');
	}
});
