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

# 2、修改配置文件 /config/signature.php，或 .env
```

##### lumen

```php
# 1、复制扩展内配置文件 /config/signature.php 到项目配置目录 /config

# 2、修改配置文件 /config/signature.php，或 .env

# 3、将以下代码段添加到 /bootstrap/app.php 文件中的 Providers 部分
$app->register(Seffeng\LaravelSignature\SignatureServiceProvider::class);

# 4、/bootstrap/app.php 添加配置加载代码
$app->configure('signature');
```

### 示例

```php
# 客户端示例
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
            // $httpClient = new Client(['base_uri' => 'http://domain.com']);
            // $request = $httpClient->get('/test', ['headers' => $headers, 'query' => $params]);
        } catch (SignatureException $e) {
            var_dump($e->getMessage());
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
```

```php
# 服务端示例，可通过中间件使用，或参考 /tests/SignatureTest.php
use Closure;
use Seffeng\LaravelSignature\Exceptions\SignatureException;
use Seffeng\LaravelSignature\Exceptions\SignatureAccessException;
use Seffeng\LaravelSignature\Exceptions\SignatureTimeoutException;
use Seffeng\LaravelSignature\Facades\Signature as SignatureFacade;
use Seffeng\LaravelSignature\Middleware\Signature as Middleware;

class Signature extends Middleware
{
    /**
     *
     * {@inheritDoc}
     * @see \Seffeng\LaravelSignature\Middleware\Signature::handle()
     */
    public function handle($request, Closure $next, string $server = null)
    {
        try {
            !is_null($server) && SignatureFacade::setServer($server)->loadServer();
            // $accessKeyId 用于查询应用信息，获取 secret 和 IP 等
            $accessKeyId = $request->header(SignatureFacade::getHeaderAccessKeyId());
            if (true) { // 通过数据库查询secret，自行创建数据表
                $application = Application::where('access_key_id', $accessKeyId)->first();
                if (!$application) {
                    throw new SignatureException('应用不存在！');
                }
                $accessKeySecret = $application->access_key_secret;
            } else {    // 通过配置，自行添加配置字段
                if ($accessKeyId !== config('signature.servers.default.accessKeyId', '')) {
                    throw new SignatureException('应用不存在！');
                }
                $accessKeySecret = config('signature.servers.default.accessKeySecret', '');
            }
            $this->setAccessKeySecret($accessKeySecret);
            $this->setAllowIp([]);  // 可配置或通过数据库查询ip，自行创建数据表
            //$this->setDenyIp([]); // 可配置或通过数据库查询ip，自行创建数据表

            if (parent::handle($request, $next)) {
                return $next($request);
            }
            throw new SignatureException('签名错误！');
        } catch (\Error $e) {
            throw $e;
        } catch (SignatureTimeoutException $e) {
            throw $e;
        } catch (SignatureAccessException $e) {
            throw $e;
        } catch (SignatureException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
```



### 备注

1、测试脚本 tests/SignatureTest.php 仅作为示例供参考。

