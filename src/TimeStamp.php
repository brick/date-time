<?php
namespace Brick\DateTime;
class TimeStamp{
    public static function current_timestamp(){
        return time();
    }

    public static function current_timestamp_str():string{
        return  strval(time());
    }
}
