<?php
namespace qg;

if ($parts[1] === 'page') {
    $pid = $parts[2] ?? null;
    if ($pid) {
        $Page = Page($pid);
        $_3 = $parts[3] ?? null;
        if ($_3 === 'files') {
            $_4 = $parts[4] ?? null;
            if ($_4 === null) {
                $return = [];
                foreach ($Page->Files() as $name => $File) {
                    $return[] = [
                        'placeholder' => $name,
                        'name' => $File->name(),
                        'url' => (string)Url($File->url()),
                    ];
                }
                Answer($return);
                //echo json_encode($return,JSON_PRETTY_PRINT);
                exit;
            }
        }
    }
}
