<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class dump {
    static function html($x) {
        echo '<pre style="font-size:10px; color:#000; overflow:auto; max-width:100%; background:rgba(255,255,100,.4); padding:4px;">';
        echo hee(var_export($x,1));
        echo '</pre>';
    }
    static function h($x,$return=false){
        $str =
        '<pre style="font-size:10px; color:#000; overflow:auto; max-width:100%">'.
        self::_h($x).
        '</pre>';
        if ($return) return $str;
        else echo $str;
    }
    static function _h($x){
        if (is_array($x)) {
            $str = '<table style="border-collapse:collapse">';
            foreach ($x as $n => $v) {
                $str .= '<tr>';
                $str .= '<td style="padding:0; border:1px solid #fff; background:rgb(255,200,200)">'.hee($n);
                $str .= '<td style="padding:0; border:1px solid #fff; background:rgb(240,240,200)">'.self::_h($v);
            }
            $str .= '</table>';
            return $str;
        } else {
            return hee($x);
        }
    }
}
