<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class dbScheme {
	static function get() {
		$str = '<dbscheme>'."\n";
		foreach (D()->Tables() as $T) {
			$str .= "\t".'<table name="'.$T.'">'."\n";
				foreach ($T->Fields() as $F) {
					$str .= "\t\t".
					'<field ' .
						'name="'.$F.'" ' .
						'type="'.$F->getType().'" ' .
						($F->getLength()       ? 'length="'.$F->getLength().'" ' : '')  .
						($F->getSpecial()      ? 'special="'.$F->getSpecial().'" ' : '')  .
						($F->getDefault()      ? 'default="'.$F->getDefault().'" ' : '')  .
						($F->isAutoincrement() ? 'autoincrement="true" ':'') .
						($F->getNull()         ? 'null="'.$F->getNull().'" ' : '') .
						($F->getKey()          ? 'key="'.$F->getKey().'" ' : '')  .
						($F->Parent()          ? 'parent="'.$F->Parent().'" ' : '')  .
						($F->vs['on_parent_delete'] ? 'on_parent_delete="'.$F->vs['on_parent_delete'].'" ' : '')  .
						($F->vs['on_parent_copy']   ? 'on_parent_copy="'.$F->vs['on_parent_copy'].'" ' : '')  .
					'/>'."\n";
				}
			$str .= "\t".'</table>'."\n";
		}
		$str .= '</dbscheme>'."\n";
		return $str;
	}
	static function check($xml) {
		$dom = new \DomDocument;
		$dom->loadXML( $xml );
		self::checkTables($dom->firstChild);
	}
	private static function checkTables($schemeNode) {
		foreach ($schemeNode->childNodes as $table) {
			$table->nodeName === 'table' && self::checkTable($table);
		}
	}
	private static function checkTable($node) {
		$T = D()->{$node->getAttribute('name')};
		if (!$T) $T = D()->addTable($node->getAttribute('name'));
		if (!$T) { trigger_error('table "'.$node->getAttribute('name').'" can not be created!?'); return; } // zzz?
		self::checkFields($node);
	}
	private static function checkFields($tableNode) {
		$T = D()->{$tableNode->getAttribute('name')};
		foreach ($tableNode->childNodes as $node) {
			if ($node->nodeName !== 'field') continue;
			$data = [];
			foreach ($node->attributes as $name => $obj) $data[$name] = $obj->value;
			unset($data['autoincrement']);
			$F = $T->{$data['name']};
			if (!$F) $F = $T->addField($data);
			else $F->change($data);
			isset($data['key'])              && $F->setKey($data['key']);

			$node->hasAttribute('autoincrement') && $F->setAutoincrement($node->getAttribute('autoincrement'));

			isset($data['parent'])           && $F->setParent($data['parent']);
			isset($data['on_parent_delete']) && $F->setOnParentDelete($data['on_parent_delete']);
			isset($data['on_parent_copy'])   && $F->setOnParentCopy($data['on_parent_copy']);
		}
	}
}
