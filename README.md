<div style="text-align: center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px" alt="">
    </a>
    <h1 style="text-align: center">基于Yii 2框架的网盘系统</h1>
</div>


这是一个基于[Yii 2](https://www.yiiframework.com/) PHP框架设计的网盘系统，作为我的毕业设计作业，它具备一些网盘该有的功能。

项目基于Yii 2 基础项目模板构建，部分使用了模板内容。

项目结构
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources

目前已实现的功能
-------------------

用户登录、注册

目录显示

文件操作(下载,删除,重命名)

文件夹操作(打包下载,删除,重命名)

文件/文件夹上传(支持拖拽上传文件)

文件多选/批量操作,打包,下载，删除,复制,剪切,粘贴,解压

文件校验信息查看

文件/文件夹分享

分享管理

文件预览(支持图像,视频,音频,文本,pdf) _(因为找不到好的ppt转换方案，所以不支持预览office文档😕)_

文件收集(做的差不多了,可能有待改进的地方)

登录验证码(支持reCAPTCHA,hCaptcha,Turnstile三选一或关闭)

文件管理中的右键菜单

容量显示

个人资料显示、修改

修改密码

设置二步验证

部分安全强化(ACL、RBAC)

首页容量显示(点！按钮)

容量限制

文件保险箱(端到端加密)(数据存储在data\用户id.secret)

WebAuthn 登录验证/管理

文件/文件夹搜素

Google Analysis、Microsoft Clarify集成

管理员相关功能:用户管理、管理员个人设置、系统设置

计划实现的功能
-------------------

管理员相关功能:
审计日志(**登录日志**、分享访问日志、收集任务访问日志)、系统状态监控、分享和收集任务管理

首页公告、用户反馈

反馈处理、公告管理

忘记密码

设计主页，删除无用页面

分享功能强化

安全强化

init setup page

未计划开发的功能
-------------------

回收站

开发环境
------------

Windows 11 Pro (22631.3296) \ Windows 10 Enterprise LTSC 2021 (19044.4046)

PhpStorm 2023.3.4

PHP 8.3.4 (这个项目需要PHP>=8.2)

MariaDB 11.3.2

Apache 2.4.58.1

Redis(Memurai) 4.1.1

安装步骤（以下内容已过时，等待后期更新）
------------

### 通过压缩档案进行安装

在[本项目](https://git.chenx221.cyou/chenx221/test_project)的release下载最新版本的归档文件，解压到web根目录下.

执行composer install，安装必要的依赖项。

在`config/web.php`文件中设置一些随机的密钥：

```php
'request' => [
    // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
    'cookieValidationKey' => '<secret random string goes here>',
],
```

现在你可以通过以下链接访问你的站点：

~~~
http://localhost/basic/web/
~~~

配置
-------------

### 数据库

像这样修改config/db.php文件：

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

**提示:**
- 我们不会帮你创建好数据库，请在使用项目前自行创建。
- 检查`config/`目录下的其他配置文件以客制化你的安装。


TESTING
-------

Tests are located in `tests` directory. They are developed with [Codeception PHP Testing Framework](https://codeception.com/).
By default, there are 3 test suites:

- `unit`
- `functional`
- `acceptance`

Tests can be executed by running

```
vendor/bin/codecept run
```

The command above will execute unit and functional tests. Unit tests are testing the system components, while functional
tests are for testing user interaction. Acceptance tests are disabled by default as they require additional setup since
they perform testing in real browser. 


### Running  acceptance tests

To execute acceptance tests do the following:  

1. Rename `tests/acceptance.suite.yml.example` to `tests/acceptance.suite.yml` to enable suite configuration

2. Replace `codeception/base` package in `composer.json` with `codeception/codeception` to install full-featured
   version of Codeception

3. Update dependencies with Composer 

    ```
    composer update  
    ```

4. Download [Selenium Server](https://www.seleniumhq.org/download/) and launch it:

    ```
    java -jar ~/selenium-server-standalone-x.xx.x.jar
    ```

    In case of using Selenium Server 3.0 with Firefox browser since v48 or Google Chrome since v53 you must download [GeckoDriver](https://github.com/mozilla/geckodriver/releases) or [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/downloads) and launch Selenium with it:

    ```
    # for Firefox
    java -jar -Dwebdriver.gecko.driver=~/geckodriver ~/selenium-server-standalone-3.xx.x.jar
    
    # for Google Chrome
    java -jar -Dwebdriver.chrome.driver=~/chromedriver ~/selenium-server-standalone-3.xx.x.jar
    ``` 
    
    As an alternative way you can use already configured Docker container with older versions of Selenium and Firefox:
    
    ```
    docker run --net=host selenium/standalone-firefox:2.53.0
    ```

5. (Optional) Create `yii2basic_test` database and update it by applying migrations if you have them.

   ```
   tests/bin/yii migrate
   ```

   The database configuration can be found at `config/test_db.php`.


6. Start web server:

    ```
    tests/bin/yii serve
    ```

7. Now you can run all available tests

   ```
   # run all available tests
   vendor/bin/codecept run

   # run acceptance tests
   vendor/bin/codecept run acceptance

   # run only unit and functional tests
   vendor/bin/codecept run unit,functional
   ```

### Code coverage support

By default, code coverage is disabled in `codeception.yml` configuration file, you should uncomment needed rows to be able
to collect code coverage. You can run your tests and collect coverage with the following command:

```
#collect coverage for all tests
vendor/bin/codecept run --coverage --coverage-html --coverage-xml

#collect coverage only for unit tests
vendor/bin/codecept run unit --coverage --coverage-html --coverage-xml

#collect coverage for unit and functional tests
vendor/bin/codecept run functional,unit --coverage --coverage-html --coverage-xml
```

You can see code coverage output under the `tests/_output` directory.
