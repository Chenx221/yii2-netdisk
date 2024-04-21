<?php

namespace app\controllers;

use app\models\Announcements;
use app\models\EntryForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout', 'get-announcement'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'get-announcement' => ['get'],
                ],
            ],
        ];
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
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        if(Yii::$app->user->isGuest){
            return $this->render('index');
        }
        //fetch latest 3 announcements
        $latestAnnouncements = Announcements::fetchLatestAnnouncements();
        return $this->render('index', [
            'latestAnnouncements' => $latestAnnouncements
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionGetAnnouncement($id): ?Announcements
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return Announcements::findOne($id);
        }
        throw new NotFoundHttpException();
    }

    public function actionEntry(): string
    {
        $model = new EntryForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            return $this->render('entry', ['model' => $model]);
        }
    }
}
