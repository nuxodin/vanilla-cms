<?php
namespace qg;
?>
<div><?php
$file = appPATH.'qg/cmsPhpFiles/'.$Cont.'.php';
is_file($file) ? include $file : $Cont->edit && touch($file);
?></div>
