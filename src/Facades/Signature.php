<?php
/**
 * @link http://github.com/seffeng/
 * @copyright Copyright (c) 2020 seffeng
 */
namespace Seffeng\LaravelSignature\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @author zxf
 * @date   2020年9月14日
 * @method static \Seffeng\LaravelSignature\SignatureManager setAccessKeyId(string $accessKeyId)
 * @method static \Seffeng\LaravelSignature\SignatureManager setAccessKeySecret(string $accessKeySecret)
 * @method static \Seffeng\LaravelSignature\SignatureManager setClient(string $client = null)
 * @method static \Seffeng\LaravelSignature\SignatureManager setServer(string $server = null)
 * @method static \Seffeng\LaravelSignature\SignatureManager loadClient()
 * @method static \Seffeng\LaravelSignature\SignatureManager loadServer()
 * @method static \Seffeng\LaravelSignature\SignatureManager setTimestamp(int $time = null)
 * @method static \Seffeng\LaravelSignature\SignatureManager setVersion(string $version)
 * @method static string sign(string $method, string $uri, array $params = [])
 * @method static string verify(string $signature, string $method, string $uri, array $params = [])
 * @method static string getHeaderAccessKeyId()
 * @method static string getHeaderSignature()
 * @method static string getHeaderTimestamp()
 * @method static string getHeaderVersion()
 * @method static string getSignature()
 * @method static string getVersion()
 * @method static string getClient()
 * @method static string getServer()
 * @method static array getHeaders(array $headers = [])
 * @method static boolean verifyTimestamp(int $timestamp)
 *
 * @see \Seffeng\LaravelSignature\SignatureManager
 */
class Signature extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'seffeng.laravel.signature';
    }
}
