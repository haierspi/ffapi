<?php
namespace ff\helpers;

class System
{

    /**
     * 获取内存使用
     *
     * @return array
     * @Author HaierSpi haierspi@qq.com
     * @DateTime 2020-03-18
     */

    public static function memoryUsage()
    {
        $memoryUsage = (!function_exists('memory_get_usage')) ? '0' : memory_get_usage();
        $memoryUsageUnit = self::sizeConvert($memoryUsage);

        return [$memoryUsage, $memoryUsageUnit];
    }

    /**
     * 将大小转换成带单位
     *
     * @param [int] $size
     * @return string
     * @Author HaierSpi haierspi@qq.com
     * @DateTime 2020-03-18
     */
    public static function sizeConvert($size)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}
