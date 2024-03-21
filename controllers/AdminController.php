<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

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
                            'actions' => ['index', 'system', 'user', 'info'],
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
     * @return string
     */
    public function actionUser(): string
    {
        return $this->render('user');
    }

    /**
     * @return string
     */
    public function actionInfo(): string
    {
        return $this->render('info');
    }
}
