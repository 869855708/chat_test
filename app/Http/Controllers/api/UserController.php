<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

//    public function __construct()
//    {
//        $this->middleware('auth:api', ['except' => ['login']]);
//    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('name', 'password');

        if (count($credentials) < 2) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('name', $credentials['name'])
            ->where('password', ($credentials['password']))
            ->first();

        if (empty($user) || !$token = JWTAuth::fromUser($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    /**
     * 注册
     * @return void
     */
    public function register(AuthRequest $request)
    {
            // 获取通过验证的数据...
            $validated = $request->validated();

            dd($validated);
    }

    public function show()
    {

        return 112;
    }
}
