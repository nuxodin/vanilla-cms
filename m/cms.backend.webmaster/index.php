<?php
namespace qg;

$SET = G()->SET['cms.backend.webmaster'];
$_SESSION['SettingsEditor roots'][$SET->i] = 1; // allow change settings
?>
<div class=beBoxCont>
    <!--div class="c1-box Links" style="flex-basis:300px">
        <div class=-head>Brower-Kompatibilität</div>
        <table class=c1-style>
        	<tr>
        		<td> IE / Edge ab
        		<td> <input value="11" placeholder="standard 11">
        	<tr>
        		<td> Android
                <td> <input value="4.4" placeholder="standard 5">
        </table>
    </div-->
    <div class="c1-box  Links">
        <div class=-head>Test-Tools</div>
        <div class=-body style="overflow:auto; max-height:480px;">
            <?php
        	$domain = $_SERVER['HTTP_HOST'];
            ?>
            <h3> Performance </h3>
            <a target=_blank href="https://developers.google.com/speed/pagespeed/insights/?url=<?=$domain?>">
              <b>Performance PageSpeed Insights</b> developers.google.com
            </a>
            <a target=_blank href="http://www.webpagetest.org/">
              <b>Performance</b> www.webpagetest.org
            </a>

            <h3>Conform</h3>
            <a target=_blank href="https://validator.w3.org/unicorn/check?ucn_uri=<?=$domain?>&doctype=Inline&charset=(detect%20automatically)&ucn_task=conformance#">
              <b>HTML, CSS, feed validator</b> w3.org
            </a>
            <a target=_blank href="http://validator.w3.org/check?uri=<?=$domain?>&charset=%28detect+automatically%29&doctype=Inline&group=0">
              <b>HTML</b> w3.org
            </a>
            <a target=_blank href="http://jigsaw.w3.org/css-validator/validator?uri=<?=$domain?>&profile=css3&usermedium=all&warning=1&vextwarning=&lang=de">
              <b>CSS</b> w3.org
            </a>
            <a target=_blank href="https://validator.w3.org/i18n-checker/check?uri=<?=$domain?>#validate-by-uri+">
              <b>Internationalization</b> w3.org
            </a>
            <a target=_blank href="http://realfavicongenerator.net/favicon_checker?site=<?=$domain?>">
              <b>Favion</b> realfavicongenerator.net
            </a>
            <a target=_blank href="https://webhint.io/scanner/">
              <b>Compatibility</b> webhint.io (Microsoft)
            </a>


            <h3>SEO</h3>
            <a target=_blank href="https://www.google.com/webmasters/tools/submit-url?hl=de">
              bei google eintragen
            </a>
            <a target=_blank href="https://www.bing.com/toolbox/submit-site-url">
              bei bing eintragen
            </a>
            <a target=_blank href="https://www.seoptimer.com/<?=$domain?>">
                seoptimer.com
            </a>
            <a target=_blank href="https://website.grader.com/results/<?=$domain?>">
                website.grader.com
            </a>
            <a target=_blank href="http://www.woorank.com/de/www/<?=$domain?>">
              woorank.com
            </a>
            <a target=_blank href="http://www.opensiteexplorer.org/links?site=<?=urlencode( Url( Page(2)->url() ) )?>">
              opensiteexplorer.org
            </a>
            <a target=_blank href="https://ahrefs.com/site-explorer/overview/subdomains/?target=<?=$domain?>">
              ahrefs.com
            </a>
            <a target=_blank href="http://www.seobility.net/de/seocheck/check?url=<?=urlencode( Url( Page(2)->url() ) )?>">
              seobility.net
            </a>
            <a target=_blank href="https://www.similarweb.com/website/<?=$domain?>">
              similarweb.com
            </a>

            <h3>DNS / Network</h3>
            <a target=_blank href="http://www.dnsinspect.com/<?=$domain?>">
              <b>DNS</b> dnsinspect.com
            </a>
            <a target=_blank href="http://mxtoolbox.com/SuperTool.aspx?action=mx%3a<?=$domain?>&run=toolpage">
              <b>MX-record</b> mx-tools
            </a>
            <a target=_blank href="http://viewdns.info/dnsreport/?domain=<?=$domain?>">
              <b>dns</b> viewdns.info
            </a>
            <a target=_blank href="http://www.nabber.org/projects/dnscheck/?domain=<?=$domain?>">
              <b>dns</b> nabber.org
            </a>
            <a target=_blank href="http://cloudmonitor.ca.com/en/ping.php?vtt=1401882475&varghost=<?=$domain?>&vhost=_&vaction=ping&ping=start">
              <b>Pings from countries</b> cloudmonitor.ca.com
            </a>
            <a target=_blank href="http://ip6.nl/#!<?=$domain?>">
              <b>ip-v6 check</b> ip6.nl
            </a>
            <a target=_blank href="http://ready.chair6.net/?url=<?=$domain?>">
              <b>ip-v6 check</b> ready.chair6.net
            </a>
            <a target=_blank href="https://en.internet.nl/domain/<?=$domain?>/">
              <b>ip-v6 check</b> internet.nl
            </a>

            <h3>Security</h3>
            <a target=_blank href="https://observatory.mozilla.org/analyze.html?host=<?=urldecode($domain)?>">
              <b>security</b> observatory.mozilla.org
            </a>
            <a target=_blank href="https://securityheaders.io/?q=<?=urldecode(URL(Page(2)->url()))?>">
              <b>security</b> securityheaders.io
            </a>
            <a target=_blank href="https://www.ssllabs.com/ssltest/analyze.html?d=<?=$domain?>&hideResults=on">
              <b>SSL</b> ssllabs.com
            </a>

            <h3> Content </h3>
            <a target=_blank href="http://validator.w3.org/checklink?uri=<?=$domain?>&hide_type=all&recursive=on&depth=1&check=Check">
              <b>Broken Links</b> w3.org
            </a>
            <a target=_blank href="http://www.iwebtool.com/broken_link_checker?url=<?=$domain?>">
              <b>Broken Links</b> iwebtool.com
            </a>
            <a target=_blank href="http://www.stylestats.org/?uri=<?=urlencode( 'http://'.$domain )?>">
              <b>Layout: styles / Colors</b> stylestats.org
            </a>
            <a target=_blank href="http://www.viewlike.us/operator/#u=http://<?=$domain?>|1024|768">
              <b>Layout: screen sizes</b>  viewlike.us
            </a>

            <h3> Mobile </h3>
            <a target=_blank href="https://search.google.com/test/mobile-friendly?url=<?=urldecode(URL(Page(2)->url()))?>">
              <b>search.google.com/test</b> google
            </a>
            <a target=_blank href="http://validator.w3.org/mobile/check?docAddr=<?=$domain?>&async=true">
              <b>Mobile tests</b> w3.org
            </a>
            <a target=_blank href="https://validator.w3.org/mobile-alpha/?url=<?=urlencode('http://'.$domain)?>">
              <b>new Mobile tests</b> w3.org
            </a>
            <a target=_blank href="https://www.bing.com/webmaster/tools/mobile-friendliness">
              <b>Mobile test</b> bing.com
            </a>

            <h3> Accessibility </h3>
            <a target=_blank href="http://wave.webaim.org/report#/<?=urldecode(URL(Page(2)->url()))?>">
              <b>Accessibility</b> wave.webaim.org
            </a>
            <a target=_blank href="http://tenon.io/testNow.php?url=<?=urldecode(URL(Page(2)->url()))?>">
              <b>Accessibility</b> tenon.io
            </a>
            <a target=_blank href="http://achecker.ca/checker/index.php">
              <b>Accessibility</b> achecker.ca
            </a>
            <a target=_blank href="http://www.cynthiasays.com/?">
              <b>Accessibility</b> cynthiasays.com
            </a>
            <a target=_blank href="http://www.nvaccess.org/">
              <b>Accessibility</b> www.nvaccess.org | Download a real screenreader!
            </a>

            <h3>E-Mail</h3>
            <a target=_blank href="http://www.mail-tester.com/">
              <b>E-Mail</b> mail-tester.com
            </a>

            <h3>History</h3>
            <a target=_blank href="http://wayback.archive.org/web/*/http://<?=$domain?>/">
                <b>Wayback Maschine</b> Archive.org
            </a>
            <a target=_blank href="https://webcache.googleusercontent.com/search?q=cache:<?=$domain?>">
                <b>Google Cache</b> google.com
            </a>

            <div style="padding:10px">more...</div>

            <a target=_blank target=_blank href="http://uitest.com/de/check/">
                <b>Diverse Checks</b> uitest.com
            </a>
            <a target=_blank href="http://tools.pingdom.com">
                <b>Performance</b> tools.pingdom.com
            </a>
            <a target=_blank href="http://maplatency.com/">
                <b>Performance</b> http://maplatency.com/
            </a>
            <a target=_blank href="http://www.gomeznetworks.com/custom/instant_test.html">
                <b>Waterfall by diffrent countries</b> gomeznetworks.com
            </a>
            <a target=_blank href="https://www.languagetool.org/de/">
                <b>Spellcheck (Firefox-Plugin)</b> languagetool.org
            </a>
            <a target=_blank href="http://www.seorch.de/">
                <b>SEO</b> www.seorch.de
            </a>
            <a target=_blank href="http://www.programmierung-webdesign-seo.de/seo-check/analyze/">
                <b>SEO</b> programmierung-webdesign-seo.de
            </a>
            <a target=_blank href="http://www.seitenreport.de/analyse/ergebnis.html">
                <b>SEO</b> seitenreport.de
            </a>
            <a target=_blank target=_blank href="http://browsershots.org/">
                <b>Layout: Browser screenshots</b> browsershots.org
            </a>

        	<style>
              .Links a {
                  display:block;
                  padding:5px;
              }
            </style>
        </div>
    </div>

    <div class="c1-box Links" style="flex-basis:440px">
        <div class=-head>SEO</div>
        <div class=-body>
            <b>robots.txt</b>
            <?php $S = $SET['robots.txt']; ?>
            <textarea oninput="$fn('SettingsEditor::set')(<?=$S->i?>,this.value)" style="width:100%; height:300px"><?=hee($S)?></textarea>
        </div>
        <a target=_blank href="/sitemap.xml">sitemap.xml</a>
        <table class=c1-style>
        	<tr>
        		<td> Title Prefix
        		<td>
                    <?php $S = $SET['html title prefix']; ?>
        			<input oninput="$fn('SettingsEditor::set')(<?=$S->i?>,this.value)" value="<?=hee($S)?>">
        	<tr>
        		<td> Title Suffix
        		<td>
                    <?php $S = $SET['html title suffix']; ?>
        			<input oninput="$fn('SettingsEditor::set')(<?=$S->i?>,this.value)" value="<?=hee($S)?>">
        </table>
    </div>

    <div class=c1-box style="flex-grow:0">
        <div class=-head>Tags</div>
        <div data-part="classes" style="max-height:500px; overflow:auto">
            <?php include 'parts/classes.php' ?>
        </div>
    </div>

    <div class=c1-box>
        <table class="c1-style">
            <thead>
                <tr class=c1-box-head>
                    <th> Webmaster Tools
                    <th> Authcodes
            <tbody>
                <tr>
                    <?php $S = $SET['webmaster code google']; ?>
                    <td><a target=_blank href="https://www.google.com/webmasters/tools/dashboard?hl=de&siteUrl=http://<?=$domain?>/&authuser=0">Google Webmaster Tools</a>
                    <td><input oninput="$fn('SettingsEditor::set')(<?=$S->i?>,this.value)" value="<?=hee($S)?>" placeholder="code (google.....html)">
                <tr>
                    <?php $S = $SET['webmaster code bing']; ?>
                    <td><a target=_blank href="https://www.bing.com/webmaster/home/dashboard?url=<?=hee('http://'.$domain)?>">Bing Webmaster Tools</a>
                    <td><input oninput="$fn('SettingsEditor::set')(<?=$S->i?>,this.value)" value="<?=hee($S)?>" placeholder="code">
                <tr>
                    <?php $S = $SET['webmaster code yandex']; ?>
                    <td><a target=_blank href="http://webmaster.yandex.com/site/">Yandex webmaster Tools</a>
                    <td><input oninput="$fn('SettingsEditor::set')(<?=$S->i?>,this.value)" value="<?=hee($S)?>" placeholder="code">
        </table>
        <br><br>
        <table class=c1-style>
            <thead>
                <tr>
                    <th> Analytics
                    <th> Authcodes
            <tbody>
                <tr>
                    <?php
                    $S = $SET['analytics code google'];
                    ?>
                    <td><a target=_blank href="https://www.google.com/analytics/web/?authuser=0">Google Analytics</a>
                    <td><input oninput="$fn('SettingsEditor::set')(<?=$S->i?>,this.value)" value="<?=$S?>" placeholder="UA-.........-..">
                <tr>
        </table>
    </div>

    <div class=c1-box>
      	<div class=-head>
            404 Requests
            <a href="<?=hee(Url()->addParam('404requestDeleteAll',1))?>"><button>alle löschen</button></a>
        </div>
        <?php
        if (isset($_GET['404requestDelete'])) {
            D()->query("DELETE FROM cms_cont_notfound WHERE request = ".D()->quote($_GET['404requestDelete']));
            header('location: '.Url(Page()->url())); exit;
        }
        if (isset($_GET['404requestDeleteAll'])) {
            D()->query("DELETE FROM cms_cont_notfound");
            header('location: '.Url(Page()->url())); exit;
        }
        $sql =
        "SELECT count(t.log_id) as num, max(t.log_id) as last_log, t.request " .
        "FROM cms_cont_notfound t 			" .
        "GROUP BY request 					" .
        "ORDER BY num DESC, last_log DESC 	" .
        "LIMIT 1000	";
        if (!$rows = D()->all($sql)) {
            echo '<div class=-body>keine Einträge</div>';
        } else { ?>
            <div style="overflow:auto; max-height:500px">
                <table class=c1-style>
                    <thead>
                      <tr>
                          <th> Anzahl Aufrufe
                          <th> Log-id
                          <th>
                          <th> Request
                    <tbody>
                    <?php
                    foreach ($rows as $vs) {
                      echo '<tr>';
                          echo '<td>';
                              echo $vs['num'];
                          echo '<td>';
                              echo $vs['last_log'];
                          echo '<td>';

                              echo '<a href="'.Url()->addParam('404requestDelete', $vs['request']).'"><img src="'.sysURL.'cms.frontend.1/pub/img/delete.svg" style="cursor:pointer; height:20px;"></a>';
                          echo '<td>';
                              $xss = strpos($vs['request'],'javascript') !== false || strpos($vs['request'],'script') !== false;
                              echo $xss ? '<span style="color:red">xss?</span> ' : '<a href="'.hee($vs['request']).'"> ';
                              echo hee($vs['request']);
                              echo !$xss ? '</a>' : '';
                              $Log = D()->log->Entry($vs['last_log']);
                              if (!$Log->is()) {
                                echo '<br>no log entry!';
                              } else {
                                $referer = $Log->referer;
                                echo $referer ? '<br>Referer: <a href="'.hee($referer).'">'.hee($referer).'</a>' : '';
                                $browser = D()->client->Entry($Log->Sess()->client_id)->browser;
                                echo $browser ? '<br>Browser: '.hee($browser) : '';
                              }
                    }
                    ?>
                </table>
            </div>
    	<?PHP } ?>
    </div>

    <div class=c1-box style="flex:600px">
        <div class=-head>Recent requests</div>
        <?php
        $sql =
        " SELECT log.* ".
        "   ,sess.ip ".
        "   ,client.browser ".
        "   ,usr.id as usr_id ".
        "   ,usr.email ".
        " FROM log ".
        "  LEFT JOIN sess ON log.sess_id = sess.id ".
        "  LEFT JOIN usr ON sess.usr_id = usr.id ".
        "  LEFT JOIN client ON sess.client_id = client.id ".
        " WHERE 1 ".
        "  AND log.url NOT LIKE '".Url(appURL)."dbFile%' ".
        //    "  AND sess.usr_id != ".Usr(). // performance!!
        //    "  AND client.browser NOT LIKE '%bot%' ".
        //    "  AND client.browser NOT LIKE '%libwww-perl%' ".
        //    "  AND client.browser NOT LIKE '%Java/%' ".
        " ORDER BY log.id DESC LIMIT 1000 ";
        ?>
        <div style="max-height:500px; overflow:auto;">
            <table class=c1-style style="font-size:10px">
            	<thead>
        	    	<tr>
        	    		<th> Wann
        	    		<th> Url
        	    		<th>
        	    		<th> IP
        	    		<th> Browser
        	    <tbody>
        	    	<?php foreach (D()->Query($sql) as $data) {
                        if ($data['usr_id'] == Usr()->id) continue;
                        if (util::ua_is_bot($data['browser'])) continue;
                        ?>
            	    	<tr title="log_id: <?=$data['id']?>">
            	    		<td style="font-size:10px"> <?=strftime('%x %X',$data['time'])?>
            	    		<td style="word-break:break-all"> <div style="max-width:400px"><a href="<?=hee($data['url'])?>"><?=hee($data['url'])?></a></div>
            	    		<td>
            	    			<div style="max-width:400px; word-break:break-all">
            		    			<?= $data['email'] ? 'Usr: '.hee($data['email']).'<br>' : '' ?>
            		    			<?= $data['referer'] && !preg_match('/\/\/[^\/]*'.preg_quote($_SERVER['HTTP_HOST']).'\//',$data['referer']) ? 'Referer: '.hee($data['referer']) : '' ?>
            		    		</div>
            	    		<td> <?=$data['ip']?>
            	    		<td title="<?=hee($data['browser'])?>">
            		    		<?php
                                $browserInfo = util::ua_info($data['browser']);
                                echo $browserInfo['browser'].' '.$browserInfo['version'];
            		    		?>
        	    	<?php } ?>
        	</table>
        </div>
    </div>

</div>
