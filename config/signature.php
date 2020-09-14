<?php
/**
 * @link http://github.com/seffeng/
 * @copyright Copyright (c) 2020 seffeng
 */
return [
    /**
     * 调试模式[false-验证签名，true-不验证签名]
     */
    'debug' => env('SIGNATURE_DEBUG', env('APP_DEBUG', false)),

    /**
     * 默认使用环境[server-服务端, client-客户端]
     */
    'env' => env('SIGNATURE_ENV', 'client'),

    /**
     * CLIENT[默认客户端]
     */
    'client' => env('SIGNATURE_CLIENT', 'default'),

    /**
     * SERVER[默认服务端]
     */
    'server' => env('SIGNATURE_SERVER', 'default'),

    /**
     * 服务端
     */
    'servers' => [

        /**
         * 不同项目配置服务端KEY，一般服务端只有本项目一个；
         * 因前端可能暴露配置信息，可以独立一个服务端配置
         */
        'default' => [
            /**
             * 签名使用的哈希算法名称
             */
            'algo' => env('SIGNATURE_ALGO', 'sha1'),

            /**
             * 服务器时差
             */
            'timeout' => env('SIGNATURE_TIMEOUT', 300),

            /**
             * 签名前缀[签名字符串前面拼接的字符]
             */
            'prefix' => env('SIGNATURE_PREFIX', ''),

            /**
             * 签名连接符[签名字符串之间拼接的字符]
             */
            'connector' => env('SIGNATURE_CONNECTOR', '&'),

            /**
             * 签名后缀[签名字符串最后拼接的字符]
             */
            'suffix' => env('SIGNATURE_SUFFIX', ''),

            /**
             * 请求头app id 对应参数名[$header['Access-Key-Id']]
             */
            'headerAccessKeyId' => env('SIGNATURE_HEADER_ACCESS_KEY_ID', 'Access-Key-Id'),

            /**
             * 请求头时间戳 对应参数名[$header['Timestamp']]
             */
            'headerTimestamp' => env('SIGNATURE_HEADER_TIMESTAMP', 'Timestamp'),

            /**
             * 请求头Signature对应参数名[$header['Signature']]
             */
            'headerSignature' => env('SIGNATURE_HEADER_SIGNATURE', 'Signature'),

            /**
             * 请求头Signature对应标签[$header['Signature'] = "Signature $sign"]
             */
            'headerSignatureTag' => env('SIGNATURE_HEADER_SIGNATURE_TAG', 'Signature'),
        ],

        // 更多服务端...
    ],

    /**
     * 客户端
     */
    'clients' => [

        /**
         * 不同项目配置客户端KEY
         */
        'default' => [
            /**
             * 服务端域名
             * host [http://api.com]
             */
            'host' => env('SIGNATURE_HOST', ''),

            /**
             * 版本[V1, v2, v2020...]
             * version
             */
            'version' => env('SIGNATURE_VERSION', ''),

            /**
             * SIGNATURE AccessKeyId
             */
            'accessKeyId' => env('SIGNATURE_ACCESS_KEY_ID', 'AccessKeyId'),

            /**
             * SIGNATURE AccessKeySecret
             */
            'accessKeySecret' => env('SIGNATURE_ACCESS_KEY_SECRET', 'AccessKeySecret'),

            /**
             * 签名使用的哈希算法名称
             */
            'algo' => env('SIGNATURE_ALGO', 'sha1'),

            /**
             * 签名前缀[签名字符串前面拼接的字符]
             */
            'prefix' => env('SIGNATURE_PREFIX', ''),

            /**
             * 签名连接符[签名字符串之间拼接的字符]
             */
            'connector' => env('SIGNATURE_CONNECTOR', '&'),

            /**
             * 签名后缀[签名字符串最后拼接的字符]
             */
            'suffix' => env('SIGNATURE_SUFFIX', ''),

            /**
             * 请求头app id 对应参数名[$header['Access-Key-Id']]
             */
            'headerAccessKeyId' => env('SIGNATURE_HEADER_ACCESS_KEY_ID', 'Access-Key-Id'),

            /**
             * 请求头时间戳 对应参数名[$header['Timestamp']]
             */
            'headerTimestamp' => env('SIGNATURE_HEADER_TIMESTAMP', 'Timestamp'),

            /**
             * 请求头Signature对应参数名[$header['Signature']]
             */
            'headerSignature' => env('SIGNATURE_HEADER_SIGNATURE', 'Signature'),

            /**
             * 请求头Signature对应标签[$header['Signature'] = "Signature $sign"]
             */
            'headerSignatureTag' => env('SIGNATURE_HEADER_SIGNATURE_TAG', 'Signature'),
        ],

        // 更多客户端...
    ],
];
