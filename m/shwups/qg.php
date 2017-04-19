<?php
namespace qg;

qg::need('cms.installation.default');

// Notify Update
qg::on('deliverHtml',function(){
    //if (!debug) return;
    if (!Usr()->superuser) return;
    $usr_data = (array)json_decode(G()->SET['shwups']['update_notified']->custom()->v, true);
	$usr_data['lasttime'] = $usr_data['lasttime'] ?? 0;
    if (G()->SET['qg']['module_changed']->v > $usr_data['lasttime']) {
        html::$content .= '<script>alert("Das System wurde aktualisiert. Hilf das System zu verbessern, indem du allfällige Fehler meldest. (CMS-Panel > Weiteres > Feedback)")</script>';
        $usr_data['lasttime'] = time();
        G()->SET['shwups']['update_notified']->setUser(json_encode($usr_data));
    }
    // time_limit(100);
    // foreach (module::all() as $Module) {
    //     if ($Module->server !== qg::Store()->host) continue;
    //     // $Module->local_time; // check also?
    //     if ($Module->local_updated > @$usr_data['lasttime']) {
    //         $version = @$usr_data['module'][$Module->name]['version'];
    //         $changelogs = qg::Store()->changelog($Module->name);
    //         foreach ($changelogs as $changelog) {
    //             if ($changelog['time'] > @$usr_data['lasttime']) {
    //                 $messages[] = $Module->name . ': ' . $changelog['notes'];
    //             }
    //         }
    //     }
    // }
});
