<?php
namespace qg;

if (!Usr()->superuser) return false; /* show default settings */
?>
<?php
$file = realpath(appPATH.'qg/cmsPhpFiles/'.$Cont.'.php');
$_SESSION['fileEditor']['allow'][$file] = 1;
$src = appURL.'editor?file='.urldecode($file);
?>
<a style="color:inherit; position:absolute; right: -1px; top: 50px; z-index:1;" target=_blank href="<?=$src?>">
	<button style="padding:10px 12px">open</button>
</a>
<div style="height:500px;">
	<iframe id="<?=$i=i()?>" src="<?=$src?>" style="position:absolute; top:0; left:0; right:0; bottom:0; min-height:120px; width:100%; height:100%; border:0"></iframe>
	<script>
	!function(){
		var iframe = document.getElementById('<?=$i?>');
		iframe.onload = function() {
			var win = iframe.contentWindow;
			win && win.document.getElementById('saveButton').addEventListener('click', function(e){
				$fn('page::reload')(<?=$Cont?>);
			});
		}.c1Debounce(970);
	}();
	</script>
</div>
