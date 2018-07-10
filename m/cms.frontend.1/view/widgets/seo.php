<?php namespace qg;
$textTitle = L('Titel');
$textDescr = L('Beschreibung');
$placeholderTitle = L('max. ###1### Zeichen','55').', '.L('wichtige Wörter zuerst');
$placeholderDescr = L('max. ###1### Zeichen','156');
L::nsStart('');
?>
<div class=qgCmsFront1SeoManager pid=<?=$Cont?>>
    <?=$textTitle?>:
    <?php $T=$Cont->Text('_title') ?>
    <input style="width:100%;display:block" cmstxt=<?=$T->id?> value="<?=hee($T)?>" required pattern=".{10,55}" maxlength=100 placeholder="<?=hee($placeholderTitle)?>">
    <br>

    <?=$textDescr?>:
    <?php $T=$Cont->Text('_meta_description') ?>
    <textarea class=-desc style="display:block; width:100%; height:45px" cmstxt=<?=$T->id?> required pattern=".{60,156}" maxlength=220 placeholder="<?=hee($placeholderDescr)?>" rows=4 cols=70><?=hee($T)?></textarea>
    <br>

    <style>
    .qgCmsFront1SeoManager :invalid,
    .qgCmsFront1SeoManager .-invalid.-invalid {
        border-bottom-color:var(--cms-access-3);
    }
    </style>
</div>
<?php
L::nsStop();
