<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 上午 7:56
 */

namespace app\index\controller;
use think\Db;

class Statistics
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
    public function dashGetSum(){
        $saleTable=$this->mdb.'.sale_all';
        $where['time']=['like',$_GET['date'].'%'];
        $where['type']=array(['=','S'],['=','B'],'or');
        $data['sum']=Db::table($saleTable)
            ->where($where)
            ->sum('sum');
        $creditTable=$this->mdb.'.sale';
        $finishTable=$this->mdb.'.sale_finish';
        $data['credit']=Db::table($creditTable)
            ->where($where)
            ->sum('sum');
        $where['type']='P';
        $data['old_credit']=Db::table($saleTable)->where($where)->sum('sum');
        $bdetailTable=$this->mdb.'.bdetail';
        $bwhere['time']=['like',$_GET['date'].'%'];
        $bwhere['summary']=['like','客户付款%'];
        $data['cash']=Db::table($bdetailTable)->where($bwhere)->sum('sum');
        $bwhere['summary']=['like','客户付款订货%'];
        $data['order']=Db::table($bdetailTable)->where($bwhere)->sum('sum');
        $bwhere['summary']=['like','%支出条目-%'];
        $data['cost']=Db::table($bdetailTable)->where($bwhere)->sum('sum');
        $bwhere['summary']=['like','%收入条目-%'];
        $data['income']=Db::table($bdetailTable)->where($bwhere)->sum('sum');
        $purchaseTable=$this->mdb.'.purchase_all';
        $supplyTable=$this->mdb.'.supply b';
        $data['onWay']=Db::table($purchaseTable)->alias('a')
            ->join($supplyTable,'a.supply_id=b.supply_id')
           // ->fetchSql(true)
            ->where(['on_way'=>1])
            ->field('supply_name,time,purchase_id,finish')
            ->select();
        $infoTable=$this->mdb.'.info';
        $rs=Db::table($infoTable)->find(1);
        $today=strtotime (date("y-m-d h:i:s"));
        $etime=strtotime($rs['etime']);
        $data['etime']=($today-$etime)>259200;
        return json_encode($data);

    }
    public function saleToday(){
        $where['a.time']=['like',$_GET['date'].'%'];
        $where['type']=array(['=','B'],['=','S'],'or');
        $where['fx']=['gt',0];
        $saleTable=$this->mdb.'.sale_all a';
        $goodsTable=$this->mdb.'.goods c';
        $detailTable=$this->mdb.'.sale_detail b';
        $fxTable=$this->mdb.'.fx d';
        $unitTable=$this->mdb.'.unit e';
        $rs=Db::table($saleTable)
            ->join($detailTable,'a.sale_id=b.sale_id')
            ->join($goodsTable,'b.goods_id=c.goods_id')
            ->join($fxTable,'d.goods_id=b.goods_id and d.unit_id=b.unit_id')
            ->join($unitTable,"d.unit_id=e.unit_id")
            ->where($where)
            ->field('goods_name,unit_name,b.goods_id,sum(number/fx) as number,sum(b.sum) as sum')
            ->group('b.goods_id,type')
            ->page($_GET['page'],10)
            ->order('sum desc, a.sale_id desc')
            ->select();
        unset($where['fx']);
        $data['lists']=$rs;
        $total_count=Db::table($saleTable)->alias('a')
            ->join($detailTable.' b','a.sale_id=b.sale_id')
            ->where($where)
            ->count('distinct b.goods_id');
        $data['total_count']=$total_count;
        return json_encode($data);

    }
    public function onWay(){
        $purchaseDetailTable=$this->mdb.'.purchase_detail';
        $goodsTable=$this->mdb.'.goods b';
        $data['lists']=Db::table($purchaseDetailTable)->alias('a')
            ->join($goodsTable,'a.goods_id=b.goods_id')
            ->where(['purchase_id'=>$_GET['purchase_id']])
            ->page($_GET['page'],10)
            ->field('goods_name,number,sum')
            ->select();
        $data['total_count']=Db::table($purchaseDetailTable)
            ->where(['purchase_id'=>$_GET['purchase_id']])
            ->count();
        return json_encode($data);
    }
    public function receiveGoods(){
        $purchaseTable=$_GET['finish']==1?$this->mdb.'.purchase_finish':$this->mdb.'.purchase';
        $rs=Db::table($purchaseTable)
            ->where(['purchase_id'=>$_GET['purchase_id']])
            //->fetchSql(true)
            ->update(['on_way'=>0]);
      //  echo $rs;
        $purchaseTable=$this->mdb.'.purchase_all';
        $supplyTable=$this->mdb.'.supply b';
        $data['onWay']=Db::table($purchaseTable)->alias('a')
            ->join($supplyTable,'a.supply_id=b.supply_id')
            ->where(['on_way'=>1])
            ->field('supply_name,time,purchase_id,finish')
            ->select();
        return json_encode($data);

    }
    public function creditToday(){
        $saleTable=$this->mdb.'.sale';
        $memberTable=$this->mdb.'.member b';
        $where['time']=['like',$_GET['date'].'%'];
        $where['type']='S';
        $rs=Db::table($saleTable)->alias('a')
            ->join($memberTable,'a.member_id=b.member_id')
            ->where($where)
            ->field('member_name,sum(sum) as sum,sale_id,a.member_id')
            ->group('a.member_id')
            ->order('sum desc,sale_id desc')
            ->page($_GET['page'],10)
            ->select();
        $data['lists']=$rs;
        $total_count=Db::table($saleTable)->alias('a')
            ->where($where)
            ->count('distinct member_id');
        $data['total_count']=$total_count;
        return json_encode($data);
    }
    public function cashToday(){
        $bdetail=$this->mdb.'.bdetail a';
        $bank=$this->mdb.'.bank b';
        $where['time']=['like',$_GET['date'].'%'];
        $where['summary']=['like','客户付款%'];
        if(isset($_GET['user'])) {
            $user=Db::table("hs_0.user")->where("name","=",$this->user)->find();
            if($user['role_id']!=0){
                $where['user']=$this->user;
            }
        }
        $rs=Db::table($bdetail)
            ->join($bank,'a.bank_id=b.bank_id')
            ->where($where)
            ->field('user,a.bank_id,sum(sum) as sum,bank_name')
            ->page($_GET['page'],10)
            ->group('user,a.bank_id')
            ->select();
        $data['lists']=$rs;
        $total_count=sizeof($rs);
        $data['total_count']=$total_count;
        return json_encode($data);
    }
    public function cashMoreToday(){
        $bdetail=$this->mdb.'.bdetail a';
        $bank=$this->mdb.'.bank b';
        $where['time']=['like',$_GET['date'].'%'];
        $where['summary']=['like','客户付款%'];
        $where['user']=$_GET['user'];
        $where['a.bank_id']=$_GET['bank_id'];
        $rs=Db::table($bdetail)
            ->join($bank,'a.bank_id=b.bank_id')
            ->where($where)
            ->field('user,sum,bank_name,summary,time')
            ->order('time desc')
            ->page($_GET['page'],10)
            ->select();
        $data['lists']=$rs;
        $total_count=Db::table($bdetail)
            ->where($where)
            ->count();
        $data['total_count']=$total_count;
        return json_encode($data);
    }
    public function costToday(){
        $bdetail=$this->mdb.'.bdetail a';
        $bank=$this->mdb.'.bank b';
        $where['time']=['like',$_GET['date'].'%'];
        $where['summary']=$_GET['type']=='cost'?['like','%支出条目-%']:['like','%收入条目-%'];
        $rs=Db::table($bdetail)
            ->join($bank,'a.bank_id=b.bank_id')
            ->where($where)
            ->field('user,sum,bank_name,summary,time')
            ->order('time desc')
            ->page($_GET['page'],10)
            ->select();
        $data['lists']=$rs;
        $total_count=sizeof($rs);
        /*$total_count=Db::table($bdetail)
            ->where($where)
            ->count('distinct user,bank_id');*/
        $data['total_count']=$total_count;
        return json_encode($data);
    }
    public function getOrderStatistics(){
        $where['type']='order';
        $where['a.time']=['like',date("Y-m-d",strtotime('+1 day')).'%'];
        $data['orders1']=$this->getOrderDate($where);
        $where['a.time']=['like',date("Y-m-d",strtotime('+2 day')).'%'];
        $data['orders2']=$this->getOrderDate($where);
        $where['a.time']=['like',date("Y-m-d",strtotime('+3 day')).'%'];
        $data['orders3']=$this->getOrderDate($where);
        return json_encode($data);

    }
    public function getOrderDate($where){
        $saleTable=$this->mdb.'.sale a';
        $saleDetailTable=$this->mdb.'.sale_detail b';
        $goodsTable=$this->mdb.'.goods c';
        $unitTable=$this->mdb.'.unit d';
       /* $unitpriceTable=$this->mdb.'.unit_price';
       $rs=Db::table($saleTable)
            ->join($saleDetailTable,'a.sale_id=b.sale_id')
            ->join($goodsTable,'c.goods_id=b.goods_id')
            ->where($where)
            ->field('goods_name, c.goods_id,c.unit_id as unit_id_0,sum(b.sum) as sum')
            ->order('sum desc, a.sale_id desc')
            ->group('b.goods_id')
            ->select();
        $i=0;
        foreach ($rs as $value){
            $twhere=$where;
            $twhere['goods_id']=$value['goods_id'];
            $twhere['unit_id']=$value['unit_id_0'];
            $rs[$i]['number']=Db::table($saleTable)->alias('a')
                ->join($saleDetailTable.' b','a.sale_id=b.sale_id')
                ->where($twhere)
                ->sum('number');
            $twhere['unit_id']=['<>',$value['unit_id_0']];
            $temp=Db::table($saleTable)->alias('a')
                ->join($saleDetailTable.' b','a.sale_id=b.sale_id')
                ->where($twhere)
                ->field('unit_id,number')
                ->select();
            for($j=0;$j<sizeof($temp);$j++){
                $uwhere['unit_id']=$temp[$j]['unit_id'];
                $uwhere['goods_id']=$value['goods_id'];
                $fx=Db::table($unitpriceTable)->where($uwhere)->field('fx')->find();
                $rs[$i]['number']+=$temp[$j]['number']/$fx['fx'];
            }
            $unit=Db::table($unitTable)->find($value['unit_id_0']);
            $rs[$i]['unit_name']=$unit['unit_name'];
            $i++;
        }*/
        $rs=Db::table($saleTable)
            ->join($saleDetailTable,'a.sale_id=b.sale_id')
            ->join($goodsTable,'c.goods_id=b.goods_id')
            ->join($unitTable,'d.unit_id=b.unit_id')
            ->where($where)
            ->field('goods_name,sum(b.number) as number,d.unit_name')
            ->order('number desc, a.sale_id desc')
            ->group('b.goods_id,b.unit_id')
            ->select();
        return $rs;
    }
    public function Sale(){
            if(isset($_GET['sdate'])&&isset($_GET['edate']))
                $where['a.time']=['between',array($_GET['sdate'],$_GET['edate'])];
            else
                $where['a.time']=['like',date("Y-m").'%'];
            if(isset($_GET['member_id']))$where['member_id']=$_GET['member_id'];
        $saleTable=$this->mdb.'.sale_all a';
        $goodsTable=$this->mdb.'.goods c';
        $detailTable=$this->mdb.'.sale_detail b';
        $fxTable=$this->mdb.'.fx d';
        $rs['data']=Db::table($saleTable)
            ->join($detailTable,'a.sale_id=b.sale_id')
            ->join($goodsTable,'b.goods_id=c.goods_id')
            ->join($fxTable,'d.goods_id=b.goods_id and d.unit_id=b.unit_id')
            ->where($where)
            ->where('type',['=','S'],['=','B'],'or')
            ->where('fx','>',0)
            ->field('goods_name,goods_sn,b.goods_id,sum(number/fx) as num,sum(b.sum) as ttl')
            ->group('b.goods_id')
            ->select();
        /*$rs['sum']=Db::table($saleTable)
            ->join($detailTable,'a.sale_id=b.sale_id')
            ->where($where)
            ->where('type',['=','S'],['=','B'],'or')
            ->sum('b.sum');*/
        $rs['sum']=Db::table($saleTable)
            ->where($where)
            ->where('type',['=','S'],['=','B'],'or')
            ->sum('sum');
        return json_encode($rs);
    }
    public function salePlot(){
        $saleTable=$this->mdb.'.sale_all a';
        $goodsTable=$this->mdb.'.goods c';
        $detailTable=$this->mdb.'.sale_detail b';
        $fxTable=$this->mdb.'.fx d';
        $where['fx']=['>',0];
        if(isset($_GET['member_id']))$where['member_id']=$_GET['member_id'];
        if(isset($_GET['goods_id']))$where['b.goods_id']=$_GET['goods_id'];
        $rs=Db::table($saleTable)
            ->join($detailTable,'a.sale_id=b.sale_id')
            ->join($goodsTable,'b.goods_id=c.goods_id')
            ->join($fxTable,'d.goods_id=b.goods_id and d.unit_id=b.unit_id')
            ->where($where)
            ->where('type',['=','S'],['=','B'],'or')
            ->field('sum(ROUND(number/fx,2)) as num,sum(b.sum) as ttl,DATE_FORMAT(a.time,"%Y-%m") as myTime')
            ->group('myTime')
            ->order('myTime desc')
            ->limit(0,14)
            ->select();

        return json_encode($rs);
    }

    public function purchase(){
        if(isset($_GET['sdate'])&&isset($_GET['edate']))
            $where['a.time']=['between',array($_GET['sdate'],$_GET['edate'])];
        else
            $where['a.time']=['like',date("Y-m").'%'];
        if(isset($_GET['supply_id']))$where['supply_id']=$_GET['supply_id'];
        $purchaseTable=$this->mdb.'.purchase_all a';
        $goodsTable=$this->mdb.'.goods c';
        $detailTable=$this->mdb.'.purchase_detail b';
        $fxTable=$this->mdb.'.fx d';
        $rs['data']=Db::table($purchaseTable)
            ->join($detailTable,'a.purchase_id=b.purchase_id')
            ->join($goodsTable,'b.goods_id=c.goods_id')
            ->join($fxTable,'d.goods_id=b.goods_id and d.unit_id=b.unit_id')
            ->where($where)
            ->where('type',['=','S'],['=','B'],'or')
            ->where('fx','>',0)
            ->field('goods_name,goods_sn,b.goods_id,sum(number/fx) as num,sum(b.sum) as ttl')
            ->group('b.goods_id')
            ->select();
        $rs['sum']=Db::table($purchaseTable)
            ->join($detailTable,'a.purchase_id=b.purchase_id')
            ->where($where)
            ->where('type',['=','S'],['=','B'],'or')
            ->sum('b.sum');
        return json_encode($rs);
    }
    public function purchasePlot(){
        $purchaseTable=$this->mdb.'.purchase_all a';
        $goodsTable=$this->mdb.'.goods c';
        $detailTable=$this->mdb.'.purchase_detail b';
        $fxTable=$this->mdb.'.fx d';
        $where['fx']=['>',0];
        if(isset($_GET['supply_id']))$where['supply_id']=$_GET['supply_id'];
        if(isset($_GET['goods_id']))$where['b.goods_id']=$_GET['goods_id'];
        $rs=Db::table($purchaseTable)
            ->join($detailTable,'a.purchase_id=b.purchase_id')
            ->join($goodsTable,'b.goods_id=c.goods_id')
            ->join($fxTable,'d.goods_id=b.goods_id and d.unit_id=b.unit_id')
            ->where($where)
            ->where('type',['=','S'],['=','B'],'or')
            ->field('sum(number/fx) as num,sum(b.sum) as ttl,DATE_FORMAT(a.time,"%Y-%m") as myTime')
            ->group('myTime')
            ->order('myTime desc')
            ->limit(0,14)
            ->select();
        return json_encode($rs);
    }
    public function Profit(){
        if(isset($_GET['sdate'])&&isset($_GET['edate']))
            $where['a.time']=['between',array($_GET['sdate'],$_GET['edate'])];
        else
            $where['a.time']=['like',date("Y-m").'%'];
        $accountTable=$this->mdb.'.account a';
        $goodsTable=$this->mdb.'.goods b';
       // $stockDetailTable=$this->mdb.'.stock_change c';
        $stockDetailTable=$this->mdb.'.stock_detail c';
        $rs['data']=Db::table($accountTable)
            ->join($goodsTable,'a.goods_id=b.goods_id')
           // ->join($stockDetailTable,'a.goods_id=c.goods_id and a.time=c.time','LEFT')
            ->field('goods_name,a.goods_id, sum(income-cost) as profit')
            //->field('goods_name,a.goods_id, sum(income-cost) as profit')
            ->where($where)
            ->group('a.goods_id')
            ->select();
        $awhere['time']=$where['a.time'];
        $awhere['type']="stockChang";
        $rs['D']=Db::table($stockDetailTable)
            ->field("goods_id,sum(Dstock) as Dstock,sum(Dsum) as Dsum")
            ->where($awhere)
            ->group('goods_id')
            ->select();
        //$where['subject']=0;
        $where['goods_id']=['gt',0];
        $rs['sum']=Db::table($accountTable)
            //->join($goodsTable,'a.goods_id=b.goods_id')
           // ->join($stockDetailTable,'a.goods_id=c.goods_id and a.time=c.time','LEFT')
           // ->field('goods_name,a.goods_id, sum(income-cost) as profit,sum(Dstock) as Dstock,sum(Dsum) as Dsum')
            ->where($where)
           // ->group('a.goods_id')
            ->sum("income-cost");
        return json_encode($rs);

    }

    public function profitPlot(){
        $accountTable=$this->mdb.'.account';
        $where['goods_id']=['>',0];
        $rs=Db::table($accountTable)
            ->where($where)
            ->field('sum(income-cost) as profit,DATE_FORMAT(time,"%Y-%m") as myTime')
            ->group('myTime')
            ->order('myTime desc')
            ->limit(0,14)
            ->select();
        return json_encode($rs);
    }
    public function profitPlotGoods(){
        $accountTable=$this->mdb.'.account a';
        $stockDetailTable=$this->mdb.'.stock_change c';
        $where['a.goods_id']=$_GET['goods_id'];
        $rs['plot']=Db::table($accountTable)
            ->where($where)
            ->join($stockDetailTable,'a.goods_id=c.goods_id and a.time=c.time','LEFT')
            ->field('sum(income-cost) as profit,sum(Dstock) as Dstock,DATE_FORMAT(a.time,"%Y-%m") as myTime')
            ->group('myTime')
            ->order('myTime desc')
            ->limit(0,14)
            ->select();
        $saleTable=$this->mdb.'.sale_all a';
        $saleDetailTable=$this->mdb.'.sale_detail b';
        $fxTable=$this->mdb.'.fx c';
        $cwhere['b.goods_id']=$_GET['goods_id'];
        $cwhere['fx']=['>',0];
        $rs['number']=Db::table($saleTable)
            ->join($saleDetailTable,'a.sale_id=b.sale_id')
            ->join($fxTable,'b.goods_id=c.goods_id and b.unit_id=c.unit_id')
            ->where($cwhere)
            ->where('type',['=','S'],['=','B'],'or')
            ->field('sum(number/fx) as num,DATE_FORMAT(a.time,"%Y-%m") as myTime')
            ->group('myTime')
            ->order('myTime desc')
            ->limit(0,14)
            ->select();
        return json_encode($rs);
    }
    public function orderList(){
        $cwhere=array();
        if(isset($_GET['search'])) $cwhere['goods_sn|goods_name']=['like','%'.$_GET['search'].'%'];
        if(isset($_GET['sdate'])&&isset($_GET['edate']))
            $cwhere['p.time']=['between',array($_GET['sdate'],$_GET['edate'])];
        $purchaseTable=$this->mdb.'.purchase_all p';
        $purchaseDetailTable=$this->mdb.'.purchase_detail pDetail';
        $saleTable=$this->mdb.'.sale_detail s';
        $feeTable=$this->mdb.'.staff_credit f';
        $fxTable=$this->mdb.'.fx fx';
        $goodsTable=$this->mdb.'.goods goods';
        $stockTable=$this->mdb.'.stock stock';
        $count=Db::table($purchaseTable)
            ->join($purchaseDetailTable,'p.purchase_id=pDetail.purchase_id')
            ->join($goodsTable,'pDetail.goods_id=goods.goods_id')
            ->where($cwhere)
            ->where('is_order','=',1)
            ->order('p.time desc')
            ->column('distinct inorder ');
        $j=$_GET['page']-1;
        $rs=array();
        for($i=0;$i<10&&($i+$j*10)<sizeof($count);$i++){
            $where['inorder']=$count[$i+$j*10];
            $rs[$i]['inorder']=$count[$i+$j*10];
            $pInfo=Db::table($purchaseTable)
                ->join($purchaseDetailTable,'p.purchase_id=pDetail.purchase_id')
                ->join($goodsTable,'pDetail.goods_id=goods.goods_id')
                ->join($fxTable,'pDetail.goods_id=fx.goods_id and pDetail.unit_id=fx.unit_id')
                ->where($where)
                ->where('is_order','=',1)
                ->field('sum(number/fx) as pNumber,sum(pDetail.sum) as pSum')
                ->select();
            $rs[$i]['pNumber']=$pInfo[0]['pNumber'];
            $rs[$i]['pSum']=$pInfo[0]['pSum'];
            $rs[$i]['fee']=Db::table($feeTable)->where($where)->sum('sum');
            $sInfo=Db::table($saleTable)
                ->join($fxTable,'fx.goods_id=s.goods_id and fx.unit_id=s.unit_id')
                ->where($where)
                ->field('sum(number/fx) as sNumber,sum(sum) as sSum')
                ->select();
            $rs[$i]['sNumber']=$sInfo[0]['sNumber'];
            $rs[$i]['sSum']=$sInfo[0]['sSum'];
            $stock=Db::table($stockTable)->where($where)->field('sum(number) as stockNumber,sum(sum) as stockSum')->select();
            if($stock){
                $rs[$i]['stockNumber']=$stock[0]['stockNumber'];
                $rs[$i]['stockSum']=$stock[0]['stockSum'];
            }else{
                $rs[$i]['stockNumber']=0;
                $rs[$i]['stockSum']=0;
            }
        }
        $data['lists']=$rs;
        $data['total_count']=sizeof($count);
        return json_encode($data);
    }
    public function orderDetail(){
        $where['inorder']=$_GET['inorder'];
        $purchaseTable=$this->mdb.'.purchase_all p';
        $purchaseDetailTable=$this->mdb.'.purchase_detail pDetail';
        $saleTable=$this->mdb.'.sale_detail s';
        $fxTable=$this->mdb.'.fx fx';
        $goodsTable=$this->mdb.'.goods goods';
        $stockTable=$this->mdb.'.stock stock';
        $supply=$this->mdb.'.supply supply';
        $pInfo=Db::table($purchaseTable)
            ->join($purchaseDetailTable,'p.purchase_id=pDetail.purchase_id')
            ->join($goodsTable,'pDetail.goods_id=goods.goods_id')
            ->join($fxTable,'pDetail.goods_id=fx.goods_id and pDetail.unit_id=fx.unit_id')
            ->join($supply,'supply.supply_id=p.supply_id','LEFT')
            ->where($where)
            ->where('is_order','=',1)
            ->field('sum(number/fx) as pNumber,sum(pDetail.sum) as pSum,pDetail.goods_id,p.summary,goods_name,supply_name')
            ->group('pDetail.goods_id')
            ->select();
       for($i=0;$i<sizeof($pInfo);$i++){
           $where['s.goods_id']=$pInfo[$i]['goods_id'];
           $sInfo=Db::table($saleTable)
               ->join($fxTable,'fx.goods_id=s.goods_id and fx.unit_id=s.unit_id')
               ->where($where)
               ->field('sum(number/fx) as sNumber,sum(sum) as sSum')
               ->select();
           $pInfo[$i]['sNumber']=$sInfo[0]['sNumber'];
           $pInfo[$i]['sSum']=$sInfo[0]['sSum'];
           //unset($where['s.goods_id']);
           $swhere['goods_id']=$pInfo[$i]['goods_id'];
           $swhere['inorder']=$_GET['inorder'];
           $stock=Db::table($stockTable)->where($swhere)->field('sum(number) as stockNumber,sum(sum) as stockSum')->select();
          // return json_encode($stock);
           if($stock){
               $pInfo[$i]['stockNumber']=$stock[0]['stockNumber'];
               $pInfo[$i]['stockSum']=$stock[0]['stockSum'];
           }else{
               $pInfo[$i]['stockNumber']=0;
               $pInfo[$i]['stockSum']=0;
           }
       }
        return json_encode($pInfo);
    }
    public function incomePlot(){
        $accountTable=$this->mdb.'.account a';
        $where['subject']=['neq',0];
        //$subjectTable=$this->mdb.'.subject b';
        if(isset($_GET['subject'])) $where['subject']=$_GET['subject'];
        $rs=Db::table($accountTable)
            ->where($where)
            ->field('sum(cost) as cost,sum(income) as income,DATE_FORMAT(time,"%Y-%m") as myTime')
            ->group('myTime')
            ->order('myTime desc')
            ->limit(0,14)
            ->select();
        return json_encode($rs);
    }
    public function income(){
        $accountTable=$this->mdb.'.account a';
        $where['subject']=['neq',0];
        $subjectTable=$this->mdb.'.subject b';
        if(isset($_GET['subject'])) $where['subject']=$_GET['subject'];
        if(isset($_GET['sdate'])&&isset($_GET['edate']))
            $where['time']=['between',array($_GET['sdate'],$_GET['edate'])];
        else
            $where['time']=['like',date("Y-m").'%'];
        $rs=Db::table($accountTable)
            ->join($subjectTable,'a.subject=b.subject_id')
            ->where($where)
            ->field('sum(cost) as cost,sum(income) as income,subject,subject_name')
            ->group('subject_id')
            ->select();
        return json_encode($rs);
    }

    public function Asset(){
        $mdb=$this->mdb;
        $stockTable=$mdb.".stock";
        $data['stock']=\think\Db::table($stockTable)->sum('sum');
        $saleTable=$mdb.".sale";
        $data['mcredit']=\think\Db::table($saleTable)->sum('sum');
        $purchaseTable=$mdb.".purchase";
        $data['scredit']=\think\Db::table($purchaseTable)->sum('sum');
        $staffTable=$mdb.".staff_credit";
        $data['staffCredit']=\think\Db::table($staffTable)->where('finish','=',0)->sum('sum');
        $bankTable=$mdb.".bank";
        $data['bank']=\think\Db::table($bankTable)->sum('balance');
        $data['asset']=$data['stock']+$data['mcredit']+$data['bank']-$data['scredit']-$data['staffCredit'];
        $rs[0]=$data;
        return json_encode($rs);
    }

    public function AssetPlot(){
        $assetTable=$this->mdb.'.asset';
        $rs=Db::table($assetTable)->order('asset_id desc')->limit(0,14)->select();
        return json_encode($rs);
    }
}