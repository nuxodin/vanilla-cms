<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

if (isset($vars['login_as'])) {
    $allowed = $Cont->SET['allow_login_as']->setType('bool')->v || Usr()->superuser;
    if (!$allowed) return false;
    $Usr = Usr($vars['login_as']);
    if ($Usr->superuser && !Usr()->superuser) return false;
    Auth::login($vars['login_as']);
    return 1;
}

if (isset($vars['delete'])) {
    $Usr = Usr($vars['delete']);
    if ($Usr->superuser && !Usr()->superuser) return false;
    D()->usr->delete($vars['delete']);
    return 1;
}

if (isset($vars['save'])) {
    $Usr = Usr($vars['save']);
    if ($Usr->superuser && !Usr()->superuser) return false;
    if (!$Usr->is()) return false;
    $name  = $vars['name'];
    $value = $vars['value'];
    $allowed = ['active'=>1,'email'=>1,'firstname'=>1,'lastname'=>1,'company'=>1,'superuser'=>1,'pw'=>1];
    if (!isset($allowed[$name])) return false;
    if ($name === 'pw') $value = Auth::pw_hash($value);
    if ($name === 'superuser' && !Usr()->superuser) return false;
    $data = [
        $name => $value,
        'log_id_ch' => liveLog::$id,
    ];
    $Usr->setVs($data);
    return 1;
}

if (isset($vars['set_grp'])) {
    $Usr = Usr($vars['set_grp']);
    $Grp = D()->grp->Entry($vars['grp_id']);
    if ($Usr->superuser && !Usr()->superuser) return false;
    if (!$Usr->is()) return false;
    if (!$Grp->is()) return false;
    if ($vars['add']) {
        D()->query("REPLACE INTO usr_grp  SET grp_id = '".$Grp."', usr_id = '".$Usr."'");
    } else {
        D()->query("DELETE FROM usr_grp WHERE grp_id = '".$Grp."' AND usr_id = '".$Usr."'");
    }
    return 1;
}

return false;
