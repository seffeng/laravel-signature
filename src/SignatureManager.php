<?php
/**
 * @link http://github.com/seffeng/
 * @copyright Copyright (c) 2020 seffeng
 */
namespace Seffeng\LaravelSignature;

use Seffeng\Signature\Signature;
use Seffeng\Signature\Helpers\ArrayHelper;
use Seffeng\Signature\Exceptions\SignatureException;

/**
 *
 * @author zxf
 * @date   2020年9月21日
 *
 * @see \Seffeng\Signature\Signature
 */
class SignatureManager
{
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
     * @var string
     */
    protected $typeId;

    /**
     *
     * @var array
     */
    protected $service;

    /**
     *
     * @var array
     */
    protected $config;

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
        $env = ArrayHelper::getValue($config, 'env');
        $typeId = array_search($env, self::fetchTypeNameItems());
        if ($typeId) {
            $this->setClient(ArrayHelper::getValue($config, 'client'));
            $this->setServer(ArrayHelper::getValue($config, 'server'));
            $this->setTypeId($typeId);
            $this->config = $config;
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
     * @param string $client
     * @return static
     */
    public function setClient(string $client = null)
    {
        !is_null($client) && $this->client = $client;
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月15日
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月14日
     * @param string $server
     * @return static
     */
    public function setServer(string $server = null)
    {
        !is_null($server) && $this->server = $server;
        return $this;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月15日
     * @return string
     */
    public function getServer()
    {
        return $this->server;
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
     * @date   2020年9月21日
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     *
     * @author zxf
     * @date   2020年9月21日
     * @param  mixed  $method
     * @param  mixed $parameters
     * @throws SignatureException
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->getService(), $method)) {
            return $this->getService()->{$method}(...$parameters);
        } else {
            throw new SignatureException('方法｛' . $method . '｝不存在！');
        }
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
        $isDebug = ArrayHelper::getValue($this->config, 'debug');
        if ($this->getIsServer()) {
            $options = ArrayHelper::getValue($this->config, 'servers.'. $this->server);
            if (empty($options)) {
                throw new SignatureException('The server['. $this->server .'] is not found.');
            }
            $accessKeyId = 'null';
            $accessKeySecret = 'null';
        } else {
            $options = ArrayHelper::getValue($this->config, 'clients.'. $this->client);
            if (empty($options)) {
                throw new SignatureException('The client['. $this->client .'] is not found.');
            }
            $accessKeyId = ArrayHelper::getValue($options, 'accessKeyId');
            $accessKeySecret = ArrayHelper::getValue($options, 'accessKeySecret');
        }
        $this->service = new Signature($accessKeyId, $accessKeySecret, array_merge(['debug' => $isDebug], $options));
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
