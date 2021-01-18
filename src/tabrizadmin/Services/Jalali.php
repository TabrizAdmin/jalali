<?php

namespace tabrizadmin\Services;
use tabrizadmin\Jalali\jDate;

class Jalali
{
    protected $format;

    public function __construct($format = null)
    {
        $this->format = $format;
    }

    public function date($date)
    {
        $output = jDate::forge($date)->format($this->format);
        return $output;
    }

    public function dateAgo($date)
    {
        $output = jDate::forge($date)->ago();
        return $output;
    }

    public function convertDateTime($datetime)
    {
        $date = explode('-', $datetime);
        $day_or_time = explode(' ', $date[2]);
        $time = explode(':', $day_or_time[1]);
        $year = $this->convert($date[0]);
        $month = $this->convert($date[1]);
        $day = $this->convert($day_or_time[0]);
        $hour = $this->convert($time[0]);
        $second = $this->convert($time[1]);
        $mili_second = $this->convert($time[2]);
        $gregorian = $this->jalali_to_gregorian($year, $month, $day, false);
        $string = $gregorian[0].'-'.$gregorian[1].'-'.$gregorian[2].' '.$hour.':'.$second.':'.$mili_second;
        $time = strtotime($string);
        $output = date('Y-m-d H:i:s', $time);

        return $output;
    }

    public function convertSimpleDate($year, $month, $day, $hour, $min, $second)
    {
        $gregorian = $this->jalali_to_gregorian($year, $month, $day, false);
        $string = $gregorian[0].'-'.$gregorian[1].'-'.$gregorian[2].' '.$hour.':'.$min.':'.$second;
        $time = strtotime($string);
        $output = date('Y-m-d H:i:s', $time);

        return $output;
    }

    public function converDate($date)
    {
        $date = explode('-', $date);
        $day_or_time = explode(' ', $date[2]);
        $year = $this->convert($date[0]);
        $month = $this->convert($date[1]);
        $day = $this->convert($day_or_time[0]);
        $gregorian = $this->jalali_to_gregorian($year, $month, $day, false);
        $string = $gregorian[0].'-'.$gregorian[1].'-'.$gregorian[2];
        $time = strtotime($string);
        $output = date('Y-m-d', $time);

        return $output;
    }

    public function convertEnglish($string) {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١','٠'];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

        return $englishNumbersOnly;
    }

    public function convert($string) {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١','٠'];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($num, $persian, $string);
        $persionNumbersOnly = str_replace($num, $arabic, $convertedPersianNums);

        return $persionNumbersOnly;
    }

    private function div($a,$b) {
        return (int) ($a / $b);
    }

    private function jalali_to_gregorian($j_y, $j_m, $j_d,$str)
    {
        $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

        $jy = (int)($j_y)-979;
        $jm = (int)($j_m)-1;
        $jd = (int)($j_d)-1;

        $j_day_no = 365*$jy + $this->div($jy, 33)*8 + $this->div($jy%33+3, 4);

        for ($i=0; $i < $jm; ++$i)
            $j_day_no += $j_days_in_month[$i];

        $j_day_no += $jd;

        $g_day_no = $j_day_no+79;

        $gy = 1600 + 400*$this->div($g_day_no, 146097); /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */
        $g_day_no = $g_day_no % 146097;

        $leap = true;
        if ($g_day_no >= 36525) /* 36525 = 365*100 + 100/4 */
        {
            $g_day_no--;
            $gy += 100*$this->div($g_day_no,  36524); /* 36524 = 365*100 + 100/4 - 100/100 */
            $g_day_no = $g_day_no % 36524;

            if ($g_day_no >= 365)
                $g_day_no++;
            else
                $leap = false;
        }

        $gy += 4*$this->div($g_day_no, 1461); /* 1461 = 365*4 + 4/4 */
        $g_day_no %= 1461;

        if ($g_day_no >= 366) {
            $leap = false;

            $g_day_no--;
            $gy += $this->div($g_day_no, 365);
            $g_day_no = $g_day_no % 365;
        }

        for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++)
            $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
        $gm = $i+1;
        $gd = $g_day_no+1;
        if($str) return $gy.'/'.$gm.'/'.$gd ;
            return array($gy, $gm, $gd);
    }
}
