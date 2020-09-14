<?php  declare(strict_types=1);

namespace Seffeng\LaravelSignature\Tests;

use PHPUnit\Framework\TestCase;
use Seffeng\LaravelSignature\Exceptions\SignatureException;
use Seffeng\LaravelSignature\Facades\Signature;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SignatureTest extends TestCase
{
    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @throws SignatureException
     * @throws \Exception
     */
    public function testSignature()
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

            /**
             * 服务端验证签名[使用中间件方式]
             * @var Request $request
             */
            Signature::loadServer();
            // 其他服务端
            // Signature::setServer('other-server')->loadServer();
            $accessKeyId = $request->header(Signature::getHeaderAccessKeyId());
            $timestamp = $request->header(Signature::getHeaderTimestamp());
            $signature = $request->header(Signature::getHeaderSignature());
            $method = $request->getMethod();
            $uri = $request->getPathInfo();
            $accessKeySecret = '';

            // 其他判断，通过数据库查询应用信息
            /**
            $application = Application::where('access_key', $accessKeyId)->first();
            if (!$application) {
                throw new SignatureException('应用不存在！');
            }
            // IP白名单验证，不在此IP列表中则验证不通过 ['192.168.1.1', '127.0.0.1', ...]
            if ($application->white_ip) {
                if (!in_array(request()->getClientIp(), $application->white_ip)) {
                    throw new SignatureException('该IP不在白名单中，禁止访问！');
                }
            }
            // IP黑名单验证，在此IP列表中则验证不通过 ['192.168.1.1', '127.0.0.1', ...]
            if ($application->black_ip) {
                if (in_array(request()->getClientIp(), $application->black_ip)) {
                    throw new SignatureException('该IP在黑名单中，禁止访问！');
                }
            }
            $accessKeySecret = $application->access_secret;
             */

            if (!Signature::verifyTimestamp($timestamp)) {
                throw new SignatureException('请求超时，请确认服务器时间！');
            }
            if (Signature::setAccessKeyId($accessKeyId)->setAccessKeySecret($accessKeySecret)->setTimestamp($timestamp)->verify($signature, $method, $uri, $request->all())) {
                /**
                 * @var \Closure $next
                 */
                return $next($request);
            }
            throw new SignatureException('签名无效！');
        } catch (SignatureException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
