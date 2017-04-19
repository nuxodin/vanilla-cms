<?php
namespace qg;

qg::on('action', function() { // check if table are created
	foreach (vers::$db as $table => $fields) vers::versTable($table);
});
qg::on('table', function($e){ // overload table function
	if (vers::$log || vers::$space) $e['table'] = vers::view($e['table'], vers::$space, vers::$log);
});

//////////////////////////////////////////////////////////
// catch db manipulations: insert history
$catch_update_insert = function($e){
	if (!liveLog::$id) return;
	extract($e, EXTR_REFS);   // Table, id, data
	$VersTable = substr($Table->_name,0,6) === '_vers_' && !$data['_vers_log']
		? $Table
		: $VersTable = vers::versTable($Table);
	if ($VersTable) {
		$Fields = $VersTable->Fields(); // fields hafe to be the right order!
		$Fields['_vers_space'] = vers::$space;
		$Fields['_vers_log'] = liveLog::$id;
		$Fields['_vers_deleted'] = 0;
		foreach($Fields as $field => $F) $selects[] = $F." AS ".$field;
		D()->query("REPLACE INTO ".$VersTable." SELECT ".implode(', ', $selects)." FROM ".$Table->_name." WHERE ".$Table->entryId2where($id));
	}
};
qg::on('dbTable::update-after', $catch_update_insert);
qg::on('dbTable::insert-after', $catch_update_insert);
qg::on('dbTable::delete-after', function($e){
	if (!liveLog::$id) return;
	extract($e, EXTR_REFS);   // Table, id, data
	$VersTable = substr($Table->_name,0,6) === '_vers_' && !$data['_vers_log']
		? $Table
		: $VersTable = vers::versTable($Table);
	if ($VersTable) {
		$data = ['_vers_log'=>liveLog::$id, '_vers_space'=>vers::$space, '_vers_deleted'=>1] + $Table->entryId2Array($id);
		D()->query("REPLACE INTO ".$VersTable." SET ".$VersTable->valuesToSet($data));
	}
});


//////////////////////////////////////////////////
// hooks vor spaces other then live (0)
qg::on('dbTable::update-before', function($e){
	if (!vers::$space) return;
	extract($e, EXTR_REFS);   // Table, id, data
	$table = $Table->_name;
	if (!isset(vers::$db[$table])) return;
	$data += $Table->entryId2Array($id) + ['_vers_log'=>0,'_vers_space'=>vers::$space];

	//vers::versTable($Table)->update($data);

	// split in space- / original-data;
	if (is_array(vers::$db[$table])) {
		$dataOriginal = [];
		foreach ($Table->Fields() as $field => $Field) {
			if (!array_key_exists($field, $data)) continue;
			if (isset(vers::$db[$table][$field])) continue;
			$dataOriginal[$field] = $data[$field];
//			unset($data[$field]);
		}
		if ($dataOriginal) {
			D()->query("UPDATE ".$Table." SET ".$Table->valuesToSet($dataOriginal)." WHERE ".$Table->entryId2where($id));
		}
	}

	vers::versTable($Table)->update($data);

	$return = $id;
});
qg::on('dbTable::insert-before', function($e){
    if (!vers::$space) return;
	extract($e, EXTR_REFS);   // Table, id, data
	if (!isset(vers::$db[$Table->_name])) return;
	$data += ['_vers_log'=>0, '_vers_space'=>vers::$space];
	$id = vers::versTable($Table)->insert($data);
	$ids = $Table->entryId2Array($id);
	$return = $Table->entryId($ids);
	// korrekt auto-increment
	if ($AutoField = $Table->getAutoIncrement()) {
		$value = $ids[(string)$AutoField];
		if (!$value) return; // insert was canceled!?
		D()->query("ALTER TABLE ".$Table." AUTO_INCREMENT=".($value+1));
	}
});
qg::on('dbTable::delete-before', function($e){
	if (!vers::$space) return;
	extract($e, EXTR_REFS);   // Table, id, data
	if (!isset(vers::$db[$Table->_name])) return;
	$data += ['_vers_log'=>0, '_vers_space'=>vers::$space];
	$return = vers::versTable($Table)->delete($data);
});


//////////////////////////////////////////////////
// auto_increment from vers-table > original-table
qg::on('dbTable::insert-after', function($e){
	if (vers::$space) return;
	extract($e, EXTR_REFS);   // Table, id, data
	if (substr($Table->_name,0,6) !== '_vers_') return;
	$originalTable = substr($Table->_name,6);
	if ($AutoField = $Table->getAutoIncrement()) {
		$ids = $Table->entryId2Array($id);
		$value = $ids[(string)$AutoField];
		if (!$value) return; // insert was canceled!?
		D()->query("ALTER TABLE ".$originalTable." AUTO_INCREMENT=".($value+1));
	}
});
