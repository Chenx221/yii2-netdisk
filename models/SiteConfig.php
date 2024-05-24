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
    public bool $clarityEnabled; // 启用 Microsoft Clarity
    public string $clarityId; // Clarity ID
    public bool $gaEnabled; // 启用 Google Analytics
    public string $gaId; // Google Analytics ID


    public function rules(): array
    {
        return [
            [['siteTitle', 'domain'], 'required'],
            [['siteTitle', 'domain', 'verifyProvider', 'recaptchaSiteKey', 'recaptchaSecret', 'hcaptchaSiteKey', 'hcaptchaSecret', 'turnstileSiteKey', 'turnstileSecret', 'ipinfoToken', 'clarityId', 'gaId'], 'string'],
            [['registrationEnabled', 'enableIpinfo', 'clarityEnabled', 'gaEnabled'], 'boolean'],
            ['verifyProvider', 'in', 'range' => ['reCAPTCHA', 'hCaptcha', 'Turnstile', 'None']],
            [['recaptchaSiteKey', 'recaptchaSecret'], 'required', 'when' => function ($model) {
                return $model->verifyProvider == 'reCAPTCHA';
            }, 'whenClient' => "function (attribute, value) {
                return $('#siteconfig-verifyprovider').val() == 'reCAPTCHA';
            }"],
            [['hcaptchaSiteKey', 'hcaptchaSecret'], 'required', 'when' => function ($model) {
                return $model->verifyProvider == 'hCaptcha';
            }, 'whenClient' => "function (attribute, value) {
                return $('#siteconfig-verifyprovider').val() == 'hCaptcha';
            }"],
            [['turnstileSiteKey', 'turnstileSecret'], 'required', 'when' => function ($model) {
                return $model->verifyProvider == 'Turnstile';
            }, 'whenClient' => "function (attribute, value) {
                return $('#siteconfig-verifyprovider').val() == 'Turnstile';
            }"],
            ['ipinfoToken', 'required', 'when' => function ($model) {
                return $model->enableIpinfo;
            }, 'whenClient' => "function (attribute, value) {
                return $('#siteconfig-enableipinfo').is(':checked');
            }"],
            ['clarityId', 'required', 'when' => function ($model) {
                return $model->clarityEnabled;
            }, 'whenClient' => "function (attribute, value) {
                return $('#siteconfig-clarityenabled').is(':checked');
            }"],
            ['gaId', 'required', 'when' => function ($model) {
                return $model->gaEnabled;
            }, 'whenClient' => "function (attribute, value) {
                return $('#siteconfig-gaenabled').is(':checked');
            }"],
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
            'clarityEnabled' => '启用 Microsoft Clarity',
            'clarityId' => 'Clarity ID',
            'gaEnabled' => '启用 Google Analytics (GA4)',
            'gaId' => 'Google Analytics ID',
        ];
    }

    public function attributeHelpTexts(): array
    {
        return [
            'siteTitle' => '你可以在这里设置网站的标题',
            'registrationEnabled' => '你可以在这里设置是否允许新用户注册<br>关闭注册后，只有管理员可以添加用户',
            'domain' => '你可以在这里设置网站使用的域名<br>开头不需要添加 http:// 或 https://<br>结尾不需要添加 /<br>本地测试时可以使用 localhost',
            'verifyProvider' => '你可以在这里设置验证码提供商<br>目前支持<a href=\'https://developers.google.com/recaptcha\' target=\'_blank\'>reCAPTCHA</a>、<a href=\'https://www.hcaptcha.com/\' target=\'_blank\'>hCaptcha</a>、<a href=\'https://www.cloudflare.com/en-ca/products/turnstile/\' target=\'_blank\'>Turnstile</a>或关闭验证码',
            'recaptchaSiteKey' => '请在这里填入reCAPTCHA Site Key',
            'recaptchaSecret' => '请在这里填入reCAPTCHA Secret',
            'hcaptchaSiteKey' => '请在这里填入hCaptcha Site Key',
            'hcaptchaSecret' => '请在这里填入hCaptcha Secret',
            'turnstileSiteKey' => '请在这里填入Turnstile Site Key',
            'turnstileSecret' => '请在这里填入Turnstile Secret',
            'enableIpinfo' => '是否使用<a href=\'https://ipinfo.io/\' target=\'_blank\'>ipinfo.io</a>查询站点上的ip信息',
            'ipinfoToken' => '请在这里填入IPinfo Token',
            'clarityEnabled' => '是否启用<a href=\'https://clarity.microsoft.com/\' target=\'_blank\'>Microsoft Clarity</a>',
            'clarityId' => '请在这里填入Clarity ID',
            'gaEnabled' => '是否启用<a href=\'https://analytics.google.com/\' target=\'_blank\'>Google Analytics</a>',
            'gaId' => '请在这里填入Google Analytics ID',
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
            $this->clarityEnabled = $_ENV['CLARITY_ENABLED'] === 'true';
            $this->clarityId = $_ENV['CLARITY_ID'];
            $this->gaEnabled = $_ENV['GA_ENABLED'] === 'true';
            $this->gaId = $_ENV['GA_ID'];
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
            if ($env === false) {
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
            $env['CLARITY_ENABLED'] = $this->clarityEnabled ? 'true' : 'false';
            $env['CLARITY_ID'] = $this->clarityId;
            $env['GA_ENABLED'] = $this->gaEnabled ? 'true' : 'false';
            $env['GA_ID'] = $this->gaId;
            $data = array_map(function ($key, $value) {
                return "$key=$value";
            }, array_keys($env), $env);
            file_put_contents(Yii::getAlias('@app/.env.pending'), implode("\n", $data));
            parse_ini_file(Yii::getAlias('@app/.env.pending'));
            $result= file_put_contents(Yii::getAlias('@app/.env'), implode("\n", $data)) == false;
            unlink(Yii::getAlias('@app/.env.pending'));
            return !($result);
        } catch (Exception $e) {
            unlink(Yii::getAlias('@app/.env.pending'));
            return false;
        }
    }
}