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
                            'actions' => ['index', 'system', 'user', 'info', 'user-view', 'user-create', 'user-update', 'user-delete'],
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
                        'user-view' => ['GET','POST'],
                        'user-create' => ['GET', 'POST'],
                        'user-update' => ['GET', 'POST'],
                        'user-delete' => ['POST'],
                        'info' => ['GET'],
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
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUserView(int $id): array|string
    {
        $model = $this->findModel($id);
        if (isset($_POST['hasEditable'])) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $oldValue = $model->name;
            // TODO: Implement the logic to update the value of the model
             if ($model->load($_POST)) {
                // read or convert your posted information
                $value = $model->name;

                // validate if any errors
                if ($model->save(true,['name'])) {
                    // return JSON encoded output in the below format on success with an empty `message`
                    return ['output' => $value, 'message' => ''];
                } else {
                    // alternatively you can return a validation error (by entering an error message in `message` key)
                    return ['output' => $oldValue, 'message' => 'Incorrect Value! Please reenter.'];
                }
            } // else if nothing to do always return an empty JSON encoded output
            else {
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
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionUserDelete(int $id): Response
    {
        $user = $this->findModel($id);
        $alreadyDisabled = $user->status == 0;
        $str = $alreadyDisabled ? '启用' : '禁用';
        if ($user->deleteAccount($alreadyDisabled)) {
            $logout_result = '';
            if (!$alreadyDisabled) {
                $logout_result = AdminSword::forceUserLogout($id);
            }
            Yii::$app->session->setFlash('success', '账户' . $str . '成功,' . $logout_result);
        } else {
            Yii::$app->session->setFlash('error', '账户' . $str . '失败');
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
