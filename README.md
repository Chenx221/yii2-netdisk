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
      data/               contains data files
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      utils/              contains some useful classes
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

文件/文件夹分享(+)

分享管理

文件预览(支持图像,视频,音频,文本,pdf) _(找不到好的office处理方案)_

文件收集(+)

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

工单支持

首页美化+公告

Google Analysis、Microsoft Clarify集成

管理员相关功能:用户管理、管理员个人设置、系统设置、审计日志(登录日志、分享访问日志、收集任务访问日志)、分享和收集任务管理、系统状态监控、工单和公告管理

计划实现的功能
-------------------

init setup page(暂无计划)

开发环境
------------

Windows 11 Pro (22631.3527) / Debian 12

PhpStorm 2024.1.1

PHP 8.3.7 / 8.2.18 (这个项目需要PHP>=8.2)

MariaDB 11.3.2 / 10.11.6

Apache 2.4.59

Garnet 1.0.8 / Redis 7.0.15

环境搭建
------------
### Docker (测试)

Docker自己装

```bash
sudo docker build -t chenx221-yii2-netdisk .
docker network create my-network
docker run -d --name mariadb-container \
    -v /home/chenx221/db.sql:/docker-entrypoint-initdb.d/db.sql \
    -e MYSQL_ROOT_PASSWORD=chenx221 \
    --network=my-network \
    mariadb:latest
docker run -d --name redis-container \
    --network=my-network \
    redis
docker run -d -p 80:80 -p 443:443 \
    -v /home/chenx221/fullchain1.pem:/etc/ssl/fullchain1.pem \
    -v /home/chenx221/privkey1.pem:/etc/ssl/privkey1.pem \
    -v /home/chenx221/data:/var/www/html/data \
    -v /home/chenx221/.env:/var/www/html/.env \
    --network=my-network \
    chenx221-yii2-netdisk
```
.env
```
DB_HOST=mariadb-container:3306
RD_HOST=redis-container
```
.sql文件别忘了+x 和777

### For Windows

#### [Wampserver](https://wampserver.aviatechno.net/)
wampserver3.3.5_x64.exe
Tools->切换默认DBMS为MariaDB
禁用Mysql
切换PHP版本为8.3.6

#### PHP
访问https://curl.se/docs/caextract.html，下载cacert.pem到C:\wamp64\bin\php\php8.3.6\extras\ssl

编辑C:\wamp64\bin\php\php8.3.6\php.ini

修改:
```ini
max_execution_time = 360
memory_limit = 2G
post_max_size = 512M
upload_max_filesize = 512M
xdebug.mode =debug
curl.cainfo = G:\wamp64\bin\php\php8.3.6\extras\ssl\cacert.pem
```
新增:
```ini
xdebug.discover_client_host = true
xdebug.client_host = 127.0.0.1
xdebug.client_port= 9000
xdebug.remote_handler=dbgp
extension=php_imagick.dll
extension=php_memcache.dll
```

编辑C:\wamp64\bin\php\php8.3.6\phpForApache.ini
修改:
```ini
max_execution_time = 360
memory_limit = 4G
post_max_size = 2G
upload_max_filesize = 2G
extension=pdo_pgsql
extension=sodium
date.timezone = "Asia/Shanghai"
xdebug.mode =debug
curl.cainfo =c:\wamp64\bin\php\php8.3.6\extras\ssl\cacert.pem
```
新增:
```ini
xdebug.discover_client_host = true
xdebug.client_host = 127.0.0.1
xdebug.client_port= 9000
xdebug.remote_handler=dbgp
extension=php_imagick.dll
extension=php_memcache.dll
```

