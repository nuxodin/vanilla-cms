<?php
namespace qg;
if ($Cont->access() < 2) return;

$count = D()->one("SELECT count(*) FROM page_redirect WHERE redirect = ".$Cont);
$number = $count ? '<span class=-info>'.$count.'</span>' : '';
echo '<span class=-title>'.L('Urls').'</span> '.$number;
