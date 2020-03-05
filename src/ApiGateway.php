<?php
/**
 * Created by VIM.
 * Author:YQ
 * Date:2020/03/03 18:28:35
 */
namespace YouMi\Aliyun\ApiGateway;

use YouMi\Aliyun\ApiGateway\Http\HttpRequest;
use YouMi\Aliyun\ApiGateway\Http\HttpClient;
use YouMi\Aliyun\ApiGateway\Constant\HttpHeader;
use YouMi\Aliyun\ApiGateway\Constant\SystemHeader;
use YouMi\Aliyun\ApiGateway\Constant\ContentType;

/**
 * ApiGateway
 * 阿里云网关接口
 * package App\Core\Plugins\Aliyun\Package\Api
 * date 2020-03-03
 * author YQ
 */
class ApiGateway
{
    protected $httpClient;

    public function __construct()
    {
        $this->initClient();
    }

    /**
     * initClient
     * 初始化配置
     * @static
     * @access private
     * @return void
     */
    private function initClient()
    {
        if(is_null($this->httpClient)){
            $conf = config('aliyun_api');
            if(!isset($conf['app_id']) || empty($conf['app_id'])){
                throw new Exception('请在config中配置aliyun_api的app_id');
            }
            if(!isset($conf['app_secret']) || empty($conf['app_secret'])){
                throw new Exception('请在config中配置aliyun_api的app_secret');
            }
            $this->httpClient = new HttpRequest('', '', '', $conf['app_id'], $conf['app_secret']);
            //设定Content-Type，根据服务器端接受的值来设置
            $this->httpClient->setHeader(HttpHeader::HTTP_HEADER_CONTENT_TYPE, ContentType::CONTENT_TYPE_TEXT);

            //设定Accept，根据服务器端接受的值来设置
            $this->httpClient->setHeader(HttpHeader::HTTP_HEADER_ACCEPT, ContentType::CONTENT_TYPE_TEXT);
            $env = env('APP_ENV');
            if(empty($env) || $env != 'product'){
                //如果是调用测试环境请设置
                $this->httpClient->setHeader(SystemHeader::X_CA_STAG, "test");
            }
            $header = 'name:vhxsd|user-agent:'.($_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36').'|client_ip:'.($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            $this->httpClient->setHeader('V-App-Client-Information' , $header);

        }
    }

    /**
     * request
     * 发送请求
     * *遗留问题，参数为0会报签名错误，需要转换为字符串0
     * @param mixed $host 
     * @param mixed $path 
     * @param string $method 
     * @param array $params 
     * @param array $signHeader 
     * @access public
     * @return void
     */
    public function request($host, $path, $method = 'GET', $params = [], $signHeader = []) {
        $hostList = config('aliyun_api.hosts');
        if(!isset($hostList[$host])){
            throw new \Exception("$host 尚未指定,请在aliyun_api.hosts配置");
        }
        $this->httpClient->setSignHeader(SystemHeader::X_CA_TIMESTAMP);
        $this->httpClient->setHost($hostList[$host]);
        $this->httpClient->setPath($path);
        $this->httpClient->setMethod($method);
        //注意：业务query部分，如果没有则无此行；请不要、不要、不要做UrlEncode处理
        if($params){
            //注意：业务header部分，如果没有则无此行(如果有中文，请做Utf8ToIso88591处理)
            //mb_convert_encoding("headervalue2中文", "ISO-8859-1", "UTF-8");
            foreach($params as $key => $row){
                $this->httpClient->setQuery($key, $row);
            }
        }
        if($signHeader){
            //注意：业务header部分，如果没有则无此行(如果有中文，请做Utf8ToIso88591处理)
            //mb_convert_encoding("headervalue2中文", "ISO-8859-1", "UTF-8");
            foreach($signHeader as $key => $row){
                $this->httpClient->setHeader($key, $row);
                $this->httpClient->setSignHeader($key);
            }
        }
        $response = HttpClient::execute($this->httpClient);
        if($response->getSuccess()){
            $result = $response->getBody();
            return json_decode($result, true);
        }else{
            throw new \Exception($response->getErrorMessage());
        }
    }
}
