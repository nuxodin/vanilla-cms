<?php
namespace qg;

$Cont->SET['history'];
$Cont->SET->make('saveLogin',1)->setType('bool');
$Cont->SET['redirect']->setHandler('qgcms-page');
$Cont->SET['logout_redirect']->setHandler('qgcms-page');
$Cont->SET['no autofocus']->setType('bool');
