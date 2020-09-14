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
 * @method static \Seffeng\LaravelSignature\Signature setAccessKeyId(string $accessKeyId)
 * @method static \Seffeng\LaravelSignature\Signature setAccessKeySecret(string $accessKeySecret)
 * @method static \Seffeng\LaravelSignature\Signature setClient(string $client)
 * @method static \Seffeng\LaravelSignature\Signature setServer(string $server)
 * @method static \Seffeng\LaravelSignature\Signature loadClient()
 * @method static \Seffeng\LaravelSignature\Signature loadServer()
 * @method static \Seffeng\LaravelSignature\Signature setTimestamp(int $time = null)
 * @method static string sign(string $method, string $uri, array $params = [])
 * @method static string verify(string $signature, string $method, string $uri, array $params = [])
 * @method static string getHeaderAccessKeyId()
 * @method static string getHeaderSignature()
 * @method static string getHeaderTimestamp()
 * @method static string getSignature()
 * @method static string getHost()
 * @method static array getHeaders(array $headers = [])
 * @method static boolean verifyTimestamp(int $timestamp)
 *
 * @see \Seffeng\LaravelSignature\Signature
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
