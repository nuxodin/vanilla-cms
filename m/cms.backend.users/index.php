<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

if (isset($_GET['id'])) {
	include 'detail.php';
} else {
	include 'overview.php';
}
