<?php
/**
 * @link http://github.com/seffeng/
 * @copyright Copyright (c) 2020 seffeng
 */
namespace Seffeng\LaravelSignature;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Seffeng\Signature\Exceptions\SignatureException;

class SignatureServiceProvider extends BaseServiceProvider
{
    /**
     *
     * {@inheritDoc}
     * @see \Illuminate\Support\ServiceProvider::register()
     */
    public function register()
    {
        $this->registerAliases();
        $this->mergeConfigFrom($this->configPath(), 'signature');

        $this->app->singleton('seffeng.laravel.signature', function ($app) {
            $config = $app['config']->get('signature');

            if ($config && is_array($config)) {
                return new SignatureManager($config);
            } else {
                throw new SignatureException('Please execute the command `php artisan vendor:publish --tag="signature"` first to generate signature configuration file.');
            }
        });
    }

    /**
     *
     * @author zxf
     * @date    2020年9月14日
     */
    public function boot()
    {
        if ($this->app->runningInConsole() && $this->app instanceof LaravelApplication) {
            $this->publishes([$this->configPath() => config_path('signature.php')], 'signature');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('signature');
        }
    }

    /**
     *
     * @author zxf
     * @date    2020年9月14日
     */
    protected function registerAliases()
    {
        $this->app->alias('seffeng.laravel.signature', SignatureManager::class);
    }

    /**
     *
     * @author zxf
     * @date    2020年9月14日
     * @return string
     */
    protected function configPath()
    {
        return dirname(__DIR__) . '/config/signature.php';
    }
}
