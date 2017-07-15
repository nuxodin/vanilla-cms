<?php
namespace qg;

ini_set('track_errors', 1); // todo: needed?

function error_report($vs) {

	if (!is_array($vs)) $vs = ['message'=>$vs];

	$vs['file']    = $vs['file'] ?? '';
	$vs['line']    = $vs['line'] ?? '';
	$vs['col']     = $vs['col'] ?? '';
	$vs['request'] = $vs['request'] ?? preg_replace('/\?$/','', 'http://'.trim($_SERVER['HTTP_HOST']).trim($_SERVER['REQUEST_URI']) );
	$vs['referer'] = $vs['referer'] ?? $_SERVER['HTTP_REFERER'];
	$vs['browser'] = $_SERVER['HTTP_USER_AGENT'];
	$vs['source']  = $vs['source'] ?? 'php';
	$vs['server']  = $_SERVER['SERVER_SIGNATURE'] ?? '';
	$vs['log_id']  = LiveLog::$id;
	$vs['ip']      = $_SERVER['REMOTE_ADDR'];
	$vs['time']    = strftime('%Y-%m-%d %H:%M:%S');

	$editUrl = function($file, $line, $col) {
		return realpath($file)
			? Url(appURL).'editor/index.php?file='.realpath($file).'&line='.$line.'&col='.$col
			: 'view-source:'.$file;//.'#'.$vs['line'];
	};
	$path = function($file) {
		if (!realpath($file)) {
			$path = Url($file)->toPath();
			if (is_file($path)) return $path;
		}
		return $file;
	};
	$fileDisplay = function($file){
		return str_replace(realpath(appPATH), '', $file);
	};

	$vs['file'] = $path($vs['file']);

	// get sample content
	if (!isset($vs['sample'])) {
		if (is_file($vs['file'])) {
			$position = 0;
			$lines = file($vs['file']);
			foreach ($lines as $lineNr => $line) {
				foreach (str_split($line) as $colNr => $char) {
					if ($lineNr >= $vs['line']-1 && $colNr >= $vs['col']-1) break 2;
					$position++;
				}
			}
			$content = implode('', $lines);
			$vs['sample'] = substr($content, max($position - 60,0), 120);
		}
	}

	if (!isset($vs['backtrace'])) {
		$vs['backtrace'] = debug_backtrace(0);
		array_shift($vs['backtrace']);
	}
	foreach ($vs['backtrace'] as $key => $backtrace) {
		$backtrace = [
			'file'     => ($backtrace['url']??'')?:($backtrace['file']??''),
			'line'     => $backtrace['line'] ?? '',
			'col'      => $backtrace['col'] ?? '',
			'function' => ($backtrace['func']??'')?:($backtrace['class']??'').($backtrace['type']??'').$backtrace['function'],
			'args'     => $backtrace['args']??null,
		];
		if (!$backtrace['file']) {
			unset($vs['backtrace'][$key]); continue;
		}
		$arg = [];
		foreach ((array)$backtrace['args'] as $n => $v) { // nicht immer ein array!?
			if (is_object($v)) { $v = '(object)'.get_class($v); }
			if (is_array($v))  {
				foreach ($v as $sn => $sv) { // nicht immer ein array!?
					if (is_object($sv)) { $sv = '(object)'.get_class($sv); }
					if (is_array($sv))  { $sv = '(Array)'; }
					$arg[$n][$sn] = (string)$sv;
				}
			} else {
				$arg[$n] = $v;
			}
		}
		$backtrace['args'] = @json_encode($arg);
		$vs['backtrace'][$key] = $backtrace;
	}
	/* print */
	if (debug && $vs['source'] !== 'js') {
		echo "\n\n".
		'<a target=_blank href="'.appURL.'editor?file='.urlencode($vs['file']).'&line='.$vs['line'].'&col='.$vs['col'].'">'."\n".
		'  '.$vs['message'].' <br> '."\n".
		'  <b>'.$fileDisplay($vs['file']).':'.$vs['line']."</b>\n".
		'</a>';
		echo "\n";
		$i=0;
		foreach ($vs['backtrace'] as $data) {
			++$i;
			if ($i > 10) break;
			echo '<a href="'.appURL.'editor?file='.urlencode($data['file']).'&line='.$data['line'].'&col='.$data['col'].'" target=_blank>&gt; </a>';
			echo "\n";
		}
		echo "<br>\n\n";
	}
	/* db */
	$dbVs = [];
	foreach ($vs as $n => $v) {
		$dbVs[$n] = is_array($v) ? json_encode($v) : $v;
	}
	D()->m_error_report->insert($dbVs);
	/* email */
	$tSty = 'style="border-collapse:collapse"';
	$tdSty = 'style="vertical-align:top; border:1px solid #888; padding:5px;"';
	$body =
	'<table '.$tSty.'>'.
	'<tr><td '.$tdSty.'> Message <td '.$tdSty.'> '.$vs['message'].
	'<tr><td '.$tdSty.'> File    <td '.$tdSty.'> <a target=_blank href="'.$editUrl($vs['file'],$vs['line'],$vs['col']).'">'.$fileDisplay($vs['file']).'</a>'.
	'<tr><td '.$tdSty.'> Line    <td '.$tdSty.'> '.$vs['line'].
	'<tr><td '.$tdSty.'> Trace   <td '.$tdSty.'> '.
		'<table '.$tSty.'>';
		foreach ($vs['backtrace'] as $trace) {
			$f = $path($trace['file']);
			$body .=
			'<tr>'.
				'<td '.$tdSty.'> <a target=_blank href="'.$editUrl($f,$trace['line'], $trace['col']).'">'.$fileDisplay($trace['file']).'</a>'.
				'<td '.$tdSty.'> '.$trace['line'].
				'<td '.$tdSty.'> '.$trace['function'].
				'<td '.$tdSty.'> '.$trace['args'];
		}
		$body .=
		'</table>'.
	'<tr><td '.$tdSty.'>Request <td '.$tdSty.'> '.$vs['request'].
	'<tr><td '.$tdSty.'>Referer <td '.$tdSty.'> '.$vs['referer'] .
	'<tr><td '.$tdSty.'>Browser <td '.$tdSty.'> '.$vs['browser'].
	'<tr><td '.$tdSty.'>log id  <td '.$tdSty.'> '.$vs['log_id'].
	'<tr><td '.$tdSty.'>ip      <td '.$tdSty.'> '.$vs['ip'].
	'<tr><td '.$tdSty.'>time    <td '.$tdSty.'> '.$vs['time'].
	'</table>';

	/* limit emails: 60 per HOUR */
	$count = D()->one("SELECT count(*) FROM m_error_report WHERE time > '".date('Y-m-d H:i:s')."' - INTERVAL 1 HOUR ");
	if ($count > 60) return;
	/* limit emails: 2 per error per Tag */
	$count = D()->one("SELECT count(*) FROM m_error_report WHERE time > '".date('Y-m-d H:i:s')."' - INTERVAL 1 DAY AND message=".D()->quote($dbVs['message'])." AND file = ".D()->quote($dbVs['file'])." AND line = ".D()->quote($dbVs['line']) );
	if ($count > 2) return;

	/* email */
	$to = G()->SET['error_report']['email']->v;
	if ($to) {
		$header  = 'MIME-Version: 1.0' . "\n";
		$header .= 'Content-type: text/html; charset=utf-8' . "\n";
		$to = implode(',', preg_split('/[ ,;]+/', $to) );
		\mail($to, 'shwups v4 error '.$vs['source'].': '.$_SERVER['HTTP_HOST'], $body, $header);
	}
}

