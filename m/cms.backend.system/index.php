<?php
namespace qg;

if (!isset($_GET['open'])) $_GET['open'] = null;
?>
<div class=beBoxCont>
	<div class=c1-box>
		<div class=-head>Cache</div>
		<div class=-body>
			<?php
			if (isset($_POST['clearCache'])) {
				time_limit(20);
				echo '<b>'.L('Cache gelöscht').':<br></b>';
				$verzeichnis = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator( appPATH.'cache/' ), true);
				foreach ($verzeichnis as $datei) {
					$d = pathinfo($datei);
					if ($d['basename'] == '.htaccess') continue;
					if (!is_file($datei)) continue;
					if (preg_match('/\.svn/', $datei)) continue;
					echo $datei.'<br>';
					unlink($datei);
				}
			}
			if (isset($_POST['clearTmp'])) {
				time_limit(20);
				echo '<b>'.L('Temporäre Dateien gelöscht').':<br></b>';
				$verzeichnis = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator( appPATH.'cache/tmp/' ), true);
				foreach ($verzeichnis as $datei) {
					$d = pathinfo($datei);
					if ($d['basename'] == '.htaccess') continue;
					if (!is_file($datei)) continue;
					if (preg_match('/\.svn/', $datei)) continue;
					echo $datei.'<br>';
					unlink($datei);
				}
			}
			?>
			<form method=post action="">
				<button name=clearTmp ><?=L('Temporäre Dateien löschen')?></button><br>
				<button name=clearCache><?=L('Temporäre Dateien und Chache löschen')?></button><br>
			</form>
			<br>
		</div>
	</div>

	<div class=c1-box>
		<div class=-head>Todos</div>
		<div class=-body>
			<?php
			if (function_exists('apache_get_modules')) {
				$mod = apache_get_modules();
				if (!in_array('mod_expires',$mod)) {
					echo '<div class="notice">Install the apache-module "mod_expires" for better performance!</div>';
				}
				if (!in_array('mod_deflate',$mod)) {
					echo '<div  class="notice">Install the apache-module "mod_deflate" for better performance!</div>';
				}
			}
			foreach (D()->query("SELECT * FROM usr ORDER BY id LIMIT 8") as $row) {
				if (auth::pw_verify('su', $row['pw'])) {
					echo '<div  class="warning">The user "'.$row['email'].'" has the default password!</div>';
				}
			}
			foreach (D()->query("SELECT * FROM usr WHERE pw != '' AND pw NOT LIKE '$%' ") as $row) {
				echo '<div  class="warning">The user "'.$row['email'].'" has the old password hash (md5)</div>';
			}

			$dbTime = (int)D()->one("SELECT UNIX_TIMESTAMP() as time");
			if ($dbTime !== time()) {
				echo
				'<div class="warning">DB-time unlike PHP-time'.
					'<table>'.
						'<tr><td>db:  <td>'.strftime('%x %X' ,$dbTime).
						'<tr><td>php: <td>'.strftime('%x %X' ,time()).
						'<tr><td>browser: <td><script>document.write(new Date().toISOString())</script>'.
					'</table>'.
				'</div>';
			}

			global $debug;
			if ($debug) {
				echo '<div  class="warning">Debugmode is active!</div>';
			}
			?>
			<style>
			.notice, .warning {
				padding:8px;
				margin:5px;
				background:#ffa;
				box-shadow:0 0 5px rgba(0,0,0,.3);
			}
			.warning {
				background:#faa;
			}
			</style>
		</div>
	</div>

	<div class=c1-box>
		<a class=-head href="<?=hee(URL()->addParam('open','phpinfo'))?>">phpinfo</a>
		<div class=-body>
			<?php if ($_GET['open']=='phpinfo') { ?>
				<?php phpinfo() ?>
			<?php } ?>
		</div>
	</div>

	<div class=c1-box>
		<a class=-head href="<?=hee(URL()->addParam('open','mysql'))?>">mysql</a>
		<div class=-body>
			<?php if ($_GET['open']=='mysql') { ?>
				<table class=c1-style>
					<?php
					$relevant = ['max_allowed_packet'=>1];
					$byte = ['max_allowed_packet'=>1];
					foreach (D()->all("show variables") as $row) {
						echo '<tr>';
							$name = $row['Variable_name'];
							$mark = isset($relevant[$name]);
							echo '<td '.($mark?'style="font-weight:bold"':'').'>'.$row['Variable_name'];

							$value = $row['Value'];
							echo '<td>';
							echo isset($byte[$name]) ? util::byte_format($value) : $value;
					}
					?>
				</table>
			<?php } ?>
		</div>
	</div>

	<div class=c1-box>
		<a class=-head href="<?=hee(URL()->addParam('open','locale'))?>">Locales</a>
		<div class=-body>
			<?php if ($_GET['open']=='locale') { ?>
				active: "<?=setLocale(0,0)?>"
				<hr>
				available on the system:
				<pre><?php system('locale -a') ?></pre>
			<?php } ?>
		</div>
	</div>
</div>
