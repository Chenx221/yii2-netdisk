<?php

namespace app\controllers;

use app\models\CollectionUploaded;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CollectionUploadedController implements the CRUD actions for CollectionUploaded model.
 */
class CollectionUploadedController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                    ],
                ],
            ]
        );
    }


    /**
     * Finds the CollectionUploaded model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return CollectionUploaded the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): CollectionUploaded
    {
        if (($model = CollectionUploaded::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
