<?php
namespace ff\os;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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

    /**
     * PHP调用shell环境 检查 进程脚本是否存活
     *
     * @param [type] $progressName
     * @return void
     * @Author HaierSpi haierspi@qq.com
     * @DateTime 2020-03-19
     */
    public static function progressIsExists($progressName, $excludeSelf = true)
    {
        $process = new ProcessInfo();

        if (empty($process->getCurrentProcessesByCommand($progressName, $excludeSelf))) {
            return false;
        } else {
            return true;
        }
    }

    public static function progressIsExistsByControllerAction( $excludeSelf = true)
    {
       return self::progressIsExists(\ff::$app->router->actionMethod, $excludeSelf);
    }


}
