<?php namespace qg;
$id = (int)$_GET['id'];
if (!$id) return;
$error = D()->row("SELECT * FROM m_error_report WHERE id = ".D()->quote($id));
if (!$error) { echo 'Error-Entry not found'; return; };
$log = D()->row("SELECT * FROM log WHERE id = ".$error['log_id']);
$sess = D()->row("SELECT * FROM sess WHERE id = ".(int)$log['sess_id']);
$usr = D()->row("SELECT * FROM usr WHERE id = ".(int)$sess['usr_id']);
?>

<div class=beBoxCont style="xfont-size:.95em">
    <div class=c1-box style="overflow:auto; width:auto; flex:0 0 auto">
		<div class=-head>Fehler</div>
		<div class=-body>
            <h2>
                <span style="color:red"><?=$error['source']?>:</span>
                <?=$error['message']?>
            </h2>
            <a style="color:inherit; text-decoration:none" target=_blank href="<?=appURL.'editor/?file='.urlencode($error['file']).'&line='.$error['line'].'&col='.$error['col']?>">
                <b><?=$error['file']?></b> line:<?=$error['line']?> column:<?=$error['col']?>
                <pre style="box-shadow:0 0 10px; padding:10px"><?=hee($error['sample'])?></pre>
            </a>
			<table class=c1-style>
				<tr>
					<th> Request
					<td> <a href="<?=hee($error['request'])?>"><?=hee($error['request'])?></a>
				<tr>
					<?php
					$info = util::ua_info($error['browser']);
					$bot = util::ua_is_bot($error['browser']);
					?>
					<th> Browser
					<td>
						<?=$bot?'<b>bot</b>':''?> <?=$info['browser']?> <?=$info['version']?><br>
						<small><?=$error['browser']?></small>
				<tr>
					<th> Time
					<td>
						<?=$error['time']?><br>
				<tr>
					<th> IP
					<td>
						<?=$error['ip']?><br>
						<small><?=gethostbyaddr($error['ip'])?></small>
			</table>
            <?php
            $print = $error;
            unset($print['backtrace']);
            unset($print['request']);
            unset($print['browser']);
            unset($print['time']);
            unset($print['ip']);
            unset($print['file']);
            unset($print['line']);
            unset($print['col']);
            unset($print['sample']);
            unset($print['message']);
            unset($print['source']);

			dump::h($print);
            dump::h($sess);
            ?>
		</div>
	</div>

    <div class=c1-box style="overflow:auto; width:auto; flex:0 0 auto">
		<div class=-head>User</div>
		<div class=-body>
            <?php
            dump::h($usr);
            ?>
		</div>
	</div>

    <div class=c1-box style="overflow:auto; width:auto; flex:0 0 auto">
		<div class=-head>Backtrace</div>
        <?php
        $bt = json_decode($error['backtrace'],1);
        ?>
        <table class=c1-style>
            <thead>
                <tr>
                    <th> File
                    <th> Function
                    <th> Arguments
            <tbody>
            <?php foreach ($bt as $item) { ?>
                <tr>
                    <td>
                        <?php
                        $fileShow = substr($item['file'],strlen(sysPATH)-2);
                        echo '';
                        ?>
                        <a href="<?=hee(appURL.'editor/?file='.urlencode($item['file']).'&line='.$item['line'].'&col='.$item['col'])?>" target=_blank>
                            <?=hee($fileShow)?>
                            <span style="opacity:.6"> : <?=hee($item['line'])?> <?=$item['col']?':'.hee($item['col']):''?> </span>
                        </a>
                    <td> <?=hee($item['function'])?>
                    <td> <?=dump::h(json_decode($item['args'],1))?>
            <?php } ?>
        </table>
	</div>

    <div class=c1-box style="overflow:auto; width:auto; flex:0 0 auto">
		<div class=-head>History</div>
		<div class=-body style="flex-grow:0">
			History of:
			<a href="<?=Url()->addParam('history_of', 'sess')?>">Sesssion</a> |
			<a href="<?=Url()->addParam('history_of', 'client')?>">Client</a> |
			<a href="<?=Url()->addParam('history_of', 'ip')?>">IP</a>
		</div>
		<?php
		switch ($_GET['history_of']??'sess') {
			case 'sess':
				$logs = D()->all("SELECT * FROM log WHERE sess_id = ".D()->quote($log['sess_id'])." AND id <= ".D()->quote($error['log_id'])." ORDER BY id DESC LIMIT 20");
				break;
			case 'client':
				$logs = D()->all("SELECT * FROM log WHERE sess_id IN(SELECT id FROM sess WHERE client_id = ".D()->quote($sess['client_id']).") AND id <= ".D()->quote($error['log_id'])." ORDER BY id DESC LIMIT 20");
				break;
			case 'ip':
				$logs = D()->all("SELECT * FROM log WHERE sess_id IN(SELECT id FROM sess WHERE ip = ".D()->quote($sess['ip']).") AND id <= ".D()->quote($error['log_id'])." ORDER BY id DESC LIMIT 20");
				break;
		}
		?>
        <table class=c1-style>
            <thead>
                <tr>
                    <th> Time / Session
                    <th> URL / Referer
                    <th> POST
            <tbody>
            <?php foreach ($logs as $item) {
				$errorItems = D()->all("SElECT * FROM m_error_report WHERE log_id = ".$item['id']." ORDER BY id DESC");
				?>
                <tr>
                    <td> <?=strftime('%x %X',$item['time'])?> <br> <?=hee($item['sess_id'])?> <br> <?=hee($item['id'])?>
                    <td>
						<a href="<?=hee($item['url'])?>" target="_blank"><?=hee($item['url'])?></a>
						<br>
						<div style="font-size:.9em; color:#aaa"><?=hee($item['referer'])?></div>
						<?php foreach ($errorItems as $eItem) { ?>
							<a style="color:red; border:1px solid; border-width:1px 0; padding:3px 0; margin-bottom:-1px; display:block;" href="<?=Url($Cont->Page->url())->addParam('id',$eItem['id'])?>">
								<?php if ($eItem['id'] === $error['id']) echo '&#x25B6;&#xFE0E;'; ?>
								<?=$eItem['message']?>
							</a>
						<?php } ?>
                    <td> <div style="max-width:600px; overflow:auto"><?=dump::h( unserialize($item['post']) )?> </div>
            <?php } ?>
        </table>
	</div>

</div>
