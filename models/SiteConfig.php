<?php

namespace app\models;

use Exception;
use Yii;
use yii\base\Model;

/**
 * Class SiteConfig
 * 配置信息
 * 少数配置不在列表中，如数据库配置等
 */
class SiteConfig extends Model
{
    public string $siteTitle; // 网站标题
    public bool $registrationEnabled; // 注册开关

    public string $domain; // 域名
    public string $verifyProvider; // 验证码提供商
    public string $recaptchaSiteKey; // reCAPTCHA Site Key
    public string $recaptchaSecret; // reCAPTCHA Secret
    public string $hcaptchaSiteKey; // hCaptcha Site Key
    public string $hcaptchaSecret; // hCaptcha Secret
    public string $turnstileSiteKey; // Turnstile Site Key
    public string $turnstileSecret; // Turnstile Secret
    public bool $enableIpinfo; // 启用 ipinfo.io 查询
    public string $ipinfoToken; // IPinfo Token

    public function rules(): array
    {
        return [
            [['siteTitle', 'siteUrl', 'domain', 'verifyProvider', 'recaptchaSiteKey', 'recaptchaSecret', 'hcaptchaSiteKey', 'hcaptchaSecret', 'turnstileSiteKey', 'turnstileSecret', 'ipinfoToken'], 'string'],
            [['registrationEnabled', 'enableIpinfo'], 'boolean'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'siteTitle' => '网站标题',
            'registrationEnabled' => '允许注册',
            'domain' => '站点域名',
            'verifyProvider' => '验证码服务',
            'recaptchaSiteKey' => 'reCAPTCHA Site Key',
            'recaptchaSecret' => 'reCAPTCHA Secret',
            'hcaptchaSiteKey' => 'hCaptcha Site Key',
            'hcaptchaSecret' => 'hCaptcha Secret',
            'turnstileSiteKey' => 'Turnstile Site Key',
            'turnstileSecret' => 'Turnstile Secret',
            'enableIpinfo' => '启用 ipinfo.io 查询',
            'ipinfoToken' => 'IPinfo Token',
        ];
    }

    /**
     * 读取配置信息
     * @return bool
     */
    public function loadFromEnv(): bool
    {
        try {
            $this->siteTitle = $_ENV['SITE_TITLE'];
            $this->registrationEnabled = $_ENV['REGISTRATION_ENABLED'] === 'true';
            $this->domain = $_ENV['DOMAIN'];
            $this->verifyProvider = $_ENV['VERIFY_PROVIDER'];
            $this->recaptchaSiteKey = $_ENV['RECAPTCHA_SITE_KEY'];
            $this->recaptchaSecret = $_ENV['RECAPTCHA_SECRET'];
            $this->hcaptchaSiteKey = $_ENV['HCAPTCHA_SITE_KEY'];
            $this->hcaptchaSecret = $_ENV['HCAPTCHA_SECRET'];
            $this->turnstileSiteKey = $_ENV['TURNSTILE_SITE_KEY'];
            $this->turnstileSecret = $_ENV['TURNSTILE_SECRET'];
            $this->enableIpinfo = $_ENV['ENABLE_IPINFO'] === 'true';
            $this->ipinfoToken = $_ENV['IPINFO_TOKEN'];
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * 保存配置信息
     * @return bool
     */
    public function saveToEnv(): bool
    {
        try {
            $env = parse_ini_file(Yii::getAlias('@app/.env'));
            if($env === false) {
                return false;
            }
            $env['SITE_TITLE'] = $this->siteTitle;
            $env['REGISTRATION_ENABLED'] = $this->registrationEnabled ? 'true' : 'false';
            $env['DOMAIN'] = $this->domain;
            $env['VERIFY_PROVIDER'] = $this->verifyProvider;
            $env['RECAPTCHA_SITE_KEY'] = $this->recaptchaSiteKey;
            $env['RECAPTCHA_SECRET'] = $this->recaptchaSecret;
            $env['HCAPTCHA_SITE_KEY'] = $this->hcaptchaSiteKey;
            $env['HCAPTCHA_SECRET'] = $this->hcaptchaSecret;
            $env['TURNSTILE_SITE_KEY'] = $this->turnstileSiteKey;
            $env['TURNSTILE_SECRET'] = $this->turnstileSecret;
            $env['ENABLE_IPINFO'] = $this->enableIpinfo ? 'true' : 'false';
            $env['IPINFO_TOKEN'] = $this->ipinfoToken;
            $data = array_map(function ($key, $value) {
                return "$key=$value";
            }, array_keys($env), $env);
            return !(file_put_contents('.env', implode("\n", $data)) == false);
        } catch (Exception) {
            return false;
        }
    }
}