<?php

/** @noinspection PhpLanguageLevelInspection */

/**
 * Created by PhpStorm.
 * User: liuzeyu
 * Date: 2018/11/21
 * Time: 17:15
 */

namespace Component\Library;

use CalendarAlertModel;
use DateInterval;
use DateTime;
use Exception;

class LunarHelper
{
    /**
     * @var Lunar
     */
    static $lunar;
    static $defaultString = '1970-01-01 08:00:00';

    /**
     * 通过 Entity 获取下一个提醒日期
     * @param CalendarAlertModel $entity
     * @return string 时间字符串
     * @throws Exception
     */
    public static function getNextAlert($entity) {
        \date_default_timezone_set('PRC');
        if ($entity->get_status() != 1 && $entity->get_status() != 0) {
            return self::$defaultString;
        }

        $startTime = $entity->get_start_time();
        $startTimestamp = (new DateTime($startTime))->format("U");
        $endTime = $entity->get_end_time();
        $period = $entity->get_period();

        /*
         * **********************************
         * 单次执行
         * **********************************
         */
        if ($entity->get_status() == 0) {
            // 单次执行
            return max($date = date('Y-m-d H:i:s'), $entity->get_start_time());
        }

        /*
         * **********************************
         * 停止执行
         * **********************************
         */
        if ($entity->get_status() == 2
            || ($entity->get_cycle_type() == 0 && (new DateTime($endTime))->format("U") < time())) {
            // 停止执行或已停止
            return self::$defaultString;
        }

        /*
         * **********************************
         * 按日或周执行，循环周期固定
         * **********************************
         */
        $cycleTime = 0;
        switch ($entity->get_cycle_type()) {
            case 1:
                // 按日循环
                $cycleTime = $period * 24 * 3600;
                break;
            case 2:
                // 按周循环
                $cycleTime = $period * 7 * 24 * 3600;
                break;
            default:
                break;
        }
        if ($cycleTime != 0) {
            while ($startTimestamp <= (new DateTime($endTime))->format("U")) {
                if ($startTimestamp >= time()) {
                    $dateobj = new DateTime("@".$startTimestamp);
                    $dateobj->setTimezone(timezone_open('Asia/HONG_KONG'));
                    return $dateobj->format("Y-m-d H:i:s");
                } else {
                    $startTimestamp += $cycleTime;
                }
            }
            return $startTime;
        }

        /*
         * **********************************
         * 按月或年执行，需考虑阴历及闰月
         * **********************************
         */
        $loopcount = 0;
        if ($entity->get_cycle_type() == 3 || $entity->get_cycle_type() == 4) {
            while ($startTimestamp <= (new DateTime($endTime))->format("U")) {
                $loopcount++;
                if ($startTimestamp >= time()) {
                    $dateobj = new DateTime("@".$startTimestamp);
                    $dateobj->setTimezone(timezone_open('Asia/HONG_KONG'));
                    return $dateobj->format("Y-m-d H:i:s");
                } else {
                    if ($entity->get_lunar() == 0) {
                        // 阳历
                        $date = date_create(date('Y-m-d H:i:s', $startTimestamp));
                        if ($entity->get_cycle_type() == 3) {
                            $date->add(new DateInterval('P'.$period.'M'));
                        } else {
                            $date->add(new DateInterval('P'.$period.'Y'));
                        }
                        $startTimestamp = $date->format("U");
                    } else {
                        // 阴历
                        if (!isset(self::$lunar)) {
                            self::$lunar = new Lunar();
                        }
                        $year = date('Y', $startTimestamp);
                        $month = date('m', $startTimestamp);
                        $date = date('d', $startTimestamp);
                        $extra = date('H:i:s', $startTimestamp);
                        $lunardate = self::$lunar->convertSolarToLunar($year, $month, $date);

                        if ($entity->get_cycle_type() == 4) {
                            $leapmonth = self::$lunar->getLeapMonth($year);
                            $month = $leapmonth != 0 && $leapmonth < $lunardate[4] ? $lunardate[4] - 1 : $lunardate[4];
                            $year = $lunardate[0] + $period;
                        } else {
                            $year = $lunardate[0];
                            $month = $lunardate[4] + $period;
                        }
                        $date = $lunardate[5];

                        // 闰月处理
                        $leapmonth = self::$lunar->getLeapMonth($year);
                        if ($leapmonth != 0 && $leapmonth < $month and $entity->get_cycle_type() == 4) {
                            $month++;
                        }

                        $solararray = self::$lunar->convertLunarToSolar($year, $month, $date);
                        $startTimestamp = strtotime($solararray[0].'-'.$solararray[1].'-'.$solararray[2].' '.$extra);
                    }
                }
            }
            return self::$defaultString;
        }

        /*
         * **********************************
         * 按工作日执行
         * **********************************
         */
        if ($entity->get_cycle_type() == 5) {
            while($startTimestamp <= (new DateTime($endTime))->format("U")) {
                $jsoninfo = file_get_contents("http://api.goseek.cn/Tools/holiday?date="
                    .date('Ymd', $startTimestamp));
                $info = json_decode($jsoninfo, true);
                if (empty($info)) {
                    return $startTime;
                }
                if ($info['data'] == 0 && $startTimestamp >= time()) {
                    if (--$period === 0) {
                        return date('Y-m-d H:i:s', $startTimestamp);
                    }
                }
                $startTimestamp += 24 * 3600;
            }
            return self::$defaultString;
        }

        /*
         * **********************************
         * 传入数据错误兜底
         * **********************************
         */
        return self::$defaultString;
    }

    /**
     * @param String $date 日期字符串
     * @return string
     * @throws Exception
     */
    public static function getLunarDate($date) {
        if (!isset(self::$lunar)) {
            self::$lunar = new Lunar();
        }
        $timestamp = (new DateTime($date))->format("U");
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        $date = date('d', $timestamp);
        $solar = self::$lunar->convertSolarToLunar($year, $month, $date);
        return $solar[0].'-'.$solar[1].'-'.$solar[2].' '.date('H:i:s', $date);
    }
}
