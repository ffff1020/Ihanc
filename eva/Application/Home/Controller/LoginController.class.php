<?php
namespace Home\Controller;
use Think\Controller;
//use Aliyun\SmsDemo;
class LoginController extends Controller {
    public function index(){
    	cookie('key',null);
    	cookie(null);
    	session('DB',null);
    	$this->display("login/index");
 	}

 	public function logOut(){
 		$this->redirect('index/logOut','',0,'');
 	}
 	public function getCode(){
 	    if($_GET['name']==null) return false;
 	    $condition['name']=$_GET['name'];
        $admin=M('user');
        $rs=$admin->where($condition)->select();
        if(!$rs) {echo '{"success": 0}';return false;}
        $host = "http://sms.market.alicloudapi.com";
        $path = "/singleSendSms";
        $method = "GET";
        $appcode = "3b5d414021c1471c87fa225fb399e716";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $data=rand(100000,999999);
        $Cdata['code']=(string)$data;
        $code='{"code":"'.$data.'"}';
        $tel=$rs[0]['tel'];
        echo substr($tel,7,4);;
        $querys = "ParamString=".$code."&RecNum=".$tel."&SignName=瀚盛科技&TemplateCode=SMS_84110017";
        $bodys = "";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $Cdata['iduser']=$rs[0]['iduser'];
        $codeUpdate=$admin->data($Cdata)->save();
       // return $codeUpdate;
      // if($codeUpdate) return var_export(curl_exec($curl));
        set_time_limit(0);
        header('Content-Type: text/plain; charset=utf-8');
        $response =\Aliyun\SmsDemo::sendSms($tel,(string)$data);
        $result= json_decode(json_encode($response),TRUE);
        if($result['Message']=='OK'){
            echo '{"success": 1}';
        }else
            echo '{"success": 2}';

       // print_r($response);
       // return json_encode($result);
    }

    public function telLogin(){
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
        if (! openssl_private_decrypt(base64_decode($_POST['authData']), $decrypted, $privateKey)) {
            echo json_encode(['error_description' => 'Decrypted fail.']);
            exit();
        }
// free the private key
        openssl_free_key($privateKey);
// return interface json data
        //echo json_encode($password);
        $info=explode(':',$decrypted);
        //登录
        $where['name']=$info[0];
        //echo $info[0];
        $where['pwd']=md5($info[1]);
        $where['code']=$info[2];
        $admin=M('user');
        $rs=$admin->where($where)->select();
      //  echo $rs;
        if($rs){
            $etime=strtotime($rs[0]['etime']);
            $thistime=strtotime(date('Y-m-d H:i:s'));
            if($etime>$thistime){
                cookie('exp',1);
            }else{
                cookie('exp',0);
            }
            //在log表中添加登陆
            $log=M('user_log');
            $ldata['iduser']=$rs[0]['iduser'];
            $ldata['ltime']=date('Y-m-d H:i:s');
            $log_id=$log->add($ldata);

            //update数据库中的IP,logintime
            $data['ltime']=microtime();
            $data['ip']=get_client_ip();
            $data['iduser']=$rs[0]['iduser'];
            $data['code']=rand(100000,999999);
            $admin->save($data);
            //登录成功,进行cookies的设置
            cookie('log_id',$log_id);
            cookie('id',$rs[0]['iduser']);
            cookie('nick',$rs[0]['nick']);
            $str=$rs[0]['iduser'].$rs[0]['name'].$data['ltime'].$data['ip'];
            $key=authcode($str,'ENCODE',C('COOKIE_KEY'));
            cookie('key',$key);
            $db='ihanc_'.$rs[0]['db'].'.';
            session('DB',$db);
            //查看asset表，在月初插入asset数据
            $asset=M(I('session.DB').'asset');
            $where['time']=date('Y-m',strtotime('-1 month'));
            $rs=$asset->where($where)->select();
            if(!$rs){
                $stock=M(I('session.DB').'stock_sum_view');
                $stocklist=$stock->sum('sum');
                $data['stock']=$stocklist;
                $data['time']=$where['time'];
                $bank=M(I('session.DB').'bank');
                $data['bank']=$bank->sum('sum');
                $sale=M(I('session.DB').'sale');
                $data['mcredit']=$sale->sum('sum');
                $purchase=M(I('session.DB').'purchase');
                $data['scredit']=$purchase->sum('sum');
                $freight=M(I('session.DB').'freight');
                $data['freight']=$freight->where('status=0')->sum('freight');
                $asset->add($data);
            }
            $this->redirect('index/index','',0,'');
        }else{
            $this->assign('info',"用户名密码或验证码不正确,请重新输入!");
            $this->display('login/index');
        }

    }
}