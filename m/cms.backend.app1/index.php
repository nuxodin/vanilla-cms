<?php namespace qg; ?>
<div class=beBoxCont>
	<div class=c1-box>
		<div class=-head>Icons</div>
		<div class=-body>
			<div style="margin:-5px">
				<?php
				$iconDims = [558,270,256,192,180,160,152,144,128,120,114,96,76,72,64,60,57,48,32,16];
				foreach ($iconDims as $dim) {
					$File = Page(1)->File('app'.$dim);
					?>
					<form action="" method=post enctype=multipart/form-data style="display:inline-block; margin:5px; position:relative" onclick="$(this).find('[type=file]')[0].click()">
						<div class=hoverFadeIn style="font-size:<?=(int)max(10,$dim/5)?>px;">
							<?=$dim?>x<?=$dim?><br>
							<span style="font-size:.6em">click here</span>
						</div>
						<img src="<?=appURL?>app-icon-<?=$dim?>.png"
							 style="width:<?=$dim?>px; height:<?=$dim?>px; box-shadow:0 0 8px; display:block; background-image:repeating-linear-gradient(to left, #eee, #eee 10px, #fff 10px, #fff 20px); background-size:20px; <?=$File->exists()?'':'opacity:.3;'?>">
						<input type=file name="<?=hee($File->uploadTicket())?>" onchange="this.form.submit()" style="position:absolute; width:1px; height:1px; overflow:hidden; opacity:0">
					</form>
				<?php } ?>
				<style>
				.hoverFadeIn {
					position:absolute; top:0; left:0; right:0; bottom:0; text-align:center; padding-top:25%;
					background:#fff;
					opacity:0;
					cursor:pointer;
					transition:opacity .2s;
					z-index:2;
				}
				.hoverFadeIn:hover {
					opacity:.8;
				}
				</style>
			</div>
		</div>
	</div>

	<div class=c1-box style="flex-basis:700px">
		<div class=-head>Einstellungen</div>
		<?php
		$Editor = new SettingsEditor(G()->SET['app1']);
		echo $Editor->show();
		?>
		<div class=-body style="flex:0 0 auto">
			For a list of recommended categories see:
			<a href="https://github.com/w3c/manifest/wiki/Categories" target=_blank>https://github.com/w3c/manifest/wiki/Categories</a>
			<br>
			<br>
	        <h2>Manifest-Standards:</h2>
			<a href="http://www.w3.org/TR/appmanifest" target=_blank>http://www.w3.org/TR/appmanifest</a> und <br>
	        <a href="https://developer.mozilla.org/de/docs/Apps/Manifest" target=_blank>https://developer.mozilla.org/de/docs/Apps/Manifest</a> und <br>
			<a href="https://developer.chrome.com/webstore/hosted_apps" target=_blank>https://developer.chrome.com/webstore/hosted_apps</a>
			<br>
			<br>
			<h2>Manifest-Files</h2>
			<a target=_blank href="<?=appURL?>w3c.manifest.json">W3C webapp manifest</a><br>
			<a target=_blank href="<?=appURL?>manifest.webapp">Firefox-Web-App-Manifest</a><br>
			<a target=_blank href="<?=appURL?>app.crx">Chrome-CRX-File</a><br>
			<br><br><br>
		</div>
	</div>
</div>
