<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // 重写render异常的全局捕获 适应api
    public function render($request, Throwable $e){
        ## 验证错误
        // 我们仅仅处理 api的异常，所以这里要排除下其他前缀路由
        if($request->is("api/*")){
            //如果抛出的异常是 ValidationException 的实例，我们就可以确定该异常是表单验证异常
            if($e instanceof ValidationException){
                ## 下面是你需要包装的数据
                $result = [
                    "code"=>JsonResponse::HTTP_BAD_REQUEST,
                    "msg"  =>  $e->validator->errors()->first(), # 更好的获取错误的方法
                    ##"msg"=>array_values($exception->errors())[0][0],//这里 ValidationException 异常的格式通常是数组的形式，如果不确定如何取值可以打印下看下结构
                    "data"=>[]
                ];
                return response()->json($result);
            }
            //来自token的错误捕获
            if($e instanceof TokenBlacklistedException){
                return response()->json([
                    'code'=>JsonResponse::HTTP_UNAUTHORIZED, 'msg'=>$e->getMessage(), 'data'=>[]
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }
            // 暂时这样写
//            return response()->json([
//                'code'=>JsonResponse::HTTP_INTERNAL_SERVER_ERROR, 'msg'=>$e->getMessage(), 'data'=>[]
//            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
