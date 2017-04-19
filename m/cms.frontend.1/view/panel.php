<?php
namespace qg;

$Cont = Page();

global $cmsWidgetCont;
$cmsWidgetCont = $Cont;

G()->js_data['cmsFrontend1Data'] = G()->SET['cms.frontend.1']['custom']->get();

L::nsStart('cms');
?>
<div id=qgCmsFrontend1 class="q1Rst qgCMS -open -sidebar-open">
	<div class=-sidebar>
		<a class="-item qgCMS_editmode_switch -active" href="<?=Url()->addParam('qgCms_editmode',1)?>" title="Bearbeiten (E)">
			<div style="opacity:0"><i></i></div>
		</a>
		<?php
		cmsFrontend1WidgetSidebar('tree'    , L('Struktur'));
		cmsFrontend1WidgetSidebar('settings', L('Einstellungen'));
		cmsFrontend1WidgetSidebar('add'     , L('Module'));
		cmsFrontend1WidgetSidebar('more'    , L('Weiteres'));
		?>
		<div class=-sensor></div>
	</div>
</div>
<?php L::nsStop('cms'); ?>
