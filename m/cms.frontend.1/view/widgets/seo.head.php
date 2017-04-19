<?php
namespace qg;
if ($Cont->access() < 2) return;

$number = trim($Cont->Text('_meta_description')) ? '' : '<span class=-info>!</span>';
echo '<span class=-title>'.L('SEO').'</span> '.$number;
