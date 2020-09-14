<?php
/**
 * @link http://github.com/seffeng/
 * @copyright Copyright (c) 2020 seffeng
 */
namespace Seffeng\LaravelSignature\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Seffeng\LaravelSignature\Exceptions\SignatureException;
use Seffeng\LaravelSignature\Facades\Signature as SignatureFacade;

class Signature
{
    /**
     *
     * @author zxf
     * @date   2020年9月14日
     */
    public function __construct()
    {

    }

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
            $accessKeyId = $request->header(SignatureFacade::getHeaderAccessKeyId());
            $timestamp = $request->header(SignatureFacade::getHeaderTimestamp());
            $signature = $request->header(SignatureFacade::getHeaderSignature());
            $method = $request->getMethod();
            $uri = $request->getPathInfo();
            $accessKeySecret = '';

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
