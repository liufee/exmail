<?php
/**
 *@link http://blog.feehi.com
 *@author wfee <job@feehi.com>
 *@created at 2016-9-19
 *@version 2.0
 */
namespace Feehi;

class Qqexmail{

    private static $_instance;

    private $host = 'https://exmail.qq.com';
    private $hostapi = 'http://openapi.exmail.qq.com:12211';
    private $client_id;
    private $client_secret;

    public $access_token;

    /**
     * @param $client_id  当前管理员账号
     * @param $client_secret  接口key
     */
    public static function getInstance($client_id, $client_secret)
    {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($client_id, $client_secret);
        }
        return self::$_instance;
    }

    public function __clone()
    {
        trigger_error("clone is not allowed", E_USER_ERROR);
    }

    private function __CONSTRUCT($client_id, $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->getAccessToken();
    }

    /**
     *@description OAuth 验证授权
     */
    private function getAccessToken()
    {
        $data =array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        );
        $result = $this->getResult($this->host.'/cgi-bin/token', $data);
        $this->access_token = $result->access_token;
    }

    /**
     *@description 获取 Authkey
     *@param $alias string email format like demo@feehi.com
     */
    public function getAuthkey($alias)
    {
        $data = array(
            'alias' => $alias,
            'access_token' => $this -> access_token,
        );
        $url = $this->hostapi.'/openapi/mail/authkey';
        $result = $this->getResult($url, $data);
        $this->auth_key = $result->auth_key;
        return $this->auth_key;
    }

    /**
     *@description 一键登录
     *@param $username string email format like admin@feehi.com
     *@return string url
     */
    public function getAutoLoginUrl($alias)
    {
        $data = array(
            'fun' => 'bizopenssologin',
            'method' => 'bizauth',
            'ticket' => $this->getAuthkey($alias),
            'agent' => $this->client_id,
            'user' => $alias
        );
        $url = $this->host.'/cgi-bin/login?';
        $url .= http_build_query($data);
        return $url;
    }

    /**
     * @description 客户端维持长连接
     * @param int $ver
     */
    public function listen($ver=0)
    {
        $url = $this->hostapi.'/openapi/listen';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'Ver' => $ver,
            'access_token' => $this->access_token,
        ];
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 获取成员资料
     * @param $alias string e.g demo@feehi.com
     */
    public function getUser($alias)
    {
        $url = $this->hostapi.'/openapi/user/get';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'alias' => $alias,
            'access_token' => $this->access_token,
        ];
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 同步成员账号资料-增加/修改/删除邮箱用户
     * @param $action 1删除，2新增，3修改
     * @param $alias 邮箱账号 e.g demo@feehi.com
     * @param string $name  姓名
     * @param string $gender  性别，1男，2女
     * @param string $position 职位
     * @param string $tel 电话
     * @param string $mobile 手机
     * @param string $extId 编号id
     * @param string $password 密码
     * @param string $md5 1密码已经md5加密过，0明文密码
     * @param string $partyPath 所属部门，根部门为空，下级部门以'/'隔开
     * @param string $slave 邮箱别名，最多5个邮箱别名，单个别名传string，多个别名传索引数组
     * @param string $openType 成员状态，1启用，2禁用
     */
    public function sync($action, $alias, $name='', $gender='', $position='', $tel='', $mobile='', $extId='', $password='', $md5='', $partyPath='', $slave='', $openType='')
    {
        $url = $this->hostapi.'/openapi/user/sync';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'action' => $action,
            'alias' => $alias,
            'name' => $name,
            'gender' => $gender,
            'position' => $position,
            'tel' => $tel,
            'mobile' => $mobile,
            'extId' => $extId,
            'password' => $password,
            'md5' => $md5,
            'partyPath' => $partyPath,
            'slave' => $slave,
            'openType' => $openType,
            'access_token' => $this->access_token,
        ];
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 获取某个版本号后的用户更新情况
     * @param int $ver
     */
    public function getUpdateInfoByVer($ver=0)
    {
        $url = $this->hostapi.'/openapi/user/list';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'Ver' => $ver,
            'access_token' => $this->access_token,
        ];
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @decription 获取未读邮件数
     * @param $alias 邮箱账号 e.g demo@feehi.com
     */
    public function getUnreadMailCount($alias){
        $url = $this->hostapi.'/openapi/mail/newcount';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'Alias' => $alias,
            'access_token' => $this->access_token,
        ];
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 同步部门
     * @param $action 类型，1=删除, 2=新增, 3=修改
     * @param $dstPath 目标部门，删除和新增只需传目标部门
     * @param string $srcPath 源部门
     */
    public function partySync($action, $dstPath, $srcPath='')
    {
        if(empty($action)) throw new Exception('Action cannot be empty');
        if(empty($dstPath)) throw new Exception('dstPath cannot be empty');
        $url = $this->hostapi.'/openapi/party/sync';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'access_token' => $this->access_token,
        ];
        switch($action){
            case 1://del
                $data['dstpath'] = $dstPath;
                break;
            case 2://add
                $data['dstpath'] = urlencode($dstPath);
                break;
            case 3://mod
                $data = array_merge($data, [
                    'srcpath' => $srcPath,
                    'dstpath' => $dstPath,
                ]);
                break;
        }
        $data = array_merge($data, ['action'=>$action]);
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 获取子部门列表
     * @param string $partyPath 部门路径，为空查看根部门
     */
    public function getPartyList($partyPath='')
    {
        $url = $this->hostapi.'/openapi/party/list';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'access_token' => $this->access_token,
            'PartyPath' => $partyPath,
        ];
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description  获取部门下成员列表
     * @param string $partyPath
     */
    public function getUserListByParty($partyPath='')
    {
        $url = $this->hostapi.'/openapi/partyuser/list';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'access_token' => $this->access_token,
            'PartyPath' => $partyPath,
        ];
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 检查邮件帐号是否可用
     * @param $email array|sring 邮箱格式 传入索引数组检查多个账户名是否可用，字符串检查单个账户名是否可用
     */
    public function checkUsernameAvailable($email)
    {
        $url = $this->hostapi.'/openapi/user/check';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'access_token' => $this->access_token,
            'email' => $email
        ];
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 添加邮件群组
     * @param $groupname 邮件群组名称
     * @param $group_admin 邮件群组账号  如  sales_party@feehi.com
     * @param $status
     * @param $members
     */
    public function groupAdd($groupname, $group_admin, $status, $members)
    {
        if(!in_array($status, ['all', 'inner', 'group', 'list'])) throw new Exception("status must be one of them: all, inner, group, list");
        $url = $this->hostapi.'/openapi/group/add';
        $header = ['Authorization' => $this->access_token];
        $data = [
            'group_name' => $groupname,
            'group_admin' => $group_admin,
            'status' => $status,
            'members' => $members,
            'access_token' => $this->access_token,
        ];
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 删除邮件群组
     * @param $group_alias   邮件群组账号
     */
    public function groupDelete($group_alias)
    {
        $header = ['Authorization' => $this->access_token];
        $data = [
            'group_alias' => $group_alias,
            'access_token' => $this->access_token,
        ];
        $url = $this->hostapi.'/openapi/group/delete';
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 添加邮件群组成员
     * @param $group_alias
     * @param $members
     */
    public function groupAddMember($group_alias, $members)
    {
        $header = ['Authorization' => $this->access_token];
        $data = [
            'group_alias' => $group_alias,
            'members' => $members,
            'access_token' => $this->access_token,
        ];
        $url = $this->hostapi.'/openapi/group/addmember';
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 删除邮件群组成员
     * @param $group_alias
     * @param $members
     */
    public function groupDeleteMember($group_alias, $members)
    {
        $header = ['Authorization' => $this->access_token];
        $data = [
            'group_alias' => $group_alias,
            'members' => $members,
            'access_token' => $this->access_token,
        ];
        $url = $this->hostapi.'/openapi/group/deletemember';
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 添加邮件群组权限列表
     * @param $group_alias
     * @param $allow_list
     */
    public function groupAddPermission($group_alias, $allow_list)
    {
        $header = ['Authorization' => $this->access_token];
        $data = [
            'group_alias' => $group_alias,
            'allow_list' => $allow_list,
            'access_token' => $this->access_token,
        ];
        $url = $this->hostapi.'/openapi/group/addallowlist';
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 删除邮件群组权限列表
     * @param $group_alias
     * @param $allow_list
     */
    public function groupDeletePermission($group_alias, $allow_list)
    {
        $header = ['Authorization' => $this->access_token];
        $data = [
            'group_alias' => $group_alias,
            'allow_list' => $allow_list,
            'access_token' => $this->access_token,
        ];
        $url = $this->hostapi.'/openapi/group/delallowlist';
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 开启强制启用微信动态密码
     * @param $alias
     */
    public function enableForceWeChatToken($alias)
    {
        $header = ['Authorization' => $this->access_token];
        $data = [
            'alias' => $alias,
            'access_token' => $this->access_token,
        ];
        $url = $this->hostapi.'/openapi/user/openwxtoken';
        $result = $this->getResult($url, $data, $header);
        return $result;
    }

    /**
     * @description 关闭强制启用微信动态密码
     * @param $alias
     */
    public function disForceWeChatToken($alias)
    {
        $header = ['Authorization' => $this->access_token];
        $data = [
            'alias' => $alias,
            'access_token' => $this->access_token,
        ];
        $url = $this->hostapi.'/openapi/user/closewxtoken';
        $result = $this->getResult($url, $data, $header);
        return $result;
    }


    private function getResult($url, $data, $headers = [])
    {
        $result = HttpClient::post($url, $data, $headers);
        if( $result === '' ) return true;
        $result = json_decode($result);
        return $result;
    }
}
?>
