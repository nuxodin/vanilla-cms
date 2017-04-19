<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

// like set_time_limit but cannot be lower than the value previously set
function time_limit($value=null) {
    $old = (int)ini_get('max_execution_time');
    if ($value !== null && $old < $value) {
        ini_set('max_execution_time', $value);
    } else {
        $value = $old;
    }
    return $value;
}

function number($v) {
  return str_replace(',','.',$v);
}

function form_input($conf) {
	$tag = 'input';
    $close = false;
	$content = '';
	switch ($conf['type']) {
		case 'textarea':
			$tag = 'textarea';
            $close = true;
			$content = hee($conf['value']);
			unset($conf['value']);
			unset($conf['type']);
			break;
		case 'select':
			$tag = 'select';
            $close = true;
			$content = '';
			$assoc = (bool)count(array_filter(array_keys($conf['options']), 'is_string'));
			foreach ($conf['options'] as $key => $option) {
				$key = $assoc ? $key : $option;
				$content .= '<option '.($assoc?'value="'.hee($key).'" ':'').($conf['value']===$key?'selected':'').'>'.hee($option).'</option>';
			}
			unset($conf['value']);
			unset($conf['type']);
			unset($conf['options']);
			break;
		case 'checkbox':
			$conf['checked'] = $conf['value'] ? true : false;
			unset($conf['value']);
			break;
	}
	qg::fire('form_input_get', $conf);

	$attris = [];
	foreach ($conf as $name => $value) {
		if ($value === false) {
		} elseif ($value === false) {
			$attris[] = $name;
		} else {
			if (preg_match('/^[0-9a-z_-]+$/i', $value)) { // todo: better regexp
				$attris[] = $name.'='.$value;
			} else {
				$attris[] = $name.'="'.hee($value).'"';
			}
		}
	}
    if ($content) $close = true;
	return '<'.$tag.' '.implode(' ',$attris).'>'.($content?:'').($close?'</'.$tag.'>':'');
}

// html entity encode
function hee($str) {
	//$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
	return htmlentities($str, ENT_QUOTES, 'UTF-8', false);
}

class template {
	function __construct($vs = []) {
		$this->___vars = (array)$vs;
	}
	function assign($n, $v) {
		$this->___vars[$n] =& $v;
	}
	function get($__file) {
		extract($this->___vars, EXTR_REFS);
		ob_start();
		include $__file;
		return ob_get_clean();
	}
	function renderMarker($txt) {
		$ps = explode('###', $txt);
		$t = false;
		$ret = '';
		foreach ($ps as $p) {
			$t = !$t;
			$ret .= ( $t ? $p : ( isset($this->___vars[$p])? $this->___vars[$p] : '###'.$p.'###') );
		}
		return $ret;
	}
}

//if available: fastcgi_finish_request() ???
$str = ob_get_clean();
ob_start();
echo $str;
register_shutdown_function(function(){

    !headers_sent() && qg::fire('output-before');

    $hasBgEvent = isset(qg::$events['background']) && qg::$events['background'];
    //$hasBgEvent = qg::$events['background'] ?: false; test
	if ($hasBgEvent) {
        if (!headers_sent()) {
            function_exists('apache_setenv') && apache_setenv('no-gzip', 1); // neu test existence
            ini_set('zlib.output_compression', 0);
            ini_set('implicit_flush', 1);
            header("Content-Length: ".ob_get_length());
    		header("Content-Encoding: none");
    		header("Connection: close");
        } else {
            trigger_error('can not run background-events in background, headers sent!');
        }
	}
    if (ob_get_length() > 0) ob_end_flush();
	if ($hasBgEvent) {
        flush();
		ignore_user_abort(true);
		//time_limit(60*10); // should be defined in the Event-Listener
		session_write_close();
		qg::fire('background');
	}
});

function Answer($data) {
	if (isset(G()->Answer))	$data += G()->Answer;
	header('Content-type: application/json');
	echo json_encode($data);
	exit();
}

class G {}
function G() {
	static $g;
	if (!$g) $g = new G();
	return $g;
}
function i() {
    return randString(10);
}
function D() {
	static $db;
	if (!$db) $db = new db('mysql:host='.qg_dbhost.';dbname='.qg_dbname, qg_dbuser, qg_dbpass);
	return $db;
}
function table($table) {
	qg::fire('table', ['table'=>&$table]);
	return $table;
}
function mail($to, $subject, $message, $additional_headers=null, $additional_parameters=null) {
	$toWebmaster = debug ? G()->SET['qg']['mail']['on debugmode to']->v : false;
	$message = ($toWebmaster ? 'original receiver :'.$to." <br><br>\n\n" : '').$message;
	$to      = $toWebmaster ?: $to;
	$subject = ($toWebmaster?'Debug! ':'').$subject;
	return \mail($to, $subject, $message, $additional_headers, $additional_parameters);
}

