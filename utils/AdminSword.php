<?php

namespace app\utils;

use app\models\User;
use Yii;
use yii\base\Exception;

/**
 * 管理员工具
 */
class AdminSword
{
    /**
     * 强制用户下线
     * 通过修改用户的auth_key来实现
     * @param $id
     * @return string
     * @throws Exception
     */
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