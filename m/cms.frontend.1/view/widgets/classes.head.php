<?php
namespace qg;
if ($Cont->access() < 2) return;

$count = count($Cont->Classes());
$number = $count ? '<span class=-info>'.$count.'</span>' : '';
echo '<span class=-title>'.L('Tags').'</span> '.$number;
