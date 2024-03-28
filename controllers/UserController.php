<?php

namespace app\controllers;

use app\models\PublicKeyCredentialSourceRepository;
use app\models\User;
use app\utils\FileSizeHelper;
use JsonException;
use OTPHP\TOTP;
use Random\RandomException;
use ReCaptcha\ReCaptcha;
use Throwable;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\Exception\AuthenticatorResponseVerificationException;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
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
                            'actions' => ['login', 'register', 'verify-two-factor', 'verify-assertion', 'request-assertion-options'],
                            'roles' => ['?', '@'], // everyone can access public share
                        ],
                        [
                            'allow' => true,
                            'actions' => ['logout', 'setup-two-factor', 'change-password', 'download-recovery-codes', 'remove-two-factor', 'set-theme', 'change-name', 'create-credential-options', 'create-credential', 'credential-list', 'credential-delete'],
                            'roles' => ['@'], // only logged-in user can do these ( admin included )
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
                        'change-name' => ['POST'],
                        'create-credential-options' => ['GET'],
                        'create-credential' => ['POST'],
                        'request-assertion-options' => ['GET'],
                        'verify-assertion' => ['POST'],
                        'credential-list' => ['GET'],
                        'credential-delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function init(): void
    {
        parent::init();

        if (Yii::$app->user->can('admin')) {
            $this->layout = 'admin_main';
        } elseif (Yii::$app->user->isGuest) {
            $this->layout = 'guest_main';
        } else {
            $this->layout = 'main';
        }
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
     * @return array
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    protected function checkCaptcha(): array
    {
        $verifyProvider = Yii::$app->params['verifyProvider'];
        $captchaResponse = null;
        $isCaptchaValid = false;
        if ($verifyProvider === 'reCAPTCHA') {
            $captchaResponse = Yii::$app->request->post('g-recaptcha-response');
            $isCaptchaValid = $this->validateRecaptcha($captchaResponse);
        } elseif ($verifyProvider === 'hCaptcha') {
            $captchaResponse = Yii::$app->request->post('h-captcha-response');
            $isCaptchaValid = $this->validateHcaptcha($captchaResponse);
        } elseif ($verifyProvider === 'Turnstile') {
            $captchaResponse = Yii::$app->request->post('cf-turnstile-response');
            $isCaptchaValid = $this->validateTurnstile($captchaResponse);
        }
        return array($verifyProvider, $captchaResponse, $isCaptchaValid);
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
     * 查找公钥凭证模型
     * @param $id
     * @return PublicKeyCredentialSourceRepository|null
     * @throws NotFoundHttpException
     */
    protected function findCredentialModel($id): ?PublicKeyCredentialSourceRepository
    {
        if (($model = PublicKeyCredentialSourceRepository::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 显示登录页并接收登录请求
     * GET时显示登录页，如果带有username参数则直接到输入密码页
     * POST时验证用户名密码，如果用户启用了二步验证则重定向到二步验证页面
     *
     * @param string|null $username
     * @return string|Response
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionLogin(string $username = null): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', '账户已登录，请不要重复登录');
            return $this->goHome();
        }

        $model = new User(['scenario' => 'login']);

        if (($model->load(Yii::$app->request->post()) && $model->validate()) | $username !== null) {
            if ($model->password === null) {
                if ($username !== null) {
                    $model->username = $username;
                }
                $user = User::findOne(['username' => $model->username]);
                if ($user === null) {
                    Yii::$app->session->setFlash('error', '用户不存在');
                    return $this->render('login', [
                        'model' => $model,
                    ]);
                } elseif ($user->status === 0) {
                    Yii::$app->session->setFlash('error', '用户已停用，请联系管理员获取支持');
                    return $this->render('login', [
                        'model' => $model,
                    ]);
                }
                $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();
                $fidoCredentials = $publicKeyCredentialSourceRepository->findAllForUserEntity($user);
                if (empty($fidoCredentials) | $username !== null) { //未设置FIDO
                    return $this->render('_login1', [
                        'model' => $model,
                    ]);
                } else { //已设置FIDO
                    return $this->render('_login2', [
                        'model' => $model,
                    ]);
                }
            } else {
                // 根据 verifyProvider 的值选择使用哪种验证码服务
                list($verifyProvider, $captchaResponse, $isCaptchaValid) = $this->checkCaptcha();

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
                            // user to home page, admin to admin/index
                            if (Yii::$app->user->can('admin')) {
                                return $this->redirect(['admin/index']);
                            } else {
                                return $this->goHome();
                            }

                        }
                    } else {
                        Yii::$app->session->setFlash('error', '用户名密码错误或账户已禁用');
                    }
                } else {
                    Yii::$app->session->setFlash('error', '请等待验证码加载并完成验证');
                }
            }
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
        return $this->render('_login1', [
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
            if (!is_null($model->totp_input)) {
                $otp = TOTP::createFromSecret($user->otp_secret);
                if ($otp->verify($model->totp_input)) {
                    $user->last_login = date('Y-m-d H:i:s');
                    $user->last_login_ip = Yii::$app->request->userIP;
                    if (!$user->save(false)) {
                        Yii::$app->session->setFlash('error', '登陆成功，但出现了内部错误');
                    }
                    Yii::$app->user->login($user, $model->rememberMe ? 3600 * 24 * 30 : 0);
                    Yii::$app->session->remove('login_verification');
                    if (Yii::$app->user->can('admin')) {
                        return $this->redirect(['admin/index']);
                    } else {
                        return $this->goHome();
                    }
                } else {
                    Yii::$app->session->setFlash('error', '二步验证代码错误');
                }
            } elseif (!is_null($model->recoveryCode_input)) {
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
                    if (Yii::$app->user->can('admin')) {
                        return $this->redirect(['admin/index']);
                    } else {
                        return $this->goHome();
                    }
                } else {
                    Yii::$app->session->setFlash('error', '恢复代码错误');
                }
            } else {
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

        return $this->verifyResponse($verifyUrl, $hcaptchaSecret, $hcaptchaResponse);
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

        return $this->verifyResponse($verifyUrl, $turnstileSecret, $turnstileResponse);
    }

    /**
     * Logs out the current user.
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        Yii::$app->session->setFlash('success', '已登出');
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
            list($verifyProvider, $captchaResponse, $isCaptchaValid) = $this->checkCaptcha();

            if (($captchaResponse !== null && $isCaptchaValid) || ($verifyProvider === 'None')) {
                $raw_password = $model->password;
                $model->password = Yii::$app->security->generatePasswordHash($raw_password);
                $model->auth_key = Yii::$app->security->generateRandomString();
                $model->created_at = date('Y-m-d H:i:s');
                $model->role = 'user'; // 管理员只能通过现有管理员操作添加
                $model->name = $model->username; //用户默认昵称为用户名，后期可以修改
                if ($model->save(false)) { // save without validation
                    $userFolder = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->id;
                    if (!is_dir($userFolder)) {
                        mkdir($userFolder);
                    }
                    $secretFolder = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->id . '.secret';
                    if (!is_dir($secretFolder)) {
                        mkdir($secretFolder);
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
     * 显示用户信息
     * 同时接收post请求来修改用户bio
     * 支持参数focus指定页面加载时自动展开哪一块区域 [null,storage,bio,password,advanced]
     *
     * @param string|null $focus
     * @return string|Response
     */
    public function actionInfo(string $focus = null): Response|string
    {
        $model = Yii::$app->user->identity;
        $usedSpace = FileSizeHelper::getUserHomeDirSize();
        $vaultUsedSpace = FileSizeHelper::getUserVaultDirSize();
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
        $org_password = $model->password;
        //verify old password
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (!Yii::$app->security->validatePassword($model->oldPassword, $org_password)) {
                Yii::$app->session->setFlash('error', '原密码错误');
            } else {
                $model->password = Yii::$app->security->generatePasswordHash($model->newPassword);
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Password changed successfully.');
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to change password.');
                }
            }
        }else{
            Yii::$app->session->setFlash('error', 'Failed to validate password.');
        }
        if (Yii::$app->user->can('admin')) {
            return $this->redirect(['admin/info', 'focus' => 'password']);
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
        if (Yii::$app->user->can('admin')) {
            return $this->redirect(['admin/info']);
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
            $user->save(false);
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
            if (Yii::$app->user->can('admin')) {
                return $this->redirect(['admin/info', 'focus' => 'advanced']);
            }
            return $this->redirect(['user/info', 'focus' => 'advanced']);
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

    /**
     * 修改用户昵称
     * @return Response
     */
    public function actionChangeName(): Response
    {
        $model = Yii::$app->user->identity;
        if ($model->load(Yii::$app->request->post()) && $model->save(true, ['name'])) {
            Yii::$app->session->setFlash('success', '昵称已更新');
        } else {
            Yii::$app->session->setFlash('error', '昵称更新失败');
        }
        if (Yii::$app->user->can('admin')) {
            return $this->redirect(['admin/info']);
        }
        return $this->redirect(['user/info']);
    }

    /**
     * 获取所有的公钥凭证
     * @return Response|string
     */
    public function actionCredentialList(): Response|string
    {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_creIndex', [
                'dataProvider' => new ActiveDataProvider([
                    'query' => PublicKeyCredentialSourceRepository::find()->where(['user_id' => Yii::$app->user->id]),
                ]),
            ]);
        } else {
            Yii::$app->session->setFlash('error', '非Ajax请求');
            if (Yii::$app->user->can('admin')) {
                return $this->redirect(['admin/info']);
            }
            return $this->redirect('info');
        }
    }

    /**
     * 删除指定的公钥凭证
     * @param $id
     * @return Response|string
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionCredentialDelete($id): Response|string
    {
        if (Yii::$app->request->isPjax) {
            $publicKeyCredentialSourceRepository = $this->findCredentialModel($id);
            if ($publicKeyCredentialSourceRepository->user_id !== Yii::$app->user->id) {
                Yii::$app->session->setFlash('error', '非法操作');
                if (Yii::$app->user->can('admin')) {
                    return $this->redirect(['admin/info']);
                }
                return $this->redirect('info');
            }
            $publicKeyCredentialSourceRepository->delete();
            return $this->renderAjax('_creIndex', [
                'dataProvider' => new ActiveDataProvider([
                    'query' => PublicKeyCredentialSourceRepository::find()->where(['user_id' => Yii::$app->user->id]),
                ]),
            ]);
        } else {
            Yii::$app->session->setFlash('error', '非Pjax请求,无法删除');
            if (Yii::$app->user->can('admin')) {
                return $this->redirect(['admin/info']);
            }
            return $this->redirect('info');
        }
    }

    /*
     * 以下WebAuthn(FIFO)验证代码已经调好了，不要乱动
     */


    /**
     * 创建公钥凭证选项
     * @return Response
     * @throws RandomException
     */
    public function actionCreateCredentialOptions(): Response
    {
        $id = Yii::$app->params['domain'];
        $user = Yii::$app->user->identity;

        $rpEntity = PublicKeyCredentialRpEntity::create(
            'NetDisk Application',
            $id
        );

        $userEntity = PublicKeyCredentialUserEntity::create(
            $user->username,
            $user->id,
            $user->name,
        );

        $challenge = random_bytes(16);
        $publicKeyCredentialCreationOptions =
            PublicKeyCredentialCreationOptions::create(
                $rpEntity,
                $userEntity,
                $challenge
            );

        // 将选项存储在会话中，以便在后续的验证步骤中使用
        Yii::$app->session->set('publicKeyCredentialCreationOptions', $publicKeyCredentialCreationOptions);

        // 将选项发送给客户端
        return $this->asJson($publicKeyCredentialCreationOptions);
    }

    /**
     * 创建公钥凭证
     * @return Response
     */
    public function actionCreateCredential(): Response
    {
        $data = Yii::$app->request->getRawBody();
        $json_decode = json_decode($data, true);
        $fido_name = empty($json_decode['fido_name']) ? '未命名的设备' : $json_decode['fido_name'];
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
        $webauthnSerializerFactory = new WebauthnSerializerFactory($attestationStatementSupportManager);
        $serializer = $webauthnSerializerFactory->create();
        $publicKeyCredential = $serializer->deserialize($data, PublicKeyCredential::class, 'json');
        $authenticatorAttestationResponse = $publicKeyCredential->response;
        if (!$authenticatorAttestationResponse instanceof AuthenticatorAttestationResponse) {
            return $this->asJson(['message' => 'Invalid response type']);
        }

        // 什么时候更新开发文档?
        $ceremonyStepManagerFactory = new CeremonyStepManagerFactory();
        $ceremonyStepManager = $ceremonyStepManagerFactory->creationCeremony();
        $authenticatorAttestationResponseValidator = AuthenticatorAttestationResponseValidator::create(
            null,
            null,
            null,
            null,
            null,
            $ceremonyStepManager
        );

        $publicKeyCredentialCreationOptions = Yii::$app->session->get('publicKeyCredentialCreationOptions');
        try {
            $publicKeyCredentialSource = $authenticatorAttestationResponseValidator->check( //response -> source
                $authenticatorAttestationResponse,
                $publicKeyCredentialCreationOptions,
                Yii::$app->params['domain']
            );
            $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();
            $publicKeyCredentialSourceRepository->saveCredential($publicKeyCredentialSource, $fido_name); //receive source
            return $this->asJson(['verified' => true]);
        } catch (Throwable $e) {
            return $this->asJson(['message' => $e->getMessage(), 'verified' => false]);
        }
    }

    /**
     * 请求验证选项
     * @param string|null $username
     * @return Response
     * @throws RandomException
     */
    public function actionRequestAssertionOptions(string $username = null): Response
    {
        $user = null;
        if ($username !== null) {
            $user = User::findOne(['username' => $username]);
            if ($user === null) {
                return $this->asJson(['message' => 'User not found']);
            }
        } else {
            $user = Yii::$app->user->identity;
        }
        if ($user === null) {
            return $this->asJson(['message' => 'Guest? Sure?']);
        }
        $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();
        $registeredAuthenticators = $publicKeyCredentialSourceRepository->findAllForUserEntity($user);

        $allowedCredentials = array_map(
            static function (PublicKeyCredentialSourceRepository $credential): PublicKeyCredentialDescriptor {
                $data = $credential->data;
                $webauthnSerializerFactory = new WebauthnSerializerFactory(new AttestationStatementSupportManager());
                $publicKeyCredentialSource = $webauthnSerializerFactory->create()->deserialize($data, PublicKeyCredentialSource::class, 'json');
                return $publicKeyCredentialSource->getPublicKeyCredentialDescriptor();
            },
            $registeredAuthenticators
        );
        $publicKeyCredentialRequestOptions =
            PublicKeyCredentialRequestOptions::create(
                random_bytes(32), // Challenge
                allowCredentials: $allowedCredentials
            );
        Yii::$app->session->set('publicKeyCredentialRequestOptions', $publicKeyCredentialRequestOptions);

        return $this->asJson($publicKeyCredentialRequestOptions);
    }


    /**
     * 验证断言
     * 用于已登录情况下验证fifo设置是否成功
     * @param int $is_login
     * @param int $remember
     * @return Response
     * @throws JsonException
     */
    public function actionVerifyAssertion(int $is_login = 0, int $remember = 0): Response
    {
        $data = Yii::$app->request->getRawBody();

        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
        $webauthnSerializerFactory = new WebauthnSerializerFactory($attestationStatementSupportManager);
        $serializer = $webauthnSerializerFactory->create();

        $publicKeyCredential = $serializer->deserialize($data, PublicKeyCredential::class, 'json');

        $authenticatorAssertionResponse = $publicKeyCredential->response;
        if (!$authenticatorAssertionResponse instanceof AuthenticatorAssertionResponse) {
            return $this->asJson(['message' => 'Invalid response type']);
        }

        $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();
        $publicKeyCredentialSourceRepository1 = $publicKeyCredentialSourceRepository->findOneByCredentialId(
            $publicKeyCredential->id
        );
        if ($publicKeyCredentialSourceRepository1 === null) {
            return $this->asJson(['message' => 'Invalid credential']);
        }

        $PKCS = $webauthnSerializerFactory->create()->deserialize($publicKeyCredentialSourceRepository1->data, PublicKeyCredentialSource::class, 'json');
        $ceremonyStepManagerFactory = new CeremonyStepManagerFactory();
        $ceremonyStepManager = $ceremonyStepManagerFactory->requestCeremony();
        $authenticatorAssertionResponseValidator = AuthenticatorAssertionResponseValidator::create(
            null,
            null,
            null,
            null,
            null,
            $ceremonyStepManager
        );
        $publicKeyCredentialRequestOptions = Yii::$app->session->get('publicKeyCredentialRequestOptions');
        try {
            $publicKeyCredentialSource = $authenticatorAssertionResponseValidator->check(
                $PKCS, //credential source
                $authenticatorAssertionResponse, //user response
                $publicKeyCredentialRequestOptions,
                Yii::$app->params['domain'],
                $publicKeyCredentialSourceRepository1->user_id //我也不知道这个是什么，不过看了眼源码，移动设备验证时userhandle传入的是Null
            );
        } catch (AuthenticatorResponseVerificationException $e) {
            return $this->asJson(['message' => $e->getMessage(), 'verified' => false]);
        }


        if ($is_login === 1) {
            $user = User::findOne(['id' => $publicKeyCredentialSourceRepository1->user_id]);
            $user->last_login = date('Y-m-d H:i:s');
            $user->last_login_ip = Yii::$app->request->userIP;
            if (!$user->save(false)) {
                Yii::$app->session->setFlash('error', '登陆成功，但出现了内部错误');
            }
            Yii::$app->user->login($user, $remember === 1 ? 3600 * 24 * 30 : 0);
            $publicKeyCredentialSourceRepository1->saveCredential($publicKeyCredentialSource, '', false);
            if (Yii::$app->user->can('admin')) {
                return $this->asJson(['verified' => true, 'redirectTo' => 'index.php?r=admin%2Findex']);
            }
            return $this->asJson(['verified' => true, 'redirectTo' => 'index.php']);
        }
        // Optional, but highly recommended, you can save the credential source as it may be modified
        // during the verification process (counter may be higher).
        $publicKeyCredentialSourceRepository1->saveCredential($publicKeyCredentialSource, '', false);
        return $this->asJson(['verified' => true]);
    }

    /**
     * @param string $verifyUrl
     * @param mixed $hcaptchaSecret
     * @param $hcaptchaResponse
     * @return bool
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    private function verifyResponse(string $verifyUrl, mixed $hcaptchaSecret, $hcaptchaResponse): bool
    {
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
}
