<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use Swagger\Annotations\Info;
//use Swagger\Annotations\OA;

class SwaggerController extends Controller
{

    /**
     * 返回JSON格式的Swagger定义
     *
     * 这里需要一个主`Swagger`定义：
     * @SWG\Swagger(
     *   @SWG\Info(
     *     title="我的`Swagger`API文档",
     *     version="1.0.0"
     *   )
     * )
     */
    public function getJSON()
    {
        $swagger = \Swagger\scan(app_path('Http/Controllers/'));
        return response()->json($swagger, 200);
    }
    /**
     * 假设是项目中的一个API
     */
    public function getMyData()
    {
    }
}
