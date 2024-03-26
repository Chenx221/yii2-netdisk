<?php

namespace app\utils;

use ipinfo\ipinfo\Details;
use ipinfo\ipinfo\IPinfo;
use ipinfo\ipinfo\IPinfoException;
use Yii;

class IPLocation
{
    private IPinfo $client;
    private bool $is_disabled = true;

    /**
     * 初始化IPLOCATION实例
     */
    public function __construct()
    {
        $status = Yii::$app->params['enableIpInfo'];
        if ($status) {
            $this->is_disabled = false;
            $this->client = new IPinfo(Yii::$app->params['ipinfoToken']);
        }
    }

    /**
     * 获取IP详细信息
     * 传入ip地址，返回ip详细信息(Details对象)
     * 报SSL certificate problem错误进来看这里
     * @param string $ip
     * @return Details|null
     */
    public static function getDetails(string $ip): ?Details
    {
        $instance = new self();
        if ($instance->is_disabled) {
            return null;
        }
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

    /**
     * 获取IP详细信息
     * 输出格式化的IP详细信息(字符串)
     * @param string $ip
     * @return string
     */
    public static function getFormatDetails(string $ip): string
    {
        $details = self::getDetails($ip);
        return $ip.' (' . ($details->bogon ? ('Bogon IP') : ($details->city . ', ' . $details->country)) . ')';
    }

}