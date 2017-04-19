<?php
namespace qg;

html::addCSSFile(sysURL.'core/css/q1Rst.css');
html::addCSSFile(sysURL.'core/js/c1/css/normalize.css');
html::addCSSFile(sysURL.'core/js/c1/css/recommend.css');

html::addJSFile(sysURL.'core/js/c1.js');
html::addJSFile(sysURL.'core/js/jQuery.js');
html::addJSFile(sysURL.'core/js/qg.js');

html::addCSSFile(sysURL.'fileEditor/pub/codemirror/lib/codemirror.css');
html::addCSSFile(sysURL.'fileEditor/pub/codemirror/theme/eclipse.css');

html::addJSFile(sysURL.'fileEditor/pub/codemirror/lib/codemirror.js', 1, false);

html::addJSFile(sysURL.'fileEditor/pub/codemirror/addon/hint/show-hint.js');
html::addCSSFile(sysURL.'fileEditor/pub/codemirror/addon/hint/show-hint.css');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/addon/hint/javascript-hint.js');

html::addJSFile(sysURL.'fileEditor/pub/codemirror/addon/scroll/annotatescrollbar.js');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/addon/search/matchesonscrollbar.js');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/addon/search/searchcursor.js');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/addon/search/match-highlighter.js');

html::addJSFile(sysURL.'fileEditor/pub/codemirror/addon/fold/xml-fold.js');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/addon/edit/matchtags.js');

html::addJSFile(sysURL.'fileEditor/pub/codemirror/addon/edit/trailingspace.js');

html::addJSFile(sysURL.'fileEditor/pub/codemirror/mode/xml/xml.js');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/mode/javascript/javascript.js');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/mode/css/css.js');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/mode/clike/clike.js');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/mode/php/php.js');
html::addJSFile(sysURL.'fileEditor/pub/codemirror/mode/htmlmixed/htmlmixed.js');

html::addJSFile(sysURL.'fileEditor/pub/main.js');
html::addCSSFile(sysURL.'fileEditor/pub/main.css');

html::$title = basename($file).' | Editor';
header('content-type: text/html; charset=utf-8');

G()->js_data['qgToken'] = qg::token();

?><!DOCTYPE HTML>
<html lang="<?=L()?>">
	<head>
		<?=html::getHeader()?>
	<body>
		<button
			class=q1Rst
			id=saveButton
			style="position:fixed;
					right:-1px;
					top:10px;
					z-index:10;
					padding:10px 12px;
					display:none;
					background-image: linear-gradient(rgba(255,255,255,.5),rgba(205,205,205,.5));">
			<?=is_writable($file)?'save':'rechte zum speichern fehlen!'?>
		</button>
		<?php
		$ext = preg_replace('/.*\.([^.])/', '$1', $file);
		$mime = File::extensionToMime($ext);
		$mime = str_replace('application/x-javascript','text/javascript', $mime);
		?>
		<div style="height:100%; width:100%">
			<textarea id=editor mime="<?=$mime?>" line="<?=$_GET['line']??''?>" style="width:100%; height:100%;"><?=hee(file_get_contents($file))?></textarea>
		</div>
	</body>
</html>