添加[php扩展](https://git.chenx221.cyou/chenx221/yii2-netdisk/releases/download/db.backup/php83.zip)

#### Apache
编辑httpd.conf

修改:
```apacheconf
LoadModule ssl_module modules/mod_ssl.so
```
新增:
```apacheconf
Define MYPORT8081 8081
Listen 0.0.0.0:${MYPORT8081}
Listen [::0]:${MYPORT8081}
```

编辑httpd-vhosts.conf

新增:
```apacheconf
<VirtualHost *:8081>
ServerName env2.chenx221.cyou
DocumentRoot "c:/wamp64/www/netdisk/web"
<Directory  "c:/wamp64/www/netdisk/web/">
Options +Indexes +Includes +FollowSymLinks +MultiViews
AllowOverride All
Require all granted
LimitRequestBody 2147483648
</Directory>
SSLEngine on
SSLCertificateFile "C:\wamp64\fullchain1.pem"
SSLCertificateKeyFile "C:\wamp64\privkey1.pem"
</VirtualHost>
```

#### mariadb
phpmyadmin> root
修改密码

下载[DBeaver Community](https://dbeaver.io/download/)

新建数据库yii2basic
恢复数据库从release.sql

#### redis(Garnet)
[下载运行库](https://dotnet.microsoft.com/zh-cn/download/dotnet/thank-you/runtime-8.0.4-windows-x64-installer?cid=getdotnetcore)

修改Garnet.xml路径
```
.\WinSW-x64.exe install .\Garnet.xml
```
服务 运行Garnet服务

#### 其他
安装[git](https://git-scm.com/download/win)
```
git clone https://git.chenx221.cyou/chenx221/yii2-netdisk.git
```

重命名yii2-netdisk为netdisk

在netdisk中新建data文件夹

修改环境变量path，追加`C:\wamp64\bin\php\php8.3.6`

安装[composer](https://getcomposer.org/Composer-Setup.exe)

在netdisk目录下`composer install`

.env看下面linux部分配置

#### 额外说明
预留了一个管理员账户

username:admin

password:administrator

### For Linux (以Debian 12为例)

#### 安装依赖
```bash
sudo apt install php8.2 php8.2-zip php8.2-xsl php8.2-imagick php8.2-gmp php8.2-curl php8.2-bcmath php8.2-gd php8.2-mysql php8.2-imap php8.2-ldap php8.2-memcache php8.2-common php8.2-soap php8.2-xdebug php8.2-sqlite3 mariadb-server apache2 redis composer git
```
#### apache
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo nano /etc/apache2/sites-available/netdisk.conf
```
```apacheconf
<VirtualHost *:8081>
  ServerName env.chenx221.cyou
	DocumentRoot "/var/www/netdisk/web"
	<Directory  "/var/www/netdisk/web">
	  Options +Indexes +Includes +FollowSymLinks +MultiViews
	  AllowOverride All
	  Require all granted
      LimitRequestBody 2147483648
	</Directory>
  SSLEngine on
  SSLCertificateFile "/var/www/fullchain1.pem"
  SSLCertificateKeyFile "/var/www/privkey1.pem"
</VirtualHost>
```
上传证书到`/var/www/`目录，别的不多说了
```bash
sudo a2ensite netdisk.conf
sudo nano /etc/apache2/conf-available/netdisk-conf.conf
```
```apacheconf
Listen 0.0.0.0:8081
Listen [::0]:8081
```
```bash
sudo a2enconf netdisk-conf.conf
sudo systemctl restart apache2
```

#### php
```bash
sudo nano /etc/php/8.2/apache2/php.ini
```
修改
```ini
max_execution_time = 360
memory_limit = 4G
post_max_size = 2G
upload_max_filesize = 2G
```
增加
```ini
date.timezone = "Asia/Shanghai"
[xdebug]
xdebug.mode =debug
xdebug.output_dir ="/tmp"
xdebug.show_local_vars=0
xdebug.log="/tmp/xdebug.log"
xdebug.log_level=7
xdebug.profiler_output_name=trace.%H.%t.%p.cgrind
xdebug.use_compression=false
xdebug.discover_client_host = true
xdebug.client_host = 127.0.0.1
xdebug.client_port= 9003
xdebug.remote_handler=dbgp
```

```bash
sudo systemctl restart apache2
```

#### mariadb
```bash
sudo mariadb-secure-installation
```
```
Enter current password for root (enter for none):
Switch to unix_socket authentication [Y/n] n
Change the root password? [Y/n] y
Remove anonymous users? [Y/n] y
Disallow root login remotely? [Y/n] y
Remove test database and access to it? [Y/n] y
Reload privilege tables now? [Y/n] y
```
上传sql文件到服务器，导入数据库
```
mysql -u root -p
CREATE DATABASE yii2basic;
CREATE USER 'chenx221'@'localhost' IDENTIFIED BY 'chenx221';
GRANT ALL PRIVILEGES ON yii2basic.* TO 'chenx221'@'localhost';
FLUSH PRIVILEGES;
use yii2basic;
source /home/chenx221/release.sql;
```

#### 其他配置
```bash
cd /var/www
sudo mkdir .cache
sudo chown -R www-data:www-data .cache
sudo chmod -R 755 .cache
sudo git clone https://git.chenx221.cyou/chenx221/yii2-netdisk.git
//如果你看到这个README.md，那么这个项目已经公开了
sudo mv yii2-netdisk netdisk
sudo chown -R www-data:www-data /var/www/netdisk
sudo chmod -R 755 /var/www/netdisk
cd netdisk
sudo -u www-data mkdir data
sudo -u www-data cp .env.example .env
```
参照.env.example文件，修改.env文件中的配置，以下是基础结构
```env
SITE_TITLE=
REGISTRATION_ENABLED=
DOMAIN=
DB_HOST=
DB_NAME=
DB_USERNAME=
DB_PASSWORD=
VERIFY_PROVIDER=
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET=
HCAPTCHA_SITE_KEY=
HCAPTCHA_SECRET=
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET=
COOKIE_VALIDATION_KEY=
ENABLE_IPINFO=
IPINFO_TOKEN=
CLARITY_ENABLED=
CLARITY_ID=
GA_ENABLED=
GA_ID=
```
```bash
sudo -u www-data composer install
```

#### 额外说明
预留了一个管理员账户

username:admin

password:administrator

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