function byte_format($file_size) {
	$index = 0;
	$units = ['Bytes','KB','MB','GB','TB'];
	while ($file_size > 400) {
		$file_size /= 1024;
		$index++;
	}
    return round($file_size,1).' '.$units[$index];
}

function helper_optionsFromArray($array, $selected=[], $use_index=true) {
	$selected = (array)$selected;
	$str = '';
	foreach ($array as $i => $v) {
		$value = $use_index ? $i : $v;
		$select = in_array($value, $selected) ? 'selected' : '';
		$str .= '<option value="'.hee($value).'" '.$select.'>'.hee($v).'</option>';
	}
	return $str;
}

function sqlSearchHelper($search, $fields) {
	$searches = explode(' ', $search, 4);
	$wheres = [];
	foreach ($searches as $search) {
		$or = [];
		foreach ($fields as $f) {
			$orders[] = " ".$f." LIKE ".D()->quote($search.'%')." DESC ";
			$or[] = " ".$f." LIKE ".D()->quote('%'.$search.'%')." ";
		}
		$wheres[] = '('.implode(' OR ', $or).')';
	}
	$where = implode(' AND ', $wheres);
	$order = implode(',', $orders);
	return ['where'=>$where, 'order'=>$order];
}

function copyDir($src, $dest) {
	mkdir($dest);
	foreach (scandir($src) as $file) {
		$df = $dest.'/'.$file;
		$sf = $src.'/'.$file;
		if (!is_readable($sf)) continue;
		if (is_dir($sf) && ($file!='.') && ($file!='..')) {
			copyDir($sf, $df);
		} else {
			@copy($sf, $df);
		}
	}
}
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object)
			if ($object != "." && $object != "..")
				if (filetype($dir."/".$object) == "dir")
					rrmdir($dir."/".$object);
				else
					unlink($dir."/".$object);
				reset($objects);
				rmdir($dir);
	}
}

function br2nl($string) {
	return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function cutText($txt, $maxLength = 200, $minLength = 0, $ending = '…') {
	$txt = (string)$txt;
	if (strlen($txt) < $maxLength) return $txt;
	while (substr($txt,$maxLength-1,1) != ' ' && $maxLength > $minLength)
		$maxLength--;
	return substr($txt, 0, $maxLength).$ending;
}

function randString($max = 16, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789") {
	$str = '';
    $length = strlen($chars)-1;
	for ($i = 0; $i < $max; $i++) {
		$rand_key = mt_rand(0, $i === 0 ? $length-10 : $length);
        $str .= $chars[$rand_key];
	}
	return $str;
}

function urlize($str) {
	$str = strtr($str, [
			'þ' => 'th',
			'ð' => 'dh',
			'ß' => 'ss',
			'æ' => 'ae',
			'ä' => 'ae',
			'Ä' => 'Ae',
			'ü' => 'ue',
			'Ü' => 'Ue',
			'ö' => 'oe',
			'Ö' => 'Oe',
			'™' => 'tm',
			'∏' => 'pi',
			'π' => 'pi',
			'Π' => 'pi',
			'&amp;' => 'and',
			'&' => 'and',
	]);
	$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$str = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $str);
	$str = trim($str, '- ');
	$str = strtolower($str); // eliminate?
	$str = preg_replace("/[\/_|+ -]+/", '-', $str);
	return $str;
}

