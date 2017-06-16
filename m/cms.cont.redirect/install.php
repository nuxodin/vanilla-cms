<?php
namespace qg;

if (!D()->one("SELECT name FROM module WHERE name = 'cms.cont.redirect'")) {
	D()->query("INSERT INTO module SET access = '1', name = 'cms.cont.redirect'");
}