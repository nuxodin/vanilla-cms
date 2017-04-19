<?php
namespace qg;

$SET = G()->SET['cms.frontend.1'];

$SET->make('show classes',1)->setType('bool');
$SET->make('show urls',1)->setType('bool');
$SET->make('show access.time',1)->setType('bool');

$SET['custom']['widget']['access']->custom();
$SET['custom']['widget']['access.grp']->custom();
$SET['custom']['widget']['access.time']->custom();
$SET['custom']['widget']['access.usr']->custom();
$SET['custom']['widget']['classes']->custom();
$SET['custom']['widget']['cont']->custom();
$SET['custom']['widget']['divers']->custom();
$SET['custom']['widget']['extended']->custom();
$SET['custom']['widget']['preview']->custom();
$SET['custom']['widget']['seo']->custom();
$SET['custom']['widget']['sets']->custom();
$SET['custom']['widget']['txts']->custom();
$SET['custom']['widget']['urls']->custom();
$SET['custom']['widget']['options']->custom();
$SET['custom']['widget']['media']->custom();
$SET['custom']['widget']['superuser']->custom();

$SET['custom']['sidebar']->custom();
$SET['custom']['tree_show_c']->custom();
$SET['custom']['crowd out']->custom();
