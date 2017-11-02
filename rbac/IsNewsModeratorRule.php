<?php

namespace asb\yii2\modules\news_1b_160430\rbac;

use asb\yii2\modules\news_1b_160430\Module;
//use asb\yii2\common_2_170212\web\UserIdentity;

use Yii;
use yii\rbac\Rule;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class IsNewsModeratorRule extends Rule
{
    public $name = 'ruleIsNewsModerator';

    protected $role  = 'roleNewsModerator';

    protected $group = 'moderators';

    /**@param string|integer $userId the user ID.
     * @param Item $item the role or permission that this rule is associated width
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with. */
    public function execute($userId, $item, $params)
    {//echo __METHOD__;
        //$identity = UserIdentity::findIdentity($userId);
        $module = Module::getModuleByClassname(Module::className());
        $userIdentity = $module->userIdentity;
        $identity = $userIdentity::findIdentity($userId);//var_dump($identity->attributes);exit;
        if (empty($identity)) return false;

        //$hasRole = Yii::$app->authManager->getAssignment($this->role, $userId);//var_dump($hasRole);exit;
        //if (empty($hasRole)) return false; // !! not need - forbid roles inheritance

        if (method_exists($identity, 'getGroups')) { // for CMS#1
            $groups = $identity->getGroups();//var_dump($groups);
            if (!in_array($this->group, $groups)) return false;
        }
        //echo 'IsNewsModeratorRule OK<br>';
        return true;
    }

}
