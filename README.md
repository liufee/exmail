腾讯企业邮箱开放接口PHP-SDK
=================
SDK利用腾讯企业邮箱提供的api，实现自己创建、修改、删除、登录邮箱...与公司的OA、网站无缝结合，支持帐号管理、邮件提醒、快捷登录等功能。

安装
------------------------


### 1.使用Composer（推荐）
如果还没有安装Composer, 点击[此处](https://getcomposer.org/doc/00-intro.md#installation-nix "composer")根据提示安装 .

编辑composer.json文件
```
    {
        "require": {
            "feehi/exmail" : "*"
        }
    }
```
国内composer被墙了，使用镜像
```
    {
        "require": {
            "feehi/exmail" : "*"
        },
        "repositories": [
            {"type": "composer", "url": "https://packagist.phpcomposer.com"},
            {"packagist": false}
        ]
    }
```

```bash
$ php composer.phar update
```
完毕后，在项目中
```php 
    require "/path/to/vendor/autoload.php";
```

### 2.使用归档文件
-------------------
点击[此处](http://7xjkuy.com1.z0.glb.clouddn.com/exmail.tar.gz "腾讯企业邮箱PHP-SDK")下载归档文件

解压，复制到项目中

在src同级目录下有个autoload.php
```php 
    require "/path/to/autoload.php";
```


## 使用方法
```php
//出现ip_freq_block错误，请到腾讯企业邮箱web管理页面：可使用此开放接口的IP，添加/修改当前服务器的ip
require "/path/to/autoload.php";
$client_id = '管理员账号';
$client_secret = '接口key';
$exmail = Feehi\Qqexmail::getInstance($client_id, $client_secret);
print_r( $exmail->access_token)."\r\n\r\n";//获取Access Token
print_r( $exmail->getAutoLoginUrl('job@feehi.com') )."\r\n\r\n";//获取job@feehi.com自动登录的链接
print_r( $exmail->listen(1) )."\r\n\r\n";//客户端维持长连接,传入的值为本地维护的最新版本号
print_r( $exmail->getUser("job@feehi.com") )."\r\n\r\n";//获取job@feehi.com个人资料
print_r( $exmail->sync(2, 'newuser@feehi.com', 'demo_new_user', 1, 'programmer', '0755-123456', '13888888', '10', 'PASSword123456', 0) )."\r\n\r\n";//同步成员账号资料,第一个参数:1删除，2新增，3修改
print_r( $exmail->getUpdateInfoByVer(1) )."\r\n\r\n";//获取某个版本号后的用户更新情况
print_r( $exmail->getUnreadMailCount('job@feehi.com') )."\r\n\r\n";//获取job@feehi.com未读邮件数量
print_r( $exmail->partySync(2, 'newParty') )."\r\n\r\n";//同步部门,第一个参数:1=删除, 2=新增, 3=修改
print_r( $exmail->getPartyList("/") )."\r\n\r\n";//获取某个部门子部门
print_r( $exmail->getUserListByParty("/") )."\r\n\r\n";//获取某部门下的成员邮箱账号
print_r( $exmail->checkUsernameAvailable("job@feehi.com") )."\r\n\r\n";//检查邮箱账号job@feehi.com是否被注册，需要检查多个邮箱，传入数组，如:['job@feehi.com', 'admin@feehi.com']
print_r( $exmail->groupAdd("sales_party", "sales@feehi.com", "all", ["job@feehi.com", "admin@feehi.com"]) )."\r\n\r\n";//新建邮件群组，群组名称sales_party，群组邮箱号sales@feehi.com,群组权限all代表所有成员都可以群发邮件，还有inner,group,list可选，群组成员job@feehi.com, admin@feehi.com
//print_r( $exmail->groupDelete("sales@feehi.com") )."\r\n\r\n";//删除邮件群组sales@feehi.com
print_r( $exmail->groupAddMember("sales@feehi.com", ['demo@feehi.com', 'newuser@feehi.com']) )."\r\n\r\n";//增加邮件群组成员，把demo1和demo2加入sales邮件群组
//print_r( $exmail->groupDeleteMember("sales@feehi.com", ['demo@feehi.com', 'newuser@feehi.com']) )."\r\n\r\n";//删除邮件群组成员，把demo1和demo2移出sales邮件群组
print_r( $exmail->groupAddPermission("sales@feehi.com", ['demo@feehi.com', 'newuser@feehi.com']) )."\r\n\r\n";//添加邮件群组群发权限，添加sales@feehi.com群组下的demo1和demo2允许群发邮件
print_r( $exmail->groupDeletePermission("sales@feehi.com", ["demo@feehi.com", "newuser@feehi.com"]) )."\r\n\r\n";//取消群发群组权限，取消demo1和demo2在sales群组下的群发邮件权限
print_r( $exmail->enableForceWeChatToken("job@feehi.com") )."\r\n\r\n";//强制开启job@feehi.com登录需要微信验证
print_r( $exmail->disForceWeChatToken("job@feehi.com") )."\r\n\r\n";//取消强制job@feehi.com登录需要微信验证

```