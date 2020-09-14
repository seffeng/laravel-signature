<?php
/**
 * @link http://github.com/seffeng/
 * @copyright Copyright (c) 2020 seffeng
 */
namespace Seffeng\LaravelSignature;

use Seffeng\LaravelHelpers\Helpers\Arr;
use Seffeng\LaravelSignature\Exceptions\SignatureException;

class Signature
{
    /**
     *
     * @var string
     */
    protected $host;

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
     * 接口版本
     * @var string
     */
    protected $version;

    /**
     *
     * @var string
     */
    protected $timestamp;

    /**
     * [default]
     * @var string
     */
    protected $client;

    /**
     * [default]
     * @var string
     */
    protected $server;

    /**
     *
     * @var array
     */
    protected static $config;

    /**
     *
     * @var string
     */
    protected $typeId;

    /**
     * 服务器时差
     * @var integer
     */
    protected $timeout;

    /**
     * 签名前缀[签名字符串前面拼接的字符]
     * @var string
     */
    protected $prefix;

    /**
     * 签名连接符[签名字符串之间拼接的字符]
     * @var string
     */
    protected $connector;

    /**
     * 签名后缀[签名字符串最后拼接的字符]
     * @var string
     */
    protected $suffix;

    /**
     * 请求头app id 对应参数名[$header['Access-Key-Id']]
     * @var string
     */
    protected $headerAccessKeyId;

    /**
     * 请求头时间戳 对应参数名[$header['Timestamp']]
     * @var string
     */
    protected $headerTimestamp;

    /**
     * 请求头Signature对应参数名[$header['Signature']]
     * @var string
     */
    protected $headerSignature;

    /**
     * 请求头Signature对应标签[$header['Signature'] = "Signature $sign"]
     * @var string
     */
    protected $headerSignatureTag;

    /**
     * 签名字符串
     * @var string
     */
    protected $signature;

    /**
     *
     * @var string
     */
    protected $algo;

