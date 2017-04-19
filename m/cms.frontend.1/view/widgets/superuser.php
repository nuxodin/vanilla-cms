<?php
namespace qg;
if (!Usr()->superuser) return;

if (isset($param['delete'])) {
    unlink($param['delete']);
}
if (isset($param['create'])) {
    $root = $param['in'] === 'app' ? $Cont->modPath : appPATH.'qg/'.$Cont->vs['module'].'/';
    $file = $root.$param['create'];
    !file_exists(dirname($file)) && mkdir(dirname($file), 0777, true);
    touch($file);
}
?>
<div class=qgCmsFront1SuperuserManager pid="<?=$Cont?>" style="display:flex; flex-flow:wrap; margin:-10px;">
    <div scope=custom style="margin:10px; flex:1 1 auto">
        <table class=-styled style="width:100%">
            <th colspan=3>
                Custom Files
                <input class=-create placeholder=create style="width:100%">
        <?php
        $path = appPATH.'qg/'.$Cont->vs['module'];
        if (file_exists($path)) {
            $verzeichnis = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator($path), true );
            $files = [];
            foreach ($verzeichnis as $datei) $files[(string)$datei] = $datei;
            ksort($files);
            $startOffset = strlen(realpath($path)) +1;
            foreach ($files as $datei) {
                $show = substr($datei, $startOffset);
                $search = str_replace('\\', '/', $datei);
                if (!is_file($datei)) continue;
                $file = realpath($datei);
                $src = appURL.'editor?file='.urlencode($file);
                ?><tr itemid="<?=hee($file)?>">
                    <td><a href="<?=hee($src)?>" target=<?=md5($file)?>><?=$show?></a>
                    <td><?=strftime('%x %H:%M',filemtime($file))?>
                    <td class=-remove style="cursor:pointer; padding-left:0">
                        <img src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg" alt="löschen">
                <?php
            }
        }
        ?>
        </table>
    </div>

    <div scope=app style="margin:10px; flex:1 1 auto">
        <table class=-styled style="width:100%">
            <tr>
                <th colspan=3>
                    App Files
                    <input class=-create placeholder=create style="width:100%">
        <?php
  		if (is_dir($Cont->modPath)) {
            $verzeichnis = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $Cont->modPath ), true );
            $files = [];
            foreach ($verzeichnis as $datei) $files[(string)$datei] = $datei;
            ksort($files);
            $startOffset = strlen( realpath( $Cont->modPath ) ) +1;
            foreach ($files as $datei) {
                $show = substr($datei, $startOffset);
                $search = str_replace('\\', '/', $datei);
                if (!is_file($datei)) continue;
                $file = realpath($datei);
                $src = appURL.'editor?file='.urlencode($file);
                ?>
                <tr itemid="<?=hee($file)?>">
                    <td><a href="<?=hee($src)?>" target=<?=md5($file)?>><?=$show?></a>
                    <td><?=strftime('%x %H:%M',filemtime($file))?>
                    <td class=-remove style="cursor:pointer; padding-left:0">
                        <img src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg" alt="löschen">
                <?php
            }
		}
        ?>
        </table>
    </div>

</div>
