<?php
/**
 * @link http://github.com/seffeng/
 * @copyright Copyright (c) 2020 seffeng
 */
namespace Seffeng\LaravelSignature\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Seffeng\Signature\Exceptions\SignatureException;
use Seffeng\LaravelSignature\Facades\Signature as SignatureFacade;
use Seffeng\Signature\Exceptions\SignatureTimeoutException;
use Seffeng\Signature\Exceptions\SignatureAccessException;

class Signature
{
    /**
     *
     * @var array
     */
    protected $allowIp;

    /**
     *
     * @var array
     */
    protected $denyIp;

    /**
     *
     * @var string
     */
    protected $accessKeySecret;

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
    public function handle($request, Closure $next, string $server = null)
    {
        try{
            $accessKeyId = $request->header(SignatureFacade::getHeaderAccessKeyId());
            $timestamp = $request->header(SignatureFacade::getHeaderTimestamp());
            $signature = $request->header(SignatureFacade::getHeaderSignature());
            $version = $request->header(SignatureFacade::getHeaderVersion(), '');
            $method = $request->getMethod();
            $uri = $request->getPathInfo();

            if ($this->getAllowIp() && !in_array($request->getClientIp(), $this->getAllowIp())) {
                throw new SignatureAccessException('该IP未授权，禁止访问！');
            } elseif ($this->getDenyIp() && in_array($request->getClientIp(), $this->getDenyIp())) {
                throw new SignatureAccessException('该IP未授权，禁止访问！');
            }

            SignatureFacade::setAccessKeyId($accessKeyId)->setAccessKeySecret($this->getAccessKeySecret())->setVersion($version)->setTimestamp($timestamp);
            if (SignatureFacade::verify($signature, $method, $uri, $request->all())) {
                return true;
            }
            throw new SignatureException('签名无效！');
        } catch (\Error $e) {
            throw $e;
        } catch (SignatureTimeoutException $e) {
            throw new SignatureTimeoutException('请求超时，请确认服务器时间！');
        } catch (SignatureException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @author zxf
     * @date   2020年9月15日
     * @param string $accessKeySecret
     */
    public function setAccessKeySecret(string $accessKeySecret)
    {
        $this->accessKeySecret = $accessKeySecret;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月15日
     * @return string
     */
    public function getAccessKeySecret()
    {
        return $this->accessKeySecret;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月15日
     * @param array $allowIP
     * @return void
     */
    public function setAllowIp(array $allowIp)
    {
        $this->allowIp = $allowIp;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月15日
     * @return array
     */
    public function getAllowIp()
    {
        return $this->allowIp;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月15日
     * @param array $blackIp
     * @return void
     */
    public function setDenyIp(array $denyIp)
    {
        $this->denyIp = $denyIp;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月15日
     * @return array
     */
    public function getDenyIp()
    {
        return $this->denyIp;
    }
}
