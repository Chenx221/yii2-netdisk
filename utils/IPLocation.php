<?php

namespace app\utils;

use ipinfo\ipinfo\Details;
use ipinfo\ipinfo\IPinfo;
use ipinfo\ipinfo\IPinfoException;
use Yii;

class IPLocation
{
    private IPinfo $client;

    public function __construct()
    {
        $this->client = new IPinfo(Yii::$app->params['ipinfoToken']);
    }
    public static function getDetails(string $ip): ?Details
    {
        $instance = new self();
        try {
            return $instance->client->getDetails($ip);
        } catch (IPinfoException $e) {
            Yii::error($e->getMessage());
            /*
             * Note:
             * 如果出现SSL certificate problem: unable to get local issuer certificate
             * 下载 https://curl.haxx.se/ca/cacert.pem 到 php\extras\ssl 并在php.ini中配置
             * curl.cainfo = "文件的绝对路径"
             * 解决方法参考: https://martinsblog.dk/windows-iis-with-php-curl-60-ssl-certificate-problem-unable-to-get-local-issuer-certificate/
             */
            return null;
        }
    }

}