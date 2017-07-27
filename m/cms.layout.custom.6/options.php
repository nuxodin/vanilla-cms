<?php
namespace qg;

$module = $Cont->vs['module'];
$LPage = layoutCustom6::layoutPage();

if (is_file(appPATH.'qg/'.$module.'/options.php')) {
	include appPATH.'qg/'.$module.'/options.php';
  	echo '<br><br>';
}

if ($LPage->access() < 2) return;

?>
<b>Layout</b>
<hr>
<div id=customLayoutSettings>
  	<button style="font-size:18px; float:right" id=layoutCssEditor>css-Editor</button>

  	<script>
	$('#layoutCssEditor').on('click', function(e) {
		e.preventDefault();
		cmsLayouter3_styleEditor();
	});
  	</script>

	<?php foreach (['pub/custom.css','pub/base.css','pub/main.js'] as $part) { ?>
		<?php
		$path = appPATH.'qg/'.$module.'/'.$part;
		$_SESSION['fileEditor']['allow'][$path] = 1;
		?>
	  	<a target=editor href="<?=appURL.'editor?file='.urldecode($path)?>"><?=$part?> Datei bearbeiten<br></a>
	<?php } ?>

	<br>
	<a target=_blank href="//www.google.com/webfonts">Font-CSS-File:</a>
	<input placeholder="https://fonts.googleapis.com/..." value="<?=hee($LPage->SET['font-css-file'])?>" onchange="$fn('page::setDefault')(<?=$LPage?>,{'font-css-file': this.value }).run(function() { location.href = location.href });" style="width:100%">

	<br>
	<br>
	<div id=mCmsLayouter3_container>
		<h3>Bilder <button id=mCmsLayouter3_pickfiles>hochladen</button></h3>
		<script>
		{
			let btn = document.getElementById('mCmsLayouter3_pickfiles');
			btn.addEventListener('click',()=>{
				let fileInp = document.createElement('input');
				fileInp.type = 'file';
				fileInp.click();
				fileInp.addEventListener('change',()=>{
					for (file of fileInp.files) {
						let data = new FormData();
						data.append('file', file);
						fetch(appURL+'?mLayoutCustom6_upload', {
						  method: 'POST',
						  credentials: 'same-origin',
						  body: data
						}).then(()=>{
							cms.cont(cms.cont.active).showWidget('options');
						});
					}
				})
			})
		}
		</script>
		<?php
		$path = appPATH.'qg/'.$module.'/pub/img/';
		!is_dir($path) && mkdir($path);
		$url = path2uri($path);
		$images = [];
		foreach (scanDir($path) as $f) {
			if (!is_file($path.$f)) continue;
			$images[] = $url.$f;
		}
		?>
		<table style="width:100%" class=-styled>
			<?php foreach ($images as $url) { ?>
				<?php $file = preg_replace('/.*\/([^\/]+)/','$1',$url); ?>
				<tr>
					<td style="width:50px"> <img src="<?=$url?>" style="min-height:20px; max-height:35px; max-width:160px; box-shadow:0 0 5px rgba(0,0,0,.5); background:linear-gradient(45deg,#f3f3f3 50%,#fff 50%); background-size:8px 8px;" />
					<td> <?=$file?>
					<td onclick="var el = this; confirm('Möchten Sie die Datei wirklich löschen?') && $fn('page::api')(<?=$Cont?>,{deleteImg:'<?=$file?>'}).run(() => cms.cont(cms.cont.active).showWidget('options') )" style="width:20px; padding-right:6px">
						<img src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg" alt="löschen">
			<?php } ?>
		</table>
	</div>

</div>
