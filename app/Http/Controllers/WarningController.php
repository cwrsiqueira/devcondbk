<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\Warning;
use App\Models\Unit;

class WarningController extends Controller
{
    public function getMyWarnings(Request $request)
    {
        $array = ['error'=>''];

        $property = $request->input('property');
        if($property)
        {
            $user = auth()->user();
            $unit = Unit::where('id', $property)->where('id_owner', $user['id'])->count();

            if($unit > 0)
            {
                $warnings = Warning::where('id_unit', $property)
                ->orderBy('datecreated', 'DESC')
                ->orderBy('id', 'DESC')
                ->get();

                foreach ($warnings as $warnkey => $warnvalue)
                {
                    $warnings[$warnkey]['datecreated'] = date('d/m/Y', strtotime($warnvalue['datecreated']));
                    $photoList = [];
                    $photos = explode(',', $warnvalue['photos']);
                    foreach ($photos as $photo)
                    {
                        if(!empty($photo))
                        {
                            $photoList[] = asset('storage/'.$photo);
                        }
                    }
                    $warnings[$warnkey]['photos'] = $photoList;
                }

                $array['list'] = $warnings;
            }
            else
            {
                $array['error'] = 'Usuário sem acesso a esta unidade.';
            }
        }
        else
        {
            $array['error'] = 'A propriedade é necessária.';
        }

        return $array;
    }

    public function addWarningFile(Request $request)
    {
        $array = ['error'=>''];

        $validator = Validator::make
        (
            $request->all(),
            [
                'photo' => 'required|file|mimes:jpg,png'
            ]
        );

        if(!$validator->fails())
        {
            $file = $request->file('photo')->store('public');
            $array['photo'] = asset(Storage::url($file));
        }
        else
        {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }

    public function setWarning(Request $request)
    {
        $array = ['error'=>''];
        $data = $request->all();

        $validator = Validator::make
        (
            $data,
            [
                'title' => 'required',
                'property' => 'required',
            ]
        );

        if(!$validator->fails())
        {
            $newWarning = new Warning();
            $newWarning->id_unit = $data['property'];
            $newWarning->title = $data['title'];
            $newWarning->status = 'IN_REVIEW';
            $newWarning->datecreated = NOW();

            if(!empty($data['list']) && \is_array($data['list']))
            {
                $photos = [];
                foreach ($data['list'] as $listitem) {
                    $url = \explode('/', $listitem);
                    $photos[] = end($url);
                }
                $newWarning->photos = \implode(',', $photos);
            }
            else
            {
                $newWarning->photos = '';
            }
            $newWarning->save();
        }
        else
        {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }
}
