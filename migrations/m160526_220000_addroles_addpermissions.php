<?php

use asb\yii2\modules\news_1b_160430\rbac\IsNewsModeratorRule;
use asb\yii2\modules\news_1b_160430\rbac\IsNewsAuthorRule;
use asb\yii2\modules\news_1b_160430\rbac\IsNewsOwnerRule;

use yii\db\Migration;

/**
 * @author ASB <ab2014box@gmail.com>
 */
class m160526_220000_addroles_addpermissions extends Migration
{
    // roles names
    public $roleRoot          = 'roleRoot';  // system developer // !! tune for your system
    public $roleAdmin         = 'roleAdmin'; // system admin     // !! tune for your system
    public $roleNewsAuthor    = 'roleNewsAuthor';    // news author, no moderator
    public $roleNewsModerator = 'roleNewsModerator'; // news moderator, no author
    
    public function init()
    {
        parent::init();

        // if problems with autoload (classes not found):
        //Yii::setAlias('@asb/yii2', dirname(dirname(dirname(__DIR__))) . '/yii2-common_2_170212');
        //Yii::setAlias('@asb/yii2/modules/news_1b_160430', dirname(__DIR__));//var_dump(Yii::$aliases);exit;
    }

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // rules
        $ruleIsNewsModerator = new IsNewsModeratorRule;
        $auth->add($ruleIsNewsModerator);

        $ruleIsNewsAuthor = new IsNewsAuthorRule;
        $auth->add($ruleIsNewsAuthor);

        $ruleIsNewsOwner = new IsNewsOwnerRule;
        $auth->add($ruleIsNewsOwner);
        
        // roles
        $roleNewsModerator = $auth->createRole($this->roleNewsModerator);
        $roleNewsModerator->ruleName = $ruleIsNewsModerator->name;
        $auth->add($roleNewsModerator);

        $roleNewsAuthor = $auth->createRole($this->roleNewsAuthor);
        $roleNewsAuthor->ruleName = $ruleIsNewsAuthor->name;
        $auth->add($roleNewsAuthor);

      //$auth->addChild($roleNewsModerator, $roleNewsAuthor); //!! no: moderator is not author

        $roleAdmin = $auth->getRole($this->roleAdmin);
        $auth->addChild($roleAdmin, $roleNewsModerator); // system admin is moderator too

        $roleRoot = $auth->getRole($this->roleRoot); // system developer - need all roles by default
        $auth->addChild($roleRoot, $roleNewsAuthor); // root inherit roleNewsModerator from admin

        // permissions
        $createNews = $auth->createPermission('createNews');
        $createNews->description = 'Create news';
        $auth->add($createNews);

        $deleteNews = $auth->createPermission('deleteNews');
        $deleteNews->description = 'Delete news';
        $auth->add($deleteNews);

        $updateNews = $auth->createPermission('updateNews');
        $updateNews->description = 'Update news';
        $auth->add($updateNews);

        $updateOwnNews = $auth->createPermission('updateOwnNews');
        $updateOwnNews->description = 'Update own news';
        $updateOwnNews->ruleName = $ruleIsNewsOwner->name;
        $auth->add($updateOwnNews);

        $auth->addChild($updateOwnNews, $updateNews); //??

        // permissions in roles
        $auth->addChild($roleNewsAuthor, $createNews);
        $auth->addChild($roleNewsAuthor, $updateOwnNews);
        $auth->addChild($roleNewsModerator, $updateNews);
        $auth->addChild($roleNewsModerator, $deleteNews);
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $roleNewsModerator = $auth->getRole('roleNewsModerator');
        $roleNewsAuthor = $auth->getRole('roleNewsAuthor');

        $createNews = $auth->getPermission('createNews');
        $deleteNews = $auth->getPermission('deleteNews');
        $updateNews = $auth->getPermission('updateNews');
        $updateOwnNews = $auth->getPermission('updateOwnNews');

        $auth->removeChild($roleNewsAuthor, $createNews);
        $auth->removeChild($roleNewsAuthor, $updateOwnNews);
        $auth->removeChild($roleNewsModerator, $updateNews);
        $auth->removeChild($roleNewsModerator, $deleteNews);

        $auth->removeChild($updateOwnNews, $updateNews); //??

        $auth->remove($createNews);
        $auth->remove($deleteNews);
        $auth->remove($updateNews);
        $auth->remove($updateOwnNews);

        $roleRoot = $auth->getRole('roleRoot');
        $roleAdmin = $auth->getRole('roleAdmin');
        $auth->removeChild($roleAdmin, $roleNewsModerator);
        $auth->removeChild($roleRoot, $roleNewsAuthor);
      //$auth->removeChild($roleNewsModerator, $roleNewsAuthor); // already not set

        $auth->remove($roleNewsAuthor);
        $auth->remove($roleNewsModerator);
        
        $ruleIsNewsOwner = $auth->getRule('ruleIsNewsOwner');
        $auth->remove($ruleIsNewsOwner);
        $ruleIsNewsAuthor = $auth->getRule('ruleIsNewsAuthor');
        $auth->remove($ruleIsNewsAuthor);
        $ruleIsNewsModerator = $auth->getRule('ruleIsNewsModerator');
        $auth->remove($ruleIsNewsModerator);
    }

}