// regular errors
set_error_handler(function($nr, $msg, $file, $line) {
	if (0 === error_reporting()) return false; // inside "@"
	$backtrace = debug_backtrace(0);
	// neu
	if ($GLOBALS['skip_stacks']) {
		for ($i=0; $i <= $GLOBALS['skip_stacks']; $i++) array_shift($backtrace);
		$file = $backtrace[0]['file'];
		$line = $backtrace[0]['line'];
	}
	$vs = [
		'message'   => $msg,
		'file'      => $file,
		'line'      => $line,
		'nr'        => $nr,
		'backtrace' => $backtrace
	];
	error_report($vs);
});

// fatal errors
register_shutdown_function(function() { // what about errors occurring in other shutdown_functions?
	$error = error_get_last();
	if ($error !== null) {
		$fatals = [
			E_USER_ERROR      => 'Fatal Error',
			E_ERROR           => 'Fatal Error',
			E_PARSE           => 'Parse Error',
			E_CORE_ERROR      => 'Core Error',
			E_CORE_WARNING    => 'Core Warning',
			E_COMPILE_ERROR   => 'Compile Error',
			E_COMPILE_WARNING => 'Compile Warning'
		];
		if (isset( $fatals[$error['type']] )) {
			// if stack trace in message:
			$inBacktrace = false;
			foreach (explode("\n", $error['message']) as $line) {
				if ($line === 'Stack trace:') {
				    $inBacktrace = true;
				    continue;
				}
				if (!$inBacktrace) {
					$message[] = $line;
				} else {
					preg_match('/#([0-9]+) ([^\(]+)\(([0-9]+)\)\:(.*)/',$line, $matches);
					if (!isset($matches[0])) continue;
					list($x, $x, $file, $line, $rest) = $matches;
					$error['backtrace'][] = [
						'file' => $file,
						'line' => $line,
						'function' => $rest,
					];
				}
	      	}
	      	$error['message'] = implode($message);
			$error['message'] = $fatals[$error['type']].': '.$error['message'];
			error_report($error);
		}
	}
	ini_set('log_errors', 1);
});
ini_set('log_errors', 0);


qg::on('action', function() {

	if (G()->SET['error_report']['javascript']->v) {
		if (debug) G()->js_data['error_report_debug'] = 1;
		html::$optimiseJs = false;
		html::addJsFile(sysURL.'core/js/c1.js','core');
		html::addJsFile(sysURL.'error_report/pub/main.js','core');
	}

	// javascript api
	if (appRequestUri === 'js-error' && G()->SET['error_report']['javascript']->v) {
		$report = json_decode(file_get_contents('php://input'), true);
		if (!$report['message']) exit();
		$report['source'] = 'js';
		//if (!isset($report['backtrace'])) $report['backtrace'] = []; // needed?
		error_report($report);
		exit();
	}

	// Content-Security-Policy api
	G()->csp_report_uri = URL(appURL).'csp-error';
	if (appRequestUri === 'csp-error') {
		$report = json_decode(file_get_contents('php://input'), true)['csp-report'];
		$sample = $report['script-sample'] ?? '';
		// $type = ($report['disposition'] ?? 'enforce') === 'enforce' ? 'Error' : 'Warning'; // chrome, no firefox!
		$type = G()->SET['qg']['csp']['enable']->v === 'report only' ? 'Report only:' : 'Blocked:'; // better reported by the browser (1 line above)
		error_report([
			'message' => $type.' "'.$report['blocked-uri'].'" blocked by "'.$report['violated-directive'].'" '.($sample?' Sample:'.$sample:''),
			'source'  => 'csp',
			'file'    => $report['source-file']??'',
			'line'    => $report['line-number']??'',
			'request' => $report['document-uri'],
			'referer' => $report['referrer'],
			'backtrace' => [],
		]);
		exit();
	}
});
