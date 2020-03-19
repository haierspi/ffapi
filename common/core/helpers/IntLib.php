<?php
namespace ff\helpers;

class IntLib
{

    //64位状态码状态获取
    public static function getBinaryStatus($status, $position)
    {
        $t = $status & pow(2, $position - 1) ? 1 : 0;
        return $t;
    }

    //64位状态码设置
    public static function setBinaryStatus($position, $value, $baseon = null)
    {
        $t = pow(2, $position - 1);
        if ($value) {
            $t = $baseon | $t;
        } elseif ($baseon !== null) {
            $t = $baseon & ~$t;
        } else {
            $t = ~$t;
        }
        return $t & 0xFFFFFFFF;
    }

    public static function setBinaryStatusByArray($positions, $values, $baseon = null)
    {
        
        foreach ($positions as $key => $position) {
            if(!is_array($values)){
                $value = $values;
            }else{
                $value = $values[$key];
            }
            $baseon = self::setBinaryStatus($position, $value, $baseon);
        }
        return $baseon;
    }

    //$intval = setstatus(6, 0, $intval);

    public static function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $EARTH_RADIUS = 6378.137;
        $radLat1 = self::rad($lat1);
        $radLat2 = self::rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = self::rad($lng1) - self::rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * $EARTH_RADIUS;
        $s = round($s * 10000) / 10000;
        return $s;
    }
    private static function rad($d)
    {
        return $d * M_PI / 180.0;
    }

}
