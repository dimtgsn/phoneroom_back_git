<?php

namespace App\Utilities;

use Carbon\Carbon;

class DateFormatting
{
    static function format($date){
        $arr = [
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря'
        ];
        return (Carbon::parse($date)->format('d') < 10 ? Carbon::parse($date)->format('d') % 10 : Carbon::parse($date)->format('d'))
            .' '.
            $arr[Carbon::parse($date)->format('m')-1]
            .' '.
            Carbon::parse($date)->format('Y')
            .' г.';
    }

}