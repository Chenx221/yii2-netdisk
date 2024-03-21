<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Class m240305_042554_init_rbac
 */
class m240305_042554_init_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;

        $user = $auth->createRole('user');
        $admin = $auth->createRole('admin');
        $auth->add($user);
        $auth->add($admin);

        // 获取所有用户
        $users = (new Query())
            ->select(['id', 'role'])
            ->from('user')
            ->all();

        // 为每个用户分配角色
        foreach ($users as $user) {
            $role = $auth->getRole($user['role']);
            if ($role) {
                $auth->assign($role, $user['id']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;

        // 删除角色和权限
        $auth->removeAll();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240305_042554_init_rbac cannot be reverted.\n";

        return false;
    }
    */
}
