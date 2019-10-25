<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22 0022
 * Time: 下午 1:11
 */

namespace app\index\controller;
use think\Db;
use think\Log;

class Sale
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
    public function member(){
        $memberTable=$this->mdb.'.member';
        $rs=Db::table($memberTable)
            ->where('member_name','like','%'.$_GET['search'].'%')
            ->whereOr('member_sn','like','%'.$_GET['search'].'%')
            ->page($_GET['page'].',10')
            ->select();
        $count=Db::table($memberTable)
            ->where('member_name','like','%'.$_GET['search'].'%')
            ->whereOr('member_sn','like','%'.$_GET['search'].'%')
            ->count();
        $data['member']=$rs;
        $data['total_count']=$count;
        return json_encode($data);
    }
    public function memberAll(){
        $memberTable=$this->mdb.'.member';
        $rs=Db::table($memberTable)
            ->orderRaw("LENGTH(member_sn)")
            ->select();
        return json_encode($rs);
    }

    public function memberUpdate(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $memberTable=$this->mdb.'.member';
        $rs=Db::table($memberTable)
            ->where(['member_name'=>$postData['member_name']])
            ->find();
        if($rs) return json_encode(['result'=>3]);
        if($postData['member_id']==''){
            $rs=Db::table($memberTable)
                ->insert($postData);
            if($rs) return json_encode(['result'=>1]);
        }else{
            $old=Db::table($memberTable)->find($postData['member_id']);
            $data['member_id']=$postData['member_id'];
            $data['member_sn']=$postData['member_sn'];
            $data['member_name']=$postData['member_name'];
            $data['member_phone']=$postData['member_phone'];
            $data['vip']=$postData['vip'];
            $rs=Db::table($memberTable)
                ->update($data);
            if($rs) {
                $old=$old['member_name']. ($old['member_sn']==$data['member_sn']?"":"编码=>".$old['member_sn']."|"). ($old['member_phone']==$data['member_phone']?"":"电话=>".$old['member_phone']);
                mylog($this->user,$this->mdb,"修改客户信息，原数据：".$old);
                return json_encode(['result'=>1]);
            }
        }
        return json_encode(['result'=>0]);
    }
    public function memberSearch()
    {
        $memberTable = $this->mdb . '.member';
        $rs = Db::table($memberTable)
            ->where('member_name', 'like', '%' . $_GET['search'] . '%')
            ->whereOr('member_sn', 'like', '%' . $_GET['search'] . '%')
            ->field('member_id,member_name,vip,member_phone')
            ->orderRaw('LENGTH(member_name)')
            ->page($_GET['page'] . ',10')
            ->select();
        $data['member'] = $rs;
        return json_encode($data);
    }
    public function sale(){
        $sale['summary']="";
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
       // print_r($postData); return;
        $back=$postData['back'];
        $trueTime=date("Y-m-d H:i:s");
        $insertTime=isset($postData['data']['time'])?$postData['data']['time']:$trueTime;
        $saleTable=$this->mdb.'.sale';
        //插入sale表
        if(sizeof($postData['detail'])>0) {
            if(isset($postData['data']['member']['sale_id'])){
                $sale_id=$postData['data']['member']['sale_id'];
                $query='delete from '.$this->mdb.'.sale_detail where sale_id='.$sale_id;
                Db::execute($query);
                $sale=Db::table($saleTable)->find($sale_id);
                $sale['time']=$insertTime;
                $sale['sum']=$postData['data']['ttl_sum']*$back;
                if($back==-1) $sale['summary']='退货：'.$sale['summary'];
                $sale['type']=$back==1?'S':'B';
                $rs = Db::table($saleTable)->update($sale);
            }else {
                $sale['member_id'] = $postData['data']['member']['member_id'];
                $sale['sum'] = $postData['data']['ttl_sum'] * $back;
                if (isset($postData['data']['summary'])) $sale['summary'] .= $postData['data']['summary'];
                $sale['time'] = $insertTime;
                if ($back == -1) $sale['summary'] = '退货：' . $sale['summary'];
                $sale['type'] = $back == 1 ? 'S' : 'B';     //S--一般进货销售记录，P--付款记录，CF--结转
                $sale['user'] = $this->user;
                $sale_id = Db::table($saleTable)
                    ->insertGetId($sale);
                if (!$sale_id) return json_encode(['result' => 0]);
            }
            //查看stock,确定inorder
            $stockTable=$this->mdb.'.stock';
            $stock_detailTable=$this->mdb.'.stock_detail';
            $unit_priceTable=$this->mdb.'.unit_price';
            $saleDetailTable = $this->mdb . '.sale_detail';
            //插入saleDetail 表
            $detail = [];
           // $i = 0;
            foreach ($postData['detail'] as $value) {
                unset($detail['remark']);
                $detail['sale_id'] = $sale_id;
                $where['goods_id']=$detail['goods_id'] = $value['good']['goods_id'];
                $detail['number'] = $value['number']*$back;
                $detail['price'] = $value['price'];
                $detail['unit_id'] = $value['unit_id'];
                $detail['sum'] = $value['sum'] * $back;
                if(isset($value['remark'])) $detail['remark']=$value['remark'];
                if(isset($value['number1'])){
                    $detail['number1'] = $value['number1'];
                    $detail['number2'] = $value['number2'];
                }
                //$detail['type']='sale';
                $where['store_id']=$detail['store_id']=$value['store_id'];

                $goodsTable=$this->mdb.".goods";
                $goods=Db::table($goodsTable)->find($value['good']['goods_id']);
                if($goods['is_order']==1)
                    $where['inorder']=['like','IN%'];

                $stock=Db::table($stockTable)->where($where)->order('stock_id')->select();
                if(!$stock){
                    $where['inorder']=$detail['inorder']=0;
                }else{
                    $j=0;
                    while(sizeof($stock)>1&&$j<sizeof($stock)&&$stock[$j]['number']<$value['number']){
                        $j++;
                    }
                    if($j==sizeof($stock)) {
                        $stock=Db::table($stockTable)->where($where)->order('number desc')->field('inorder')->find();
                        $where['inorder']=$detail['inorder']=$stock['inorder'];
                    }else
                    $where['inorder']=$detail['inorder']=$stock[$j]['inorder'];
                }
                $stock=Db::table($stockTable)->where($where)->order('inorder')->find();
                //sale_detail
                $sale_detail_id = Db::table($saleDetailTable)
                    ->insertGetId($detail);
                if (!$sale_detail_id) return json_encode(['result' => 2]);

                //插入stock_detail
                $data['inorder']=$detail['inorder'];
                $data['goods_id']=$value['good']['goods_id'];
                $data['store_id']=$value['store_id'];
                $data['inorder']=$detail['inorder'];
                if($value['good']['unit_id_0']!=$value['unit_id']){
                    $uWhere['goods_id']=$value['good']['goods_id'];
                    $uWhere['unit_id']=$value['unit_id'];
                    $uWhere['fx']=['>',0];
                    $unit_price=Db::table($unit_priceTable)->where($uWhere)->find();
                    if($unit_price)
                     $data['Dstock']=(-1)* $detail['number']/$unit_price['fx'];
                    else
                        $data['Dstock']=(-1)* $detail['number'];
                }else $data['Dstock']=(-1)* $detail['number'];
                if(!$stock){
                    $data['Dsum']= (-1)*$detail['sum'];
                    $data['sum']=$data['Dsum'];
                    $data['stock'] = $data['Dstock'] ;
                }else {
                    if($stock['number']!=0 && $stock['sum'] / $stock['number']>0)   {
                        $data['Dsum'] =  $stock['sum'] / $stock['number'] * $data['Dstock'];
                    }else
                        $data['Dsum']= (-1)*$detail['sum'];
                    $data['sum'] = $data['Dsum'] +$stock['sum'] ;
                    $data['stock'] = $data['Dstock'] + $stock['number'] ;
                }
                $data['time']=$trueTime;
                $data['user']=$this->user;
                if(isset($postData['data']['time']))$data['remark']="后期补录入时间:".$insertTime;
                $data['type']='sale';
                $rs=Db::table($stock_detailTable)->insert($data);
                if (!$rs) return json_encode(['result' => 2]);

                //插入account表
                $aData['sale_detail_id']=$sale_detail_id;
                $aData['goods_id']=$value['good']['goods_id'];
                $aData['subject']=0;
                $aData['cost']=(-1)*$data['Dsum'];
                $aData['income']=$detail['sum'];
                $aData['time']=$insertTime;
                $aData['user']=$this->user;
                $aData['store_id']=$value['store_id'];
                $accountTable=$this->mdb.'.account';
                $rs=Db::table($accountTable)->insert($aData);
                if (!$rs) return json_encode(['result' => 2]);
            }

        }
        //插入bankDetail表中
        if(isset($postData['pay']['paid_sum'])&&($postData['pay']['paid_sum']!=0)) {
            $pay['sum'] = $postData['pay']['paid_sum'] ;
            $pay['bank_id'] = $postData['pay']['bank'];
            $pay['time'] = $trueTime;
            $string = $back == 1 ? '客户付款：' : '退货，客户退款:';
            $pay['summary'] = $string . $postData['data']['member']['member_name'];
            if (isset($postData['data']['summary'])) $pay['summary'] .= $postData['data']['summary'];
            $pay['user'] = $this->user;
            $pay['mytable'] = 'sale';
            $bankTable = $this->mdb . '.bank';
            $rs = Db::table($bankTable)->find($pay['bank_id']);
            $pay['balance'] = $rs['balance'] + $pay['sum'];
            $bankDetailTable = $this->mdb . '.bdetail';
            $rs = Db::table($bankDetailTable)
                ->insert($pay);
            if (!$rs) return json_encode(['result' => 2]);
            //在sale表中，插入付款信息。
            $salePay['member_id'] = $postData['data']['member']['member_id'];
            $salePay['sum'] = $pay['sum'] *(-1);
            $salePay['time'] = $trueTime;
            $salePay['type'] = 'P';
            $salePay['user'] = $this->user;
            if (isset($postData['data']['summary'])) $salePay['summary'] = $postData['data']['summary'];
            $salePayId = Db::table($saleTable)
                ->insertGetId($salePay);
            if (!$salePayId) return json_encode(['result' => 2]);
            //该member_id 的sum(sum)是否为0，为0则删除。
            $sum = Db::table($saleTable)
                ->where(['member_id' => $postData['data']['member']['member_id']])
                ->sum('sum');
            if ($sum == 0) {
                $query = 'update ' . $saleTable . ' set cf=' . $salePayId . ' where member_id=' . $postData['data']['member']['member_id'] . ' and sale_id <>' . $salePayId." and type !='order'";
                Db::execute($query);
                $query = 'delete from ' . $saleTable . ' where member_id=' . $postData['data']['member']['member_id']." and type !='order'";
                Db::execute($query);
            }
        }
        if(isset($sale_id)&& isset($salePayId)&&
            ($postData['data']['ttl_sum']==$postData['pay']['paid_sum'])
        ){
            $query = 'update ' . $saleTable . ' set cf=' . $salePayId . ' where sale_id=' . $sale_id;
            Db::execute($query);
            $rs=Db::table($saleTable)->delete($sale_id);
            $rs=Db::table($saleTable)->delete($salePayId);
            if(!$rs) json_encode(['result'=>2]);
        }
        return json_encode(['result'=>1]);
    }
    public function getPrice(){
        $salePriceTable=$this->mdb.'.sale_price';
        $rs=Db::table($salePriceTable)
            ->where($_GET)
            ->find();
        if(!$rs){
            $goodsTable=$this->mdb.'.goods';
            $rs=Db::table($goodsTable)->field('out_price as price,promote')->find($_GET['goods_id']);
            if($rs['promote']==1){
                $memberTable=$this->mdb.'.member';
                $vip=Db::table($memberTable)->field('vip')->find($_GET['member_id']);
                $rs['price']=$rs['price']*$vip['vip'];
            }

        }
        $data['result']=$rs['price'];
        $stockTable=$this->mdb.'.stock';
        $rs=Db::table($stockTable)
            ->where(['goods_id'=>$_GET['goods_id']])
            ->find();
        $data['stock']=$rs['number'];
        return json_encode($data);
    }
    public function getMemberCredit(){
        $saleTable=$this->mdb.'.sale';
        $memberTable=$this->mdb.'.member';
            $credit=Db::table($saleTable)
                ->where(['member_id'=>$_GET['member_id']])
                ->sum('sum');
            $member=Db::table($memberTable)->find($_GET['member_id']);
            $payment=0;
        if(isset($_GET['sale_id'])) {
            $saleTable=$this->mdb.'.sale_finish';
            $sale = Db::table($saleTable)->find($_GET['sale_id']);
            $where['time']=$sale['time'];
            $where['type']="P";
            $where['user']=$sale['user'];
            $payment=Db::table($saleTable)->where($where)->sum('sum');
        }
            return json_encode(['credit'=>$credit,'tel'=>$member['member_phone'],'payment'=>$payment]);
    }
    public function getMemberPhone(){
        $memberTable=$this->mdb.'.member';
        $member=Db::table($memberTable)->find($_GET['member_id']);
        return json_encode(['tel'=>$member['member_phone']]);
    }
    public function saleList(){
        $memberTable=$this->mdb.'.member b';
        if(isset($_GET['type'])){
            $where['type'] ='order';
            $saleTable = $this->mdb . '.sale';
        }else {
            $where['type'] = ['in', 'S,B'];
            $saleTable = $this->mdb . '.sale_all';
        }
       /*
        if(isset($_GET['user'])){
            $user=Db::table("hs_0.user")->where("name","=",$this->user)->find();
            if($user['role_id']!=0){
                $where['user']=$this->user;
            }
        }*/
        if($_GET['search']){
            $where['summary|member_sn|member_name']=['like','%'.$_GET['search'].'%'];
        }
        if($_GET['edate']&&$_GET['sdate'])
            $where['a.time']=['between',array($_GET['sdate'],$_GET['edate'])];
        $count=Db::table($saleTable)->alias('a')
            ->join($memberTable,'a.member_id=b.member_id')
            ->where($where)
            ->count();
        if($count==0){
            $saleDetailTable=$this->mdb.'.sale_detail d';
            $goodsTable=$this->mdb.'.goods c';
            unset($where['summary|member_sn|member_name']);
            $var=explode("|",$_GET['search']);
            if(sizeof($var)>1)
                $where['b.member_sn|b.member_name'] = ['like', '%' . $var[1] . '%'];
            else{
                $var=explode("+",$_GET['search']);
                if(sizeof($var)>1)
                   $where['b.member_sn|b.member_name'] = ['like', '%' . $var[1] . '%'];
            }
            $where['goods_sn|goods_name']=['like','%'.$var[0].'%'];
            $rs=Db::table($saleTable)->alias('a')
                ->join($saleDetailTable,'a.sale_id=d.sale_id')
                ->join($goodsTable,'d.goods_id=c.goods_id')
                ->join($memberTable,'a.member_id=b.member_id')
                ->where($where)
                ->field('distinct a.*,b.member_name')
                ->order('a.time desc')
                ->page($_GET['page'],10)
                ->select();
            $count=Db::table($saleTable)->alias('a')
                ->join($saleDetailTable,'a.sale_id=d.sale_id')
                ->join($goodsTable,'d.goods_id=c.goods_id')
                ->join($memberTable,'a.member_id=b.member_id')
                ->where($where)
                ->count('distinct a.sale_id');
        }else{
            $rs=Db::table($saleTable)->alias('a')
                ->join($memberTable,'a.member_id=b.member_id')
                ->where($where)
                ->field('a.*,b.member_name')
                ->order('time desc')
                ->page($_GET['page'],10)
                ->select();
            $count=Db::table($saleTable)->alias('a')
                ->join($memberTable,'a.member_id=b.member_id')
                ->where($where)
                ->count('distinct a.sale_id');
        }
        $data['lists']=$rs;
        $data['total_count']=$count;
        return json_encode($data);
    }
    public function deleteOrder(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $saleDetailTable=$this->mdb.'.sale_detail';
        $saleTable=$this->mdb.'.sale';
        $query='delete from '.$saleDetailTable.' where sale_id ='.$postData['sale_id'];
        Db::execute($query);
        $rs=Db::table($saleTable)
            ->delete($postData['sale_id']);
        if($rs) {
            $saleTable = $this->mdb . '.sale_finish';
            $rs=Db::table($saleTable)
                ->delete($postData['sale_id']);
            mylog($this->user,$this->mdb,'删除'.$postData['info']);
            if($rs) return $this->orderList();
        }
        return json_encode(['result'=>0]);
    }
    public function deleteSale(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $saleDetailTable=$this->mdb.'.sale_detail';
        $saleTable=$this->mdb.'.sale';
        $origin=Db::table($saleDetailTable)->where(['sale_id'=>$postData['sale_id']])->select();
        $rs=Db::table($saleTable)
            ->delete($postData['sale_id']);
        if($rs) {
            foreach ($origin as $value){
                if(!$this->detailDelete($value['sale_detail_id'],$postData['info'])){
                    return json_encode(['result'=>0]);
                };
            }
            $saleTable = $this->mdb . '.sale_finish';
            $rs=Db::table($saleTable)
                ->delete($postData['sale_id']);
            mylog($this->user,$this->mdb,'删除销售单,'.$postData['info']);
            if($rs) return json_encode(['result'=>1]);
        }
        return json_encode(['result'=>0]);
    }
    private function detailDelete($detail_id,$info){
        $saleDetailTable=$this->mdb.'.sale_detail';
        $fxTable=$this->mdb.'.fx fx';
        $origin=Db::table($saleDetailTable)->alias('a')
            ->join($fxTable,'fx.goods_id=a.goods_id and fx.unit_id=a.unit_id')
            ->field('a.goods_id,store_id,inorder,(number/fx) as number')
            ->find($detail_id);
        $stockTable=$this->mdb.'.stock';
        $data['goods_id']=$origin['goods_id'];
        $data['store_id']=$origin['store_id'];
        $data['inorder']=$origin['inorder'];
        $stock=Db::table($stockTable)->where($data)->find();
        $rs=Db::table($saleDetailTable)->delete($detail_id);
        //account
        $awhere['sale_detail_id']=$detail_id;
        $awhere['subject']=0;
        $accountTable=$this->mdb.'.account';
        $account=Db::table($accountTable)->where($awhere)->find();
        if($stock){
            //stock_detail
            $data['user']=$this->user;
            $data['time']=date('Y-m-d H:i:s');
            $data['Dstock']=$origin['number'];
            //$data['Dsum']=$stock['sum']/$stock['number']*$origin['number'];
            $data['Dsum']=$account['cost'];
            $data['stock']=$stock['number']+$data['Dstock'];
            $data['sum']=$stock['sum']+$data['Dsum'];
            $data['type']='saleDelete';
            $data['remark']='销售单删除'.$info;
            $stockDetailTable=$this->mdb.'.stock_detail';
            $rs=Db::table($stockDetailTable)->insert($data);
            if(!$rs) return false;
        }else{
            //stock_detail
            $data['user']=$this->user;
            $data['time']=date('Y-m-d H:i:s');
            $data['Dstock']=$origin['number'];
            $data['Dsum']=$account['cost'];
            $data['stock']=$data['Dstock'];
            $data['sum']=$data['Dsum'];
            $data['type']='saleDelete';
            $data['remark']='销售单删除'.$info;
            $stockDetailTable=$this->mdb.'.stock_detail';
            $rs=Db::table($stockDetailTable)->insert($data);
            if(!$rs) return false;
        }
        $rs=Db::table($accountTable)->delete($account['account_id']);
        if(!$rs) return false;
        return true;
    }
    public function backFlush(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $saleFinishTable=$this->mdb.'.sale_finish';
        $payRs=array(0);
        $data[0]=Db::table($saleFinishTable)->find($postData['sale_id']);
        $data[0]['summary']=$data[0]['summary'].$this->user.'反冲于'.date("Y-m-d H:i:s");
        $saleAllTable=$this->mdb.'.sale_all';
        $saleTable=$this->mdb.'.sale';
        if($data[0]['cf']>0)
            $payRs=Db::table($saleFinishTable)->find($data[0]['cf']);
       // return json_encode($payRs);
        if(($data[0]['cf']==null)||($payRs['type']=='P')){
            $data[1]['sum']=-1*$data[0]['sum'];
            $data[1]['type']='backFlush';
            $data[1]['time']=date("Y-m-d H:i:s");
            $data[1]['user']=$this->user;
            $data[1]['summary']=$this->user.'反冲于'.date("Y-m-d H:i:s");
            $data[1]['member_id']=$data[0]['member_id'];
            $rs=Db::table($saleTable)->insert($data[0]);
            $rs=Db::table($saleTable)->insert($data[1]);
            if(!$rs) return json_encode(['result'=>0]);
            $rs=Db::table($saleFinishTable)->delete($postData['sale_id']);
            if(!$rs) return json_encode(['result'=>0]);
            $data[1]['sum']=$data[0]['sum'];
            $insertID=Db::table($saleTable)->insertGetId($data[1]);
            $rs=Db::table($saleTable)->delete($insertID);
            if(!$rs) return json_encode(['result'=>0]);
            return json_encode(['result'=>1]);
        }else{
            $where['cf']=$data[0]['cf'];
            $data=Db::table($saleFinishTable)->where($where)->select();
            foreach ($data as $value){
                $value['summary']=$this->user.'反冲于'.date("Y-m-d H:i:s");
            }
            $rs=Db::table($saleTable)->insertAll($data);
            $query="delete from ".$this->mdb.'.sale_finish'." where cf=".$data[0]['cf'];
            Db::execute($query);
            $rs=Db::table($saleTable)->delete($data[0]['cf']);
            $rs=Db::table($saleFinishTable)->delete($data[0]['cf']+1);
            if(!$rs) return json_encode(['result'=>0]);
            return json_encode(['result'=>1]);
        }
        //return json_encode(['result'=>0]);
    }
    public function saleDetail(){
        $saleTable=$this->mdb.'.sale';
        $rs['finish']=Db::table($saleTable)->where($_GET)->count();
        $saleDetailTable=$this->mdb.'.sale_detail';
        $goodsTable=$this->mdb.'.goods';
        $unitTable=$this->mdb.'.unit c';
        $storeTable=$this->mdb.'.store d';
        $rs['data']=Db::table($saleDetailTable)->alias('a')
            ->join($goodsTable.' b','a.goods_id=b.goods_id')
            ->join($unitTable,'a.unit_id=c.unit_id')
            ->join($storeTable,'a.store_id=d.store_id')
            ->field('a.goods_id,goods_name,number,price,sum,unit_name,sale_detail_id,sale_id,inorder,store_name,a.unit_id,b.unit_id as unit_id_0,number1,number2,remark')
            ->where($_GET)
            ->select();
        return json_encode($rs);
    }
    public function saleDetailUpdate(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $checkTable=$this->mdb.".sale";
        $rs=Db::table($checkTable)->find($postData['sale_id']);
        $order=$rs['type']=='order';
        if(!$rs) return json_encode(['result'=>2]);
        $saleDetailTable=$this->mdb.'.sale_detail';
        $sdata=$postData;unset($sdata['unit_check']);
        $origin=Db::table($saleDetailTable)->find($postData['sale_detail_id']);
        $rs=Db::table($saleDetailTable)
            ->update($sdata);
        if($order) return json_encode(['result'=>1]);
        if($rs){
            $sum=Db::table($saleDetailTable)
                ->where('sale_id',$postData['sale_id'])
                ->sum('sum');
            $saleTable=$this->mdb.'.sale';
            $oldSummary=Db::table($saleTable)->field('summary,member_id')->find($postData['sale_id']);
            $oldSummary['summary'].=$oldSummary['summary']?';':'';
            $rs=Db::table($saleTable)
                ->where(['sale_id'=>$postData['sale_id']])
                ->update(['sum'=>$sum,'summary'=>$oldSummary['summary'].$this->user.'修改'.date("Y-m-d H:i:s")]);
            if(!$rs) return json_encode(['result'=>0]);
            $goodsTable=$this->mdb.'.goods';
            $goods=Db::table($goodsTable)->field('goods_name')->find($origin['goods_id']);
            $memberTable=$this->mdb.'.member';
            $member=Db::table($memberTable)->find($oldSummary['member_id']);
            mylog($this->user,$this->mdb,'修改销售单明细,'.$member['member_name'].',原数据'.$goods['goods_name'].":".$origin['number'].'*￥'.$origin['price'].'='.$origin['sum']);
            $time=date('Y-m-d H:i:s');
            //account
            $accountTable=$this->mdb.'.account';
            $aWhere['subject']=0;
            $aWhere['sale_detail_id']=$postData['sale_detail_id'];
            $aOrigin=Db::table($accountTable)->where($aWhere)->find();

            //stock_detail
            if($origin['number']!=$postData['number']||!$postData['unit_check']) {
                $detail['goods_id'] = $origin['goods_id'];
                $detail['store_id'] = $origin['store_id'];
                $detail['inorder'] = $origin['inorder'];
                $stockTable = $this->mdb . '.stock';
                $stock = Db::table($stockTable)->where($detail)->find();
                $stockDetailTable = $this->mdb . '.stock_detail';
                if ($stock&&$stock['number']!=0) {
                    if ($postData['unit_check']) {
                        $detail['Dstock'] = (-1) * ($postData['number'] - $origin['number']);
                    } else {
                        $unit_priceTable = $this->mdb . '.unit_price';
                        $uWhere['goods_id'] = $origin['goods_id'];
                        $uWhere['unit_id'] = $postData['unit_id'];
                        $uWhere['fx'] = ['>', 0];
                        $unit_price1 = Db::table($unit_priceTable)->where($uWhere)->find();
                        if (!$unit_price1) $unit_price1['fx']=1;
                        $uWhere['unit_id'] = $origin['unit_id'];
                        $unit_price2 = Db::table($unit_priceTable)->where($uWhere)->find();
                        if (!$unit_price2) $unit_price2['fx']=1;
                        $detail['Dstock'] = (-1) * ($postData['number']/ $unit_price1['fx'] - $origin['number']/ $unit_price2['fx']) ;
                    }
                    $detail['Dsum'] = $stock['sum'] / $stock['number'] * ($detail['Dstock']);
                    $detail['stock'] = $stock['number'] + $detail['Dstock'];
                    $detail['sum'] = $stock['sum'] + $detail['Dsum'];
                    $detail['time'] = $time;
                    $detail['user'] = $this->user;
                    $detail['remark'] = "修改销售单：" . $origin['number'] . '*' . $origin['price'] . '=' . $origin['sum'] . '=>' . $postData['number'] . '*' . $postData['price'] . '=' . $postData['sum'];
                    $rs = Db::table($stockDetailTable)->insert($detail);
                    if (!$rs) json_encode(['result' => 0]);
                }else{
                    if ($postData['unit_check']) {
                        $detail['Dstock'] = (-1) * ($postData['number'] - $origin['number']);
                    } else {
                        $unit_priceTable = $this->mdb . '.unit_price';
                        $uWhere['goods_id'] = $postData['goods_id'];
                        $uWhere['unit_id'] = $postData['unit_id'];
                        $uWhere['fx'] = ['>', 0];
                        $unit_price = Db::table($unit_priceTable)->where($uWhere)->find();
                        $detail['Dstock'] = (-1) * ($postData['number'] - $origin['number']) / $unit_price['fx'];
                    }
                    $swhere['goods_id'] = $origin['goods_id'];
                    $swhere['store_id'] = $origin['store_id'];
                    $swhere['inorder'] = $origin['inorder'];
                    $stockDetail=Db::table($stockDetailTable)->where($swhere)->order('stock_detail desc')->find();
                    $stockDetailPrice=$stockDetail['Dsum']/$stockDetail['Dstock'];
                    $detail['Dsum'] = $stockDetailPrice * ($detail['Dstock']);
                    $detail['stock'] = $stock['number'] + $detail['Dstock'];
                    $detail['sum'] = $stock['sum'] + $detail['Dsum'];
                    $detail['time'] = $time;
                    $detail['user'] = $this->user;
                    $detail['remark'] = "修改销售单：" . $origin['number'] . '*' . $origin['price'] . '=' . $origin['sum'] . '=>' . $postData['number'] . '*' . $postData['price'] . '=' . $postData['sum'];
                    $stockDetailTable = $this->mdb . '.stock_detail';
                    $rs = Db::table($stockDetailTable)->insert($detail);
                    if (!$rs) json_encode(['result' => 0]);
                }
                $aOrigin['cost']=$aOrigin['cost']+$detail['Dsum']*(-1);
            }

            //$aOrigin['cost']=$postData['number']/$origin['number']*$aOrigin['cost'];
            $aOrigin['income']=$postData['sum'];
            //print_r($aOrigin);
            $rs=Db::table($accountTable)->update($aOrigin);
           // return json_encode($rs);
            if($rs) return json_encode(['result'=>1]);
        }
        return json_encode(['result'=>0]);
    }
    public function saleDetailDelete(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $saleDetailTable=$this->mdb.'.sale_detail';
        $saleTable=$this->mdb.".sale";
        $rs=Db::table($saleTable)->find($postData['sale_id']);
        if(!$rs){
            return json_encode(['result'=>2]);
        }
        if($rs['type']=="order"){
            $rs=Db::table($saleDetailTable)->delete($postData['sale_detail_id']);
            return json_encode(['result'=>1]);
        }

        if($this->detailDelete($postData['sale_detail_id'],$postData['info']))
        {
            $count=Db::table($saleDetailTable)
                ->where('sale_id',$postData['sale_id'])->select();
            if($count){
                $sum=Db::table($saleDetailTable)
                ->where('sale_id',$postData['sale_id'])
                ->sum('sum');
                $saleTable=$this->mdb.'.sale';
                $oldSummary=Db::table($saleTable)->field('summary')->find($postData['sale_id']);
                $oldSummary['summary'].=$oldSummary['summary']?';':'';
                $rs=Db::table($saleTable)
                ->where(['sale_id'=>$postData['sale_id']])
                ->update(['sum'=>$sum,'summary'=>$oldSummary['summary'].$this->user.'修改'.date("Y-m-d H:i:s")]);
            }else{
                $rs=Db::table($saleTable)->delete($postData['sale_id']);
                $rs=Db::table($saleTable."_finish")->delete($postData['sale_id']);
            }
            mylog($this->user,$this->mdb,"销售单删除明细项目:".$postData['info']);

            if($rs) return json_encode(['result'=>1]);
        }
        return json_encode(['result'=>0]);
    }

    public function credit(){
        $saleTable=$this->mdb.'.sale';
        $memberTable=$this->mdb.'.member b';
        $where['member_name|member_sn']=['like','%'.$_GET['search'].'%'];
        $where['type']=["neq","order"];
        $rs=Db::table($saleTable)->alias('a')
            ->join($memberTable,'a.member_id=b.member_id')
            ->field('a.member_id as member_id,sum(sum) as sum,member_name,b.member_phone')
            ->where($where)
            ->page($_GET['page'],10)
            ->group('a.member_id')
            ->order($_GET['order'])
            ->select();
        $data['credit']=$rs;
        $data['total_count']=Db::table($saleTable)
            ->alias('a')
            ->join($memberTable,'a.member_id=b.member_id')
            ->where($where)
            ->count('DISTINCT a.member_id');
        return json_encode($data);
    }
    public function ttlCredit(){
        $saleTable=$this->mdb.'.sale';
        $rs=Db::table($saleTable)->sum('sum');
        return $rs;
    }
    public function creditDetail(){
        $saleTable=$this->mdb.'.sale';
        $saleDetailTable=$this->mdb.'.sale_detail b';
        $goodsTable=$this->mdb.'.goods c';
        $unitTable=$this->mdb.'.unit d';
        $where['member_id']=$_GET['member_id'];
        if(isset($_GET['sale_id'])){
            $saleIds=explode(',',$_GET['sale_id']);
            $where['a.sale_id']=['in',$saleIds];
        }
        $rs=Db::table($saleTable)->alias('a')
            ->join($saleDetailTable,'a.sale_id=b.sale_id','LEFT')
            ->join($goodsTable,'b.goods_id=c.goods_id','LEFT')
            ->join($unitTable,'d.unit_id=b.unit_id','LEFT')
            ->field('goods_name,price,number,b.sum,type,a.time,a.sale_id,a.sum as ttl,summary,unit_name,remark')
            ->order('a.time,a.sale_id')
            ->where($where)
            ->where('type','neq','order')
            ->select();
        $data['credit']=$rs;
        $bankTable=$this->mdb.'.bank';
        $rs=Db::table($bankTable)->field("bank_id,bank_name,weight")->order('weight desc')->select();
        $data['bank']=$rs;
        $data['credit_sum']=Db::table($saleTable)->where("member_id","=",$_GET['member_id'])->sum("sum");
        $memberTable=$this->mdb.".member";
        $rs=Db::table($memberTable)->field('member_phone')->find($_GET['member_id']);
        $data['tel']=$rs['member_phone'];
        return json_encode($data);
    }
    public function payment(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $saleTable=$this->mdb.'.sale';
        $time=$insertTime=date("Y-m-d H:i:s");
        //插入sale表
        $pData['time']=$time;
        $pData['member_id']=$postData['member']['member_id'];
        $pData['type']='P';
        $pData['sum']=(-1)*$postData['pay']['paid_sum'];
        $pData['user']=$this->user;
        $saleID=Db::table($saleTable)
            ->insertGetId($pData);
        if(!$saleID) return json_encode(['result'=>0]);
        //插入bdetail表
        $bankDetailTable=$this->mdb.'.bdetail';
        $bData['time']=$time;
        $bData['sum']=$postData['pay']['paid_sum'];
        $bData['bank_id']=$postData['pay']['bank'];
        $bData['summary']='客户付款：'.$postData['member']['member_name'];
        $bData['user']=$this->user;
        $bData['mytable']='sale';
        $bankTable = $this->mdb . '.bank';
        $rs = Db::table($bankTable)->find($bData['bank_id']);
        $bankName=$rs['bank_name'];
        $bData['balance']=$rs['balance']+$bData['sum'];
        $rs=Db::table($bankDetailTable)
            ->insert($bData);
        $infoTable=$this->mdb.'.info';
        $rs=Db::table($infoTable)->find(1);
        $content=$postData['member']['member_name'].':'.$bankName.'--￥'.$postData['pay']['paid_sum'];
        \PushNotice::Push($this->user."收客户欠款",$content,$rs['ctel']);
        goEasy($rs['ctel'],$this->user."收客户欠款:".$content);

        if(!$rs) return json_encode(['result'=>2]);
        //判断sale表中插入数据是否删除
        $sum=Db::table($saleTable)
            ->where(['member_id'=>$postData['member']['member_id']])
            ->sum('sum');
        if($sum==0){
            $query = 'update ' . $saleTable . ' set cf=' . $saleID . ' where member_id=' . $postData['member']['member_id'] . ' and sale_id <>' . $saleID." and type !='order'";
            Db::execute($query);
            $query='delete from '.$saleTable. ' where member_id = '.$postData['member']['member_id']." and type !='order'";
            Db::execute($query);
            return json_encode(['result'=>1]);
        }
        if(sizeof($postData['saleID'])>0){
            $id=$postData['saleID'];
            array_push($id,$saleID);
            $sum=Db::table($saleTable)
                ->where('sale_id','in',$id)
                ->sum('sum');
            if($sum==0){
                Db::table($saleTable)->where('sale_id','in',$postData['saleID'])->update(['cf' => $saleID]);
                $rs=Db::table($saleTable)
                    ->where('sale_id','in',$id)
                    ->delete();
                if($rs) return json_encode(['result'=>1]);
            }
        }
       return json_encode(['result'=>1]);
    }
    public function carryOver(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        $saleTable=$this->mdb.'.sale';
        $where['member_id']=$postData['member_id'];
        $where['sale_id']=['in',$postData['saleID']];
        $time=date("Y-m-d H:i:s");
        $data['time']=$time;
        $data['member_id']=$postData['member_id'];
        $data['sum']=Db::table($saleTable)->where($where)->sum('sum');
        $data['user']=$this->user;
        $data['summary']=$postData['summary'];
        $data['type']='CF';
        $insertID=Db::table($saleTable)
            ->insertGetId($data);
        if(!$insertID) return json_encode(['result'=>2]);
        $rs=Db::table($saleTable)->where('sale_id','in',$postData['saleID'])->update(['cf'=>$insertID]);
        if(!$rs) return json_encode(['result'=>2]);
        $data['sum']=(-1)*$data['sum'];
        $data['type']='BCF';
        $insertBCF=Db::table($saleTable)
            ->insertGetId($data);
        if(!$insertBCF) return json_encode(['result'=>2]);
        array_push($postData['saleID'],$insertBCF);
        $rs=Db::table($saleTable)->where('sale_id','in',$postData['saleID'])->delete();
        if(!$rs) return json_encode(['result'=>2]);
        return json_encode(['result'=>1]);
    }
    public function cfPrint(){
        $saleTable=$this->mdb.'.sale_finish';
        $saleDetailTable=$this->mdb.'.sale_detail b';
        $goodsTable=$this->mdb.'.goods c';
        $unitTable=$this->mdb.'.unit d';
        $where=$_GET;
        $where['type']=['neq','BCF'];
        $rs=Db::table($saleTable)->alias('a')
            ->join($saleDetailTable,'a.sale_id=b.sale_id','LEFT')
            ->join($goodsTable,'b.goods_id=c.goods_id','LEFT')
            ->join($unitTable,'b.unit_id=d.unit_id','LEFT')
            ->field('goods_name,price,number,b.sum,type,a.time,a.sale_id,a.sum as ttl,summary,unit_name')
            ->order('a.time')
            ->where($where)
            ->select();
        $data['credit']=$rs;
        return json_encode($data);
    }
    public function printSale()
    {
        // $saleTable=$this->mdb.'.sale';
        // print_r($_GET);
        $saleTable = $this->mdb . '.sale';
        $swhere['member_id'] = $_GET['member_id'];
        $data['credit'] = Db::table($saleTable)->where($swhere)->sum('sum');
        if (!$data['credit']) $data['credit'] = 0;
        $where['sale_id'] = $_GET['sale_id'];
        $saleDetailTable = $this->mdb . '.sale_detail b';
        $goodsTable = $this->mdb . '.goods c';
        $unitTable = $this->mdb . '.unit d';
        $rs = Db::table($saleDetailTable)
            ->join($goodsTable, 'b.goods_id=c.goods_id', 'LEFT')
            ->join($unitTable, 'd.unit_id=b.unit_id')
            ->field('goods_name,price,number,unit_name as unit,sum,remark')
            ->where($where)
            ->select();
        $data['credits'] = $rs;
        $rs = Db::table($saleDetailTable)
            ->field('sum(number) as number,sum(sum) as sum')
            ->where($where)
            ->find();
        $infoTable = $this->mdb . '.info';
        $data['ttl'] = $rs;
        $rs = Db::table($infoTable)->find(1);
        $data['info'] = $rs;
        if ($rs['print_remark'] > 0) {
            $saleTable = $this->mdb . '.sale_all';
            $where['sale_id'] = $_GET['sale_id'];
            $rs = Db::table($saleTable)->where($where)->find();
            $data['summary'] = $rs['summary'];
        }
        return json_encode($data);
    }
    public function printInfo(){
        $infoTable=$this->mdb.'.info';
        $rs=Db::table($infoTable)->find(1);
        $data['info']=$rs;
        return json_encode($data);
    }
     public function printCredit(){
        $saleTable=$this->mdb.'.sale';
        $saleDetailTable=$this->mdb.'.sale_detail b';
        $goodsTable=$this->mdb.'.goods c';
        $unitTable=$this->mdb.'.unit d';
        if($_GET['sale_id']!=''){
            $swhere['member_id']=$where['member_id']=$_GET['member_id'];
            $where['a.sale_id']=['in',$_GET['sale_id']];
            $where['type']=['neq','order'];
            $rs=Db::table($saleTable)->alias('a')
                ->join($saleDetailTable,'a.sale_id=b.sale_id','LEFT')
                ->join($goodsTable,'b.goods_id=c.goods_id','LEFT')
                ->join($unitTable,'d.unit_id=b.unit_id','LEFT')
                ->field('goods_name,price,number,b.sum,a.time,a.sum as ttl,a.sale_id,unit_name as unit,type,summary,remark')
                //->fetchSql(false)
                ->order('a.time')
                ->where($where)
                ->select();
            if(!$rs){
                $rs=Db::table($saleTable."_finish")->alias('a')
                    ->join($saleDetailTable,'a.sale_id=b.sale_id','LEFT')
                    ->join($goodsTable,'b.goods_id=c.goods_id','LEFT')
                    ->join($unitTable,'d.unit_id=b.unit_id','LEFT')
                    ->field('goods_name,price,number,b.sum,a.time,a.sum as ttl,a.sale_id,unit_name as unit,type,summary,remark')
                    //->fetchSql(false)
                    ->order('a.time')
                    ->where($where)
                    ->select();
                $data['credit'] = $rs;

                $swhere['sale_id'] = ['in', $_GET['sale_id']];
               // $swhere['type'] = ['in', ["S", "B"]];
                $rs['sum'] = Db::table($saleTable."_finish")
                    ->where($swhere)
                    ->sum('sum');
                $rs['credit'] = Db::table($saleTable)
                    ->where('member_id', '=', $_GET['member_id'])
                    ->sum('sum');

                $swhere['type'] = "P";
                $rs['payment'] = Db::table($saleTable."_finish")
                    ->where($swhere)
                    ->sum('sum');
                $rs['sum']-=$rs['payment'];

                $rs['number'] = Db::table($saleTable."_finish")->alias('a')
                    ->join($saleDetailTable, 'a.sale_id=b.sale_id')
                    ->where($where)
                    ->sum('number');
                $data['ttl'] = $rs;
            }else {
                $data['credit'] = $rs;

                $swhere['sale_id'] = ['in', $_GET['sale_id']];
                //$swhere['type'] = ['in', ["S", "B"]];
                $rs['sum'] = Db::table($saleTable)
                    ->where($swhere)
                    ->sum('sum');
                $rs['credit'] = Db::table($saleTable)
                    ->where('member_id', '=', $_GET['member_id'])
                    ->sum('sum');

                $swhere['type'] = "P";
                $rs['payment'] = Db::table($saleTable)
                    ->where($swhere)
                    ->sum('sum');
                $rs['sum']-=$rs['payment'];

                $rs['number'] = Db::table($saleTable)->alias('a')
                    ->join($saleDetailTable, 'a.sale_id=b.sale_id')
                    ->where($where)
                    ->sum('number');
                $data['ttl'] = $rs;
            }
        }else
        {
            $where['member_id']=$_GET['member_id'];
            $where['type']=['neq','order'];
        $rs=Db::table($saleTable)->alias('a')
            ->join($saleDetailTable,'a.sale_id=b.sale_id','LEFT')
            ->join($goodsTable,'b.goods_id=c.goods_id','LEFT')
            ->join($unitTable,'d.unit_id=b.unit_id','LEFT')
            ->field('goods_name,price,number,b.sum,a.time,a.sale_id,a.sum as ttl,unit_name as unit,type,summary,remark')
            ->order('a.time')
            ->where($where)
            ->select();
        $data['credit']=$rs;
        $rs['credit']=Db::table($saleTable)
            ->where($where)
            ->sum('sum');
            $rs['number']=Db::table($saleTable)->alias('a')
                ->join($saleDetailTable,'a.sale_id=b.sale_id')
                ->where($where)
                ->sum('number');
            //$where['type']=['in',["S","B"]];
            $rs['sum']=Db::table($saleTable)
                ->where($where)
                ->sum('sum');
            $where['type']="P";
            $rs['payment']=Db::table($saleTable)
                ->where($where)
                ->sum('sum');
            $rs['sum']-=$rs['payment'];

        $data['ttl']=$rs;
        }
         $infoTable=$this->mdb.'.info';
        $rs=Db::table($infoTable)->find(1);
        $data['info']=$rs;
        return json_encode($data);
    }
    public function getUnitApp(){
        $unit_priceTable=$this->mdb.'.unit_price';
        $where['goods_id']=$_GET['goods_id'];
        $where['fx']=['>',0];
        $rs=Db::table($unit_priceTable)
            ->where($where)
            ->field('distinct unit_id')
            ->select();
        if(sizeof($rs)==0) return "";
        $result="";
        $result.=$rs[0]['unit_id'];
        for ($i = 1; $i < sizeof($rs); $i++) {
            $result = $result . "and" . $rs[$i]['unit_id'];
        }
        return json_encode($result);
    }
    public function getPriceApp(){
        $unit_priceTable=$this->mdb.'.unit_price';
        $rs=Db::table($unit_priceTable)->where($_GET)->find();
        return $rs['price'];
    }
    /*public function getUnit(){
        $unit_priceTable=$this->mdb.'.unit_price';
        $unitTable=$this->mdb.'.unit b';
        $where['goods_id']=$_GET['goods_id'];
        $where['fx']=['>',0];
        $rs=Db::table($unit_priceTable)->alias('a')
            ->join($unitTable,' a.unit_id=b.unit_id')
            ->where($where)
            ->field('b.unit_id,unit_name')
            ->select();
        $temp=Db::table($unitTable)->find($_GET['unit_id']);
        $rs[sizeof($rs)]=$temp;
        $result['unit']=$rs;
        if($_GET['member_id']!=0) {
            $uwhere['member_id']=$_GET['member_id'];
            $uwhere['goods_id']=$_GET['goods_id'];
            $salePriceTable = $this->mdb . '.sale_price';
            $rs = Db::table($salePriceTable)
                ->where($uwhere)
                ->find();
            if($rs){
                $result['result']=$rs['price'];
                $result['unit_price']=$rs['unit_id'];
                $res=Db::table($this->mdb . '.goods')->find($_GET['goods_id']);
                if($res['time']>$rs['time']){
                    if($rs['unit_id']==$res['unit_id']) {
                        $result['result'] = $rs['price']>$res['out_price']?$rs['price']:$res['out_price'];
                    }
                    else{
                        $pwhere['goods_id']=$_GET['goods_id'];
                        $pwhere['unit_id']=$rs['unit_id'];
                        $unit_priceTable=$this->mdb . '.unit_price';
                        $unitResult=Db::table($unit_priceTable)->where($pwhere)->find();
                        $result['result'] = $rs['price']>$unitResult['price']?$rs['price']:$unitResult['price'];
                    }
                }
            }else{
                $goodsTable=$this->mdb.'.goods';
                $price=Db::table($goodsTable)->find($_GET['goods_id']);
                $result['result']=$price['out_price'];
            }
        }else{
            $goodsTable=$this->mdb.'.goods';
            $price=Db::table($goodsTable)->find($_GET['goods_id']);
            $result['result']=$price['out_price'];
        }
        return json_encode($result);
    }*/
    public function getPriceTab(){
        if($_GET['member_id']==0){
            $where['goods_id']=$_GET['goods_id'];
            $where['unit_id']=$_GET['unit_id'];
            $unit_priceTable=$this->mdb.".unit_price";
            $rs=Db::table($unit_priceTable)->where($where)->find();
            return json_encode(['result' => $rs['price']]);
        }
        $salePriceTable = $this->mdb . '.sale_price';
        $rs = Db::table($salePriceTable)
            ->where($_GET)
            ->find();
        if(!$rs){
            $goodsTable=$this->mdb.'.goods';
            $goods=Db::table($goodsTable)->find($_GET['goods_id']);
            if($goods['unit_id']==$_GET['unit_id']){
                $rs['price']=$goods['out_price'];
            }else{
                unset($_GET['member_id']);
                $where=$_GET;
                $where['fx']=['>',0];
                $unit_priceTable=$this->mdb.'.unit_price';
                $price=Db::table($unit_priceTable)->where($where)->find();
                $rs['price']=$price['price'];
            }
        }else{
            $goodsTable=$this->mdb.'.goods';
            $goods=Db::table($goodsTable)->find($_GET['goods_id']);
            if($goods['time']>$rs['time']){
                if($goods['unit_id']==$_GET['unit_id']){
                    $rs['price']=$rs['price']>$goods['out_price']?$rs['price']:$goods['out_price'];
                }else{
                    unset($_GET['member_id']);
                    $where=$_GET;
                    $where['fx']=['>',0];
                    $unit_priceTable=$this->mdb.'.unit_price';
                    $price=Db::table($unit_priceTable)->where($where)->find();
                    $rs['price']=$rs['price']>$price['price']?$rs['price']:$price['price'];
                }
            }
        }
        return json_encode(['result' => $rs['price']]);
    }
    public function order(){
        $postData=file_get_contents("php://input",true);
        $postData=json_decode($postData,true);
        //$back=$postData['back'];
        $insertTime=date("Y-m-d H:i:s");
        $summary=isset($postData['data']['summary'])?$postData['data']['summary']:"";

        //插入sale表
        $sum = 0;
        if(sizeof($postData['detail'])>0) {
            $saleTable=$this->mdb.'.sale';
            if(isset($postData['data']['member']['sale_id'])){
                $sale_id=$postData['data']['member']['sale_id'];
                $query='delete from '.$this->mdb.'.sale_detail where sale_id='.$sale_id;
                Db::execute($query);
                $rs=Db::table($saleTable)->find($sale_id);
                if($rs['time']!=$postData['time']||$rs['summary']!=$summary) {
                    $rs['time']=$postData['time'];
                    $rs['summary']=$summary;
                    $rs = Db::table($saleTable)->update($rs);
                }
            }else {
                $where['member_id'] = $postData['data']['member']['member_id'];
                $where['type'] = 'order';
                $where['time'] = isset($postData['time'])?substr($postData['time'],0,11).'00:00:00':$insertTime;
                $rs = Db::table($saleTable)->where($where)->find();
                if ($rs) {
                    $sale_id = $rs['sale_id'];
                } else {
                    $sale['member_id'] = $postData['data']['member']['member_id'];
                    $sale['sum'] = 0;
                    $sale['summary'] =$summary;
                    $sale['time'] = $postData['time'];
                    $sale['type'] = 'order';     //S--一般进货销售记录，P--付款记录，CF--结转,order--订货
                    $sale['user'] = $this->user;
                    $sale_id = Db::table($saleTable)
                        ->insertGetId($sale);
                    if (!$sale_id) return json_encode(['result' => 0]);
                }
            }
            $saleDetailTable = $this->mdb . '.sale_detail';
            //插入saleDetail 表
            $detail = [];
           // print_r($postData);
            foreach ($postData['detail'] as $value) {
                $detail['sale_id'] = $sale_id;
                $where['goods_id']=$detail['goods_id'] = $value['good']['goods_id'];
                $detail['number'] = $value['number'];
                $detail['price'] = $value['price'];
                $detail['unit_id'] = $value['unit_id'];
                $detail['store_id']=$value['store_id'];
                $detail['sum'] = $value['sum'] ;
                $detail['inorder']=0;
                if(isset($value['remark']))$detail['remark']=$value['remark'];
                $sale_detail_id = Db::table($saleDetailTable)
                    ->insertGetId($detail);
                $sum+=$value['sum'];
                if (!$sale_detail_id) return json_encode(['result' => 2]);
                unset($detail);
            }

        }
        //插入bankDetail表中
        if(isset($postData['pay']['paid_sum'])&&($postData['pay']['paid_sum']!=0)) {
            $pay['sum'] = $postData['pay']['paid_sum'];
            $pay['bank_id'] = $postData['pay']['bank'];
            $pay['time'] = $insertTime;
            $pay['summary'] = "客户付款订货：" . $postData['data']['member']['member_name'];
            $pay['user'] = $this->user;
            $pay['mytable'] = 'sale';
            $bankTable = $this->mdb . '.bank';
            $rs = Db::table($bankTable)->find($pay['bank_id']);
            $pay['balance'] = $rs['balance'] + $pay['sum'];
            $bankDetailTable = $this->mdb . '.bdetail';
            $rs = Db::table($bankDetailTable)
                ->insert($pay);
            if (!$rs) return json_encode(['result' => 2]);
            //在sale表中，插入付款信息。
            $salePay['member_id'] = $postData['data']['member']['member_id'];
            $salePay['sum'] = $pay['sum']*(-1) ;
            $salePay['time'] = $insertTime;
            $salePay['type'] = 'P';
            $salePay['user'] = $this->user;
            $salePayId = Db::table($saleTable)
                ->insertGetId($salePay);
            if (!$salePayId) return json_encode(['result' => 2]);
        }

        $infoTable=$this->mdb.'.info';
        $rs=Db::table($infoTable)->find(1);
        \PushNotice::Push("新订单",$postData['data']['member']['member_name'].'--￥'.$sum,$rs['ctel']);
        goEasy($rs['ctel'],"新订单:".$postData['data']['member']['member_name'].'--￥'.$sum);
        return json_encode(['result'=>1]);
    }

   public function orderList(){
        $saleTable=$this->mdb.'.sale a';
        $memberTable=$this->mdb.'.member b';
        $where['type']='order';
        $rs=Db::table($saleTable)
            ->join($memberTable,'a.member_id=b.member_id')
            ->where($where)
            ->field('a.time,a.sale_id,b.member_name,b.member_id')
            ->order('time')
            ->select();
        return json_encode($rs);
   }
    public function orderDetail()
    {
        $saleDetailTable = $this->mdb . '.sale_detail';
        $goodsTable = $this->mdb . '.goods';
      //  $unitTable = $this->mdb . '.unit c';
        $result = Db::table($saleDetailTable)->alias('a')
            ->join($goodsTable . ' b', 'a.goods_id=b.goods_id')
          //  ->join($unitTable, 'a.unit_id=c.unit_id')
            ->field('a.goods_id,b.goods_name,a.unit_id,number,price,sum,b.unit_id as unit_id_0,store_id,remark')
            ->where($_GET)
            ->select();
        $unit_priceTable=$this->mdb.'.unit_price';
        $unitTable=$this->mdb.'.unit b';
        $i=0;
        foreach ($result as $item){
            $where['goods_id']=$item['goods_id'];
            $where['fx']=['>',0];
            $rs=Db::table($unit_priceTable)->alias('a')
                ->join($unitTable,' a.unit_id=b.unit_id')
                ->where($where)
                ->field('b.unit_id,unit_name')
                ->select();
            $temp=Db::table($unitTable)->find($item['unit_id_0']);
            $rs[sizeof($rs)]=$temp;
            $result[$i]['unit']=$rs;
          /* if($item['price']==0) {
                $salePriceTable = $this->mdb . '.sale_price';
                $rs = Db::table($salePriceTable)
                    ->where($_GET)
                    ->find();
                if($rs){
                    $result['result']=$rs['price'];
                }else{
                    $goodsTable=$this->mdb.'.goods';
                    $price=Db::table($goodsTable)->find($_GET['goods_id']);
                    $result['result']=$price['out_price'];
                }
            }*/
            $i++;
        }
        $data['detail']=$result;
        $saleTable=$this->mdb.'.sale';
        $summary=Db::table($saleTable)->find($_GET['sale_id']);
        $data['summary']=$summary['summary'];
        return json_encode($data);
    }
    public function editSummary(){
       $saleTable=$_GET['finish']==0?$this->mdb.'.sale':$this->mdb.'.sale_finish';
       $data['sale_id']=$_GET['sale_id'];
       $data['summary']=$_GET['summary'];
       $rs=Db::table($saleTable)->update($data);
      // return $rs;
       if($rs) return json_encode(['result'=>1]);
        return json_encode(['result'=>0]);
    }

    public function orderTransToSale(){    //app
        $saleTable=$this->mdb.".sale";
        $saleDetailTable=$this->mdb.".sale_detail";
        $sale=Db::table($saleTable)->find($_GET['sale_id']);
        if(!$sale||$sale['type']!='order') return false;
        $insertTime=date("Y-m-d H:i:s");
        $details=Db::table($saleDetailTable)->where(['sale_id'=>$_GET['sale_id']])->select();
      //  return json_encode($details);
        $stockTable=$this->mdb.'.stock';
        $stock_detailTable=$this->mdb.'.stock_detail';
        $unit_priceTable=$this->mdb.'.unit_price';
        $goodsTable=$this->mdb.".goods";
        $ttlSum=0;
        foreach ($details as $value){
            $where['store_id']=$value['store_id'];
            $where['goods_id']=$value['goods_id'];
            $goods=Db::table($goodsTable)->find($value['goods_id']);
            $sale_detail_id = $value['sale_detail_id'];
            if($goods['is_order']==1) {
                $where['inorder'] = ['like', 'IN%'];
                $stock = Db::table($stockTable)->where($where)->order('stock_id')->select();
                if (!$stock) {
                    $where['inorder'] = 0;
                } else {
                    $j = 0;
                    while (sizeof($stock) > 1 && $j < sizeof($stock) && $stock[$j]['number'] < $value['number']) {
                        $j++;
                    }
                    if ($j == sizeof($stock)) {
                        $stock = Db::table($stockTable)->where($where)->order('number desc')->field('inorder')->find();
                        $where['inorder']  = $stock['inorder'];
                    } else
                        $where['inorder']  = $stock[$j]['inorder'];
                    if($where['inorder']!=$value['inorder']){
                        $value['inorder']=$where['inorder'];
                        $rs=Db::table($saleDetailTable)->where(['sale_detail_id'=>$sale_detail_id])->setField("inorder",$where['inorder']);
                        if (!$rs) return json_encode(['result' => 2]);
                    }
                }
            }else $where['inorder'] = 0;
            $stock=Db::table($stockTable)->where($where)->order('inorder')->find();


            //插入stock_detail
            $data['inorder']=$value['inorder'];
            $data['goods_id']=$value['goods_id'];
            $data['store_id']=$value['store_id'];
            if($goods['unit_id']!=$value['unit_id']){
                $uWhere['goods_id']=$value['goods_id'];
                $uWhere['unit_id']=$value['unit_id'];
                $uWhere['fx']=['>',0];
                $unit_price=Db::table($unit_priceTable)->where($uWhere)->find();
                if($unit_price)
                    $data['Dstock']=(-1)* $value['number']/$unit_price['fx'];
                else
                    $data['Dstock']=(-1)* $value['number'];
            }else $data['Dstock']=(-1)* $value['number'];
            if(!$stock){
                $data['Dsum']= (-1)*$value['sum'];
                $data['sum']=$data['Dsum'];
                $data['stock'] = $data['Dstock'] ;
            }else {
                if($stock['number']!=0 && $stock['sum'] / $stock['number']>0)   {
                    $data['Dsum'] =  $stock['sum'] / $stock['number'] * $data['Dstock'];
                }else
                    $data['Dsum']= (-1)*$value['sum'];
                $data['sum'] = $data['Dsum'] +$stock['sum'] ;
                $data['stock'] = $data['Dstock'] + $stock['number'] ;
            }
            $data['time']=$insertTime;
            $data['user']=$this->user;
            // $data['remark']="销售";
            $data['type']='sale';
            $rs=Db::table($stock_detailTable)->insert($data);
            if (!$rs) return json_encode(['result' => 2]);

            //插入account表
            $aData['sale_detail_id']=$sale_detail_id;
            $aData['goods_id']=$value['goods_id'];
            $aData['subject']=0;
            $aData['cost']=(-1)*$data['Dsum'];
            $aData['income']=$value['sum'];
            $aData['time']=$insertTime;
            $aData['user']=$this->user;
            $aData['store_id']=$value['store_id'];
            $accountTable=$this->mdb.'.account';
            $rs=Db::table($accountTable)->insert($aData);
            if (!$rs) return json_encode(['result' => 2]);
            $ttlSum+=$value['sum'];
        }
        $sale['sum']=$ttlSum;
        $sale['type']="S";
        $sale['time']=$insertTime;
        $sale['summary']="由订单生成销售单";
        $rs=Db::table($saleTable)->update($sale);
        if (!$rs) return json_encode(['result' => 2]);
        return json_encode(['result' => 1]);
    }

    public function getUnit(){
        $unit_priceTable=$this->mdb.'.unit_price';
        $unitTable=$this->mdb.'.unit b';
        $where['goods_id']=$_GET['goods_id'];
        $where['fx']=['>',0];
        $rs=Db::table($unit_priceTable)->alias('a')
            ->join($unitTable,' a.unit_id=b.unit_id')
            ->where($where)
            ->field('b.unit_id,unit_name')
            ->select();
        $temp=Db::table($unitTable)->find($_GET['unit_id']);
        $rs[sizeof($rs)]=$temp;
        $result['unit']=$rs;
        if($_GET['member_id']!=0) {
            $uwhere['member_id']=$_GET['member_id'];
            $uwhere['goods_id']=$_GET['goods_id'];
            $saleTable=$this->mdb.".sale_all a";
            $saleDetailTable=$this->mdb.".sale_detail b";
            $rs=Db::table($saleTable)
                ->join($saleDetailTable,"a.sale_id=b.sale_id")
                ->where($uwhere)
                ->order('time desc')
                ->find();
            if($rs){
                $result['result']=$rs['price'];
                $result['unit_price']=$rs['unit_id'];
                $res=Db::table($this->mdb . '.goods')->find($_GET['goods_id']);
                if($res['time']>$rs['time']){
                    if($rs['unit_id']==$res['unit_id']) {
                        $result['result'] = $rs['price']>$res['out_price']?$rs['price']:$res['out_price'];
                    }
                    else{
                        $pwhere['goods_id']=$_GET['goods_id'];
                        $pwhere['unit_id']=$rs['unit_id'];
                        $unit_priceTable=$this->mdb . '.unit_price';
                        $unitResult=Db::table($unit_priceTable)->where($pwhere)->find();
                        $result['result'] = $rs['price']>$unitResult['price']?$rs['price']:$unitResult['price'];
                    }
                }
            }else{
                $goodsTable=$this->mdb.'.goods';
                $price=Db::table($goodsTable)->find($_GET['goods_id']);
                $result['result']=$price['out_price'];
            }
        }else{
            $goodsTable=$this->mdb.'.goods';
            $price=Db::table($goodsTable)->find($_GET['goods_id']);
            $result['result']=$price['out_price'];
        }
        return json_encode($result);
    }

    public function printPayCheck()
    {
        $saleTable = $this->mdb . '.sale_all';
        $saleDetailTable = $this->mdb . '.sale_detail b';
        $goodsTable = $this->mdb . '.goods c';
        $unitTable = $this->mdb . '.unit d';
            $where['member_id'] = $_GET['member_id'];
            $where['type'] = ['in', ["S","B",'P','Init','income','cost']];
            $tWhere=$where;
        if(isset($_GET['edate'])){
            $tWhere['time']=$where['a.time']=['between',array($_GET['sdate'],$_GET['edate'])];
        }else
            $tWhere['time']=$where['a.time']=['like',date("Y-m").'%'];
            $rs = Db::table($saleTable)->alias('a')
                ->join($saleDetailTable, 'a.sale_id=b.sale_id', 'LEFT')
                ->join($goodsTable, 'b.goods_id=c.goods_id', 'LEFT')
                ->join($unitTable, 'd.unit_id=b.unit_id', 'LEFT')
                ->field('goods_name,price,number,b.sum,a.time,a.sale_id,a.sum as ttl,unit_name as unit,type,summary')
                ->order('a.time')
                ->where($where)
                ->select();
            $data['credit'] = $rs;

            $rs['number'] = Db::table($saleTable)->alias('a')
                ->join($saleDetailTable, 'a.sale_id=b.sale_id')
                ->where($tWhere)
                ->sum('number');
            //$where['type']=['in',["S","B"]];
            $rs['sum'] = Db::table($saleTable)
                ->where($tWhere)
                ->sum('sum');
            $tWhere['type'] = "P";
            $rs['payment'] = Db::table($saleTable)
                ->where($tWhere)
                ->sum('sum');
            $rs['sum'] -= $rs['payment'];
            $saleTable=$this->mdb.'.sale';
            $rs['credit'] = Db::table($saleTable)
            ->where('member_id','=',$_GET['member_id'])
            ->sum('sum');
            $data['ttl'] = $rs;

        $infoTable = $this->mdb . '.info';
        $rs = Db::table($infoTable)->find(1);
        $data['info'] = $rs;
        return json_encode($data);
    }

    public function printAll()
    {
        $result['printTime']=date('Y-m-d H:i:s',time());
        $saleTable=$this->mdb.'.sale';
        $memberTable=$this->mdb.'.member b';
        $where['type']=["neq","order"];
        $rs=Db::table($saleTable)->alias('a')
            ->join($memberTable,'a.member_id=b.member_id')
            ->field('a.member_id as member_id,sum(sum) as sum,member_name,count(*) as count')
            ->where($where)
            //->page($_GET['page'],10)
            ->group('a.member_id')
            ->order('count')
            ->select();
        $saleDetailTable=$this->mdb.'.sale_detail b';
        $goodsTable=$this->mdb.'.goods c';
        $unitTable=$this->mdb.'.unit d';
        for($i=0;$i<sizeof($rs);$i++){
            unset($detail);
            $detail['member_name']=$rs[$i]['member_name'];
            $detail['sum']=$rs[$i]['sum'];
            $where['member_id']=$rs[$i]['member_id'];
            $detail['detail']=Db::table($saleTable)->alias('a')
                ->join($saleDetailTable,'a.sale_id=b.sale_id','LEFT')
                ->join($goodsTable,'b.goods_id=c.goods_id','LEFT')
                ->join($unitTable,'d.unit_id=b.unit_id','LEFT')
                ->field('goods_name,price,number,b.sum,type,a.time,unit_name,a.sum as ttl,a.summary,remark,a.time,a.sale_id')
                ->order('a.time')
                ->where($where)
                ->select();
            $result['data'][$i]=$detail;
        }
        $infoTable=$this->mdb.'.info';
        $rs=Db::table($infoTable)->find(1);
        $result['info']=$rs['cname'];
        return json_encode($result);
    }
    public  function backUp(){
        $result['printTime']=date('Y-m-d H:i:s',time());
        $saleTable=$this->mdb.'.sale';
        $memberTable=$this->mdb.'.member b';
        $where['type']=["neq","order"];
        $rs=Db::table($saleTable)->alias('a')
            ->join($memberTable,'a.member_id=b.member_id')
            ->field('a.member_id as member_id,sum(sum) as sum,member_name')
            ->where($where)
            ->group('a.member_id')
            ->order('member_name')
            ->select();
        $saleDetailTable=$this->mdb.'.sale_detail b';
        $goodsTable=$this->mdb.'.goods c';
        $unitTable=$this->mdb.'.unit d';
        for($i=0;$i<sizeof($rs);$i++){
            unset($detail);
            $detail['member_name']=$rs[$i]['member_name'];
            $detail['sum']=$rs[$i]['sum'];
            $where['member_id']=$rs[$i]['member_id'];
            $detail['detail']=Db::table($saleTable)->alias('a')
                ->join($saleDetailTable,'a.sale_id=b.sale_id','LEFT')
                ->join($goodsTable,'b.goods_id=c.goods_id','LEFT')
                ->join($unitTable,'d.unit_id=b.unit_id','LEFT')
                ->field('goods_name,price,number,b.sum,type,a.time,unit_name,a.sum as ttl,a.summary,remark,a.time,a.sale_id')
                ->order('a.time')
                ->where($where)
                ->select();
            $result['data'][$i]=$detail;
        }
        $infoTable=$this->mdb.'.info';
        $rs=Db::table($infoTable)->find(1);
        $result['info']=$rs['cname'];

        $purchaseTable=$this->mdb.'.purchase';
        $supplyTable=$this->mdb.'.supply b';

        $rs=Db::table($purchaseTable)->alias('a')
            ->join($supplyTable,'a.supply_id=b.supply_id')
            ->field('a.supply_id ,sum(sum) as sum,supply_name')
            ->group('a.supply_id')
            ->order('supply_name')
            ->select();
        $purchaseDetailTable=$this->mdb.'.purchase_detail b';
        $goodsTable=$this->mdb.'.goods c';
        $unitTable=$this->mdb.'.unit d';
        for($i=0;$i<sizeof($rs);$i++){
            unset($detail);
            $detail['supply_name']=$rs[$i]['supply_name'];
            $detail['sum']=$rs[$i]['sum'];
            $swhere['supply_id']=$rs[$i]['supply_id'];
            $detail['detail']=Db::table($purchaseTable)->alias('a')
                ->join($purchaseDetailTable,'a.purchase_id=b.purchase_id','LEFT')
                ->join($goodsTable,'b.goods_id=c.goods_id','LEFT')
                ->join($unitTable,'d.unit_id=b.unit_id','LEFT')
                ->field('goods_name,price,number,b.sum,type,a.time,unit_name,a.sum as ttl,a.summary,a.time,a.purchase_id')
                ->order('a.time')
                ->where($swhere)
                ->select();
            $result['credit'][$i]=$detail;
        }
        Db::table($infoTable)->where('id','=','1')->update(['etime'=>$result['printTime']]);
        return json_encode($result);
    }
}
