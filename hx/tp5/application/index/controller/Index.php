<?php
namespace app\index\controller;
use think\Db;
class Index
{
    function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Authorization,Content-Type");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }
    }
    public function index(){
       // return json_encode($_SERVER['HTTP_AUTHORIZATION']);
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) return json_encode(['error' => 0]);
        $auth=substr($_SERVER['HTTP_AUTHORIZATION'],0,-1);
        $where['etime']=['>',date("Y-m-d H:i:s")];
        $user=Db::table('hs_0.user')
            ->where(['token'=>$auth])
            ->where($where)
            ->find();
        //return json_encode($user);
        if(sizeof($user)==0){
            return json_encode(['error' => 0]);}
        else{
            $rs=Db::table('hs_0.user')
                ->update([
                    'id'=>$user['id'],
                    'etime'=>date("Y-m-d H:i:s",strtotime("+2 hour"))]);

            return json_encode(['error' => 1]);
        }
    }
    public function login()
    {
        //print_r($_SERVER['HTTP_AUTHORIZATION']); return;
        if (! isset($_SERVER['HTTP_AUTHORIZATION'])) {
            echo json_encode(['error_description' => 'An error occured.']);
            exit();
        }
// private key:
        $private_key = '-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALINH5X8NTQLJhZq
SE9KMvBZWYKYyqje66CZVc+K2XLsGqW0YNbRzU9txDhj47+W6QBreoG/EQarCFlJ
CiRQJD+YIe0V+bCUaaEelItkoq09U4IrJkYJbol1CzaKMpWCaSzePFJDRx55OspT
p/gpYvz0NioJOoTEkhKNICrkwBqtAgMBAAECgYAzSxSOYNny5ENUscmjDf0ewJ7I
wLuhapb27TWLVLTQJrSGiDBdspMzDqw4ko5J42+8bzobpq+A/ESrdB831t6Z+GQ8
vKLPSnguYS6aQhsYP2eXQu2WxD2Tjekf0ekjoc9/jz3ZFK4DuqcUisBQyBmozUR5
r9MjMqf02RfgDmsYAQJBANz74T7GejD5u81rUnlxRYUIjExSrDFyn0ojC6QfbNrG
VmSJDkoaKZrsQQhe2Ujc7Ze8mG4vTXjmZ5oFrDVaASUCQQDOQ7LhL5RdQt4H34V9
/rcyp4HgfxOHAMyBu0o07D/v1nP/YEcxfHZ8iHj/gSdw3seskOetF/mafSOPImXe
G9DpAkBaY/Erk1Xx6IToLokKwcl09B0nLv3eMAt18MXXOT92cYBvGRyuNOtlwlOL
j/iC9FN/KJaVI2YmGOCxwLZDEHC9AkEAmFEN65TDLwuOAqphXeWXS2S/WBT/Spag
brzr06ESpf3rsw5aBIUwyk3NbIDnq0YYlap8KyqlPBxlAfIY36gS4QJBAJa2at4M
VeqR1cuE/cVZV3NZgomSvm0WXu7bCAbjHX3bYkazdoBNZlCzNU2vzR4XNaZJYzX8
KqPdKDkCWPQjbpo=
-----END PRIVATE KEY-----';

        if (! $privateKey = openssl_pkey_get_private($private_key)) {
            echo json_encode(['error_description' => 'Private key is wrong.']);
            exit();
        }
// decrypt
        $decrypted = '';
        if (! openssl_private_decrypt(base64_decode($_SERVER['HTTP_AUTHORIZATION']), $decrypted, $privateKey)) {
            echo json_encode(['error_description' => 'Decrypted fail.']);
            exit();
        }
// free the private key
        openssl_free_key($privateKey);
// return interface json data
        //echo json_encode($password);
        $info=explode(':',$decrypted);
      //  print_r($info);
        $user=Db::table('hs_0.user')
              ->field('name,tel,id,role_id,MDB')
              ->where(['name|tel'=>$info[0],'pwd'=>md5($info[1]),'code'=>$info[2]])
              ->select();
        if(sizeof($user)==0) return json_encode(['error' => 0]);
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $token='';
        for ( $i = 0; $i < 33; $i++ )
           {
               $token.= $chars[ mt_rand(0, strlen($chars) - 1) ];
           }
        $rs=Db::table('hs_0.user')
            ->update(['token' => $token,
                     'id'=>$user[0]['id'],
                     'ltime'=>date("Y-m-d H:i:s"),
                     'etime'=>date("Y-m-d H:i:s",strtotime("+1 hour"))]);
        if($rs==0) return json_encode(['error' => 0]);
        $data['token']=$token;
        $data['name']=$user[0]['name'];
        $data['role_id']=$user[0]['role_id'];
        if($user[0]['role_id']>0) {
            $roleAuthTable = 'hs_' . $user[0]['MDB'] . '.role_auth a';
            $authTable = 'hs_' . $user[0]['MDB'] . '.auth b';
            $auth = Db::table($roleAuthTable)
                ->join($authTable, 'a.auth_id=b.auth_id')
                ->field('b.sys_name')
                ->where('a.role_id=' . $user[0]['role_id'])
                ->select();
            foreach ($auth as $value)
                $data['role_auth'][$value['sys_name']] = true;
        }
        return json_encode($data);
    }
    public function getCode(){
        $postData = file_get_contents("php://input", true);
        $postData = json_decode($postData, true);
        if(!$postData)  $postData=$_POST;
       // return json_encode($postData);
        $adminTable='hs_0.user';
        $rs=Db::table($adminTable)->where(['name|tel'=>$postData['data']])->find();
        if(!$rs) return 'error';
        //echo $rs['tel'];
        $result['tel']=substr($rs['tel'],7,4);
        $data=rand(100000,999999);
        $Cdata['code']=(string)$data;
        $Cdata['id']=$rs['id'];
        $codeUpdate=Db::table($adminTable)->update($Cdata);
        set_time_limit(0);
        header('Content-Type: text/plain; charset=utf-8');
        $response =\SmsDemo::sendSms($rs['tel'],(string)$data);
        $result['result']= json_decode(json_encode($response),TRUE);
        $result['tel']=substr($rs['tel'],7,4);
        return json_encode($result);

    }
    public function appLogin(){
        $info=explode(':',base64_decode($_SERVER['HTTP_AUTHORIZATION']));
        //return json_encode($info);
        $user=Db::table('hs_0.user')
            ->field('name,tel,id,role_id,MDB')
            ->where(['tel'=>$info[0],'pwd'=>md5($info[1]),'code'=>$info[2]])
           // ->fetchSql(true)
            ->select();
        //return $user;
        if(sizeof($user)==0) return "error";
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $token='';
        for ( $i = 0; $i < 33; $i++ )
        {
            $token.= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        $rs=Db::table('hs_0.user')
            ->update(['token' => $token,
                'id'=>$user[0]['id'],
                'ltime'=>date("Y-m-d H:i:s"),
                'etime'=>date("Y-m-d H:i:s",strtotime("+1 hour"))]);
        if($rs==0) return json_encode(['error' => 0]);
        $data['token']=$token;
        $data['name']=$user[0]['name'];

        $data['role']=$user[0]['role_id']==0?true:false;

        if($user[0]['role_id']>0) {
            $roleAuthTable = 'hs_' . $user[0]['MDB'] . '.role_auth a';
            $authTable = 'hs_' . $user[0]['MDB'] . '.auth b';
            $auth = Db::table($roleAuthTable)
                ->join($authTable, 'a.auth_id=b.auth_id')
                ->field('b.sys_name')
                ->where('a.role_id=' . $user[0]['role_id'])
                ->select();
            foreach ($auth as $value)
                $data['role_auth'][$value['sys_name']] = true;
        }
        return json_encode($data);
    }


}
