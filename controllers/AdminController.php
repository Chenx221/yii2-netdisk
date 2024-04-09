<?php

namespace app\controllers;

use app\models\CollectionSearch;
use app\models\CollectionTasks;
use app\models\CollectionUploadedSearch;
use app\models\DownloadLogs;
use app\models\LoginLogs;
use app\models\Share;
use app\models\ShareSearch;
use app\models\SiteConfig;
use app\models\User;
use app\models\UserSearch;
use app\utils\AdminSword;
use app\utils\FileSizeHelper;
use DateTime;
use OTPHP\TOTP;
use RuntimeException;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;
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
                            'actions' => ['index', 'system', 'user', 'info', 'user-view', 'user-create', 'user-update',
                                'user-delete', 'user-totpoff', 'user-pwdreset', 'login-log', 'access-log', 'collection-up-log',
                                'share-manage', 'share-manage-view', 'share-manage-delete', 'collection-manage', 'collection-manage-view',
                                'collection-manage-delete', 'notice-manage', 'feedback-manage', 'sysinfo'],
                            'roles' => ['admin'], // only admin can do these
                        ]
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'index' => ['GET'],
                        'system' => ['GET', 'POST'],
                        'user' => ['GET'],
                        'user-view' => ['GET', 'POST'],
                        'user-create' => ['GET', 'POST'],
                        'user-update' => ['GET', 'POST'],
                        'user-delete' => ['POST'],
                        'info' => ['GET', 'POST'],
                        'user-totpoff' => ['POST'],
                        'user-pwdreset' => ['POST'],
                        'login-log' => ['GET'],
                        'access-log' => ['GET'],
                        'collection-up-log' => ['GET'],
                        'share-manage' => ['GET'],
                        'share-manage-view' => ['GET'],
                        'share-manage-delete' => ['POST'],
                        'collection-manage' => ['GET'],
                        'collection-manage-view' => ['GET'],
                        'collection-manage-delete' => ['POST'],
                        'notice-manage' => ['GET'],
                        'feedback-manage' => ['GET'],
                        'sysinfo' => ['GET'],
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
     * @return string|Response
     * @throws HttpException
     */
    public function actionSystem(): Response|string
    {
        $siteConfig = new SiteConfig();
        if (!$siteConfig->loadFromEnv()) {
            throw new HttpException(500, 'Fatal error, Unable to load site configuration from . env file . ');
        }
        if (Yii::$app->request->isPost) {
            if ($siteConfig->load(Yii::$app->request->post()) && $siteConfig->validate()) {
                if ($siteConfig->saveToEnv()) {
                    Yii::$app->session->setFlash('success', '保存成功');
                    return $this->redirect(['system']);
                } else {
                    Yii::$app->session->setFlash('error', '保存失败');
                }
            }
        }
        return $this->render('system', [
            'siteConfig' => $siteConfig
        ]);
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
     * 显示、处理用户管理中的数据变更
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
                    return ['output' => $oldValue, 'message' => 'Incorrect Value!Please reenter . '];
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
                    return ['output' => $oldValue, 'message' => 'Incorrect Value!Please reenter . '];
                }
            } elseif (isset($_POST['storage_limit'])) { //修改用户存储限制
                $oldValue = $model->storage_limit;
                $input_limit = $_POST['storage_limit'];
                $limit = FileSizeHelper::getConvertedLimit($input_limit);
                switch ($limit) {
                    case -1:
                        $model->storage_limit = -1;
                        break;
                    case -2:
                        return ['output' => $oldValue, 'message' => '值不能为空'];
                    case -3:
                        return ['output' => $oldValue, 'message' => '格式错误'];
                    default:
                        $model->storage_limit = $limit;
                }
                if ($model->save(true, ['storage_limit'])) {
                    return ['output' => FileSizeHelper::formatMegaBytes($model->storage_limit), 'message' => ''];
                } else {
                    return ['output' => FileSizeHelper::formatMegaBytes($oldValue), 'message' => 'Incorrect Value!Please reenter . '];
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
                        $userFolder = Yii::getAlias(Yii::$app->params['dataDirectory']) . ' / ' . $model->id;
                        if (!is_dir($userFolder)) {
                            mkdir($userFolder);
                        }
                        $secretFolder = Yii::getAlias(Yii::$app->params['dataDirectory']) . ' / ' . $model->id . ' . secret';
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

        throw new NotFoundHttpException('The requested page does not exist . ');
    }

    /**
     * @param string|null $focus
     * @return Response|string
     */
    public function actionInfo(string $focus = null): Response|string
    {
        $model = Yii::$app->user->identity;
        $totp_secret = null;
        $totp_url = null;
        if (!$model->is_otp_enabled) {
            $totp = TOTP::generate();
            $totp_secret = $totp->getSecret();
            $totp->setLabel('NetDisk_' . $model->name);
            $totp_url = $totp->getProvisioningUri();
        }
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->save(true, ['bio'])) {
                Yii::$app->session->setFlash('success', '个人简介已更新');
                return $this->render('info', [
                    'model' => $model,
                    'focus' => 'bio',
                    'totp_secret' => $totp_secret,
                    'totp_url' => $totp_url,
                    'is_otp_enabled' => $model->is_otp_enabled
                ]);
            }
        }
        return $this->render('info', [
            'model' => $model,
            'focus' => $focus,
            'totp_secret' => $totp_secret,
            'totp_url' => $totp_url,
            'is_otp_enabled' => $model->is_otp_enabled == 1
        ]);
    }

    /**
     * login log
     * @return string
     */
    public function actionLoginLog(): string
    {
        $loginLogs = new LoginLogs();
        $dataProvider = $loginLogs->search($this->request->queryParams);
        return $this->render('login_log', [
            'searchModel' => $loginLogs,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionAccessLog(): string
    {
        $downloadLogs = new DownloadLogs();
        $dataProvider = $downloadLogs->search($this->request->queryParams);
        return $this->render('access_log', [
            'searchModel' => $downloadLogs,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionCollectionUpLog(): string
    {
        $collectionUploadedSearch = new CollectionUploadedSearch();
        $dataProvider = $collectionUploadedSearch->search($this->request->queryParams);
        return $this->render('collection_up_log', [
            'searchModel' => $collectionUploadedSearch,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionShareManage(): string
    {
        $searchModel = new ShareSearch();
        if ($this->request instanceof Request) {
            $dataProvider = $searchModel->search($this->request->queryParams);
            return $this->render('share_manage', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new RuntimeException('Invalid request type');
        }
    }

    /**
     * @param int $share_id
     * @return Share
     * @throws NotFoundHttpException
     */
    protected function findShareModel(int $share_id): Share
    {
        if (($model = Share::findOne(['share_id' => $share_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param int $share_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionShareManageView(int $share_id): string
    {
        return $this->render('share_manage_view', [
            'model' => $this->findShareModel($share_id),
        ]);
    }

    /**
     * @param int $share_id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionShareManageDelete(int $share_id): Response
    {
        $model = $this->findShareModel($share_id);
        $model->status = 0;
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Share delete successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete share.');
        }
        return $this->redirect(['share-manage']);
    }

    /**
     * @return string
     */
    public function actionCollectionManage(): string
    {
        $searchModel = new CollectionSearch();
        if ($this->request instanceof Request) {
            $dataProvider = $searchModel->search($this->request->queryParams);
            return $this->render('collection_manage', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new RuntimeException('Invalid request type');
        }
    }

    /**
     * Displays a single CollectionTasks model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCollectionManageView(int $id): string
    {
        return $this->render('collection_manage_view', [
            'model' => $this->findCollectionModel($id),
        ]);
    }

    /**
     * @param int $id
     * @return CollectionTasks
     * @throws NotFoundHttpException
     */
    protected function findCollectionModel(int $id): CollectionTasks
    {
        if (($model = CollectionTasks::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionCollectionManageDelete(int $id): Response
    {
        $model = $this->findCollectionModel($id);
        $model->status = 0;
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Task delete successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete task.');
        }
        return $this->redirect(['collection-manage']);
    }

    /**
     * @return string
     */
    public function actionNoticeManage(): string
    {
        return $this->render('notice_manage');
    }

    /**
     * @return string
     */
    public function actionFeedbackManage(): string
    {
        return $this->render('feedback_manage');
    }

    /**
     * @return string
     */
    public function actionSysinfo(): string
    {
        return $this->render('sysinfo');
    }

    /**
     * Get server status
     * 只兼容Windows和Linux
     * other不考虑
     * @return void
     */
    public function actionGetServerStatus(): void
    {
        //需要收集的信息
        //hostname
        //os
        //cpu
        //memory
        //server time
        //server uptime
        //is windows?
        //server load
        //server cpu usage
        //server memory usage
        //server swap usage
        //storage data drive
        //file system
        //drive size
        //drive used & free
        //dns server
        //gateway
        //network interface(status,speed,ipv4v6 address)
        //All users number
        //active users number(24h)
        //share number,collection number
        //php version,memory limit,max execution time,max upload size,max post size,extension
        //database type,version,size

        //get server hostname
        $hostname = php_uname('n');
        //get os
        $os = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' ' . php_uname('m');
        // is windows?
        $is_windows = stripos($os, 'windows') !== false;
        if ($is_windows) {
            //get cpu model for windows
            $cpu = implode("\n", array_slice(explode("\n", trim(shell_exec('wmic cpu get name'))), 1)) . ' (' . implode("\n", array_slice(explode("\n", trim(shell_exec('wmic cpu get NumberOfCores'))), 1)) . ' cores)';
            //get memory for windows
            $memory_str = shell_exec('wmic MEMORYCHIP get Capacity');
            $memoryLines = explode("\n", trim($memory_str));
            unset($memoryLines[0]);
            $totalMemory = 0;
            foreach ($memoryLines as $mem) {
                $totalMemory += intval($mem);
            }
            $memory = FileSizeHelper::formatBytes($totalMemory);
            //get server uptime for windows
            $uptime = shell_exec('net statistics workstation | find "Statistics since"');
            $uptime = explode("since", $uptime, 2)[1];
            $bootTime = DateTime::createFromFormat('m/d/Y H:i:s A', trim($uptime));
            $now = new DateTime();
            $interval = $bootTime->diff($now);
            echo $interval->format('%a days %h hours %i minutes %s seconds');
            //get server cpu usage for windows
            $cpu_usage = implode("\n", array_slice(explode("\n", trim(shell_exec('wmic cpu get loadpercentage'))), 1));
            if($cpu_usage === '') {
                $cpu_usage = '0';
            }
            //get server memory usage for windows
//            $memory_usage = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
            //TODO
        } else {
            //get cpu model
            $cpu = shell_exec('cat /proc/cpuinfo | grep "model name" | uniq | awk -F": " \'{print $2}\'');
            //get memory
            $memory_kb = intval(shell_exec("grep MemTotal /proc/meminfo | awk '{print $2}'"));
            $memory = FileSizeHelper::formatBytes($memory_kb * 1024);
            //get server uptime
            $uptime = str_replace("up ", "", trim(shell_exec('uptime -p')));
            //get server cpu usage
            $cpu_usage = shell_exec('top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk \'{print 100 - $1"%"}\'');
        }
        //get server time
        $server_time = date('Y-m-d H:i:s T');
        //get server load (only for linux)
        $load = $is_windows ? null : sys_getloadavg();





    }
}
