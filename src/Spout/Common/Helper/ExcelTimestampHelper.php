<?php

/**
 * ExcelTimestampHelper
 * 
 * Description of DateHelper
 *
 * @copyright (c) Expression year is undefined on line 7, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 07.02.2019
 */

namespace Box\Spout\Common\Helper;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * ExcelTimestampHelper
 * 
 * Description of DateHelper
 *
 * @copyright (c) Expression year is undefined on line 21, column 21 in Templates/Scripting/PHPClass.php., Contilla GmbH
 * @author Oliver Friedrich <friedrich@contilla.de>
 * @version 1.0, 07.02.2019
 */
class ExcelTimestampHelper {

    /**
     * Base date of 1st Jan 1900 = 1.0
     */
    const CALENDAR_WINDOWS_1900 = 1900;

    /**
     * Base date of 2nd Jan 1904 = 1.0
     */
    const CALENDAR_MAC_1904 = 1904;

    /**
     * Base calendar year to use for calculations
     * Value is either CALENDAR_WINDOWS_1900 (1900) or CALENDAR_MAC_1904 (1904).
     *
     * @var int
     */
    protected static $excelCalendar = self::CALENDAR_WINDOWS_1900;

    /**
     * Default timezone to use for DateTime objects.
     *
     * @var null|DateTimeZone
     */
    protected static $defaultTimeZone;

    /**
     * Set the Excel calendar (Windows 1900 or Mac 1904).
     *
     * @param int $baseDate Excel base date (1900 or 1904)
     *
     * @return bool Success or failure
     */
    public static function setExcelCalendar($baseDate) {
        if (($baseDate == self::CALENDAR_WINDOWS_1900) ||
                ($baseDate == self::CALENDAR_MAC_1904)) {
            self::$excelCalendar = $baseDate;

            return true;
        }

        return false;
    }

    /**
     * Return the Excel calendar (Windows 1900 or Mac 1904).
     *
     * @return int Excel base date (1900 or 1904)
     */
    public static function getExcelCalendar() {
        return self::$excelCalendar;
    }

    /**
     * Set the Default timezone to use for dates.
     *
     * @param DateTimeZone|string $timeZone The timezone to set for all Excel datetimestamp to PHP DateTime Object conversions
     *
     * @throws Exception
     *
     * @return bool Success or failure
     * @return bool Success or failure
     */
    public static function setDefaultTimezone($timeZone) {
        if ($timeZone = self::validateTimeZone($timeZone)) {
            self::$defaultTimeZone = $timeZone;

            return true;
        }

        return false;
    }

    /**
     * Return the Default timezone being used for dates.
     *
     * @return DateTimeZone The timezone being used as default for Excel timestamp to PHP DateTime object
     */
    public static function getDefaultTimezone() {
        if (self::$defaultTimeZone === null) {
            self::$defaultTimeZone = new DateTimeZone('UTC');
        }

        return self::$defaultTimeZone;
    }

    /**
     * Convert a MS serialized datetime value from Excel to a PHP Date/Time object.
     *
     * @param float|int $excelTimestamp MS Excel serialized date/time value
     * @param null|DateTimeZone $timeZone The timezone to assume for the Excel timestamp, if you don't want to treat it as a UTC value use the default (UST) unless you absolutely need a conversion
     *
     * @throws Exception
     *
     * @return DateTime PHP date/time object
     */
    public static function toDatetime($excelTimestamp, $timeZone = null) {

        if ($timeZone === null) {
            $timeZone = self::getDefaultTimezone();
        } elseif (!($timeZone instanceof DateTimeZone)) {
            throw new Exception('$timeZone must be of type DateTimeZone or NULL');
        }

        if ($excelTimestamp < 1.0) {
            // Unix timestamp base date
            $baseDate = new DateTime('1970-01-01', $timeZone);
        } else {
            // MS Excel calendar base dates
            if (self::$excelCalendar == self::CALENDAR_WINDOWS_1900) {
                // Allow adjustment for 1900 Leap Year in MS Excel
                $baseDate = ($excelTimestamp < 60) ? new DateTime('1899-12-31', $timeZone) : new DateTime('1899-12-30', $timeZone);
            } else {
                $baseDate = new DateTime('1904-01-01', $timeZone);
            }
        }

        $days = floor($excelTimestamp);
        $partDay = $excelTimestamp - $days;
        $hours = floor($partDay * 24);
        $partDay = $partDay * 24 - $hours;
        $minutes = floor($partDay * 60);
        $partDay = $partDay * 60 - $minutes;
        $seconds = round($partDay * 60);

        if ($days >= 0) {
            $days = '+' . $days;
        }
        $interval = $days . ' days';

        return $baseDate->modify($interval)
                        ->setTime($hours, $minutes, $seconds);
    }

    /**
     * Convert a PHP DateTime object to an MS Excel serialized date/time value.
     *
     * @param DateTimeInterface $dateValue PHP DateTime object
     *
     * @return float MS Excel serialized date/time value
     */
    public static function getExcelTimestamp(DateTimeInterface $dateValue) {
        return self::formattedPHPToExcel(
                        $dateValue->format('Y'),
                        $dateValue->format('m'),
                        $dateValue->format('d'),
                        $dateValue->format('H'),
                        $dateValue->format('i'),
                        $dateValue->format('s')
        );
    }

    /**
     * Create a excel timestamp value for given datetime parts
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     *
     * @return float Excel date/time value
     */
    private static function encodeExcelTimestamp($year, $month, $day, $hours = 0, $minutes = 0, $seconds = 0) {
        if (self::$excelCalendar == self::CALENDAR_WINDOWS_1900) {
            //
            //    Fudge factor for the erroneous fact that the year 1900 is treated as a Leap Year in MS Excel
            //    This affects every date following 28th February 1900
            //
            $excel1900isLeapYear = true;
            if (($year == 1900) && ($month <= 2)) {
                $excel1900isLeapYear = false;
            }
            $myexcelBaseDate = 2415020;
        } else {
            $myexcelBaseDate = 2416481;
            $excel1900isLeapYear = false;
        }

        //    Julian base date Adjustment
        if ($month > 2) {
            $month -= 3;
        } else {
            $month += 9;
            --$year;
        }

        //    Calculate the Julian Date, then subtract the Excel base date (JD 2415020 = 31-Dec-1899 Giving Excel Date of 0)
        $century = substr($year, 0, 2);
        $decade = substr($year, 2, 2);
        $excelDate = floor((146097 * $century) / 4) + floor((1461 * $decade) / 4) + floor((153 * $month + 2) / 5) + $day + 1721119 - $myexcelBaseDate + $excel1900isLeapYear;

        $excelTime = (($hours * 3600) + ($minutes * 60) + $seconds) / 86400;

        return (float) $excelDate + $excelTime;
    }

}
