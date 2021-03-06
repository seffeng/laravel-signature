<?php  declare(strict_types=1);

namespace Seffeng\LaravelSignature\Tests;

use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;
use Seffeng\LaravelSignature\Facades\Signature;
use Seffeng\Signature\Exceptions\SignatureException;
use Seffeng\Signature\Exceptions\SignatureTimeoutException;

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
            // $httpClient = new Client(['base_uri' => 'http://domain.com']);
            // $request = $httpClient->get('/test', ['headers' => $headers, 'query' => $params]);

            /**
             * 服务端验证签名
             * @var Request $request
             */
            // 其他服务端
            // Signature::setServer('other-server')->loadServer();
            $accessKeyId = $request->header(Signature::getHeaderAccessKeyId());
            $timestamp = $request->header(Signature::getHeaderTimestamp());
            $signature = $request->header(Signature::getHeaderSignature());
            $version = $request->header(Signature::getHeaderVersion(), '');
            $method = $request->getMethod();
            $uri = $request->getPathInfo();
            $accessKeySecret = '设置secret或通过$accessKeyId数据库查询获取！';

            // 其他判断，通过数据库查询应用信息，自行创建数据表，如[Application]
            /**
            $application = Application::where('access_key_id', $accessKeyId)->first();
            if (!$application) {
                throw new SignatureException('应用不存在！');
            }
            // IP白名单验证，不在此IP列表中则验证不通过 ['192.168.1.1', '127.0.0.1', ...]
            if ($application->white_ip) {
                if (!in_array(request()->getClientIp(), $application->white_ip)) {
                    throw new SignatureAccessException('该IP不在白名单中，禁止访问！');
                }
            }
            // IP黑名单验证，在此IP列表中则验证不通过 ['192.168.1.1', '127.0.0.1', ...]
            if ($application->black_ip) {
                if (in_array(request()->getClientIp(), $application->black_ip)) {
                    throw new SignatureAccessException('该IP在黑名单中，禁止访问！');
                }
            }
            $accessKeySecret = $application->access_key_secret;
             */

            if (Signature::setAccessKeyId($accessKeyId)->setAccessKeySecret($accessKeySecret)->setVersion($version)->setTimestamp($timestamp)->verify($signature, $method, $uri, $request->all())) {
                return true;
            }
            throw new SignatureException('签名无效！');
        } catch (SignatureTimeoutException $e) {
            throw new SignatureTimeoutException('请求超时，请确认服务器时间！');
        } catch (SignatureException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
