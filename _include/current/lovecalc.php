<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class lovecalc
{

    function __construct($firstname, $secondname)
    {
        
        $this->lovename = mb_strtolower(preg_replace("/ /", "", strip_tags(trim($firstname . $secondname))),'UTF-8');

        $alp = $this->mb_count_chars($this->lovename);
        ksort($alp);
        
        $s='';
        foreach($alp as $k=>$v){
            $s.=$v;
        }
        
        $calc=str_split($s);

        while (($letterNumber = count($calc)) > 2) {
            $letterCenter = ceil($letterNumber / 2);
            for ($i = 0; $i < $letterCenter; $i++) {
                // Just a little bit SHIFT :D
                $sum = array_shift($calc) + array_shift($calc);
                $quantity = strlen($sum);
                if ($quantity < 2) {
                    $calcmore[] = $sum;
                } else {
                    for ($a = 0; $a < $quantity; $a++) {
                        $calcmore[] = substr($sum, $a, 1);
                    }
                }
            }
            $anzc = count($calcmore);
            for ($b = 0; $b < $anzc; $b++) {
                $calc[] = $calcmore[$b];
            }
            array_splice($calcmore, 0);
        }

        $this->lovestat = $calc[0] . $calc[1];
    }

    function getlove()
    {
        return $this->lovestat;
    }

    function showlove()
    {
        return "<table height=40 width=43><tr><td background=3.gif style='font: 15px Verdana, Geneva, Arial, Helvetica, sans-serif; color: yellow;' align=center><b>$this->lovestat</b></td></tr></table>";
    }

    public function mb_count_chars($input)
    {
        $length = mb_strlen($input, 'UTF-8');
        $unique = array();
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($input, $i, 1, 'UTF-8');
            $utf8Code=$char;
            if (!array_key_exists($utf8Code, $unique)) {
                $unique[$utf8Code] = 0;
            }
            $unique[$utf8Code] ++;
        }
        return $unique;
    }
}
