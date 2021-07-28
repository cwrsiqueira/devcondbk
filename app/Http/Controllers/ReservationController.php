<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Area;
use App\Models\AreaDisabledDay;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function getReservations()
    {
        $array = ['error' => '', 'list' => []];
        $daysHelper = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        $areas = Area::where('allowed', 1)->get();

        foreach ($areas as $area)
        {
            $dayList = explode(',', $area['days']);
            $dayGroups = [];

            // Adicionando primeiro dia
            $lastDay = \intval(\current($dayList));
            $dayGroups[] = $daysHelper[$lastDay];
            \array_shift($dayList);

            // Adicionando dias relevantes
            foreach ($dayList as $day)
            {
                if(intval($day) != $lastDay+1)
                {
                    $dayGroups[] = $daysHelper[$lastDay];
                    $dayGroups[] = $daysHelper[$day];
                }
                $lastDay = intval($day);
            }

            // Adicionando último dia
            $dayGroups[] = $daysHelper[\end($dayList)];

            // Juntando as datas (dia1-dia2)
            $dates = '';
            $close = 0;
            foreach($dayGroups as $group)
            {
                if($close === 0)
                {
                    $dates .= $group;
                }
                else
                {
                    $dates .= '-'.$group.',';
                }
                $close = 1 - $close;
            }

            $dates = \explode(',', $dates);
            array_pop($dates);

            // Adicionando o TIME
            $start = date('H:i', strtotime($area['start_time']));
            $end = date('H:i', strtotime($area['end_time']));

            foreach($dates as $key => $value)
            {
                $dates[$key] .= ' '.$start.' às '.$end;
            }

            $array['list'][] =
            [
                'id' => $area['id'],
                'cover' => asset('storage/'.$area['cover']),
                'title' => $area['title'],
                'dates' => $dates
            ];
        }

        return $array;
    }

    public function getDisabledDates($id)
    {
        $array = ['error' => '', 'list' => []];

        $area = Area::find($id);
        if($area)
        {
            // Dias disabled padrão
            $days = AreaDisabledDay::where('id_area', $id)->get();
            foreach($days as $day)
            {
                $array['list'][] = $day['day'];
            }

            // Dias disabled através do allowed
            $allowedDays = explode(',', $area['days']);
            $offDays = [];
            for($q=0;$q<7;$q++)
            {
                if(!in_array($q, $allowedDays))
                {
                    $offDays[] = $q;
                }
            }

            // Listar as datas disabled até 3 meses
            $start = time();
            $end = strtotime('+3 months');

            for($current = $start; $current < $end; $current = strtotime('+1 day', $current))
            {
                $wd = date('w', $current);
                if(in_array($wd, $offDays))
                {
                    $array['list'][] = date('Y-m-d', $current);
                }
            }
        }
        else
        {
            $array['error'] = 'Area inexistente';
        }

        return $array;
    }

    public function getTimes($id, Request $request)
    {
        $array = ['error' => '', 'list' => []];

        $validator = Validator::make
        (
            $request->all(),
            [
                'date' => 'required|date_format:Y-m-d',
            ]
        );

        if(!$validator->fails())
        {
            $date = $request->input('date');
            $area = Area::find($id);
            if($area)
            {
                $can = true;

                // Verificar DISABLED
                $existDisabledDay = AreaDisabledDay::where('id_area', $id)
                ->where('day', $date)
                ->count();
                if($existDisabledDay > 0)
                {
                    $can = false;
                }

                // Verificar ENABLE
                $allowedDays = explode(',', $area['days']);
                $weekday = date('w', strtotime($date));
                if(!in_array($weekday, $allowedDays))
                {
                    $can = false;
                }

                if($can)
                {
                    $start = \strtotime($area['start_time']);
                    $end = \strtotime($area['end_time']);
                    $times = [];

                    for($lastTime = $start; $lastTime < $end; $lastTime = strtotime('+1 hour', $lastTime))
                    {
                        $times = $lastTime;
                    }

                    print_r($times);
                }
            }
            else
            {
                $array['erros'] = 'Área inexistente';
            }
        }
        else
        {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }

    public function setReservation()
    {
        $array = ['error' => ''];

        return $array;
    }
}
