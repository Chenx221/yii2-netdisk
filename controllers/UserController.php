<?php

namespace app\controllers;

use app\models\User;
use app\models\UserSearch;
use app\utils\FileSizeHelper;
use ReCaptcha\ReCaptcha;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
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
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new User();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
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
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
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
                    Yii::$app->session->setFlash('error', 'Invalid username or password.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Invalid captcha.');
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
    public function actionLogout()
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
                if ($model->save(false)) { // save without validation
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
    public function actionInfo(): Response|string
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', '请先登录');
            return $this->redirect(['user/login']);
        }

        $model = Yii::$app->user->identity;
        $dataDirectory = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id;
        $usedSpace = FileSizeHelper::getDirectorySize($dataDirectory);
        $vaultUsedSpace = 0;  // 保险箱已用空间，暂时为0
        $storageLimit = $model->storage_limit;

        return $this->render('info', [
            'model' => $model,
            'usedSpace' => $usedSpace, // B
            'vaultUsedSpace' => $vaultUsedSpace,
            'storageLimit' => $storageLimit, // MB
        ]);
    }

}
