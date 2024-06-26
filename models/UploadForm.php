<?php
/*
 * 这里的代码借鉴了 https://www.yiiframework.com/doc/guide/2.0/en/input-file-upload
 */

namespace app\models;

use app\utils\FileSizeHelper;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    public UploadedFile|null $uploadFile;
    public $targetDir; //相对路径

    public function rules(): array
    {
        return [
            [['uploadFile'], 'file', 'skipOnEmpty' => false, 'checkExtensionByMimeType' => false], //这规则奇怪的放走近科学都可以拍好几集了
        ];
    }

    public function upload(int $is_vault_file = 0): bool
    {
        if ($this->validate()) {
            if ($this->targetDir === null) {
                $this->targetDir = '.';
            }
            if (str_contains($this->targetDir, '..')) {
                return false;
            }
            $userHomeDir = Yii::getAlias(Yii::$app->params['dataDirectory']) . '/' . Yii::$app->user->id;
            if ($is_vault_file == 1) {
                $userHomeDir .= '.secret';
            }
            $absolutePath = $userHomeDir . '/' . $this->targetDir;
            if (!is_dir($absolutePath)) {
                return false;
            }
            $fileName = $this->uploadFile->fullPath;
            $directory = dirname($absolutePath . '/' . $fileName);
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
            if (!FileSizeHelper::hasEnoughSpace($this->uploadFile->size)) {
                return false;
            }
            $this->uploadFile->saveAs($absolutePath . '/' . $fileName);
            return true;
        } else {
            return false;
        }
    }
}