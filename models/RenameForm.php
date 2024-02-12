<?php

namespace app\models;

use yii\base\Model;

class RenameForm extends Model
{
    public $newName;


    public function rules()
    {
        return [
            ['newName', 'required'],
            ['newName', 'string', 'max' => 255],
            ['newName', 'match', 'pattern' => '/^[^\p{C}\/:*?"<>|\\\\]+$/u', 'message' => 'Invalid file name.'],
        ];
    }
}