<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/13
 * Time: 7:21
 */

namespace app\index\controller;
use think\Db;

class Setting
{
    private $mdb='hs_';
    private $user='';
    function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Authorization,Content-Type");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }
        if (! isset($_SERVER['HTTP_AUTHORIZATION'])) die();
        $auth=strlen($_SERVER['HTTP_AUTHORIZATION'])==35?substr($_SERVER['HTTP_AUTHORIZATION'],1,-2):substr($_SERVER['HTTP_AUTHORIZATION'],0,-1);
        $user=Db::table('hs_0.user')
            ->where(['token'=>$auth])
            ->find();
        if(sizeof($user)==0) die();
        $this->mdb.=(string)$user['MDB'];
        $this->user=$user['name'];
        insertAsset($this->mdb);
    }
    public function info(){
        $infoTable=$this->mdb.'.info';
        $rs=Db::table($infoTable)->find(1);
        return json_encode($rs);
    }
    public function editInfo(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $infoTable=$this->mdb.'.info';
        $rs=Db::table($infoTable)
            ->where('id',1)
            ->update($postData);
        return $rs;
    }
    public function cat(){
        $catTable=$this->mdb.'.cat';
        $rs=Db::table($catTable)->select();
        return json_encode($rs);
    }
    public  function catCreate(){
        $catTable=$this->mdb.'.cat';
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $data['cat_name']=$postData['cat_name'];
        $rs=Db::table($catTable)
            ->insert($data);
        if($rs==1) mylog($this->user,$this->mdb,'新建种类:'.$data['cat_name']);
        return json_encode(['result'=>$rs]);
    }
    public function catUpdate(){
        $catTable=$this->mdb.'.cat';
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $data['cat_name']=$postData['cat_name'];
        $data['cat_id']=$postData['cat_id'];
        $odata = Db::table($catTable)->find($postData['cat_id']);
        $rs=Db::table($catTable)
            ->update($data);
        if($rs==1) mylog($this->user,$this->mdb,$odata['cat_name'].'更新种类为:'.$data['cat_name']);
        return json_encode(['result'=>$rs]);
    }
    public function catDelete(){
        $goodsTable=$this->mdb.'.goods';
        $postData=$_GET;
        $rs=Db::table($goodsTable)->where($postData)->find();
        if(sizeof($rs)==0) {
            $catTable=$this->mdb.'.cat';
            $data = Db::table($catTable)->find($postData['cat_id']);
            $rs = Db::table($catTable)->delete($postData['cat_id']);
            if($rs==1) mylog($this->user,$this->mdb,'删除种类:'.$data['cat_name']);
            return json_encode(['result' => $rs]);
        }else return json_encode(['result' => 2]);
    }
    public function goods(){
        $goodsTable=$this->mdb.'.goods';
        $rs = Db::table($goodsTable)
            ->orderRaw("LENGTH(goods_sn)")
            ->select();
        return json_encode($rs);
    }
    public function getUnitPrice(){
        $unit_priceTable=$this->mdb.'.unit_price';
        $rs=Db::table($unit_priceTable)->where($_GET)->select();
        return json_encode($rs);
    }
    public function goodsQuery(){
        $goodsTable=$this->mdb.'.goods';
        if(isset($_GET['member_id']) && strstr($_GET['search'],'?')){
            $search=str_replace('?','',$_GET['search']);
            $query="SELECT c.goods_id,goods_name,c.unit_id,promote,warn_stock,is_order,c.time,out_price,a.sale_id"
                ." FROM `".$this->mdb."`.`goods` `c` LEFT JOIN `".$this->mdb."`.`sale_detail` `b` ON `c`.`goods_id`=`b`.`goods_id` LEFT JOIN"
                ."(SELECT * from `".$this->mdb."`.`sale_all` where `member_id`=".$_GET['member_id'].") `a` on `a`.sale_id=`b`.sale_id WHERE ( `goods_name` LIKE '%".$search."%' OR `goods_sn` LIKE '%".$search."%' ) group by `c`.`goods_id` order by a.sale_id DESC,LENGTH(goods_sn) LIMIT 0,8";
           // return $query;
            $rs=Db::query($query);
        }else {
            $rs = Db::table($goodsTable)
                ->where('goods_name|goods_sn', 'like', '%' . $_GET['search'] . '%')
                ->field('goods_id,goods_name,unit_id,promote,warn_stock,is_order,time,out_price')
                ->orderRaw('LENGTH(goods_sn) ')
                ->page(1, 8)
                ->select();
        }
        return json_encode($rs);
    }
    public function goodsCheck(){
        $goodsTable=$this->mdb.'.goods';
        $rs = Db::table($goodsTable)
            ->where(['goods_name'=>$_GET['search']])
            ->find();
        return json_encode($rs);
    }
    public function goodsUpdate(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $data['goods_name']=$postData['goods_name'];
        $data['goods_sn']=$postData['goods_sn'];
        $data['warn_stock']=$postData['warn_stock'];
        $data['unit_id']=$postData['unit_id']['unit_id'];
        $data['out_price']=$postData['out_price'];
        $data['promote']=$postData['promote']?1:0;
        $data['is_order']=$postData['is_order']?1:0;
        $data['cat_id']=$postData['cat_id']['cat_id'];
        $goodsTable=$this->mdb.'.goods';
        if($postData['goods_id']==0){

            $rs=Db::table($goodsTable)
                ->insertGetId($data);
            $remark[0]='添加新产品'.$data['goods_name'];
            $postData['goods_id']=$rs;
        }else{
            $remark=Db::table($goodsTable)->find($postData['goods_id']);
            $data['goods_id']=$postData['goods_id'];
            $rs=Db::table($goodsTable)
                ->update($data);
        }
        mylog($this->user,$this->mdb,'修改产品成功,old data:'.implode('|',$remark));
        if(isset($postData['unit_price1'])){
            $mData1['goods_id']=$postData['goods_id'];
            $mData1['unit_id']=$postData['unit_price1']['unit_id']['unit_id'];
            if(isset($postData['unit_price1']['fx'])&&$postData['unit_price1']['fx']>0) {
                $mData1['fx'] = $postData['unit_price1']['fx'];
                $mData1['price'] = isset($postData['unit_price1']['price']) ? $postData['unit_price1']['price'] : 0;
                $unit_priceTable = $this->mdb . '.unit_price';
                if (isset($postData['unit_price1']['unit_price_id'])) {
                    $mData1['unit_price_id'] = $postData['unit_price1']['unit_price_id'];
                    $rs = Db::table($unit_priceTable)->update($mData1);
                } else {
                    $rs = Db::table($unit_priceTable)
                        ->insert($mData1);
                }
            }
        }
        if(isset($postData['unit_price2'])){
            $mData2['goods_id']=$postData['goods_id'];
            $mData2['unit_id']=$postData['unit_price2']['unit_id']['unit_id'];
            if(isset($postData['unit_price2']['fx'])&&$postData['unit_price2']['fx']>0){
                $mData2['fx']=$postData['unit_price2']['fx'];
                $mData2['price']=isset($postData['unit_price2']['price'])?$postData['unit_price2']['price']:0;
                $unit_priceTable=$this->mdb.'.unit_price';
                if(isset($postData['unit_price2']['unit_price_id'])){
                    $mData2['unit_price_id']=$postData['unit_price2']['unit_price_id'];
                    $rs=Db::table($unit_priceTable)->update($mData2);
                }else{
                    $rs=Db::table($unit_priceTable)
                    ->insert( $mData2);
                }
            }
        }
         return json_encode(['result'=>1]);
    }
    public function pwdUpdate(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
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
        if (! openssl_private_decrypt(base64_decode($postData['data']), $decrypted, $privateKey)) {
            echo json_encode(['error_description' => 'Decrypted fail.']);
            exit();
        }
// free the private key
        openssl_free_key($privateKey);
// return interface json data
        //echo json_encode($password);
        $info=explode(':',$decrypted);
        $user=Db::table('hs_0.user')
            ->where(['name'=>$info[0],'pwd'=>md5($info[1])])
            ->find();
        if($user){
            $data['id']=$user['id'];
            $data['pwd']=md5($info[2]);
            $rs=Db::table('hs_0.user')
                ->update($data);
            if($rs) return json_encode(['result'=>1]);
        }else{
            return json_encode(['result'=>0]);
        }
    }
    public function store(){
        $storeTable=$this->mdb.'.store';
        $rs=Db::table($storeTable)->select();
        return json_encode(['store'=>$rs]);
    }
    public function storeUpdate(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $storeTable=$this->mdb.'.store';
        if($postData['store_id']==''){
            $rs=Db::table($storeTable)
                ->where(['store_name'=>$postData['store_name']])
                ->find();
            if($rs) return json_encode(['result'=>3]);
            $rs=Db::table($storeTable)
                ->insert($postData);
            if($rs) return json_encode(['result'=>1]);
        }else{
            $data['store_id']=$postData['store_id'];
            $data['store_name']=$postData['store_name'];
            $rs=Db::table($storeTable)
                ->update($data);
            if($rs) return json_encode(['result'=>1]);
        }
        return json_encode(['result'=>0]);
    }
    public function staff(){
        $staffTable=$this->mdb.'.staff a';
        $roleTable=$this->mdb.'.role b';
        $rs=Db::table($staffTable)
            ->join($roleTable,'a.role_id=b.role_id','LEFT')
            ->where('staff_name','like','%'.$_GET['search'].'%')
            ->whereOr('staff_sn','like','%'.$_GET['search'].'%')
            ->field('a.*,b.role_name')
            ->page($_GET['page'].',10')
            ->select();
        $count=Db::table($staffTable)
            ->where('staff_name','like','%'.$_GET['search'].'%')
            ->whereOr('staff_sn','like','%'.$_GET['search'].'%')
            ->count();
        $data['staff']=$rs;
        $data['total_count']=$count;
        return json_encode($data);
    }
    public function staffSearch(){
        $staffTable=$this->mdb.'.staff';
        $rs=Db::table($staffTable)
            ->where('staff_name','like','%'.$_GET['search'].'%')
            ->whereOr('staff_sn','like','%'.$_GET['search'].'%')
            ->field('staff_id,staff_name')
            ->page($_GET['page'].',10')
            ->select();
        $data['staff']=$rs;
        return json_encode($data);
    }
    public function staffUpdate(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $staffTable=$this->mdb.'.staff';
        if($postData['staff_id']==''){
            $rs=Db::table($staffTable)
                ->where(['staff_name'=>$postData['staff_name']])
                ->find();
            if($rs) return json_encode(['result'=>3]);
            $rs=Db::table($staffTable)
                ->insert($postData);
            if(!$rs) return json_encode(['result'=>0]);
            if($postData['role_id']>0){
                $adminTable='hs_0.user';
                $rs=Db::table($adminTable)
                    ->where(['tel'=>$postData['staff_phone']])
                    ->find();
                if($rs) return json_encode(['result'=>3]);
                $data['name']=$postData['staff_name'];
                $data['pwd']='e10adc3949ba59abbe56e057f20f883e';
                $data['tel']=$postData['staff_phone'];
                $data['MDB']=(int)substr($this->mdb,3);
                $data['role_id']=$postData['role_id'];
                $rs=Db::table($adminTable)->insert($data);
                if(!$rs) return json_encode(['result'=>0]);
            }
        }else{
            $adminTable='hs_0.user';
            $odata=Db::table($staffTable)->find($postData['staff_id']);
            $data['staff_id']=$postData['staff_id'];
            $data['staff_sn']=$postData['staff_sn'];
            $data['staff_name']=$postData['staff_name'];
            $data['staff_phone']=$postData['staff_phone'];
            $data['role_id']=$postData['role_id'];
            $rs=Db::table($staffTable)
                ->update($data);
            if(!$rs) return json_encode(['result'=>0]);
            if($odata['staff_phone']!=$postData['staff_phone']||$odata['role_id']!=$postData['role_id']){
                if($odata['role_id']>0&&$postData['role_id']>0) {
                    $adata['tel'] = $postData['staff_phone'];
                    $adata['role_id'] = $postData['role_id'];
                    $adata['token']='';
                    $rs = Db::table($adminTable)->where(['tel' => $odata['staff_phone']])->update($adata);
                    if (!$rs) return json_encode(['result' => 0]);
                }
                if($odata['role_id']<0&&$postData['role_id']>0){
                    unset($data);
                    $rs=Db::table($adminTable)
                        ->where(['tel'=>$postData['staff_phone']])
                        ->find();
                    if($rs) return json_encode(['result'=>3]);
                    $data['name']=$postData['staff_name'];
                    $data['pwd']='e10adc3949ba59abbe56e057f20f883e';
                    $data['tel']=$postData['staff_phone'];
                    $data['MDB']=(int)substr($this->mdb,3);
                    $data['role_id']=$postData['role_id'];
                    $rs=Db::table($adminTable)->insert($data);
                    if(!$rs) return json_encode(['result'=>0]);
                }
                if($odata['role_id']>0&&$postData['role_id']<0){
                    $query='delete from hs_0.user where tel='.$postData['staff_phone'];
                    Db::execute($query);
                }
            }
        }
        return json_encode(['result'=>1]);
    }
    public function unit(){
        $unitTable=$this->mdb.'.unit';
        if(!isset($_GET['search'])){
            $rs=Db::table($unitTable)->select();
                return json_encode($rs);
        }
        $rs=Db::table($unitTable)
            ->where('unit_name','like','%'.$_GET['search'].'%')
            ->page($_GET['page'].',10')
            ->select();
        $count=Db::table($unitTable)
            ->where('unit_name','like','%'.$_GET['search'].'%')
            ->count();
        $data['unit']=$rs;
        $data['total_count']=$count;
        return json_encode($data);
    }
    
    public function unitUpdate(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $unitTable=$this->mdb.'.unit';
        if($postData['unit_id']==''){
            $rs=Db::table($unitTable)
                ->where(['unit_name'=>$postData['unit_name']])
                ->find();
            if($rs) return json_encode(['result'=>3]);
            $rs=Db::table($unitTable)
                ->insert($postData);
            if($rs) return json_encode(['result'=>1]);
        }else{
            $data['unit_id']=$postData['unit_id'];
            $data['unit_name']=$postData['unit_name'];
            $rs=Db::table($unitTable)
                ->update($data);
            if($rs) return json_encode(['result'=>1]);
        }
        return json_encode(['result'=>0]);
    }
    
   public function role (){
        $roleTable=$this->mdb.'.role';
        $rs['role']=Db::table($roleTable)->select();
        $rs['auth']=$this->auth();
        return json_encode($rs);
   }
   public function roleUpdate(){
       $postData=file_get_contents("php://input",true);
       $postData=json_decode($postData,true);
       $data['role_name']=$postData['role_name'];
       $roleTable=$this->mdb.'.role';
       if(isset($postData['role_id'])){
           $data['role_id']=$postData['role_id'];
           $rs=Db::table($roleTable)->update($data);
       }else{
           $rs=Db::table($roleTable)->insert($data);
       }
       if($rs) return json_encode(['result'=>1]);
       return json_encode(['result'=>0]);
   }
    public function roleDelete()
    {
        $postData = file_get_contents("php://input", true);
        $postData = json_decode($postData, true);
        $data['role_id'] = $postData['role_id'];
        $roleTable = $this->mdb . '.role';
        $rs=Db::table($roleTable)->delete($data['role_id']);
        if($rs) return json_encode(['result'=>1]);
        return json_encode(['result'=>0]);
    }
    public function auth(){
       $data=array();
       $authTable=$this->mdb.'.auth';
       $rs=Db::table($authTable)->where('pid','0')->select();
       for($i=0;$i<sizeof($rs);$i++){
           $data[$i]['label']=$rs[$i]['auth_name'];
           $data[$i]['auth_id']=$rs[$i]['auth_id'];
           $temp=Db::table($authTable)->field('auth_name as label,auth_id')->where('pid',$rs[$i]['auth_id'])->select();
           $data[$i]['children']=$temp;
       }
       return $data;
    }
    public function roleAuth(){
        $data=array();
        $role_authTable=$this->mdb.'.role_auth';
        $rs=Db::table($role_authTable)->where($_GET)->select();
        for ($i=0;$i<sizeof($rs);$i++){
            $data[$i]=$rs[$i]['auth_id'];
        }
        return json_encode($data);
    }
    public function updateAuth(){
        $postData = file_get_contents("php://input", true);
        $postData = json_decode($postData, true);
        $role_authTable=$this->mdb.'.role_auth';
        $query ='delete from '.$role_authTable.' where role_id='.$postData['role_id'];
        Db::execute($query);
        $i=0;
        foreach ($postData['auth'] as $value){
            $data[$i]['role_id']=$postData['role_id'];
            $data[$i]['auth_id']=$value;
            $i++;
        }
        $rs=Db::table($role_authTable)->insertAll($data);
        if(!$rs) return json_encode(['result'=>0]);
        return json_encode(['result'=>1]);
    }
    public function mCreditAdd(){
        $postData = file_get_contents("php://input", true);
        $postData = json_decode($postData, true);
        $insertTime=date("Y-m-d H:i:s");
        $i=0;
        $data=array();
        if(isset($postData['member_id'])){
            $data[0]['time']=$insertTime;
            $data[0]['member_id']=$postData['member_id'];
            $data[0]['sum']=$postData['sum'];
            $data[0]['summary']=isset($postData['summary'])?$postData['summary']:'';
            //$data[$i]['summary'].="初期录入";
            $data[0]['type']='Init';
            $data[0]['user']=$this->user;
        }else
        foreach ($postData as $value){
            $data[$i]['time']=$insertTime;
            $data[$i]['member_id']=$value['member_id'];
            $data[$i]['sum']=$value['sum'];
            $data[$i]['summary']=isset($value['summary'])?$value['summary']:'';
           //$data[$i]['summary'].="初期录入";
            $data[$i]['type']='Init';
            $data[$i]['user']=$this->user;
            $i++;
        }
        $saleTable=$this->mdb.'.sale';
        $rs=Db::table($saleTable)->insertAll($data);
        if(!$rs) return json_encode(['result'=>0]);
        return json_encode(['result'=>1]);
    }
    public function sCreditAdd(){
        $postData = file_get_contents("php://input", true);
        $postData = json_decode($postData, true);
        $insertTime=date("Y-m-d H:i:s");
        $i=0;
        $data=array();
        foreach ($postData as $value){
            $data[$i]['time']=$insertTime;
            $data[$i]['supply_id']=$value['supply_id'];
            $data[$i]['sum']=$value['sum'];
            $data[$i]['summary']=isset($value['summary'])?$value['summary']:'';
            //$data[$i]['summary'].="初期录入";
            $data[$i]['type']='Init';
            $data[$i]['user']=$this->user;
            $i++;
        }
        $saleTable=$this->mdb.'.purchase';
        $rs=Db::table($saleTable)->insertAll($data);
        if(!$rs) return json_encode(['result'=>0]);
        return json_encode(['result'=>1]);
    }
    public function stockDetail(){
        $postData = file_get_contents("php://input", true);
        $postData = json_decode($postData, true);
        $insertTime=date("Y-m-d H:i:s");
        $stockTable=$this->mdb.'.stock';
        $stockDetailTable=$this->mdb . '.stock_detail';
        $inorder='IN'.date("Ymd").'-0';
        $unit_priceTable=$this->mdb.'.unit_price';
        foreach ($postData as $value){
            $data['inorder']=$value['is_order']==1?$inorder:0;
            $data['goods_id']=$value['goods_id'];
            $data['store_id']=$value['store_id'];
            $data['Dsum']=$value['sum'];
            $data['time']=$insertTime;
            $data['user']=$this->user;
            if($value['unit_id_0']==$value['unit_id']){
                $data['Dstock']=$value['number'];
            }else{
                $where['goods_id']=$value['goods_id'];
                $where['unit_id']=$value['unit_id'];
                $temp=Db::table($unit_priceTable)->where($where)->find();
                if($temp['fx']!=0)
                    $data['Dstock']=$value['number']/$temp['fx'];
                else
                    $data['Dstock']=$value['number'];
            }
            $data['remark']='初期库存录入';
            $swhere['goods_id']=$value['goods_id'];
            $swhere['store_id']=$value['store_id'];
            $swhere['inorder']=$data['inorder'];
            $stock=Db::table($stockTable)->where($swhere)->find();
            if(!$stock){$stock['number']=0;$stock['sum']=0;}
            $data['stock']=$data['Dstock']+$stock['number'];
            $data['sum']=$data['Dsum']+$stock['sum'];
            $rs=Db::table($stockDetailTable)->insert($data);
            if(!$rs) return json_encode(['result'=>0]);
        }
        return json_encode(['result'=>1]);
    }
    public function log(){
        $logTable=$this->mdb.'.log';
        $where=[];
        if($_GET['search']!='')
            $where['remark']=['like','%'.$_GET['search'].'%'];
        if($_GET['edate']&&$_GET['sdate'])
            $where['time']=['between',array($_GET['sdate'],$_GET['edate'])];
        $rs=Db::table($logTable)
            ->where($where)
            ->order('time desc')
            ->page($_GET['page'].',10')
            ->select();
        $count=Db::table($logTable)
            ->where($where)
            ->count();
        $data['details']=$rs;
        $data['total_count']=$count;
        //return $this->getAuth();
        return json_encode($data);
    }

    public function getAuth()
    {
        $user=Db::table('hs_0.user')->where(['name'=>$this->user])->find();
        $data['role']=$user['role_id']==0?true:false;
        if($user['role_id']>0) {
            $roleAuthTable = $this->mdb. '.role_auth a';
            $authTable =$this->mdb. '.auth b';
            $auth = Db::table($roleAuthTable)
                ->join($authTable, 'a.auth_id=b.auth_id')
                ->field('b.sys_name')
                ->where('a.role_id=' . $user['role_id'])
                ->select();
            foreach ($auth as $value)
                $data['role_auth'][$value['sys_name']] = true;
        }
        return json_encode($data);
    }
}