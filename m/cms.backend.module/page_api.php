<?php
namespace qg;
extract($vars, EXTR_REFS | EXTR_SKIP);

if (!Usr()->superuser) return false;

if ($init??0) {
    qg::install($init);
    G()->Answer['cmsInfo'] = 'Das Module wurde neu initialisiert';
}
if ($update??0) {
    $ok = qg::Store()->install($update);
    module::syncLocal(); // write to db // needed?
    G()->Answer['cmsInfo'] = $ok ? L('Das Module wurde aktualisiert') : L('Fehlgeschlagen');
    return !!$ok;
}
if ($upload??0) {
    $vs = qg::Store()->release($upload, $incVersion, $notes);
    G()->Answer['cmsInfo'] = $vs ? L('Das Module wurde exportiert') : L('Fehlgeschlagen');
    return $vs ? $vs['version'] : false;
}
if ($uninstall??0) {
    rrmdir(sysPATH.$uninstall); // security!?
    D()->query("DELETE FROM module WHERE name = ".D()->quote($uninstall));
    qg::setInstalled($uninstall, false);
    return 1;
}
if ($remoteDelete??0) {
    return qg::Store()->serverDelete($remoteDelete);
}
if (isset($title)) {
    D()->module->Entry($module)->Title()->set($title);
}
if (isset($access)) {
    D()->module->Entry($module)->access = (int)$access;
}
if ($updateAll??0) {
    $ok = qg::store()->autoUpdate();
	return $ok;
}
return false;
