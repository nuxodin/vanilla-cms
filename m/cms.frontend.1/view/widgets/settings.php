<?php namespace qg ?>
<div class="-standalone qgCMSFron1ContManager" pid="<?=$Cont?>" page-type="<?=hee($Cont->vs['type'])?>" style="font-size:1.2em; margin-bottom:1em">
    <div>

        <div class=-h1>
            <?php
            L::nsStart('');
            $T = $Cont->Title();
            L::nsStop();
            ?>
            <input<?=$Cont->edit?' contenteditable cmstxt='.$T->id:''?> value="<?=hee($T)?>" style="color:inherit; background:transparent; letter-spacing:.1em; width:100%; padding:0; border:none; outline:none; font-size:inherit" placeholder="kein Titel">
            <?php
            $path = sysPATH.$Cont->vs['module'].'/pub/';
            $url = is_file($path.'module.svg') ? path2uri($path).'module.svg' : sysURL.'cms.frontend.1/pub/img/module_default.svg';
            ?>
            <div style="margin-top:-15px">
                <svg class=-img xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="var(--cms-dark)" width="46" height="46" style="display:block">
                    <use xlink:href="<?=$url?>#main" />
                </svg>
            </div>
    	</div>

        <div>Nr. <b><?=$Cont?></b></div>
        <?php
        $Module = D()->module->Entry($Cont->vs['module']);
        ?>
        <div style="display:inline-flex; width:100%">
            <span title="<?=hee($Module->name)?>"><?=$Cont->vs['type']==='p'?'Layout':'Modul'?>: </span>
            <select class=-changemodule style="border:none; font-size:inherit; font-weight:bold; flex:1 1 auto; width:100%; padding:0; margin-top:-1px; margin-top: -4px; margin-bottom: -3px; background:transparent">
            <?php
            $modules = $Cont->vs['type'] === 'p' ? cms::getLayouts() : cms::getModules();
            foreach ($modules as $name => $path) {
                $M = D()->module->Entry($name);
                echo '<option value="'.hee($name).'" '.($name===$Cont->vs['module']?'selected':'').'>'.$M->Title();
            }
            ?>
            </select>
        </div>
    </div>
    <?php
    $P = $Cont->Parent();
    if ($P) {
        ?>
        <div class=-editparent parent="<?=$P?>" page-type="<?=hee($P->vs['type'])?>">
            <?=L('Übergeordnet:')?>
            <a href="<?=hee($P->url())?>" style="font-weight:bold;">
                <?php
                echo trim(strip_tags($P->Title()))?:$P;
                if ($P->vs['type']==='c') {
                    echo ' '.$P->vs['module'].' <span style="font-weight:normal; color:#000; font-size:20px; line-height:.5em; position:relative; margin-bottom:-2px">✎</span>';
                }
                ?>
            </a>
        </div>
    <?php
    }
    ?>
</div>

<?php
if (is_file($Cont->modPath.'options.php') || G()->SET['cms']['pages']->has($Cont->id) && count($Cont->SET)) {
    echo cmsFrontend1WidgetAccordion('options', L('Einstellungen'));
}
echo cmsFrontend1WidgetAccordion('media', 'zzzDateien');
//echo cmsFrontend1WidgetAccordion('access', 'Zugriff');
if (G()->SET['cms.frontend.1']['show access.time']->v) {
    echo cmsFrontend1WidgetAccordion('access.time', 'zzzTerminieren');
}
if ($Cont->access() > 2) {
    echo cmsFrontend1WidgetAccordion('access.grp', 'zzzGruppen-Zugriff');
    echo cmsFrontend1WidgetAccordion('access.usr', 'zzzBenutzer-Zugriff');
}
if ($Cont->vs['type'] === 'p') {
    echo cmsFrontend1WidgetAccordion('seo', 'zzzSEO');
}
if (G()->SET['cms.frontend.1']['show urls']->v) {
    echo cmsFrontend1WidgetAccordion('urls', 'zzzUrls');
}
if (G()->SET['cms.frontend.1']['show classes']->v) {
    echo cmsFrontend1WidgetAccordion('classes', 'Tags');
}

echo cmsFrontend1WidgetAccordion('extended', L('Erweitert'));
if (Usr()->superuser) echo cmsFrontend1WidgetAccordion('superuser', 'Superuser');
