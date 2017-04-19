<?php
namespace qg;

html::addCssFile(sysURL.'core/js/c1/css/normalize.css');
html::addCssFile(sysURL.'core/js/c1/css/recommend.css');
html::addCSSFile(sysURL.'cms.frontend.1/pub/css/main.css');

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
