<?php
/**
 * Created by PhpStorm.
 * User: mdman
 * Date: 12/25/2018
 * Time: 1:08 PM
 */

require 'vendor/autoload.php';
use Business\Day;
use Business\Days;
use Business\Business;
use Business\SpecialDay;

// Opening hours for each week day. If not specified, it is considered closed
$days = [
    // Standard days with fixed opening hours
    new Day(Days::MONDAY, [['09:00', '13:00'], ['2pm', '5 PM']]),
    new Day(Days::TUESDAY, [['9 AM', '10 AM']]),
    new Day(Days::WEDNESDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
    new Day(Days::THURSDAY, [['10 AM', '5 PM']]),

    // Special day with dynamic opening hours depending on the date
    new SpecialDay(Days::FRIDAY, function (\DateTime $date) {
        if ('2015-05-29' === $date->format('Y-m-d')) {
            return [['9 AM', '12:00']];
        }

        return [['9 AM', '5 PM']];
    }),
];

// Optional holiday dates
$holidays = [new \DateTime('2018-12-24'), new \DateTime('2018-12-23')];

// Optional business timezone
$timezone = new \DateTimeZone('Asia/Kolkata');

// Create a new Business instance
//$business = new Business($days, $holidays, $timezone);






//$sunday = new \DateTime();
// The closest date after Sunday is Wednesday at opening time "2015-06-03 10:00:00"
// because Monday and Tuesday are holidays
//$closestNext = $business->closest($sunday);

// The closest date before Sunday is Friday at closing time "2015-05-29 12:00:00"
//$closestLast = $business->closest($sunday, Business::CLOSEST_LAST);
//echo date_default_timezone_get();

//echo date_format($closestNext,"Y/m/d H:i:s");


//echo  date("c");

