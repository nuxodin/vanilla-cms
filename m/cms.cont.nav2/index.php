<?php namespace qg; ?>
<nav>
<?php

$Cont->SET->make('filter_visible', 'visible');

$Page = $Cont->SET['startPage']->v  ? Page($Cont->SET['startPage']->v)            : $Cont->Page;
$Page = $Cont->SET['startLevel']->v ? Page()->Parent($Cont->SET['startLevel']->v) : $Page;

$level = 0;

$getUl = function ($Page) use ($Cont, &$level, &$getUl) {
	if (!$Page || !$Page->is()) return '';

	$Children = [];
	foreach ($Page->Children('readable') as $C) {
		switch ($Cont->SET['filter_visible']->v) {
		case 'visible': if (!$C->vs['visible']) continue 2; break;
		case 'hidden':  if ($C->vs['visible'])  continue 2; break;
		}
		$Children[] = $C;
	}

	if ($Cont->SET['include contents']->v) {
		$Contents = [];
		foreach ($Page->Conts() as $FirstLevelCont) {
			foreach ($FirstLevelCont->Bough(['readable','type'=>'c']) as $Content) {
				$Contents[] = $Content;
			}
		}
		foreach ($Contents as $Content) {
			if (!$Content->vs['visible']) continue;
			$Children[] = $Content;
		}
	}
	// filter
	foreach ($Children as $key => $C) {
		if (!trim($C->Title())) unset($Children[$key]);
	}

	if (!$Children) return false;
	if ($Cont->SET['level']->v    && (int)$Cont->SET['level']->v <= (int)$level) return '';
	if ($Cont->SET['pathOnly']->v && ($level && !Page()->in($Page))) return '';

	$level++;
	$str = '<ul class=cmsChilds'.$Page.'>';
	foreach ($Children as $Page) {
		$childStr = $getUl($Page);
		$class = ' class="cmsLink'.$Page->Page.
				(!$Page->access()        ?' noAccess':''). // zzz, not possible cause filter is "readable"
				(Page()->in($Page->Page) ?' cmsInside':'').
				(Page()===$Page->Page    ?' cmsActive':''). // aria-current="page" ?
				($childStr !== false     ?' cmsHasSub':'').
				'"';
		$str .= '<li '.$class.'>' . cms_link($Page) . $childStr;
	}
	$str .= '</ul>';
	$level--;
	return $str;
};

echo $getUl($Page);
?>
</nav>
