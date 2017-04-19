<?php
namespace qg;
if ($Cont->access() < 3) return;


$P = $Cont->accessInheritParent();
$number  = !$P->isPublic() ? '<span class=-info style="font-family:qg_cms;">&#xe900;</span>' : '';
$all = D()->row("SELECT sum(if(access=1,1,0)) as access_1, sum(if(access=2,1,0)) as access_2 , sum(if(access=3,1,0)) as access_3 FROM page_access_grp WHERE page_id = ".$P);
$number .= $all['access_1'] ? '<span class="-info -access-1-bg">'.$all['access_1'].'</span>' : '';
$number .= $all['access_2'] ? '<span class="-info -access-2-bg">'.$all['access_2'].'</span>' : '';
$number .= $all['access_3'] ? '<span class="-info -access-3-bg">'.$all['access_3'].'</span>' : '';

echo '<span class=-title>'.L('Gruppen-Zugriff').'</span> '.$number;
