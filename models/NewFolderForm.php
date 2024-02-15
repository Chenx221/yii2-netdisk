<?php

namespace app\models;

use Yii;
use yii\base\Model;

class NewFolderForm extends Model
{
    public $folderName;
    public $relativePath; // 要在哪里新建文件夹的路径

    public function rules()
    {
        return [
            ['folderName', 'required'],
            ['folderName', 'string', 'max' => 255],
            ['folderName', 'match', 'pattern' => '/^[^\p{C}:*?"<>\/|\\\\]+$/u', 'message' => 'Folder name contains invalid characters.'],
            ['folderName', 'validateFolderName'],
            ['relativePath', 'match', 'pattern' => '/^[^\p{C}:*?"<>|\\\\]+$/u', 'message' => 'Path contains invalid characters.'],
            ['relativePath', 'validateRelativePath'],
        ];
    }

    public function validateRelativePath($attribute, $params, $validator)
    {
        if (str_contains($this->$attribute, '..')) {
            $this->addError($attribute, 'Invalid file path.');
        }
    }

    public function validateFolderName($attribute, $params, $validator)
    {
        $userHomeDir = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id;
        $absolutePath = $userHomeDir . '/' . $this->relativePath . '/' . $this->$attribute;
        if (file_exists($absolutePath)) {
            $this->addError($attribute, 'Folder name already exists.');
        }
    }

    public function createFolder()
    {
        $userHomeDir = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id;
        $absolutePath = $userHomeDir . '/' . $this->relativePath . '/' . $this->folderName;

        return mkdir($absolutePath, 0777);
    }
}