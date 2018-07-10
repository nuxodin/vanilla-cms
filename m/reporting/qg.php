<?php
namespace qg;

qg::on('action',function(){
    $SET = G()->SET['reporting'];
    $timeWaiting = 60*60*12;
    if (isset($_GET['reporting_request'])) $timeWaiting = 60*2;
    if ($SET['time']->v < time() - $timeWaiting) {
        //qg::on('background',function() use($SET) { // can not always save "time" in background...?!?!
            reporting_send();
            $SET['time'] = time();
        //});
    }
});

function reporting_send() {
    global $debug;

    $SET = G()->SET['reporting'];
    if (!$SET['id']->v) $SET['id'] = randString();

    $report = [
        'id'          => $SET['id']->v,
        'domain'      => $_SERVER['HTTP_HOST'],
        'app_url'     => appURL,
        'ip'          => $_SERVER['SERVER_ADDR'], // $_SERVER['LOCAL_ADDR'] on IIS ?
        'debugmode'   => $debug ?? 0,
        'client_time' => time(),
    ];
    // module data
    $sql =
    " SELECT module.*, count(page.module) as num ".
    " FROM ".
    "   module ".
    "   LEFT JOIN page on page.module = module.name ".
    //" WHERE local_version ".
    " GROUP BY module.name ";
    foreach (D()->query($sql) as $row) {
        $report['modules'][$row['name']] = [
            'version' => module::index()[$row['name']]['version'] ?? '?', // "?" if module is no longer installed
            'num_as_cont' => $row['num'],
        ];
    }
    // errors

    $report['error'] = [];
    if (D()->m_error_report) {
        $sysPATH  = str_replace('\\','/',sysPATH);
        foreach (D()->query("SELECT * FROM m_error_report WHERE time > '".date('Y-m-d H:i:s',(int)$SET['time']->v)."'") as $row) {
            $filePATH = str_replace('\\','/',$row['file']);
            if (strpos($filePATH, $sysPATH) === 0) {
                $path = substr($filePATH, strlen($sysPATH));
                list($module, $path) = explode('/',$path,2);
				$row['module']         = $module;
				$row['module_version'] = module::index()[$module]['version'] ?? '';
				$row['module_path']    = $path;
            }
            $file = $filePATH;
            $row['file'] = $file;
            $report['error'][] = $row;
        }
    }

    // send
    $url = 'https://report.shwups-cms.ch';
    $data = ['data' => json_encode($report)];
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'timeout' => 3,
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ];

    $context  = stream_context_create($options);
    time_limit(15);
    $result   = file_get_contents($url, false, $context);
    if ($result === FALSE) { /* Handle error */ }
}
