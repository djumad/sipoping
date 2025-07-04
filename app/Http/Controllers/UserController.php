<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserRespurce;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(UserLoginRequest $request){
        $data = $request->validated();
        
        $user = User::where("username", $data["username"])->first();
        
        if(!$user || !Hash::check($data["password"], $user->password)){
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "email dan password salah"
                    ]
                ]
            ] , 401));
        }

        $user->token = Str::uuid()->toString();

        $user->save();

        return new UserResource($user);
    }

    public function me(){
        $user = Auth::user();
        return new UserResource($user);
    }
    
}
