<?php

namespace app\controllers;

use app\models\User;
use app\models\UserSearch;
use app\utils\AdminSword;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdminController extends Controller
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
                            'actions' => ['index', 'system', 'user', 'info', 'user-view', 'user-create', 'user-update', 'user-delete', 'user-totpoff', 'user-pwdreset'],
                            'roles' => ['admin'], // only admin can do these
                        ]
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'index' => ['GET'],
                        'system' => ['GET'],
                        'user' => ['GET'],
                        'user-view' => ['GET', 'POST'],
                        'user-create' => ['GET', 'POST'],
                        'user-update' => ['GET', 'POST'],
                        'user-delete' => ['POST'],
                        'info' => ['GET'],
                        'user-totpoff' => ['POST'],
                        'user-pwdreset' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function init(): void
    {
        parent::init();
        $this->layout = 'admin_main';
    }

    /**
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * @return string
     */
    public function actionSystem(): string
    {
        return $this->render('system');
    }

    /**
     * Lists all User.
     *
     * @return string
     */
    public function actionUser(): string
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('user', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id ID
     * @throws NotFoundHttpException|Throwable if the model cannot be found
     */
    public function actionUserView(int $id): array|string
    {
        $model = $this->findModel($id);

        if (isset($_POST['hasEditable'])) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (isset($_POST['name'])) { //修改昵称
                $oldValue = $model->name;
                $model->name = $_POST['name'];
                if ($model->save(true, ['name'])) {
                    return ['output' => $model->name, 'message' => ''];
                } else {
                    return ['output' => $oldValue, 'message' => 'Incorrect Value! Please reenter.'];
                }
            } elseif (isset($_POST['status'])) { //修改用户状态
                if ($id == Yii::$app->user->id) {
                    return ['output' => 'ERROR', 'message' => 'You cannot change your own status'];
                }
                $oldValue = $model->status;
                $model->status = $_POST['status'];
                $status = $this->actionUserDelete($id, $model->status);
                if ($status == 1) {
                    return ['output' => $model->status == 0 ? '禁用' : '启用', 'message' => ''];
                } elseif ($status == 0) {
                    return ['output' => $oldValue == 0 ? '禁用' : '启用', 'message' => '删除失败'];
                } elseif ($status == 2) {
                    return ['output' => $oldValue == 0 ? '禁用' : '启用', 'message' => '无需操作'];
                } elseif ($status == 3) {
                    return ['output' => $oldValue == 0 ? '禁用' : '启用', 'message' => '操作不允许'];
                }
            } elseif (isset($_POST['bio'])) { //修改用户简介
                $oldValue = $model->bio;
                $model->bio = $_POST['bio'];
                if ($model->save(true, ['bio'])) {
                    return ['output' => $model->bio, 'message' => ''];
                } else {
                    return ['output' => $oldValue, 'message' => 'Incorrect Value! Please reenter.'];
                }
            } else {
                return ['output' => '', 'message' => ''];
            }
        }

        return $this->render('user_view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     * @throws Exception
     */
    public function actionUserCreate(): Response|string
    {
        $model = new User(['scenario' => 'addUser']);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->validate()) {
                $raw_password = $model->password;
                $model->password = Yii::$app->security->generatePasswordHash($raw_password);
                $model->auth_key = Yii::$app->security->generateRandomString();
                $model->role = $this->request->post('User')['role'];
                $model->created_at = date('Y-m-d H:i:s');
                $model->name = $model->username; //用户默认昵称为用户名，后期可以修改
                if ($model->save(false)) { // save without validation
                    if ($model->role == 'user') {
                        $userFolder = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->id;
                        if (!is_dir($userFolder)) {
                            mkdir($userFolder);
                        }
                        $secretFolder = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . $model->id . '.secret';
                        if (!is_dir($secretFolder)) {
                            mkdir($secretFolder);
                        }
                    }
                    Yii::$app->session->setFlash('success', '用户添加成功');
                    return $this->redirect(['user-view', 'id' => $model->id]);
                } else {
                    $model->loadDefaultValues();
                    $model->password = $raw_password;
                    Yii::$app->session->setFlash('error', '用户添加失败');
                }
            }
        }
        $model->loadDefaultValues(true);
        return $this->render('user_create', [
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
    public function actionUserUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['user_view', 'id' => $model->id]);
        }

        return $this->render('user_update', [
            'model' => $model,
        ]);
    }

    /**
     * 0: delete failed
     * 1: delete success
     * 2: nothing to do
     * 3: operation not allowed
     * @param int $id ID
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     */
    protected function actionUserDelete(int $id, int $operation): int
    {
        $user = $this->findModel($id);
        if ($user->status == $operation) {
            // nothing to do
            return 2;
        }
        if ($operation != 1 && $operation != 0) {
            return 3;
        }
        if ($user->deleteAccount($operation == 1)) {
            $logout_result = '';
            if ($operation == 0) {
                AdminSword::forceUserLogout($id);
            }
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionUserTotpoff(int $id)
    {
        try {
            $user = $this->findModel($id);
        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', '用户不存在');
            return $this->redirect(['user']);
        }
        if ($user->is_otp_enabled) {
            $user->otp_secret = null;
            $user->is_otp_enabled = 0;
            $user->recovery_codes = null;
            $user->save(false);
            Yii::$app->session->setFlash('success', '二步验证已关闭');
            return $this->redirect(['user-view', 'id' => $id]);
        } else {
            Yii::$app->session->setFlash('error', '二步验证未启用,无需关闭');
            return $this->redirect(['user-view', 'id' => $id]);
        }
    }

    /**
     * @return Response
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionUserPwdreset(): Response
    {
        $id = Yii::$app->request->post('User')['id'];
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', '密码重置成功');
            } else {
                Yii::$app->session->setFlash('error', '密码重置失败');
            }
        }
        return $this->redirect(['user-view', 'id' => $id]);
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
     * @return string
     */
    public function actionInfo(): string
    {
        return $this->render('info');
    }
}
