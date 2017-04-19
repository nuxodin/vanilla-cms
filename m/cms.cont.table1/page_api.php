<?php
namespace qg;
extract($vars, EXTR_REFS | EXTR_SKIP);

if ($Cont->access() < 2) return false;

$cols = min((int)$Cont->SET['cols']->v, 15);
$rows = min((int)$Cont->SET['rows']->v, 300);
$cols = max($cols,1);
$rows = max($rows,1);

if ($do==='rowRem') {
    $row = $Cont->SET['direction']->v ? $rows-$row-1 : $row;
    for ($i=0; $i<$rows; $i++) {
        $r = $i;
        for ($j=0; $j<$cols; $j++) {
            if ($r > $row) {
                $T1 = $Cont->Text($r.'_'.$j);
                $T2 = $Cont->Text(($r-1).'_'.$j);
                foreach (L::all() as $l) {
                    $Cont->Text(($r-1).'_'.$j, $l, $T1->get($l));
                }
            }
        }
    }
    $Cont->SET['rows'] = $rows-1;
}
if ($do==='rowAddAfter') {
    ++$rows;
    $row = $Cont->SET['direction']->v ? $rows-$row-3 : $row;
    for ($i=1; $i<$rows; $i++) {
        $r = $rows-1-$i;
        for ($j=0; $j<$cols; $j++) {
            if ($r > $row) {
                $T1 = $Cont->Text($r.'_'.$j);
                $T2 = $Cont->Text(($r+1).'_'.$j);
                foreach (L::all() as $l) {
                    $Cont->Text(($r+1).'_'.$j, $l, $T1->get($l));
                    $Cont->Text($r.'_'.$j, $l, '');
                }
            }
        }
    }
    $Cont->SET['rows'] = $rows;
}
if ($do==='colRem') {
    for ($i=0; $i<$rows; $i++) {
        $r = $rows-1-$i;
        for ($j=$cols; $j; $j--) {
            $c = $cols-$j;
            if ($c > $col) {
                $T1 = $Cont->Text($r.'_'.$c);
                $T2 = $Cont->Text($r.'_'.($c-1));
                foreach (L::all() as $l) {
                    $Cont->Text($r.'_'.($c-1), $l, $T1->get($l));
                }
            }
        }
    }
    $Cont->SET['cols'] = $cols-1;
}
if ($do==='colAddRight') {
    for ($i=0; $i<$rows; $i++) {
        $r = $rows-1-$i;
        for ($c=$cols-1; $c; $c--) {
            if ($c > $col) {
                //$T1 = $Cont->Text($r.'_'.$c);
                //$T2 = $Cont->Text($r.'_'.($c+1));
                foreach (L::all() as $lang) {
					$value = $Cont->Text($r.'_'.$c, $lang);
                    $Cont->Text($r.'_'.($c+1), $lang, $value);
                    $Cont->Text($r.'_'.$c,     $lang, '');
                }
            }
        }
    }
    $Cont->SET['cols'] = $cols + 1;
}
self::reload($Cont);
