<?php
namespace qg;

$LPage = layoutCustom6::layoutPage();
$module = $Cont->vs['module'];

html::addCssFile(appURL.'qg/'.$module.'/pub/base.css',   'dr8og', false);
html::addCssFile(appURL.'qg/'.$module.'/pub/custom.css', 'dr8og', false);
if ($font_css = $LPage->SET['font-css-file']->v) {
    $url = Url($font_css);
    G()->csp['style-src'][$url->scheme.'://'.$url->host] = 1;
    G()->csp['font-src']['*'] = 1;
    $font_css = str_replace('|', '%7C', $font_css); // rawurlencode ?
    html::$head .= '<link rel=stylesheet href="'.hee($font_css).'">'."\n";
}
if ($LPage->edit && !isset($_GET['qgCmsNoFrontend'])) {
    html::addJsFile( sysURL.$module.'/pub/qgElSty/qgCssProps.js','43l5k');
    html::addJsFile( sysURL.$module.'/pub/qgElSty/q1CssText.js','43l5k');

    html::addJSM(sysURL.$module.'/pub/qgElSty/qgStyleEditor.mjs');
    html::addJSM(sysURL.$module.'/pub/qgElSty/qgStyleSheetEditor.mjs');

    html::addJsFile( sysURL.$module.'/pub/qgElSty/spectrum.js','43l5k');
    html::addCssFile(sysURL.$module.'/pub/qgElSty/spectrum.css','43l5k',0);
    html::addCssFile(sysURL.$module.'/pub/qgElSty/main.css','43l5k');
//    html::addJsFile( sysURL.$module.'/pub/edit.js','43l5k');
    html::addJSM(sysURL.$module.'/pub/edit.mjs');
}

include appPATH.'qg/'.$module.'/index.php';
