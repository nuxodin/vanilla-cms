<?php
namespace qg;

function cutSearch1($result, $search, $limit = -1, $before=30, $after=10) {

	$searches = preg_split('/\s+/',$search);

	$pregQuoteSearches = [];
	foreach ($searches as $i => $s) {
		$s = strtolower($s);
		$pregQuoteSearches[] = preg_quote($s,'/');
		$searches[$i] = $s;
	}
	$result    = str_replace('&nbsp;',' ', $result);
	$result = util::stripTags($result);
	$result = preg_replace('/\s+/',' ',$result);

	$result = preg_split('/('.implode('|',$pregQuoteSearches).')/i', $result, $limit, PREG_SPLIT_DELIM_CAPTURE);

	$str = '';

	foreach ($result as $res) {
		if (array_search( strtolower($res),  $searches ) === false) {
		//if (strtolower($res) !== strtolower($search)) {
			if (mb_strlen($res) > $before+$after+3) {
				$str .= mb_substr($res,0,$after).'… '.mb_substr($res,-$before);
			} else {
				$str .= $res;
			}
		} else {
			$str .= '<span class=-mark>'.$res.'</span>';
		}
	}
	return $str;
}
