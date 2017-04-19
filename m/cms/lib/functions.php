<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

function Page($id = null, $vs = 0) {
	if ($id === null) return cms::$MainPage ?: cms::PageFromRequest();
	return cms::Page($id, $vs);
}
function cms_link($Cont) {
	$Cont = Page($Cont);
	$Cont->urlSeo(L());
	$href = ' href="'.$Cont->url().'"';
  	$urls = $Cont->urls();
	//$t = $urls[L()]['target'] ?: '';
	$t = $urls[L()]['target'];
	$target = $t ? ' target="'.$t.'"' : '';
	$class = ' class="cmsLink'.$Cont.' '.($Cont->access()?'':'noAccess') // todo: noAccess used?
			.(Page()->in($Cont)?' cmsInside':'')
			.(Page()===$Cont?' cmsActive':'').'"'; // aria-current="page" ?
	$eid = $Cont->edit?' cmstxt='.$Cont->Title()->id:'';
	return '<a'.$href.$class.$target.$eid.'>'.$Cont->Title().'</a>';
}
function cms_parentFile($name, $Cont = null) {
	if (!$Cont) $Cont = Page();
	while ($Cont) {
		$File = $Cont->FileHas($name);
		if ($File) break;
		$Cont = $Cont->Parent();
	}
	return $File;
}
function cms_parentSET($name, $Cont = null) {
	trigger_error('used?');
	if (!$Cont) $Cont = Page();
	while ($Cont) {
		if (G()->SET['cms']['pages']->has($Cont->id) && $Cont->SET->has($name)) {
		    return $Cont->SET[$name];
		}
		$Cont = $Cont->Parent();
	}
}
function cms_parentText($name, $Cont = null) {
	if (!$Cont) $Cont = Page();
	while ($Cont) {
		$Texts = $Cont->Texts();
		if (isset($Texts[$name])) return $Texts[$name];
		$Cont = $Cont->Parent();
	}
}
