<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

$trigger_tables = ['page_file'=>1, 'page_class'=>1, 'page_text'=>1, 'page_url'=>1, 'page'=>1];
$trigger_modify_before_table = function($e) use(&$trigger_tables){
	if (!isset($trigger_tables[$e['Table']->_name])) return;
    extract($e, EXTR_REFS);   // Table, id, data
    $event = [
        'table'     => $Table->_name,
        'data'      => &$data,
        'operation' => substr($event_type,9,-7), // insert => 'insert'
    ];
    if ($Table->_name !== 'page') {
        $event['Page'] = cms::Page($data['page_id']);
    } else if (isset($id)) {
        $event['Page'] = cms::Page($id);
    }
	qg::fire('page::modify-before',$event);
};
$trigger_modify_after_table = function($e) use(&$trigger_tables){
	if (!isset($trigger_tables[$e['Table']->_name])) return;
    extract($e, EXTR_REFS);   // Table, id, data
    $event = [
        'table'     => $Table->_name,
        'data'      => &$data,
        'operation' => substr($event_type,9,-7),
    ];
    if ($Table->_name !== 'page') {
        $event['Page'] = cms::Page($data['page_id']);
    } else {
        $event['Page'] = cms::Page($id);
    }
	qg::fire('page::modify-after',$event);
};
qg::on('dbTable::insert-before', $trigger_modify_before_table);
qg::on('dbTable::update-before', $trigger_modify_before_table);
qg::on('dbTable::delete-before', $trigger_modify_before_table);
qg::on('dbTable::insert-after',  $trigger_modify_after_table);
qg::on('dbTable::update-after',  $trigger_modify_after_table);
qg::on('dbTable::delete-after',  $trigger_modify_after_table);

$trigger_modify_before = function($e){
	$e['original_event'] = $e['event_type'];
	$e['table'] = 'text';
    qg::fire('page::modify-before', $e);
};
$trigger_modify_after  = function($e){
	$e['original_event'] = $e['event_type'];
	$e['table'] = 'text';
    qg::fire('page::modify-after', $e);
};
qg::on('page::title_set-before',     $trigger_modify_before);
qg::on('page::text_generate-before', $trigger_modify_before);
qg::on('page::text_set-before',      $trigger_modify_before);
qg::on('page::title_set-after',      $trigger_modify_after);
qg::on('page::text_generate-after',  $trigger_modify_after);
qg::on('page::text_set-after',       $trigger_modify_after);
