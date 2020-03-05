<?php
/**
 * Created by VIM.
 * Author:YQ
 * Date:2020/03/05 19:45:59
 */
namespace YouMi\Aliyun\ApiGateway;

/**
 * ApiGatewayInterface
 * 阿里云网关接口
 * date 2020-03-05
 * author YQ
 */
interface ApiGatewayInterface
{
    // request aliyun api.
    public function request(String $host, String $path, String $method, Array $params, Array $signHeader);
}
