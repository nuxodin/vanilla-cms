<?php namespace qg ?>

<div class=qgCmsFront1SeoManager pid=<?=$Cont?>>
    <?=L('Titel')?>:
    <?php $T=$Cont->text('_title') ?>
    <input style="width:100%;display:block" cmstxt=<?=$T->id?> value="<?=hee($T)?>" required pattern=".{10,55}" maxlength=100 placeholder="<?=L('max. ###1### Zeichen','55').', '.L('wichtige Wörter zuerst')?>">
    <br>

    <?=L('Beschreibung')?>:
    <?php $T=$Cont->text('_meta_description') ?>
    <textarea class=-desc style="display:block; width:100%; height:45px" cmstxt=<?=$T->id?> required pattern=".{60,156}" maxlength=220 placeholder="<?=L('max. ###1### Zeichen','156')?>" rows=4 cols=70><?=hee($T)?></textarea>
    <br>

    <?php /*
    <?=L('Schlüsselwörter')?>:
    <?php $T=$Cont->text('_meta_keywords') ?>
    <textarea style="display:block; width:100%; height:45px" cmstxt=<?=$T->id?> placeholder="ca. 15 Wörter, Komma getrannt, Schlüsselwörter werden nicht stark gewichtet" rows=4 cols=70><?=hee($T)?></textarea>
    */
    ?>

    <style>
    .qgCmsFront1SeoManager :invalid,
    .qgCmsFront1SeoManager .-invalid.-invalid {
        border-bottom-color:var(--cms-access-3);
    }
    </style>
</div>
