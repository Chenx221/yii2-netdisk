<?php
namespace app\models;
use Yii;
use yii\base\Model;

class EntryForm extends Model
{
    public $name;
    public $email;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'], // both name and email are required
            ['email', 'email'], // email has to be a valid email address
        ];
    }
}
