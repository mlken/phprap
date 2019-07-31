<?php
/**
 * 移除成员模型
 */
namespace app\models\member;

use Yii;
use app\models\Member;

class RemoveMember extends Member
{

    public $password; // 登录密码

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return [
            ['password', 'required', 'message' => '登录密码不可以为空'],

            ['password', 'validatePassword'],
            ['id', 'validateProject'],
        ];
    }

    /**
     * 字段字典
     */
    public function attributeLabels()
    {

        return [
            'password' => '登录密码',
        ];
    }


    /**
     * 验证密码是否正确
     * @param $attribute
     */
    public function validatePassword($attribute)
    {
        $user = Yii::$app->user->identity;

        if(!$user->id || !$user->validatePassword($this->password))
        {

            $this->addError($attribute, '登录密码验证失败');
        }
    }
    
    /**
     * 验证是否有项目操作权限
     * @param $attribute
     */
    public function validateProject($attribute)
    {

        if(!$this->project->hasRule('member', 'remove')){
            $this->addError($attribute, '抱歉，您没有操作权限');
        }
    }

    public function remove()
    {
        if(!$this->validate()){
            return false;
        }

        // 开启事务
        $transaction = Yii::$app->db->beginTransaction();

        $member = &$this;

        if(!$member->delete()){
            $this->addError($member->getErrorLabel(), $member->getErrorMessage());
            $transaction->rollBack();
            return false;
        }

        // 事务提交
        $transaction->commit();

        return true;

    }

}