<?php
namespace Brick\DateTime;
class TimeStamp{
    // current timestamp
    public static function current_timestamp(){
        return time();
    }
    
    // current timestamp in string
    public static function current_timestamp_str():string{
        return  strval(time());
    }

    // create new timestamp
    public function createTimestamp($day, $month, $year, $hour, $minute, $second) {
        $dateTimeString = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
        $timestamp = strtotime($dateTimeString);
        return $timestamp;
    }
}
