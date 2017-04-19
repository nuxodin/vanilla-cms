<?php
namespace qg;
if ($Cont->access() < 2) return;

$count  = count($Cont->Files());
$number = $count ? '<span class=-info>'.$count.'</span>' : '';
echo '<span class=-title>'.L('Dateien').'</span> '.$number;
