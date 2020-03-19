<?php
namespace ff\helpers;

class Time
{

    /**
     * 求两个日期之间相差的天数
     * (针对1970年1月1日之后，求之前可以采用泰勒公式)
     * @param string $day1
     * @param string $day2
     * @return number
     */
    public static function diffBetweenTwoDays($afterDate, $beforeDate)
    {
        $datetimeAfter = new \DateTime($afterDate);
        $datetimeBefore = new \DateTime($beforeDate);
        return $datetimeAfter->diff($datetimeBefore)->days;

    }
}
