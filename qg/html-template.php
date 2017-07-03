<?php
namespace qg;

header('content-type: text/html; charset=utf-8');

G()->js_data['qgToken']   = qg::token();
G()->js_data['appURL']    = appURL;
G()->js_data['sysURL']    = sysURL;
G()->js_data['c1UseSrc']  = sysURL.'core/js';
G()->js_data['moduleAge'] = G()->SET['qg']['module_changed']->v;

$body = html::getBody();

?><!DOCTYPE HTML>
<html lang=<?=L()?>>
	<head><?=html::getHeader()?>
	<body><?=$body?>
