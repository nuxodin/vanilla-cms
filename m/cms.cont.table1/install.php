<?php
namespace qg;

if (!D()->one("SELECT name FROM module WHERE name = 'cms.cont.table1'")) {
	D()->query("INSERT INTO module SET access = '1', name = 'cms.cont.table1'");
}
