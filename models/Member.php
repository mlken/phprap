<?php

namespace app\models;

use app\widgets\LinkPager;
use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "{{%member}}".
 *
 * @property int $id
 * @property string $encode_id 加密id
 * @property int $project_id 项目id
 * @property int $user_id 用户id
 * @property int $join_type 加入方式
 * @property string $project_rule 项目权限
 * @property string $env_rule 环境权限
 * @property string $module_rule 模块权限
 * @property string $api_rule 接口权限
 * @property string $member_rule 成员权限
 * @property int $creater_id 创建者id
 * @property string $created_at 创建时间
 * @property int $updater_id 更新者id
 * @property string $updated_at 更新时间
 */
class Member extends Model
{
    const INITIATIVE_JOIN_TYPE = 10; // 主动加入
    const PASSIVE_JOIN_TYPE    = 20; // 邀请加入

    public $find    = ['look,','create,','update,', 'transfer,', 'export,', 'delete,', 'remove,', 'template,'];

    public $replace = ['查看、','添加、', '编辑、', '转让、', '导出、', '删除、', '移除、', '模板、'];

    /**
     * 绑定数据表
     */
    public static function tableName()
    {
        return '{{%member}}';
    }

    /**
     * 默认验证规则
     * @return array
     */
    public function rules()
    {
        return [
            [['encode_id', 'project_id', 'user_id', 'join_type', 'env_rule', 'creater_id', 'updater_id'], 'required'],
            [['project_id', 'user_id', 'join_type', 'creater_id', 'updater_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['encode_id'], 'string', 'max' => 50],
            [['project_rule', 'env_rule', 'module_rule', 'api_rule', 'member_rule'], 'string', 'max' => 100],
            [['encode_id'], 'unique'],
        ];
    }

    /**
     * 字段映射
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'encode_id' => '加密id',
            'project_id' => '项目id',
            'user_id' => '用户id',
            'join_type' => '加入方式',
            'project_rule' => '项目权限',
            'env_rule' => '环境权限',
            'module_rule' => '模块权限',
            'api_rule' => '接口权限',
            'member_rule' => '成员权限',
            'creater_id' => '创建者id',
            'created_at' => '创建时间',
            'updater_id' => '更新者id',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 成员所有加入方式
     * @return array
     */
    public function getJoinTypeLabels()
    {
        return [
            self::INITIATIVE_JOIN_TYPE  => '主动加入',
            self::PASSIVE_JOIN_TYPE => '邀请加入',
        ];
    }

    /**
     * 获取成员加入方式标签
     * @return mixed
     */
    public function getJoinTypeLabel()
    {
        return $this->getJoinTypeLabels()[$this->join_type];
    }

    /**
     * 获取项目
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(),['id'=>'project_id']);
    }

    /**
     * 获取关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(),['id'=>'user_id']);
    }

    /**
     * 判断是否有权限
     * @param $type project|module|env|api|member
     * @param $rule create|update|delete|remove|quit
     * @return bool|int
     */
    public function hasRule($type, $rule)
    {

        $type = $type . '_rule';

        if(in_array($rule, explode(',', $this->$type))){

            return true;
        }

        return false;

    }

    /**
     * 获取权限文案
     * @param $type
     * @return string
     */
    public function getRuleLabel($type)
    {

        $type = $type . '_rule';

        $title   = $this->$type ? str_replace($this->find, $this->replace, $this->$type . ',') : '';

        return trim($title, '、');
    }

    public function search($params = [])
    {

        $this->params = $params;

        $query = static::find()->joinWith('account');

        $query->andFilterWhere([
            'project_id' => $this->params['project_id'],
        ]);

        $pagination = new Pagination([
            'pageSizeParam' => false,
            'totalCount' => $query->count(),
            'pageSize'   => $this->pageSize,
            'validatePage' => false,
        ]);

        $this->models = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id DESC')
            ->all();

        $this->count = $query->count();

        $this->sql = $query->createCommand()->getRawSql();

//        dump($this->sql);

        $this->pages = LinkPager::widget([
            'pagination' => $pagination,
            'nextPageLabel' => '下一页',
            'prevPageLabel' => '上一页',
            'firstPageLabel' => '首页',
            'lastPageLabel' => '尾页',
            'hideOnSinglePage' => true,
            'maxButtonCount' => 5,
        ]);

        return $this;

    }

}