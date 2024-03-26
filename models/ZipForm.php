<?php

namespace app\models;

use yii\base\Model;

class ZipForm extends Model
{
    public $zipFilename;
    public $zipFormat;


    public function rules(): array
    {
        return [
            [['zipFilename', 'zipFormat'], 'required'],
            ['zipFilename', 'string', 'max' => 255],
            ['zipFormat', 'in', 'range' => ['zip', '7z']],
            ['zipFilename', 'match', 'pattern' => '/^[^\p{C}\/:*?"<>|\\\\]+$/u', 'message' => 'Invalid file name.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'zipFilename' => '压缩文件名',
            'zipFormat' => '压缩格式',
        ];
    }
}