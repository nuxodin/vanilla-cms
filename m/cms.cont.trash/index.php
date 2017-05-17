<?php
namespace qg;

$TrashPage = Page(G()->SET['cms']['pageTrash']->v);
$Cont->set('sort',9999999999);

html::addJsFile(sysURL.'core/js/c1.js');
html::addJsFile(sysURL.'core/js/c1/dom.js');
html::addJsFile(sysURL.'core/js/c1/onElement.js');
html::addJsFile(sysURL.'core/js/qg.js');
html::addJsFile(sysURL.'core/js/jQuery.js');
html::addJsFile(sysURL.'cms/pub/js/cms.js');
?>
<div class="qgCMS q1Rst">
    <?php
    $Elements = [];
    foreach ($TrashPage->Children(['access'=>2,'type'=>'*']) as $P) {
        if ($Cont->in($P)) continue;
        if (!G()->SET['cms']['pages']->has($P->id)) continue;
        if (!$P->SET->has('__deleted_from')) continue;
        $Elements[] = $P;
    }
    usort($Elements, function($A, $B){
        return $A->SET['__deleted_time']->v < $B->SET['__deleted_time']->v;
    });
    if (isset($vars['removeAll'])) {
        foreach ($Elements as $P) {
            Api::call('page::remove', [$P->id]);
        }
        $Elements = [];
    }
    if (isset($vars['restore'])) {
        $Page   = Page($vars['restore']);
        $ToPage = Page($Page->SET['__deleted_from']->v);
        if ($Page->access() >= 2 && $Page->access() >= 2) {
            $ToPage->insertBefore($Page, $Page->SET['__deleted_before']->v);
            unset($Page->SET['__deleted_from']);
            unset($Page->SET['__deleted_before']);
            unset($Page->SET['__deleted_time']);
            echo '<script>alert("Bitte passen Sie die Berechtigungen auf der wiederhergestellten Seite an!"); location.href = "'.$Page->url().'" </script>';
        }
    }
    ?>
    <?php if ($Elements) { ?>
        <button class=-removeAll style="margin-bottom:10px"><?=L('Papierkorb leeren')?></button>
    <?php } else { ?>
        <div style="text-align:center">Der Papierkorb ist leer</div>
    <?php } ?>
    <div class=-list>
        <?php foreach ($Elements as $P) {
            $DeletedFrom = Page($P->SET['__deleted_from']);
            ?>
            <div class=-item itemid="<?=$P?>">
                <table>
                    <tr>
                        <td colspan=2 style="font-size:1.3em; padding-bottom:5px; color:<?=$P->vs['type']=='p'?'var(--cms-color);':'var(--cms-access-3)'?>">
                            <?=trim($P->Title())?$P->Title():'(kein Titel)'?>
                    <tr>
                        <th> Id:
                        <td> <?=$P?>
                    <tr>
                        <th> Module:
                        <td> <?=$P->vs['module']?>
                    <tr>
                        <th> Gelöscht am:
                        <td>
                            <?php
                            $date = strftime('%x',(int)$P->SET['__deleted_time']->v);
                            $time = strftime('%H:%M',(int)$P->SET['__deleted_time']->v);
                            if ($date === strftime('%x')) $date = 'heute';
                            $seconds = time() - $P->SET['__deleted_time']->v;
                            if ($seconds < 60*59) {
                                $date = '';
                                $time = 'vor '.round($seconds/60).' Minuten';
                            }
                            if ($seconds < 59) {
                                $time = 'vor '.round($seconds).' Sekunden';
                            }
                            echo $date.' '.$time;
                            ?>
                    <tr>
                        <th> Gelöscht von:
                        <td> <?= $DeletedFrom->is() ? $DeletedFrom->Title().' ('.$DeletedFrom.')' : 'nicht mehr vorhanden'; ?>
                </table>
                <div class=-more>
                    <button class=-restore><?=L('Wiederherstellen')?></button>
                    <button class=-remove><?=L('Endgültig löschen')?></button>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class=-preview>
        <iframe></iframe>
    </div>
</div>
