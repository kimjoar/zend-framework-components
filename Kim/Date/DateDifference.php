<?php
/**
 * Zend Framework
 *
 * @category   Kim
 * @package    Kim_Date
 * @subpackage Kim_Date_DateDifference
 * @author     Kim Joar Bekkelund <mail@kimjoar.net>
 */

/**
 * Include needed Date classes
 */
require_once 'Zend/Date/Exception.php';

/**
 * Get the relative difference between to dates
 *
 * Accepts both Unix timestamps and regular dates, and also in some cases
 * discrete values between dates. It accepts all values that the PHP function
 * strtotime accepts.
 *
 * Examples of accepted dates:
 * - 11.12.2007 (Will it do this correct?)
 * - 15
 * - tomorrow
 * - 11. dec 2008
 *
 * @see http://www.php.net/strtotime
 */
class Kim_Date_RelativeDifference
{
    /**
     * Seconds between two given dates
     * @var int
     */
    private static $_secondDifference;

    /**
     * Date from
     * @var mixed
     */
    private static $_from;

    /**
     * Date to
     * @var mixed
     */
    private static $_to;

    /**
     * A discrete set of dates, using the index as the lowest amount of seconds
     * for the given value.
     *
     * E.g. between 0 and 60, we want to show the number as seconds, and
     * between 60 and 3600 we want to show the number as minutes, and so on.
     *
     * Remarks:
     * - This is a Norwegian translation.
     * - A month is 28 days (7 days/week * 4 weeks)
     * - A year is 365 days, and we do not check for leap years.
     * These choices have been done to ease the mathematical calculation.
     */
    private static $_discreteDates = array(
        0        => array('1 sekund', '%d sekunder'),
        60       => array('1 minutt', '%d minutter'),
        // 60 * 60
        3600     => array('1 time'  , '%d timer'   ),
        // 60 * 60 * 24
        86400    => array('1 dag'   , '%d dager'   ),
        // 60 * 60 * 24 * 7
        604800   => array('1 uke'   , '%d uker'    ),
        // 60 * 60 * 24 * 7 * 4
        2419200  => array('1 måned' , '%d måneder' ),
        // 60 * 60 * 24 * 365
        31536000 => array('1 år'    , '%d år'      )
    );

    /**
     * Get the difference between to given dates, specified according to a
     * discrete set of dates.
     *
     * The dates can only be Unix timestamps. You can use strtotime to parse
     * about any English textual datetime description into a Unix timestamp.
     * This will be changed in a later version to allow actual dates as input.
     *
     * @todo Make this work on dates from a given date to a given date. This
     *       way we can create date differences as e.g. "yesterday",
     *       "last week", ...
     * @todo Test how this works with time zones.
     * @todo Work together with ZF's DateObject
     *
     * @param  string $date1 Unix timestamp 1
     * @param  string $date2 Unix timestamp 2
     */
    public static function getDifference($fromDate, $toDate)
    {
        /**
         * Get the Unix timestamp of the input dates
         */
        self::$_from = self::getUnixTimestamp($fromDate);
        self::$_to   = self::getUnixTimestamp($toDate);

        self::$_secondDifference = abs(self::$_from - self::$_to);

        /**
         * Sort the discrete dates on descending index, because our
         * implementation depends on it.
         */
        krsort(self::$_discreteDates);

        /**
         * Loop through the discrete dates starting with the largest date. As
         * soon as we find the first index that is smaller that the given
         * difference in seconds, we have found the correct value.
         *
         * @todo This can be explained better. Show a set of number, and how it
         *       works.
         */
        foreach (self::$_discreteDates AS $seconds => $formats)
        {
            if (self::$_secondDifference < $seconds) {
                continue;
            }

            //        if (self::$_secondDifference >= $seconds) {
            // Watch out so we don't try to divide by 0. We solve this by
            // setting the value 1, which keep the number as is.
            $seconds = $seconds == 0 ? 1 : $seconds;

            $amount = self::$_secondDifference/$seconds;

            // Want to use singular forms of nouns if the difference is less
            // than two of some timeunit.
            $difference = ($amount < 2 ? $formats[0] : sprintf($formats[1], $amount));

            // Ends execution of the current foreach since we have found the
            // correct value
            break;
            //        }
        }

        if (!isset($difference)) {
            throw new Zend_Date_Exception("Difference not found.");
        }
        return $difference;
    }

    /**
     * Get Unix timestamp for given date
     *
     * @todo How can we check that the incomming dates are Unix timestamps
     *       already? strtotime can't handle Unix timestamps. If we can check
     *       for this, we can change the function to receive actual dates
     *       instead.
     *       Possible solutions:
     *       - What about checking if the incomming date is an integer. If it
     *         is, we can safely assume that the date is a Unix timestamp.
     * @todo Make sure that an incomming Unix timestamp is not too large
     *
     * @param  mixed $date
     * @return int   Unix timestamp
     */
    private static function getUnixTimestamp($date)
    {
        /**
         * If the date is an integer we can safely assume that it is a Unix
         * timestamp, if not we have to make it a Unix timestamp.
         */
        $unixTimestamp = $date;

        if (!is_int($date)) {
           $unixTimestamp = strtotime($date);
        }

        /**
         * strtotime returns FALSE on failure. Check for this and return if the
         * date could not be parsed into a Unix timestamp.
         *
         * As of PHP 5.1.0 strtotime returns FALSE on failure, instead of -1, so
         * remember to check PHP version is an error occurs.
         */
        if (false === $unixTimestamp) {
            throw new Zend_Date_Exception('Date could not be changed to Unix timestamp.');
        }
        return $unixTimestamp;
    }
}