    /**
     * [client]
     * @var string
     */
    const TYPE_CLIENT = 1;
    /**
     * [server]
     * @var string
     */
    const TYPE_SERVER = 2;

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param array $config
     */
    public function __construct(array $config)
    {
        $env = Arr::getValue($config, 'env');
        $typeId = array_search($env, self::fetchTypeNameItems());
        if ($typeId) {
            $this->client = Arr::getValue($config, 'client');
            $this->server = Arr::getValue($config, 'server');
            $this->setTypeId($typeId);
            static::$config = $config;
            if ($this->getIsServer()) {
                $this->loadServer();
            } else {
                $this->loadClient();
            }
        } else {
            throw new SignatureException('使用环境[env]配置错误，仅支持[' . implode(', ', self::fetchTypeNameItems()) . ']！');
        }
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return string
     */
    public function sign(string $method, string $uri, array $params = [])
    {
        $this->setSignature($method, $uri, $params);
        return $this->getSignature();
    }

    /**
     * 签名验证
     * @author zxf
     * @date   2020年9月14日
     * @param string $signature
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return boolean
     */
    public function verify(string $signature, string $method, string $uri, array $params = [])
    {
        return hash_equals($this->sign($method, $uri, $params), $signature);
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param array $headers
     * @return array
     */
    public function getHeaders(array $headers = [])
    {
        return array_merge($headers, [
            $this->headerAccessKeyId => $this->accessKeyId,
            $this->headerTimestamp => $this->getTimestamp(),
            $this->headerSignature => $this->getSignature()
        ]);
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return string
     */
    public function getAccessKeyId()
    {
        return $this->accessKeyId;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param string $accessKeyId
     */
    public function setAccessKeyId(string $accessKeyId)
    {
        $this->accessKeyId = $accessKeyId;
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return string
     */
    public function getAccessKeySecret()
    {
        return $this->accessKeySecret;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param string $accessKeySecret
     */
    public function setAccessKeySecret(string $accessKeySecret)
    {
        $this->accessKeySecret = $accessKeySecret;
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return string
     */
    public function getHeaderAccessKeyId()
    {
        return $this->headerAccessKeyId;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return string
     */
    public function getHeaderSignature()
    {
        return $this->headerSignature;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return string
     */
    public function getHeaderTimestamp()
    {
        return $this->headerTimestamp;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param string $client
     * @return static
     */
    public function setClient(string $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param string $server
     * @return static
     */
    public function setServer(string $server)
    {
        $this->client = $server;
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param string $uri
     * @return string
     */
    public function getUri(string $uri = '')
    {
        return ($this->getVersion() ? ('/'. $this->getVersion()) : '') . $uri;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     */
    public function setTimestamp(int $time = null)
    {
        $this->timestamp = is_null($time) ? time() : $time;
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return string
     */
    public function getTimestamp()
    {
        if (is_null($this->timestamp)) {
            $this->setTimestamp();
        }
        return $this->timestamp;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return number
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param int $timestamp
     * @return boolean
     */
    public function verifyTimestamp(int $timestamp)
    {
        $time = time();
        if (abs($timestamp - $time) > $this->getTimeout()) {
            return false;
        }
        return true;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return static
     */
    public function loadClient()
    {
        $this->setTypeId(self::TYPE_CLIENT);
        $this->loadConfig();
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return static
     */
    public function loadServer()
    {
        $this->setTypeId(self::TYPE_SERVER);
        $this->loadConfig();
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param array $params
     * @return string
     */
    protected function signParameters(array $params = [])
    {
        $string = $this->connector;
        if ($params) {
            ksort($params);
            foreach ($params as $key => $value) {
                $string .= urlencode($key) .'='. urlencode(is_array($value) ? json_encode($value) : $value) . $this->connector;
            }
            $string = substr($string, 0, -1);
        }
        return $string;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param string $signature
     * @return string
     */
    protected function getSignatureWithTag(string $signature)
    {
        return empty($this->headerSignatureTag)  ? $signature : ($this->headerSignatureTag . ' ' .  $signature);
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return \Seffeng\LaravelSignature\Signature
     */
    protected function setSignature(string $method, string $uri, array $params = [])
    {
        $paramsSign = $this->signParameters($params);
        $signString = $this->prefix . $method . $this->connector . $this->getUri($uri) . $this->connector . $this->headerAccessKeyId. '=' .
            $this->getAccessKeyId() . $this->connector . $this->headerTimestamp . '='. $this->getTimestamp() . $paramsSign . $this->suffix;
            $this->signature = $this->getSignatureWithTag(base64_encode(hash_hmac($this->algo, $signString , $this->getAccessKeySecret(), true)));
            return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return integer
     */
    protected function getTypeId()
    {
        return $this->typeId;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param int $typeId
     */
    protected function setTypeId(int $typeId)
    {
        $this->typeId = $typeId;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return boolean
     */
    protected function getIsClient()
    {
        return !$this->getIsServer();
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return boolean
     */
    protected function getIsServer()
    {
        return $this->getTypeId() === self::TYPE_SERVER;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @throws SignatureException
     */
    protected function loadConfig()
    {
        if ($this->getIsServer()) {
            $server = Arr::getValue(static::$config, 'servers.'. $this->server);
            if (empty($server)) {
                throw new SignatureException('The server is not found.'. '['. $this->server .']');
            }
            $this->timeout = Arr::getValue($server, 'timeout');
            $this->prefix = Arr::getValue($server, 'prefix');
            $this->connector = Arr::getValue($server, 'connector');
            $this->suffix = Arr::getValue($server, 'suffix');
            $this->headerAccessKeyId = Arr::getValue($server, 'headerAccessKeyId');
            $this->headerTimestamp = Arr::getValue($server, 'headerTimestamp');
            $this->headerSignature = Arr::getValue($server, 'headerSignature');
            $this->headerSignatureTag = Arr::getValue($server, 'headerSignatureTag');
            $this->algo = Arr::getValue($server, 'algo');
        } else {
            $client = Arr::getValue(static::$config, 'clients.'. $this->client);
            if (empty($client)) {
                throw new SignatureException('The client is not found.'. '['. $this->client .']');
            }

            $this->accessKeyId = Arr::getValue($client, 'accessKeyId');
            $this->accessKeySecret = Arr::getValue($client, 'accessKeySecret');
            $this->host = Arr::getValue($client, 'host');
            $this->version = Arr::getValue($client, 'version');
            $this->prefix = Arr::getValue($client, 'prefix');
            $this->connector = Arr::getValue($client, 'connector');
            $this->suffix = Arr::getValue($client, 'suffix');
            $this->headerAccessKeyId = Arr::getValue($client, 'headerAccessKeyId');
            $this->headerTimestamp = Arr::getValue($client, 'headerTimestamp');
            $this->headerSignature = Arr::getValue($client, 'headerSignature');
            $this->headerSignatureTag = Arr::getValue($client, 'headerSignatureTag');
            $this->algo = Arr::getValue($client, 'algo');

            if (empty($this->accessKeyId) || empty($this->accessKeySecret) || empty($this->host)) {
                throw new SignatureException('Warning: accesskeyid, accesskeysecret, host cannot be empty.');
            }
        }

        if (empty($this->headerAccessKeyId) || empty($this->headerTimestamp) || empty($this->headerSignature)) {
            throw new SignatureException('Warning: headerAccessKeyId, headerTimestamp, headerSignature cannot be empty.');
        }

        if (!in_array($this->algo, hash_hmac_algos())) {
            throw new SignatureException('Warning: the algo is not support.');
        }
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return array
     */
    protected static function fetchTypeNameItems()
    {
        return [
            self::TYPE_CLIENT => 'client',
            self::TYPE_SERVER => 'server',
        ];
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @return array
     */
    protected static function fetchTypeItems()
    {
        return array_keys(self::fetchTypeNameItems());
    }
}
