<?php
namespace qg;

$search = $vars['search'] ?? ($_GET['CmsPage'.$Cont] ?? '');
$search = trim($search);

$res['search'] = $search;
$res['res'] = [];

if (!$search) return $res;

$wheres[] = "1";
if ($Cont->SET['module']->v) {
	$wheres[] = "p.module LIKE '".$Cont->SET['module']->v."'";
}

$words = preg_split('/\P{L}+/u', $search);
$mysqlSearch = '';
foreach ($words as $word) {
	$mysqlSearch .= ' +'.$word;
}
$sqlAgainst = "AGAINST (".D()->quote(trim($mysqlSearch))." IN BOOLEAN MODE)";

// title
$sqls[] =
" SELECT 																" .
" 	'title' as type														" .
" 	,MATCH(t.text) ".$sqlAgainst." AS relevance							" .
"	,p.id AS id															" .
"	,t.text as text														" .
" FROM 																	" .
"	page p		  														" .
" 	join text t ON t.id = p.title_id AND t.lang = '".L()."'             " .
" WHERE ".implode(' AND ', $wheres)."									" .
"	AND ( 																" .
"		MATCH(t.text) ".$sqlAgainst."									" .
"		OR t.text LIKE ".D()->quote('%'.$search.'%')."					" .
"	)																	" .
" GROUP BY p.id														    " .
" ORDER BY relevance													" .
" LIMIT 100																";

// texts
$sqls[] =
" SELECT 																" .
" 	'text' as type														" .
" 	,MATCH(t.text) ".$sqlAgainst." AS relevance							" .
"	,p.id AS id															" .
"	,t.text 															" .
"	,pt.name															" .
" FROM 																	" .
"	page p		  														" .
"	join page_text pt ON pt.page_id = p.id								" .
" 	join text t ON t.id = pt.text_id AND t.lang = '".L()."'             " .
" WHERE ".implode(' AND ', $wheres)."   								" .
"	AND ( 																" .
"		MATCH(t.text) ".$sqlAgainst." 									" .
"		OR t.text LIKE ".D()->quote('%'.$search.'%')."					" .
"	)																	" .
" GROUP BY p.id															" .
" ORDER BY relevance													" .
" LIMIT 200															    ";

/*
if ($Cont->SET['files']->setType('bool')->v) {
	// files
	$sqls[] =
	" SELECT 																" .
	" 	'file' as type														" .
	" 	,MATCH(f.text) ".$sqlAgainst." AS relevance		" .
	"	,p.id AS id															" .
	"	,f.name 															" .
	"	,f.text 															" .
	"	,f.id as file_id 													" .
	" FROM 																	" .
	"	page p		  														" .
	"	join page_file pf ON pf.page_id = p.id								" .
	" 	join file f ON f.id = pf.file_id                                    " .
	" WHERE 1																" .
	"	AND ( 																" .
	"		f.name LIKE ".D()->quote($search.'%')."							" .
	"		OR MATCH(f.text) ".$sqlAgainst."				" .
	"		OR f.text LIKE ".D()->quote($search.'%')."						" .
	"	)																	" .
	" GROUP BY p.id														    " .
	" ORDER BY relevance													" .
	" LIMIT 100																";
}
*/

//$start = microtime(true);

$groupBy = $Cont->SET->make('group by','pages')->setHandler('select')->setOptions('contents','pages')->v;
$startPage = $Cont->SET['startPage']->setHandler('qgcms-page')->v;


$enableOnlinStart = $Cont->SET['online_start in relevance']->setType('bool')->v;
$now = time();
$oldIf = 60 * 60 * 24 * 356 * 4;

$getClosestVisibleContent = function($Cont){
    while (1) {
        if ($Cont->vs['visible'] || $Cont->vs['type'] === 'p') return $Cont;
        $Next = $Cont->Parent();
        if (!$Next || !$Next->is()) return $Cont;
        $Cont = $Next;
    }
};

foreach ($sqls as $sql) {
	foreach (D()->query($sql) as $vs) {
		$P = Page($vs['id']);

		if (!$P->Page->vs['searchable']      ) continue;
		if (!$P->isReadable()                ) continue;
		if ($startPage && !$P->in($startPage)) continue;

        $group = $groupBy === 'contents' ? $getClosestVisibleContent($P)->id : $P->Page->id;

		if (!isset($res['res'][$group]['text']))      $res['res'][$group]['text']      = '';
		if (!isset($res['res'][$group]['relevance'])) $res['res'][$group]['relevance'] = 0;

		$res['res'][$group]['relevance'] += $vs['relevance'];

		switch ($vs['type']) {
			case 'file':
				$res['res'][$group]['files'][] = $vs;
				$res['res'][$group]['text'] .= ' '.$vs['text'];
				break;
			case 'text':
				$res['res'][$group]['texts'][] = $vs;
				if (strpos($vs['name'], '_') !== 0) {
					$res['res'][$group]['text'] .= ' '.$vs['text'];
				}
				$text = strtolower(strip_tags($vs['text']));

				$num = 0;
				foreach ($words as $word) {
					$num += substr_count($text, $word);
				}

				//$num = substr_count($text, $search);
				$num = min($num,7);
				$res['res'][$group]['relevance'] += 0.2 * $num;
				break;
			case 'title':
				$res['res'][$group]['titles'][] = $vs;
				$res['res'][$group]['text'] .= '';
				$res['res'][$group]['relevance'] += 3;
				break;
		}
      	if ($enableOnlinStart && $P->Page->vs['online_start']) {
      		$timeScore = $oldIf / ($now - $P->Page->vs['online_start']);
          	$timeScore = $timeScore;
            $res['res'][$group]['relevance'] = $timeScore * 300;
      	}

    }
}

//echo microtime(true)-$start.' Sekunden benötigt';
uasort($res['res'], function($a, $b) {
	return ($a['relevance'] > $b['relevance']) ? -1 : 1;
});

return $res;
