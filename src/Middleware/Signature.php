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
    protected $accessKeyId;

    /**
     *
     * @var string
     */
    protected $accessKeySecret;

    /**
     *
     * @var integer
     */
    protected $timestamp;

    /**
     *
     * @var string
     */
    protected $signature;

    /**
     *
     * @var string
     */
    protected $version;

    /**
     *
     * @var string
     */
    protected $method;

    /**
     *
     * @var string
     */
    protected $uri;

    /**
     *
     * @var array
     */
    protected $params;

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
            !is_null($server) && SignatureFacade::setServer($server)->loadServer();
            if (SignatureFacade::getIsDebug()) {
                return true;
            }
            is_null($this->getAccessKeyId()) && $this->setAccessKeyId($request->header(SignatureFacade::getHeaderAccessKeyId()));
            is_null($this->getTimestamp()) && $this->setTimestamp($request->header(SignatureFacade::getHeaderTimestamp()));
            is_null($this->getSignature()) && $this->setSignature($request->header(SignatureFacade::getHeaderSignature()));
            is_null($this->getVersion()) && $this->setVersion($request->header(SignatureFacade::getHeaderVersion(), ''));
            is_null($this->getMethod()) && $this->setMethod($request->getMethod());
            is_null($this->getUri()) && $this->setUri($request->getPathInfo());

            if ($this->getAllowIp() && !in_array($request->getClientIp(), $this->getAllowIp())) {
                throw new SignatureAccessException('该IP未授权，禁止访问！');
            } elseif ($this->getDenyIp() && in_array($request->getClientIp(), $this->getDenyIp())) {
                throw new SignatureAccessException('该IP未授权，禁止访问！');
            }

            SignatureFacade::setAccessKeyId($this->getAccessKeyId())->setAccessKeySecret($this->getAccessKeySecret())->setVersion($this->getVersion())->setTimestamp($this->getTimestamp());
            $this->setParams($request->all());
            if (SignatureFacade::verify($this->getSignature(), $this->getMethod(), $this->getUri(), $this->getParams())) {
                return true;
            }
            throw new SignatureException('签名无效！');
        } catch (\Error $e) {
            throw $e;
        } catch (SignatureAccessException $e) {
            throw $e;
        } catch (SignatureTimeoutException $e) {
            throw $e;
        } catch (SignatureException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @param string $accessKeyId
     */
    public function setAccessKeyId(string $accessKeyId)
    {
        $this->accessKeyId = $accessKeyId;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @return string
     */
    public function getAccessKeyId()
    {
        return $this->accessKeyId;
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
     * @param array $allowIp
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
     * @param array $denyIp
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

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @param integer $timestamp
     */
    public function setTimestamp(int $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @param string $uri
     */
    public function setUri(string $uri)
    {
        $this->uri = $uri;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @param string $signature
     */
    public function setSignature(string $signature)
    {
        $this->signature = $signature;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @param string $version
     */
    public function setVersion(string $version)
    {
        $this->version = $version;
    }

    /**
     *
     * @author zxf
     * @date   2021年8月26日
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     *
     * @author zxf
     * @date   2021年9月7日
     * @param array $params
     * @param bool $force
     * @return static
     */
    public function setParams(array $params, bool $force = false)
    {
        ($force || is_null($this->params)) && $this->params = $params;
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2021年9月7日
     * @return array
     */
    public function getParams()
    {
        return is_array($this->params) ? $this->params : [];
    }
}