function array2formatedStr($v) {
	$v = new _arrayToString($v);
	return
	'<style>					' .
	'	table.postlist, table.posttable { 		' .
	'		border-collapse: collapse;			' .
	'		margin:5px 5px 5px 0    			' .
	'	} 										' .
	'	table.postlist td, table.posttable td { ' .
	'		font-family: arial;					' .
	'		font-size: 13px;					' .
	'		padding:3px 5px;					' .
	'		border: 1px solid rgb(170,170,170);	' .
	'		border-style:solid none;			' .
	'		vertical-align:top;					' .
	'	}										' .
    '	table.posttable td ,					' .
    '	table.posttable th {					' .
    '		padding-right:10px;					' .
	'	}										' .
	'											' .
	'</style>									'.
	$v;
}
class _arrayToString {
	static $tdCss = 'font-family:arial;font-size:13px;padding:4px 20px 4px 0px;border:1px solid #888;border-width:1px 0;vertical-align:top; text-align:left;';
	function __construct($value) {
		switch ($this->valueType($value)) {
			case 'value':
				$m = nl2br(htmlspecialchars( $value ));
				break;
			case 'list':
				$m = $this->asList($value)."\n\n";
				break;
			case 'table':
				$m = $this->asTable($value);
				break;
			case 'tableWidthRowsFirst':
				$m = $this->asTableWidthRowsFirst($value);
				break;
		}
		$this->string = $m;
		return $m;
	}
	function __toString() {
		return $this->string;
	}
	function asList($array) {
		$m = '<table class="postlist" style="border-collapse:collapse" cellpadding="0" cellspacing="0">';
		foreach ($array AS $name => $value) {
			if (substr($name,0,1) !== '_') {
				$m .= '<tr>';
				$name = str_replace('_', ' ', $name);
				$m .= '<td style="'.self::$tdCss.'vertical-align:top;"><strong>'.htmlspecialchars($name).'</strong>';
				$m .= '<td style="'.self::$tdCss.'">';
				$m .= $this->__construct($value);
			}
		}
		$m .= '</table>';
		return $m;
	}
	function asTable($array) {
		$i = 0;
		$values = [];
		$m = '<table class="posttable" style="border-collapse:collapse;margin:0" cellpadding="0" cellspacing="0" >';
		$m .= '<tr>';
		foreach ($array AS $name => $sameValues) {
			$m .= '<td style="'.self::$tdCss.'"><b>'.htmlspecialchars($name).'</b>';
			$i++;
			$j = 0;
			foreach ($sameValues AS $index => $value)
				$values[$j++][$i] = $value;
		}
		foreach ($values AS $vs) {
			$m .= '<tr>';
			foreach ($vs AS $value)
				$m .= '<td style="'.self::$tdCss.'">'.htmlspecialchars($value);
		}
		$m .= '</table>';
		return $m;
	}
	function asTableWidthRowsFirst($table) {
		$m = '<table class="posttable" style="border-collapse: collapse" cellpadding="0" cellspacing="0">';
		foreach ($table as $rowId => $row) {
			$m .= '<tr>';
			foreach ($row as $cellName => $cell)
				$m .= '<th style="'.self::$tdCss.'">'.$cellName;
			break;
		}
		foreach ($table as $rowId => $row) {
			$m .= '<tr>';
			foreach ($row as $cellName => $cell)
				$m .= '<td style="'.self::$tdCss.'">'.$cell;
		}
		$m .= '</table>';
		return $m;
	}
	function valueIsTableWidthRowsFirst($table) {
		if (!is_array($table)) return false;
		foreach ($table AS $rowId => $rowVs) {
			if (!is_array($rowVs)) return false;
			if (!is_numeric($rowId)) return false;
		}
		return true;
	}
	function valueType($value) {
		if ($this->valueIsTableWidthRowsFirst($value)) return 'tableWidthRowsFirst';
		if (is_array($value)) {
			$normal = 0;
			foreach ($value AS $name => $vs) {
				if (is_array($vs)) {
					foreach ($vs AS $index => $egal) {
						if (!is_numeric($index)) {
							$normal = 1;
						}
					}
					if (!is_numeric($name)) {

					}
				} else {
					$normal = 1;
				}
			}
			if ($normal) {
				return 'list';
			} else {
				return 'table';
			}
		}
		return 'value';
	}
}

/* todo?
function hyperlink($text) {
	// If the user has decided to deeply use html and manage himself hyperlink
	// cancel the make clickable() function and return the text untouched.

	if (preg_match ( "<(a|img)[[:space:]]*(href|src)[[:space:]]*=(.*)>", $text) ) {
		return $text;
	}

	// pad it with a space so we can match things at the start of the 1st line.
	$ret = " " . $text;

	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, or comma.

	$ret = preg_replace("#([\n ])([a-z]+?)://([^, \n\r]+)#i",
	"\\1<a target=\"_self\" href=\"\\2://\\3\" >\\2://\\3</a>",
	$ret);

	// matches a "www.xxxx.yyyy[/aaaa]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// yyyy contains either alphanum, "-", or "."
	// aaaa is optional.. will contain everything up to the first space, newline, or comma.
	// This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
	// This is to keep it from getting annoying and matching stuff that's not meant to be a link.

	$ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \n\r]*)?)#i",
	"\\1<a href=\"http://www.\\2.\\3\\4\" >www.\\2.\\3\\4</a>",
	$ret);

	// matches an email@domain type address at the start of a line, or after a space.
	// Note: before the @ sign, the only valid characters are the alphanums and "-", "_", or ".".
	// After the @ sign, we accept anything up to the first space, linebreak, or comma.

	$ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^, \n\r]+)#i",
	"\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>",
	$ret);

	// Remove our padding..
	$ret = substr($ret, 1);

	return($ret);
}
*/
