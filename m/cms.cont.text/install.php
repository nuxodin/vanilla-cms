<?php
namespace qg;

if (!D()->one("SELECT name FROM module WHERE name = 'cms.cont.text'")) {
	D()->query("INSERT INTO module SET access = '1', name = 'cms.cont.text'");
}
