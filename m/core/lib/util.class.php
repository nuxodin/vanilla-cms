<?php
namespace qg;

class util {
    static function extractEmails($txt){
        $emails = [];
        foreach (preg_split('/[\s,;]+/',$txt) as $email) {
            if (strpos($email, '@') === false) continue;
            $emails[$email] = 1;
        }
        return array_keys($emails);
    }
    static function byte_format($file_size){
        $index = 0;
    	$units = ['Bytes','KB','MB','GB','TB'];
    	while ($file_size > 400) {
    		$file_size /= 1024;
    		$index++;
    	}
        return round($file_size,1).' '.$units[$index];
    }
    static function optionsFromArray($array, $selected=[], $use_index=true) {
    	$selected = (array)$selected;
    	$str = '';
    	foreach ($array as $i => $v) {
    		$value = $use_index ? (string)$i : $v; // new: (string)$i else, 0 is in [''=>'x'];
    		$select = in_array($value, $selected) ? 'selected' : '';
    		$str .= '<option value="'.hee($value).'" '.$select.'>'.hee($v).'</option>';
    	}
    	return $str;
    }
    static function sqlSearchHelper($search, $fields) {
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
    static function cutText($txt, $maxLength = 200, $minLength = 0, $ending = '…') {
    	$txt = (string)$txt;
    	if (strlen($txt) < $maxLength) return $txt;
    	while (substr($txt,$maxLength-1,1) != ' ' && $maxLength > $minLength)
    		$maxLength--;
    	return substr($txt, 0, $maxLength).$ending;
    }
    static function stripTags($text){
        //$text = preg_replace('#<script(.*?)/script>#ism',' ',$text);
        //$text = preg_replace('#<style(.*?)/style>#ism',' ',$text);
        $text = preg_replace('#\s+</h[1-6]>#ism',': ',$text);
        //$text = preg_replace('#</(p|div)>#ism',' ',$text);
        $text = preg_replace('#<br[ /]*>#ism',' ',$text);
        // Remove invisible content
        $text = preg_replace([
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu',
            ],' ',$text);
        // Add line breaks before and after blocks
        $text = preg_replace([
                '@</?((address)|(blockquote)|(center)|(del))@iu',
                '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                '@</?((table)|(th)|(td)|(caption))@iu',
                '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                '@</?((frameset)|(frame)|(iframe))@iu',
            ],"\n\$0",$text);
      	$text = trim(strip_tags($text));
      	//$text = preg_replace('# #ism',' ',$text); ???
        //$text = preg_replace('#\s+#ism',' ',$text);
        $text = preg_replace("#\n+\s+#ism","\n",$text);
        $text = preg_replace("# +#ism"," ",$text);
        return $text;
    }

    static function ua_is_bot($ua){
        $contains = ['bot','libwww-perl','Java','spider','Outlook','perl','Yahoo','Headless','Check','Validator','search','Go-http','Python','Node.js','rawler','Zombie.js','Google Page Speed Insights'];
        foreach ($contains as $contain) {
            if (strpos($ua, $contain) !== false) return true;
        }
        if (strpos($ua, '/') === false) return true;
    }
    static function ua_info($userAgent){
        $ua = str_replace('Mozilla/', '', $userAgent);
        $ua = str_replace('Gecko/', '', $ua);
        $ua = str_replace('AppleWebKit/', '', $ua);
        $ua = str_replace('Version/', 'Safari/', $ua);
        $ua = str_replace('MSIE ', 'IE/', $ua);
        $ua = preg_replace('/ Chrome.*Edge\//', 'Edge/', $ua);
        preg_match('/([a-zA-Z]+)\/([0-9\.]+)/', $ua, $matches); // every version a dot?
    	$browser = 'other';
        $version = null;
    	if ($matches) {
    		list(,$browser,$version) = $matches;
    		if ($browser =='Trident') {
    			$browser = 'IE';
    			if (preg_match('/rv:([0-9.]+)/',$ua,$tmp)) $version = $tmp[1];
    		}
    	}
        return [
            'browser' => $browser,
            'version' => $version,
            //'bot'     => self::ua_is_bot($userAgent),
        ];
    }
    static function niceDate($ts, $options=[]/*todo*/){
        // https://stackoverflow.com/questions/2690504/php-producing-relative-date-time-from-timestamps
        if (!ctype_digit($ts)) $ts = strtotime($ts);
        $diff = time() - $ts;
        if ($diff == 0)
            return 'now';
        elseif ($diff > 0) {
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 120) return $diff.' seconds ago';
                if ($diff < 86400) return 'Today '.date('H:i:s', $ts);
                // if ($diff < 60) return $diff.' seconds ago';
                // if ($diff < 120) return '1 minute ago';
                // if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
                // if ($diff < 7200) return '1 hour ago';
                // if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
            }
            if ($day_diff == 1) return 'Yesterday '.date('H:i:s', $ts);
            if ($day_diff < 3)  return strftime('%a %H:%M:%S', $ts);
            return strftime('%x %H:%M:%S', $ts);
            // if ($day_diff < 7) return $day_diff . ' days ago';
            // if ($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
            // if ($day_diff < 60) return 'last month';
            //return date('F Y', $ts);
        } else { // todo
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 120) return 'in a minute';
                if ($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
                if ($diff < 7200) return 'in an hour';
                if ($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
            }
            if ($day_diff == 1) return 'Tomorrow';
            if ($day_diff < 4) return date('l', $ts);
            if ($day_diff < 7 + (7 - date('w'))) return 'next week';
            if (ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
            if (date('n', $ts) == date('n') + 1) return 'next month';
            return date('F Y', $ts);
        }
    }
}
