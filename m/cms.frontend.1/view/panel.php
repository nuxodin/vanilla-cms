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
		<a class="-item qgCMS_editmode_switch -active" href="<?=hee(Url()->addParam('qgCms_editmode',1))?>" title="Bearbeiten (E)">
			<div style="opacity:0"><i></i></div>
		</a>
		<?php
		cmsFrontend1WidgetSidebar('tree'    , L('Struktur'), L('Übersicht aller Seiten, <br>Seiten erstellen, verschieben, löschen...'));
		cmsFrontend1WidgetSidebar('settings', L('Einstellungen'), L('Einstellungen, Dateien, Rechte der aktuellen Seite'));
		cmsFrontend1WidgetSidebar('add'     , L('Module'), L('Inhalte hinzufügen, z.B. ein Textfeld oder eine Tabelle'));
		cmsFrontend1WidgetSidebar('more'    , L('Weiteres'), L('CMS-Feedback, Passwort ändern...'));
		?>
		<div class=-sensor></div>
	</div>
</div>
<?php L::nsStop('cms'); ?>
