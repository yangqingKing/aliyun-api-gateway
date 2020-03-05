
## 说明
基于hyperf框架的HTTP客户端的协程化的阿里云api的实现

## 安装

```
composer require yangqing/aliyun-api-gateway
```

## 配置
在`config`目录下配置文件`aliyun_api.php`
```php
return [
    'app_id' => env('ALIYUN_API_APPID', ''), // 阿里云api授权应用的AppKey
    'app_secret' => env('ALIYUN_API_APP_SECRET', ''), // 阿里云api授权应用的AppSecret
    'hosts' => [ // 阿里云的接口域名
        'host1' => 'http://api.public.com',
    ],
];

```

## 使用场景
```
    $c = new \YouMi\Aliyun\ApiGateway\ApiGateway();
    $data = $c->request($host, $path, $method, $params);
```

* $host 域名标识,在配置文件`config/aliyun.php`的`hosts`中配置, 如：host1
* $path 请求路径，即url后面的路径，如：/index
* $method 请求方法，`GET`, `POST`, `PUT`
* $params 请求参数，数组格式

> 返回json_decode后的数组数据
