# Laravel Signature

### 安装

```shell
# 安装
$ composer require seffeng/laravel-signature
```

##### Laravel

```php
# 1、生成配置文件
$ php artisan vendor:publish --tag="signature"
```

##### lumen

```php
# 1、将以下代码段添加到 /bootstrap/app.php 文件中的 Providers 部分
$app->register(Seffeng\LaravelSignature\SignatureServiceProvider::class);
```

### 示例

```php
# 客户端示例
use GuzzleHttp\Client;
use Seffeng\LaravelSignature\Exceptions\SignatureException;
use Seffeng\LaravelSignature\Facades\Signature;

class SiteController extends Controller
{
    public function test()
    {
        try {
            /**
             * 客户端使用签名
             * @var string $method
             */
            $method = 'GET';
            $uri = '/test';
            $params = ['page' => 1];
            $signature = Signature::sign($method, $uri, $params);
            $headers = Signature::getHeaders();
            print_r($headers);

            // 其他客户端
            //$signature = Signature::setClient('other-client')->loadClient()->sign($method, $uri, $params);
            //$headers = Signature::getHeaders();
            //print_r($headers);

            // 通过请求传递 $headers，如使用 GuzzleHttp
            $httpClient = new Client(['base_uri' => Signature::getHost()]);
            $request = $httpClient->get('/test', ['headers' => $headers, 'query' => $params]);
        } catch (SignatureException $e) {
            var_dump($e->getMessage());
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
```

```php
# 服务端示例，通过中间件
use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Seffeng\LaravelSignature\Exceptions\SignatureException;
use Seffeng\LaravelSignature\Facades\Signature as SignatureFacade;

class Signature
{
    /**
     *
     * @author zxf
     * @date    2020年9月14日
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @throws HttpException
     * @throws SignatureException
     * @return void
     */
    public function handle($request, Closure $next)
    {
        try{
            SignatureFacade::loadServer();
            // 其他服务端
            // SignatureFacade::setServer('other-server')->loadServer();
            $accessKeyId = $request->header(SignatureFacade::getHeaderAccessKeyId());
            $timestamp = $request->header(SignatureFacade::getHeaderTimestamp());
            $signature = $request->header(SignatureFacade::getHeaderSignature());
            $method = $request->getMethod();
            $uri = $request->getPathInfo();
            $accessKeySecret = '';

            // 其他判断，通过数据库查询应用信息，自行创建数据表
            /**
            $application = Application::where('access_key', $accessKeyId)->first();
            if (!$application) {
                throw new SignatureException('应用不存在！');
            }
            if ($application->white_ip) {
                if (!in_array(request()->getClientIp(), $application->white_ip)) {
                    throw new SignatureException('该IP不在白名单中，禁止访问！');
                }
            }
            if ($application->black_ip) {
                if (in_array(request()->getClientIp(), $application->black_ip)) {
                    throw new SignatureException('该IP在黑名单中，禁止访问！');
                }
            }
            $accessKeySecret = $application->access_secret;
             */

            if (!SignatureFacade::verifyTimestamp($timestamp)) {
                throw new SignatureException('请求超时，请确认服务器时间！');
            }
            if (SignatureFacade::setAccessKeyId($accessKeyId)->setAccessKeySecret($accessKeySecret)->verifySign($signature, $method, $uri, $request->all())) {
                return $next($request);
            }
            throw new SignatureException('签名无效！');
        } catch (\Error $e) {
            throw new HttpException(450, '缺少参数！');
        } catch (SignatureException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
```



### 备注

1、本扩展仅用于个人项目应用之间接口签名验证。

