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
  	$urls = $Cont->urls();
	$t = $urls[L()]['target'];
	$target = $t ? ' target="'.$t.'"' : '';
	return '<a'.cms_link_attributes($Cont).$target.'>'.$Cont->Title().'</a>';
}
function cms_link_attributes($Cont) {
	$Cont = Page($Cont);
	$Cont->urlSeo(L()); // needed? zzz
	$href = ' href="'.$Cont->url().'"';
	$class = ' class="cmsLink'.$Cont.' '.($Cont->access()?'':'noAccess') // todo: noAccess used?
			.(Page()->in($Cont)?' cmsInside':'')
			.(Page()===$Cont?' cmsActive':'').'"'; // aria-current="page" ?
	$cmstxt = $Cont->edit?' cmstxt='.$Cont->Title()->id:'';
	return $href.$class.$cmstxt;//.$target;
}
function cms_url($pid_or_url, &$return=[]){
	$pid_or_url = trim($pid_or_url);
	$return['target'] = '_blank';
	if (is_numeric($pid_or_url)) {
		$Page = Page($pid_or_url);
		if ($Page->is()) {
			$return['target'] = '_self';
			return $Page->url();
		} else {
			return false;
		}
	}
	if ($pid_or_url === '') return false;
	if (!preg_match('/^[a-z]+:/',$pid_or_url)) { // no protocol
		return 'http://'.$pid_or_url;
	}
	return $pid_or_url;
}
function cms_text($pid, $name, $options=[]){
	$Cont = Page($pid);
	$T = $name==='title' ? $Cont->Title() : $Cont->Text($name);
	$tag = $options['tag']??'div';
	if ($Cont->edit) {
		if (!isset($options['contenteditable'])) $options['contenteditable'] = true;
		$options['cmstxt'] = $T->id;
	}
	$text = (string)$T;
	if ($text === '' && isset($options['initial'])) {
		if (is_array($options['initial'])) {
			foreach (L::$all as $l) {
				$LT = $T->get($l);
				$LT->get() === '' && $LT->set($options['initial'][$l]??'');
			}
			$text = (string)$T;
		} else {
			$text = $options['initial'];
			$T->set($text);
		}
	}
	if (($options['if']??0) && !$Cont->edit && !trim(strip_tags($text))) {
		return '';
	}
	unset($options['if']);
	unset($options['tag']);
	unset($options['initial']);
	$attrStr = '';
	foreach ($options as $n => $v) {
		if ($v===false) continue;
		$attrStr .= $v === true ? ' '.$n : ' '.$n.'="'.hee($v).'"';
	}
	return '<'.$tag.$attrStr.'>'.$text.'</'.$tag.'>';
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
