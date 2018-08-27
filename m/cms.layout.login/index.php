<?php
namespace qg;

html::addCssFile(sysURL.'core/css/c1/normalize.css');
html::addCssFile(sysURL.'core/css/c1/recommend.css');
html::addCSSFile(sysURL.'cms.frontend.1/pub/css/main.css');
html::addJsFile(sysURL.'core/js/c1.js');

html::$meta['viewport'] = 'width=device-width, initial-scale=1, maximum-scale=1';

?>
<div id=container class=qgCMS>
    <div id=head>
        <div id=title><?=$Cont->Title()?></div>
        <div id=subtitle><?=$_SERVER['HTTP_HOST']?></div>
    </div>
    <div id=content>
        <?=$Cont->Cont('main')->get()?>
    </div>
</div>
