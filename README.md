
Simple news module
==================

This module-package contain backend and frontend parts together.
Additional system features support by 'asbsoft/yii2-common...' package-kernel.

Module support multilanguage news.
News can show if has body for at least one language.

Module use visual editor for create news text with images etc.

Module use 'roleNewsAuthor' and 'roleNewsModerator' in addition to standard 'roleAdmin'.
Author can create and update own news but can't delete and set news visible on frontend.
Moderator can't create but can edit, delete and change visibility of every news.
Module's roles contant module's name (roleNEWSauthor) to avoid conflicts with another content-modules.

Features
--------
- Time save in UTC time zone format.
  Frontend provide time correction according time zone getting from user's browser.
- System languages interface provide by LangHelper supported by asbsoft/yii2-common...-package.
  System can have languages visible on frontend and unvisible (for future addition for example).
  If module params['editAllLanguages'] == true you can edit news bodies for unvisible languages.


Installation
------------
- Use 'composer require asbsoft/news_1b_160430' to install.
  If you can't use composer download and unpack module
  to /vendor/asbsoft/yii2module/news_1b_160430/
  Such folder help system to find module automatically.
  (Suppose that system have such alias: Yii::setAlias('@asb/yii2/modules', '@vendor/asbsoft/yii2module');)
- Apply module's migrations (once).
- Create in your Yii2-application new module .../modules/news/Module.php contains
  ```php
    namespace {APP}\modules\news;  // {APP} may be backend/frontend/app/project or your own namespace prefix
    class Module extends \asb\yii2\modules\news_1b_160430\Module {}
  ```
  in proper place: @app/modules/news/ or @project/modules/news/
  or @backend/modules/news/ and @backend/modules/news/ together (for advanced Yii2-application)
  In config/config.php file in this/these place(s) you have to redefine 'routesConfig'
  initially defined in asbsoft/news_1b_160430/config/config.php.
  *Attention* For advanced Yii2-application you will have two such news-modules
  but with different configs contain:
  * for backend 'routesConfig' => { 'main'  => false, ... }
  * for frontend 'routesConfig' => { 'admin'  => false, ... }
- Add such module(s) to your system's config(s).


Note
----
Namespace like ...\news_1b_160430 is no problem for routing
because UniModule (used as parent module) has mechanism
for create tuning routes from config/routes-...-files.
But if you project has simple routes building by common rules such
'<module:\w+>/<controller:\w+>/<action:\w+>/<param:\w+>' => '<module>/<controller>/<action>',
you can create new module @app/modules/news contains Module.php only with code
class Module extends \asb\yii2\modules\news_1b_160430\Module {}
New module extends all features from this module(package).
Append this new module (not news_1b_160430) to system config 'modules' array.

