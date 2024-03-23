<?php

namespace app\utils;

use app\models\User;
use Yii;

class AdminSword
{
    public static function forceUserLogout($id):string
    {
        $user = User::findOne($id);
        if ($user) {
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->save(false);
            return '用户已被强制下线';
        } else {
            return '用户不存在';
        }
    }
}