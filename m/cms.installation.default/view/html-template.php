<?php
namespace qg;

header('content-type: text/html; charset=utf-8');
$Page = Page()->Page;
$title = strip_tags($Page->Text('_title'));
html::$title = trim($title) ? $title : strip_tags($Page->Title());
html::$meta['description'] = strip_tags($Page->text('_meta_description'));
html::$meta['keywords']    = strip_tags($Page->text('_meta_keywords'));
html::$meta['generator']   = 'Vanilla CMS 5.0';

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
