<?php
namespace qg;
if ($Cont->access() < 2) return;

$number = $Cont->isOnline() ? '' : '<span class=-info>!</span>';
echo '<span class=-title>'.L('Terminieren').'</span> '.$number;
