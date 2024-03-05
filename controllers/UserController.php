<?php

namespace app\controllers;

use app\models\User;
use app\utils\FileSizeHelper;
use OTPHP\TOTP;
use ReCaptcha\ReCaptcha;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\httpclient\Client;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['delete', 'info'],
                            'roles' => ['user'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['login', 'register'],
                            'roles' => ['?', '@'], // everyone can access public share
                        ],
                        [
                            'allow' => true,
                            'actions' => ['logout', 'setup-two-factor', 'change-password'],
                            'roles' => ['@'], // everyone can access public share
                        ]
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'login' => ['GET', 'POST'],
                        'logout' => ['GET', 'POST'],
                        'register' => ['GET', 'POST'],
                        'delete' => ['POST'],
                        'info' => ['GET', 'POST'],
                        'change-password' => ['POST'],
                        'setup-two-factor' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * 删除账户(仅自身)
     * @return Response
     */
    public function actionDelete(): Response
    {
        $model = Yii::$app->user->identity;

        if ($model->deleteAccount()) {
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('success', '账户删除成功');
            return $this->redirect(['user/login']);
        } else {
            Yii::$app->session->setFlash('error', '账户删除失败');
            return $this->redirect(['user/info']);
        }

    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): User
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Displays the login page.
     * visit via https://devs.chenx221.cyou:8081/index.php?r=user%2Flogin
     *
     * @return string|Response
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', '账户已登录，请不要重复登录');
            return $this->goHome();
        }

        $model = new User(['scenario' => 'login']);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // 根据 verifyProvider 的值选择使用哪种验证码服务
            $verifyProvider = Yii::$app->params['verifyProvider'];
            $captchaResponse = null;
            $isCaptchaValid = false;
            if ($verifyProvider === 'reCAPTCHA') {
                $captchaResponse = Yii::$app->request->post('g-recaptcha-response', null);
                $isCaptchaValid = $this->validateRecaptcha($captchaResponse);
            } elseif ($verifyProvider === 'hCaptcha') {
                $captchaResponse = Yii::$app->request->post('h-captcha-response', null);
                $isCaptchaValid = $this->validateHcaptcha($captchaResponse);
            } elseif ($verifyProvider === 'Turnstile') {
                $captchaResponse = Yii::$app->request->post('cf-turnstile-response', null);
                $isCaptchaValid = $this->validateTurnstile($captchaResponse);
            }

            if (($captchaResponse !== null && $isCaptchaValid) || ($verifyProvider === 'None')) {
                if ($model->login()) {
                    //login success
                    $user = Yii::$app->user->identity;
                    $user->last_login = date('Y-m-d H:i:s');
                    $user->last_login_ip = Yii::$app->request->userIP;
                    if ($user->save(false)) {
                        return $this->goBack();
                    } else {
                        Yii::$app->session->setFlash('error', '登陆成功，但出现了内部错误');
                    }
                } else {
                    Yii::$app->session->setFlash('error', '用户名密码错误或账户已禁用');
                }
            } else {
                Yii::$app->session->setFlash('error', '请等待验证码加载并完成验证');
            }
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * 验证 reCAPTCHA 的响应
     * 无法保证这项服务在中国大陆的可用性
     * @param $recaptchaResponse
     * @return bool
     */
    private function validateRecaptcha($recaptchaResponse): bool
    {
        $recaptcha = new ReCaptcha(Yii::$app->params['reCAPTCHA']['secret']);
        $resp = $recaptcha->verify($recaptchaResponse, $_SERVER['REMOTE_ADDR']);

        return $resp->isSuccess();
    }

    /**
     * 验证 hCaptcha 的响应
     * @param $hcaptchaResponse
     * @return bool
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    private function validateHcaptcha($hcaptchaResponse): bool
    {
        $hcaptchaSecret = Yii::$app->params['hCaptcha']['secret'];
        $verifyUrl = 'https://api.hcaptcha.com/siteverify';

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($verifyUrl)
            ->setData(['secret' => $hcaptchaSecret, 'response' => $hcaptchaResponse])
            ->send();

        if ($response->isOk) {
            $responseData = $response->getData();
            return isset($responseData['success']) && $responseData['success'] === true;
        }

        return false;
    }

    /**
     * 验证 Turnstile 的响应
     * @param $turnstileResponse
     * @return bool
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    private function validateTurnstile($turnstileResponse): bool
    {
        $turnstileSecret = Yii::$app->params['Turnstile']['secret'];
        $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($verifyUrl)
            ->setData(['secret' => $turnstileSecret, 'response' => $turnstileResponse])
            ->send();

        if ($response->isOk) {
            $responseData = $response->getData();
            return isset($responseData['success']) && $responseData['success'] === true;
        }

        return false;
    }

    /**
     * Logs out the current user.
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Displays the registration page.
     * visit via https://devs.chenx221.cyou:8081/index.php?r=user%2Fregister
     * @return string|Response
     * @throws Exception
     */
    public function actionRegister(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', '账户已登录，无法进行注册操作');
            return $this->goHome();
        }

        $model = new User(['scenario' => 'register']);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // 根据 verifyProvider 的值选择使用哪种验证码服务
            $verifyProvider = Yii::$app->params['verifyProvider'];
            $captchaResponse = null;
            $isCaptchaValid = false;
            if ($verifyProvider === 'reCAPTCHA') {
                $captchaResponse = Yii::$app->request->post('g-recaptcha-response', null);
                $isCaptchaValid = $this->validateRecaptcha($captchaResponse);
            } elseif ($verifyProvider === 'hCaptcha') {
                $captchaResponse = Yii::$app->request->post('h-captcha-response', null);
                $isCaptchaValid = $this->validateHcaptcha($captchaResponse);
            } elseif ($verifyProvider === 'Turnstile') {
                $captchaResponse = Yii::$app->request->post('cf-turnstile-response', null);
                $isCaptchaValid = $this->validateTurnstile($captchaResponse);
            }

            if (($captchaResponse !== null && $isCaptchaValid) || ($verifyProvider === 'None')) {
                $raw_password = $model->password;
                $model->password = Yii::$app->security->generatePasswordHash($raw_password);
                $model->auth_key = Yii::$app->security->generateRandomString();
                $model->created_at = date('Y-m-d H:i:s');
                $model->role = 'user';
                $model->name = $model->username; //用户默认昵称为用户名，后期可以修改
                if ($model->save(false)) { // save without validation
                    $userFolder = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->id;
                    if (!is_dir($userFolder)) {
                        mkdir($userFolder);
                    }
                    Yii::$app->session->setFlash('success', 'Registration successful. You can now log in.');
                    return $this->redirect(['login']);
                } else {
                    $model->password = $raw_password;
                    Yii::$app->session->setFlash('error', 'Failed to register user.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Invalid captcha.');
            }
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionInfo(string $focus = null): Response|string
    {
        $model = Yii::$app->user->identity;
        $usedSpace = FileSizeHelper::getDirectorySize(Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id);
        $vaultUsedSpace = 0;  // 保险箱已用空间，暂时为0
        $storageLimit = $model->storage_limit;
        $totp_secret = null;
        $totp_url = null;
        if (!$model->is_otp_enabled) {
            $totp = TOTP::generate();
            $totp_secret = $totp->getSecret();
            $totp->setLabel('NetDisk_'.$model->name);
            $totp_url = $totp->getProvisioningUri();
        }
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '个人简介已更新');
                return $this->render('info', [
                    'model' => $model,
                    'usedSpace' => $usedSpace, // B
                    'vaultUsedSpace' => $vaultUsedSpace, // B
                    'storageLimit' => $storageLimit, // MB
                    'focus' => 'bio',
                    'totp_secret' => $totp_secret,
                    'totp_url' => $totp_url,
                ]);
            }
        }
        return $this->render('info', [
            'model' => $model,
            'usedSpace' => $usedSpace, // B
            'vaultUsedSpace' => $vaultUsedSpace, // B
            'storageLimit' => $storageLimit, // MB
            'focus' => $focus,
            'totp_secret' => $totp_secret,
            'totp_url' => $totp_url,
        ]);
    }

    /**
     * @return Response|string
     * @throws Exception
     */
    public function actionChangePassword(): Response|string
    {
        $model = Yii::$app->user->identity;
        $model->scenario = 'changePassword';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->password = Yii::$app->security->generatePasswordHash($model->newPassword);
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Password changed successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to change password.');
            }
        }
        return $this->redirect(['user/info', 'focus' => 'password']);
    }

    /**
     * @return string
     */
    public function actionSetupTwoFactor(): string
    {
        $user = Yii::$app->user->identity;
        $totp = TOTP::create();
        $user->otp_secret = $totp->getSecret();
        $user->is_otp_enabled = true;
        $user->save(false);

        $otpauth = $totp->getProvisioningUri($user->username);
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($otpauth);

        return $this->render('setup-two-factor', ['qrCodeUrl' => $qrCodeUrl]);
    }

}
