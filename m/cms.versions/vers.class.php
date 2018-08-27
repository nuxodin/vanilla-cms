<?php
namespace qg;

class vers {
	static $db = [];
	static $space = 0;
	static $log = 0;
	static $tableEntriesCopying = false; // flag to warn modification inside tableEntriesCopyTo()

	static function setLog($log=0) {
		$old = self::$log;
		$log = (int)$log;
		if ($log) cache::$enabled = false;
		self::$log = $log;
		return $old;
	}
	static function setSpace($space) {
		$old = self::$space;
		$space = (int)$space;
		if ($space) cache::$enabled = false;
		self::$space = $space;
		self::ensureSpace($space);
		return $old;
	}
	static function setVers($space, $log=null) {
		if (is_array($space)) list($space, $log) = $space;
		return [
			self::setSpace($space),
			self::setLog($log),
		];
	}
	static function ensureSpace($space) {
		static $data;
		if (isset($data[$space])) return;
		$data[$space] = D()->row("SELECT * FROM vers_space WHERE space = ".$space);
		if ($data[$space]) return;
		$data[$space] = ['space'=>$space, 'time_created'=>time()];
		foreach (self::$db as $table => $fields) {
			$versTable = self::versTable($table);

			// delete all data in that space (active- and history-data);
			D()->query("DELETE FROM ".$versTable." WHERE _vers_space = ".$space);

			// insert initial space version
			if ($space) {
				D()->query("INSERT ".$versTable." SELECT *, 0 AS _vers_log, ".$space." AS _vers_space, 0 AS _vers_deleted FROM ".$table);
				qg::fire('vers::createSpace',['space'=>$space]);
			}
			// insert initial histery
			D()->query("INSERT ".$versTable." SELECT *, ".liveLog::$id." AS _vers_log, ".$space." AS _vers_space, 0 AS _vers_deleted FROM ".$table); // new, not "1" as log_id!
		}
		D()->vers_space->insert($data[$space]);
	}
	static function view($table, $space=0, $log=0) {
		$table = (string)$table;
		if (!isset(self::$db[$table])) return $table;
		if ($log === 0 && $space === 0) {
			return $table;
		} else {
			$versTable = self::versTable($table);
			$view = '_vers_'.$log.'_space_'.$space.'_'.$table;
			if (!D()->$view) {
				foreach (D()->$table->Fields() as $field => $Field) {
					if (self::$db[$table] === true || isset(self::$db[$table][$field])) {

						if (isset(self::$db[$table][$field]) && is_string(self::$db[$table][$field])) {
							$sqlFields[] = self::$db[$table][$field]; // beta zzz?
						} else {
							$sqlFields[] = 'm.'.$field;
						}

					} else {
						$sqlFields[] = 'original.'.$field;
					}
					if ($Field->isPrimary()) {
						$joins_mm[]       = 'mm.'.$field.' = m.'.$field;
						$joins_original[] = 'm.'.$field.' = original.'.$field;
					}
				}
				if ($log) {
					// http://stackoverflow.com/questions/15310219/mysql-get-record-with-lowest-value-views-select-contains-a-subquery-in-the-fr
					$sql =
					"CREATE VIEW ".$view." \n".
					"AS \n".
					" SELECT \n".
					"    ".implode(",\n    ",$sqlFields)." \n".
					" FROM \n".
					"   ".$versTable." m \n".
					"   LEFT JOIN ".$table." original ON ".implode(" AND ",$joins_original)." \n".
					" WHERE 1 \n".
					"    AND !m._vers_deleted \n".
					"    AND m._vers_space = ".$space." \n".
					"    AND m._vers_log BETWEEN 1 AND ".$log."-1 \n".
					"    AND NOT EXISTS ( \n".
					"       SELECT 1 \n".
					"       FROM ".$versTable." mm \n".
					"       WHERE 1 \n".
					"          AND mm._vers_space = ".$space." \n".
					"          AND mm._vers_log BETWEEN 1 AND ".$log."-1 \n".
					"          AND mm._vers_log > m._vers_log \n".
					"          AND ".implode(" AND ",$joins_mm)." \n".
					"       LIMIT 1 \n".
					"   ) \n".
					"";
					register_shutdown_function(function() use($view) {
						//D()->query("DROP VIEW IF EXISTS ".$view); // IF EXISTS => can be that the next request has dropped the view already??
						D()->query("DROP VIEW ".$view);
						D()->removeTable($view); // for the cache
					});
				} else {
					$sql =
					" CREATE VIEW ".$view." \n".
					" AS \n".
					" SELECT \n".
					//"    * \n".
					"    ".implode(",\n    ",$sqlFields)." \n".
					" FROM \n".
					"     ".$versTable." m \n".
					"     LEFT JOIN ".$table." original ON ".implode(" AND ",$joins_original)." \n".
					" WHERE m._vers_space = ".$space." AND m._vers_log = 0 \n";
				}
				D()->query("DROP VIEW IF EXISTS ".$view); // can be that the previews request has not dropped the view yet...
				D()->query($sql);
				D()->addTable($view); // for the cache
			}
			return $view;
		}
	}
	static function versTable($table) {
		$table = (string)$table;
		if (!isset(self::$db[$table])) return false;
		$versTable = '_vers_'.$table;
		if (!D()->$versTable) {
			D()->query("CREATE TABLE ".$versTable." LIKE ".$table);
			D()->addTable($versTable); // for the cache
			$T = D()->$versTable;
			$F = $T->addField('_vers_log');     $F->setType('int');     $F->setSpecial('UNSIGNED'); $F->setKey('PRI');
			$F = $T->addField('_vers_space');   $F->setType('int');     $F->setSpecial('UNSIGNED'); $F->setKey('PRI');
			$F = $T->addField('_vers_deleted'); $F->setType('tinyint'); $F->setSpecial('UNSIGNED'); $F->setKey('MUL');
		}
		return D()->$versTable;
	}
	static function tableEntriesCopyTo($table, $filter, $fromSpace, $fromLog, $toSpace) {
		$Table = D()->$table;
		$where = $Table->valuesToWhere($filter);
		$oldEntries = $newEntries = [];

		foreach (D()->all("SELECT * FROM ".self::view($table, $toSpace,   0       )." WHERE ".$where) as $entry) $oldEntries[$Table->entryId($entry)] = $entry;
		foreach (D()->all("SELECT * FROM ".self::view($table, $fromSpace, $fromLog)." WHERE ".$where) as $entry) $newEntries[$Table->entryId($entry)] = $entry;

		$oldVers = self::setVers($toSpace, 0);
		vers::$tableEntriesCopying = true;
		//foreach ($newEntries as $id => $entry) isset($oldEntries[$id])  ?  $Table->update($entry) : $Table->insert($entry); // ensure ok?
		foreach ($newEntries as $id => $entry) isset($oldEntries[$id])  ?  $Table->update($entry) : $Table->ensure($entry);
		foreach ($oldEntries as $id => $entry) !isset($newEntries[$id]) && $Table->delete($entry);
		vers::$tableEntriesCopying = false;
		self::setVers($oldVers);
	}
	static function qgSettingsCopyTo($basis, $offset, $fromSpace, $fromLog, $toSpace) {
		self::tableEntriesCopyTo('qg_setting', ['basis' => $basis, 'offset'=>$offset], $fromSpace, $fromLog, $toSpace); // deletes double?
		$id = D()->one("SELECT id FROM ".self::view('qg_setting', $toSpace)." WHERE basis = ".$basis." AND offset = ".D()->quote($offset));
		self::tableEntriesCopyTo('qg_setting', ['basis' => $id], $fromSpace, $fromLog, $toSpace); // delete deleted
		if (!$id) return;
		foreach (D()->all("SELECT basis, offset FROM ".self::view('qg_setting', $fromSpace, $fromLog)." WHERE basis = ".$id) as $row) { // recursiv
			self::qgSettingsCopyTo($row['basis'], $row['offset'], $fromSpace, $fromLog, $toSpace);
		}
	}
}
