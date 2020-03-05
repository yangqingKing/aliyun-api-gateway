## 安装

## 配置

## 使用场景
```
    $c = new \YouMi\Aliyun\ApiGateway\ApiGateway();
    $data = $c->request($host, $path, $method, $params);
```

* $host 域名标识,在配置文件`config/aliyun.php`的`hosts`中配置
* $path 请求路径，即url后面的路径，如：/index
* $method 请求方法，`GET`, `POST`, `PUT`
* $params 请求参数，数组格式

> 返回json_decode后的数组数据
