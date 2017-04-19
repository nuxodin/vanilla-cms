<?php
namespace qg;

//if (isset($_GET['qgCmsNoFrontend'])) return; zzz
G()->csp['img-src']['blob:'] = true;
G()->csp['default-src']["'self'"] = true; /* as of firefox 50, svgs loaded via "use" are blocked */
$Cont = Page();
$cmsSET = G()->SET['cms'];
$cmsSET->make('last_backend_page',$cmsSET['backend']->v)->custom();
$cmsSET->make('last_frontend_page',2)->custom();
$edit = $cmsSET['editmode']->v;
G()->js_data['Page'] = $Cont->id;
G()->js_data['cmsToggleEditUrl']  = (string)Url()->addParam('qgCms_editmode', $cmsSET['editmode']->v ? 0 : 1, false)->addParam('cmspid', cms::$RequestedPage->id, false);
if (Usr()->superuser) G()->js_data['cmsToggleDebugUrl'] = (string)Url()->addParam('debugmode',debug?0:1,false);
$inBackend = $Cont->in($cmsSET['backend']->v);
$cmsSET[$inBackend?'last_backend_page':'last_frontend_page']->setUser($Cont);
$BPage = Page($cmsSET[$inBackend?'last_frontend_page':'last_backend_page']->v);
if ($BPage->access()) G()->js_data['cmsBackendUrl'] = (string)Url($BPage->url());
G()->js_data['qgCmsEditmode'] = (int)$edit;
if (!$edit) return;
G()->js_data['cmsClipboard'] = (int)$cmsSET['clipboard']->v;
L::nsStart('cms');
include sysPATH.'cms.frontend.1/view/panel.php';
L::nsStop();
