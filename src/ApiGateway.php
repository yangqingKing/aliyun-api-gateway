<?php
/**
 * Created by VIM.
 * Author:YQ
 * Date:2020/03/03 18:28:35
 */
namespace YouMi\Aliyun\ApiGateway;

use Psr\Container\ContainerInterface;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use Hyperf\Contract\ConfigInterface;
use YouMi\Aliyun\ApiGateway\Http\HttpRequest;
use YouMi\Aliyun\ApiGateway\Http\HttpResponse;
use YouMi\Aliyun\ApiGateway\Http\HttpClient;
use YouMi\Aliyun\ApiGateway\Util\SignUtil;
use YouMi\Aliyun\ApiGateway\Constant\HttpHeader;
use YouMi\Aliyun\ApiGateway\Constant\SystemHeader;
use YouMi\Aliyun\ApiGateway\Constant\ContentType;
use Exception;

/**
 * ApiGateway
 * 阿里云网关接口
 * package App\Core\Plugins\Aliyun\Package\Api
 * date 2020-03-03
 * author YQ
 */
class ApiGateway implements ApiGatewayInterface
{
    /**
     * @var Closure
     */
    private $client;

    /**
     * @var ConfigInterface
     */
    private $config;

    protected $httpClient;

    public function __construct(ContainerInterface $container)
    {
        $this->client = $container->get(GuzzleClientFactory::class)->create();
        $this->config = $container->get(ConfigInterface::class);

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
            $conf = $this->config->get('aliyun_sls', []);
            //$conf = config('aliyun_api');
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
        $hostList = $this->config->get('aliyun_sls.hosts', []);
        //$hostList = config('aliyun_api.hosts');
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
        $response = $this->requestHttpApi($this->httpClient);
        //$response = HttpClient::execute($this->httpClient);
        if($response->getSuccess()){
            $result = $response->getBody();
            return json_decode($result, true);
        }else{
            throw new \Exception($response->getErrorMessage());
        }
    }

    /**
     * requestHttpApi
     * 请求http接口
     * param mixed $request 
     * access private
     * return array
     * Date: 2020-03-05
     * Created by YQ
     */
    private function requestHttpApi($request)
    {
        return $this->DoHttp($request->getHost(),
            $request->getPath(),
            $request->getMethod(),
            $request->getAppKey(),
            $request->getAppSecret(),
            $request->getHeaders(),
            $request->getQuerys(),
            $request->getBodys(),
            $request->getSignHeaders());
    }

    /**
     *请求Request
     */
    private function DoHttp($host, $path, $method, $appKey, $appSecret, $headers, $querys, $bodys, $signHeaderPrefixList)
    {
        $headers = $this->initialBasicHeader($path, $appKey, $appSecret, $method, $headers, $querys, $bodys, $signHeaderPrefixList);
        $result = $this->initHttpRequest($host, $path, $method, $headers, $querys, $bodys);

        $response = new HttpResponse();

        $response->setHttpStatusCode($result->getStatusCode());
        $response->setBody($result->getBody());
        $response->setHeader($result->getHeaders());
        $response->extractKey();
        //$response->setContentType(curl_getinfo($curl, CURLINFO_CONTENT_TYPE));
        return $response;
    }

    /**
     *准备请求Request
     */
    private function initHttpRequest($host, $path, $method, $headers, $querys, $bodys)
    {
        $url = $host;
        if (0 < strlen($path)) {
            $url.= $path;
        }
        if (is_array($querys)) {
            if (0 < count($querys)) {
                $sb = "";
                foreach ($querys as $itemKey => $itemValue) {
                    if (0 < strlen($sb)) {
                        $sb .= "&";
                    }
                    if (0 < strlen($itemValue) && 0 == strlen($itemKey))
                    {
                        $sb .= $itemValue;
                    }
                    if (0 < strlen($itemKey)) {
                        $sb .= $itemKey;
                        if (0 < strlen($itemValue)) {
                            $sb .= "=";
                            $sb .= URLEncode($itemValue);
                        }
                    }
                }
                $url .= "?";
                $url .= $sb;
            }
        }

        $streams = array();
        $httpBody = '';
        if (is_array($bodys)) {
            if (0 < count($bodys)) {
                if (count($bodys) == count($streams) && 1 == count($streams)) {
                    $httpBody = $streams[0];
                } elseif (0 < count($bodys)) {
                    $httpBody = http_build_query($bodys);
                }
            }
        }

        try {
            $response = $this->client->request($method, $url, ['body' => $httpBody, 'headers' => $headers]);
            return $response;
        } catch (Exception $e) {
            throw new \RuntimeException($e->getMessage(),$e->getCode());
        }
    }

    /**
     *准备请求的基本header
     */
    private function initialBasicHeader($path, $appKey, $appSecret, $method, $headers, $querys, $bodys, $signHeaderPrefixList)
    {
        if (null == $headers) {
            $headers = array();
        }
        $sb = "";
        //时间戳
        date_default_timezone_set('PRC');
        $headers[SystemHeader::X_CA_TIMESTAMP] = strval(time()*1000);
        //防重放，协议层不能进行重试，否则会报NONCE被使用；如果需要协议层重试，请注释此行
        $headers[SystemHeader::X_CA_NONCE] = strval(self::NewGuid());

        $headers[SystemHeader::X_CA_KEY] = $appKey;
        $headers[SystemHeader::X_CA_SIGNATURE] = SignUtil::Sign($path, $method, $appSecret, $headers, $querys, $bodys, $signHeaderPrefixList);

        return $headers;
    }

    public static function CheckValidationResult($sender, $certificate, $chain, $errors)
    {
        return true;
    }

    private static function NewGuid()
    {
        mt_srand((double)microtime()*10000);
        $uuid = strtoupper(md5(uniqid(rand(), true)));
        return $uuid;
    }
}
