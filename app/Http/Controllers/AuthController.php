<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Unit;

class AuthController extends Controller
{
    public function unauthorized()
    {
        return response()->json([
            'error' => 'Não Autorizado'
        ], 401);
    }

    public function register(Request $request)
    {
        $array = ['error'=>''];

        $data = $request->all();

        $validator = Validator::make
        (
            $data,
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'cpf' => 'required|digits:11|unique:users,cpf',
                'password' => 'required',
                'password_confirm' => 'required|same:password',
            ]
        );

        if(!$validator->fails())
        {
            $newUser = new User();
            $newUser->name = $data['name'];
            $newUser->email = $data['email'];
            $newUser->cpf = $data['cpf'];
            $newUser->password = \password_hash($data['password'], PASSWORD_DEFAULT);
            $newUser->save();

            $token = auth()->attempt
            (
                [
                    'cpf' => $data['cpf'],
                    'password' => $data['password']
                ]
            );

            if(!$token)
            {
                $array['error'] = 'Ocorreu um erro!';
                return $array;
            }

            $array['token'] = $token;

            $user = auth()->user();
            $array['user'] = $user;

            $properties = Unit::select(['id', 'name'])
            ->where('id_owner', $user['id'])
            ->get();

            $array['user']['properties'] = $properties;
        }
        else
        {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }

    public function login(Request $request)
    {
        $array = ['error'=>''];

        $data = $request->all();

        $validator = Validator::make
        (
            $data,
            [
                'cpf' => 'required|digits:11',
                'password' => 'required',
            ]
        );

        if(!$validator->fails())
        {
            $token = auth()->attempt
            (
                [
                    'cpf' => $data['cpf'],
                    'password' => $data['password']
                ]
            );

            if(!$token)
            {
                $array['error'] = 'CPF e/ou senha inválidos!';
                return $array;
            }

            $array['token'] = $token;

            $user = auth()->user();
            $array['user'] = $user;

            $properties = Unit::select(['id', 'name'])
            ->where('id_owner', $user['id'])
            ->get();

            $array['user']['properties'] = $properties;
        }
        else
        {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }
}
