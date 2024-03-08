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
use yii\web\RangeNotSatisfiableHttpException;
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
                            'roles' => ['user'], // only user can do these
                        ],
                        [
                            'allow' => true,
                            'actions' => ['login', 'register', 'verify-two-factor'],
                            'roles' => ['?', '@'], // everyone can access public share
                        ],
                        [
                            'allow' => true,
                            'actions' => ['logout', 'setup-two-factor', 'change-password', 'download-recovery-codes', 'remove-two-factor', 'set-theme'],
                            'roles' => ['@'], // only logged-in user can do these
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
                        'download-recovery-codes' => ['GET'],
                        'remove-two-factor' => ['POST'],
                        'verify-two-factor' => ['GET', 'POST'],
                        'set-theme' => ['POST'],
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
                // 验证用户名和密码
                $user = User::findOne(['username' => $model->username]);
                if ($user !== null && $user->validatePassword($model->password)) {
                    // 如果用户启用了二步验证，将用户重定向到二步验证页面
                    if ($user->is_otp_enabled) {
                        Yii::$app->session->set('login_verification', ['id' => $user->id]);
                        return $this->redirect(['user/verify-two-factor']);
                    } else {
                        //login without 2FA
                        $user->last_login = date('Y-m-d H:i:s');
                        $user->last_login_ip = Yii::$app->request->userIP;
                        if (!$user->save(false)) {
                            Yii::$app->session->setFlash('error', '登陆成功，但出现了内部错误');
                        }
                        Yii::$app->user->login($user, $model->rememberMe ? 3600 * 24 * 30 : 0);
                        return $this->goHome();
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
     * @return Response|string
     */
    public function actionVerifyTwoFactor(): Response|string
    {
        if (!Yii::$app->session->has('login_verification')) {
            Yii::$app->session->setFlash('error', '非法访问');
            return $this->goHome();
        }

        $model = new User();
        $user = User::findOne(Yii::$app->session->get('login_verification')['id']);

        if ($model->load(Yii::$app->request->post())) {
            // 验证二步验证代码
            if(!is_null($model->totp_input)){
                $otp = TOTP::createFromSecret($user->otp_secret);
                if ($otp->verify($model->totp_input)) {
                    $user->last_login = date('Y-m-d H:i:s');
                    $user->last_login_ip = Yii::$app->request->userIP;
                    if (!$user->save(false)) {
                        Yii::$app->session->setFlash('error', '登陆成功，但出现了内部错误');
                    }
                    Yii::$app->user->login($user, $model->rememberMe ? 3600 * 24 * 30 : 0);
                    Yii::$app->session->remove('login_verification');
                    return $this->goHome();
                } else {
                    Yii::$app->session->setFlash('error', '二步验证代码错误');
                }
            }elseif (!is_null($model->recoveryCode_input)) {
                $recoveryCodes = explode(',', $user->recovery_codes);
                if (in_array($model->recoveryCode_input, $recoveryCodes)) {
                    //remove the used recovery code
                    $recoveryCodes = array_diff($recoveryCodes, [$model->recoveryCode_input]);
                    $user->recovery_codes = implode(',', $recoveryCodes);
                    $user->last_login = date('Y-m-d H:i:s');
                    $user->last_login_ip = Yii::$app->request->userIP;
                    if (!$user->save(false)) {
                        Yii::$app->session->setFlash('error', '登陆成功，但出现了内部错误');
                    }
                    Yii::$app->session->setFlash('success', '登陆成功，但请注意已经使用的恢复代码已失效');
                    Yii::$app->user->login($user, $model->rememberMe ? 3600 * 24 * 30 : 0);
                    Yii::$app->session->remove('login_verification');
                    return $this->goHome();
                } else {
                    Yii::$app->session->setFlash('error', '恢复代码错误');
                }
            }else{
                Yii::$app->session->setFlash('error', '请输入二步验证代码或恢复代码');
            }
        }

        return $this->render('verifyTwoFactor', [
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
     * @param string|null $focus
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
            $totp->setLabel('NetDisk_' . $model->name);
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
                    'is_otp_enabled' => $model->is_otp_enabled
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
            'is_otp_enabled' => $model->is_otp_enabled == 1
        ]);
    }

    /**
     * 更改密码
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
     * 启用二步验证
     * @return Response
     * @throws Exception
     */
    public function actionSetupTwoFactor(): Response
    {
        $user = Yii::$app->user->identity;

        if ($user->load(Yii::$app->request->post())) {
            $totp_secret = $user->otp_secret;
            $totp_input = $user->totp_input;

            $otp = TOTP::createFromSecret($totp_secret);
            if ($otp->verify($totp_input)) {
                $recoveryCodes = $this->generateRecoveryCodes();
                $user->is_otp_enabled = 1;
                $user->recovery_codes = implode(',', $recoveryCodes);
                $user->save();
                Yii::$app->session->setFlash('success', '二步验证已启用');
            } else {
                Yii::$app->session->setFlash('error', '二步验证启用失败，请重新添加');
            }
        }
        return $this->redirect(['user/info']);
    }

    /**
     * 移除二步验证
     */
    public function actionRemoveTwoFactor(): void
    {
        $user = Yii::$app->user->identity;
        if ($user->is_otp_enabled) {
            $user->otp_secret = null;
            $user->is_otp_enabled = 0;
            $user->recovery_codes = null;
            $user->save();
            Yii::$app->session->setFlash('success', '二步验证已关闭');
        } else {
            Yii::$app->session->setFlash('error', '二步验证未启用,无需关闭');
        }
    }

    /**
     * 生成10组随机的恢复代码
     * @return array
     * @throws Exception
     */
    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = Yii::$app->security->generateRandomString(10);
        }
        return $codes;
    }

    /**
     * 获取恢复代码(以txt文本的形式提供)
     * @return Response|\yii\console\Response
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionDownloadRecoveryCodes(): Response|\yii\console\Response
    {
        // 获取当前登录的用户模型
        $user = Yii::$app->user->identity;

        // 检查用户是否启用了 TOTP
        if ($user->is_otp_enabled) {
            // 获取恢复代码
            $recoveryCodesString = implode("\n", explode(',', $user->recovery_codes));
            // 发送恢复代码给用户
            return Yii::$app->response->sendContentAsFile(
                $recoveryCodesString,
                'recovery_codes.txt',
                ['mimeType' => 'text/plain']
            );
        } else {
            // 如果用户没有启用 TOTP，返回一个错误消息
            Yii::$app->session->setFlash('error', '获取失败，您还没有启用二步验证。');
            return $this->redirect(['user/info']);
        }
    }

    /**
     * 更改用户主题
     * @return Response
     */
    public function actionSetTheme(): Response
    {
        $darkMode = Yii::$app->request->post('dark_mode', 0);
        $user = Yii::$app->user->identity;
        $user->dark_mode = $darkMode;
        $user->save();
        return $this->asJson(['success' => true]);
    }
}
