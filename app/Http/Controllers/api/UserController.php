<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

    /**
     * 登录
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"user"},
     *     summary="登录",
     *     @OA\Parameter(name="isFastLogin", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="tel", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="verification_code", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="password", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="  {err_code: int32, msg:string, data:[]}  "
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('name', 'password');

        if (count($credentials) < 2) {
            return response()->json(['error' => '参数缺少'], 401);
        }

        $user = User::where('name', $credentials['name'])->first();

        if (empty($user)) {
            return response()->json(['code' => 404, 'msg' => '密码错误或用户不存在', 'data' => []]);
        }

        if (!password_verify($credentials['password'], $user->password)) {
            return response()->json(['code' => 404, 'msg' => '密码错误或用户不存在', 'data' => []]);
        }

        if (!$token = JWTAuth::fromUser($user)) {
            return response()->json(['code' => 401, 'msg' => '获取用户令牌失败，请重新尝试!', 'data' => []], 401);
        }

        return response()->json([
            'data'         => $user,
            'access_token' => $token,
            'token_type'   => 'bearer'
        ]);

    }


    /**
     * 注册
     * @return void
     */
    public function register(AuthRequest $request)
    {
        // 获取通过验证的数据...
        $data = $request->validated();
        // 密码hash加密、加盐处理
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // 请求参数校验通过后处理注册入库
        $user = User::create($data);
        if (!$user) {
            return response()->json(['code' => 400, 'msg' => '注册失败', 'data' => []]);
        }
        $user['token'] = JWTAuth::fromUser($user);
        return response()->json(['code' => 200, 'msg' => '注册成功', 'data' => $user]);
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        try {
            $token = JWTAuth::getToken();
            if (JWTAuth::setToken($token)) {
                //获取用户
                $user = JWTAuth::parseToken()->authenticate();
                return response()->json(['code' => 200, 'msg' => 'success', 'data' => $user]);
            } else {
                return response()->json(['code' => 400, 'msg' => '获取用户信息失败,请检查token令牌是否有效', 'data' => []]);
            }
        } catch (TokenExpiredException $e) {
            // token失效等...
            return response()->json(['code' => 400, 'msg' => 'Error: ' . $e->getMessage(), 'data' => []]);
        }
    }



    /**
     * 退出
     * @return void
     */
    public function logout()
    {
        try {
            JWTAuth::setToken(JWTAuth::getToken())->invalidate();   // 把当前token加入黑名单
            return response()->json(['code' => 200, 'msg' => 'success', 'data' => []]);
        } catch (TokenExpiredException $e){
            return response()->json(['code' => 400, 'msg' => 'Error: ' . $e->getMessage(), 'data' => []]);
        }
    }

    /**
     * 刷星token
     * @return void
     */
    public function refresh()
    {
        // 把当前token加入黑名单，返回新的token
        return response()->json(['code' => 200, 'msg' => 'success', 'data' => ['token'=>JWTAuth::setToken(JWTAuth::getToken())->refresh()]]);
    }
}
