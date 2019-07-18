<?php
namespace Home\Controller;
use Think\Controller;
class StatisticsController extends Controller {
	public function _initialize(){
		if(!is_login()){
			$this->redirect('login/index');
		}
	}
	public function salereportTable(){ 
	  $sale=M(I('session.DB').'sale_all');
	  $where['idgoods']=array('gt',0);
	  if($_POST){
	  	$where['time']=array('between',array($_POST['sdate']." 00:00:00",$_POST['edate']." 23:59:59"));
	  	$ttlSum=$sale->where($where)->field('sum(sum) as sum')->select();
	  	$info=$_POST['sdate'].'到'.$_POST['edate'].'销售统计 营业额：￥'.$ttlSum[0]['sum'];
	  }else{
	   	 $where['time']=array('like','%'.date('Y-m').'%');
	   	 $ttlSum=$sale->where($where)->field('sum(sum)')->select();
		 $info=date('Y-m').'月销售统计 营业额：￥'.$ttlSum[0]['sum'];
	  }
	  $sale_report=$sale->where($where)->field('idgoods,sum(number) as number,sum(sum) as sum')->group('idgoods')->select();
	  $ttl=$sale->where($where)->sum('number');
	  $glist=F(I('session.DB').'goods');
	  if(!$glist){
	  	$goods=M(I('session.DB').'goods');
	  	$glist=$goods->getField('idgoods,gname');
	  	F(I('session.DB').'goods',$glist);
	  }
	  foreach ($sale_report as $key => $value) {
	  	$temp=$value['idgoods'];
	  	$sale_report[$key]['gname']=$glist[$temp];
	  	//$ttl+=$value['number'];
	  	$sale_report[$key]['percent']=number_format($value['number']/$ttl*100,2);
	  	$sale_report[$key]['price']=number_format($value['sum']/$value['number'],2);
	  }
	  $this->assign('ttl',number_format($ttl));
	  $this->assign('sale_report',$sale_report);
	  $this->assign('info',$info);
      $this->display("statistics/salereportTable");
	}
	
	public function salereport(){
		$this->display("statistics/salereport");
	}
	
	public function compare(){
		$sale=M(I('session.DB').'sale_all');
		$slist=$sale->where($_GET)->field('sum(number) as number ,date_format(time,"%Y-%m") as mytime')->group('mytime')->order('mytime desc')->limit(13)->select();
		foreach ($slist as $key => $value) {
			$list['number'][$key]=$value['number'];
			$list['ticks'][$key]=$value['mytime'];
		}
		echo json_encode($list);
	}
	
	public function asset(){
		    $asset=M(I('session.DB').'asset');
			$stock=M(I('session.DB').'stock_sum_view');
			$stocklist=$stock->sum('sum');
			$data['stock']=$stocklist;
			//print_r($stocklist);return;
			$bank=M(I('session.DB').'bank');
			$data['bank']=$bank->sum('sum');
			$sale=M(I('session.DB').'sale');
			$data['mcredit']=$sale->sum('sum');
			$purchase=M(I('session.DB').'purchase');
			$data['scredit']=$purchase->sum('sum');
			$freight=M(I('session.DB').'freight');
			$data['scredit']+=$freight->where('status=0')->sum('freight');
			$ttl=$data['bank']+$data['mcredit']-$data['scredit']+$data['stock'];
			$this->assign("asset_list",$data);
			$this->assign("ttl",$ttl);
		$this->display("statistics/asset");
	}
	
	public function mytest(){
		$asset=M(I('session.DB').'asset');
		$rs=$asset->limit(0,13)->order('idasset desc')->select();
		echo json_encode($rs);
		
	}
	
	public function profit(){
		$this->display("statistics/profit");
	}
	public function profitTable(){
		$data=$_POST?$_POST:$_GET;
		$p=$data['p']?$data['p']:0;
		$where['inorder']=array('neq','NULL');
		if($data['sdate']&&$data['edate']){
			$where['time']=array('between',array($data['sdate']." 00:00:00",$data['edate']." 23:59:59"));
			$params['edate']=$data['edate'];
			$params['sdate']=$data['sdate'];
			$info=$data['sdate'].'到'.$data['edate'];
		}else{
			$where['time']=array('like',date('Y-m').'%');
			$info=date('Y-m').'月';
		}
		$purchase=M(I('session.DB').'purchase_all');
		$count=$purchase->where($where)->field('count(distinct inorder) as count')->find();
		$count=$count['count'];
		$plist=$purchase->where($where)->field('inorder,sum(number) as number,sum(sum) as sum')->group('inorder')->order('inorder desc')->page($p.',10')->select();
		$Page=specialpage($count, $params);
		$show=$Page->show();
		$this->assign('page',$show);
		$this->assign('info',$info);
		$sale=M(I('session.DB').'sale_all');
		$freight=M(I('session.DB').'freight');
		$stock=M(I('session.DB').'stock');
		for ($i = 0; $i < sizeof($plist); $i++) {
			$swhere['inorder']=$plist[$i]['inorder'];
			$plist[$i]['freight']=$freight->where($swhere)->sum('freight');
			$swhere['idgoods']=array('gt',0);
			$plist[$i]['s_num']=$sale->where($swhere)->sum('number');
			$plist[$i]['s_sum']=$sale->where($swhere)->sum('sum');
			//$plist[$i]['stock']=$stock->where($where)->sum('stock');
			$stocklist=$stock->where($swhere)->field('idgoods,stock')->select();
			//print_r($stocklist);
			$stock_num=0;
			$stock_sum=0;
			foreach ($stocklist as $value) {
				$swhere['idgoods']=$value['idgoods'];
				$sum=$purchase->where($swhere)->field('sum(number) as number,sum(sum) as sum')->find();
				//print_r($sum);
				$temp=$value['stock']/$sum['number']*$sum['sum'];
				//echo $sum['sum'];
				$stock_sum+=round($temp);
				$stock_num+=round($value['stock']);
			}
			$plist[$i]['stock_sum']=$stock_sum;
			$plist[$i]['stock']=$stock_num;
		}
		$this->assign('profit_table',$plist);
		$this->display("statistics/profitTable");
	}
	
	public function profitDetail(){
		//print_r($_POST);return;
		$where['inorder']=$_POST['inorder'];
		$freight=M(I('session.DB').'freight');
		$fee=$freight->where($where)->sum('freight');
		$puchase=M(I('session.DB').'purchase_all');
		$ttl_num=$puchase->where($where)->sum('number');
		$plist=$puchase->where($where)->field('idgoods,sum(sum) as sum,sum(number) as number')->group('idgoods')->select();
		$sale=M(I('session.DB').'sale_all');
		$stock=M(I('session.DB').'stock');
		$glist=F(I('session.DB').'goods');
		if(!$glist){
			$goods=M(I('session.DB').'goods');
			$glist=$goods->getField('idgoods,gname');
			F(I('session.DB').'goods');
		}
		$rs=array();
		for ($i = 0; $i < sizeof($plist); $i++) {
			$temp=$where['idgoods']=$plist[$i]['idgoods'];
			$sum=$sale->where($where)->sum('sum');
			$number=$sale->where($where)->sum('number');
			$rs['categories'][$i]=$glist[$temp]."平均售价:￥".round($sum/$number,2);
			$stocknumber=$stock->where($where)->field('stock')->find();
			$stocksum=0;
			if($stocknumber){
				$stocksum=round($stocknumber['stock']/$plist[$i]['number']*$plist[$i]['sum']);
			}
			$rs['series'][$i]=round($sum+$stocksum-$plist[$i]['sum']-$plist[$i]['number']/$ttl_num*$fee);
			$rs['price'][$i]=round($sum/$number,2);
		}
		
		echo json_encode($rs);
	}
	
	public function profitpie(){
		$data=$_POST;
		$where['inorder']=array('neq','NULL');
		$where['idgoods']=array('gt',0);
		
		if($data['sdate']&&$data['edate']){
			$where['time']=array('between',array($data['sdate']." 00:00:00",$data['edate']." 23:59:59"));
		}else{
			$where['time']=array('like',date('Y-m').'%');
		}
		
		$purchase=M(I('session.DB').'purchase_all');
		$plist=$purchase->where($where)->field('idgoods,inorder,sum(sum) as sum,sum(number) as number')->group('idgoods,inorder')->select();
		if(!$plist){
			$mydata['info']=1;
			echo json_encode($mydata);
			return;
		}
		$sale=M(I('session.DB').'sale_all');
		$freight=M(I('session.DB').'freight');
		$freight_list=$freight->where($where)->field('inorder,sum(freight) as freight')->select();
		$flist=array();
		foreach ($freight_list as $value) {
			$inorder=$value['inorder'];
			$flist[$inorder]=$value['freight'];
		}
		$stock=M(I('session.DB').'stock');
		
		$rs=array();
		for ($i = 0; $i < sizeof($plist); $i++) {
			$dwhere['idgoods']=$plist[$i]['idgoods'];
			$idgoods=$plist[$i]['idgoods'];
			$dwhere['inorder']=$plist[$i]['inorder'];
			$inorder=$plist[$i]['inorder'];
			$ttl_sale=$sale->where($dwhere)->sum('sum');
			$ttl_sale=$ttl_sale>0?$ttl_sale:0;
			$rs[$idgoods]['profit']+=$ttl_sale-$plist[$i]['sum'];
			$ttl_profit+=$ttl_sale-$plist[$i]['sum'];
			$fee=$flist[$inorder];
			if($fee!=0){
				$fwhere['inorder']=$plist[$i]['inorder'];
				$temp_num=$purchase->where($fwhere)->sum('number');
				$temp=round($plist[$i]['number']/$temp_num*$fee);
				$rs[$idgoods]['profit']-=$temp;
				$ttl_profit-=$temp;
			}
			//$mydata['info'].='$ttl_sale-$plist[$i]["sum"].:'.$ttl_sale."-".$plist[$i]["sum"];
			$stock_list=$stock->where($dwhere)->field('stock')->find();
			if($stock_list){
				$temp_sum=$plist[$i]['sum'];
				$temp_num=$plist[$i]['number'];
				$stock_sum=round($stock_list['stock']/$temp_num*$temp_sum);
				$rs[$idgoods]['profit']+=$stock_sum;
				$ttl_profit+=$stock_sum;
				$mydata['info'].='stock_sum.:'.$stock_sum."|";
			}
		}
		/*foreach ($rs as $key => $value) {
		 $mydata['info'].='rs:'.$value['profit']."|";
		}
		echo json_encode($mydata);
		return;*/
		
		$goods=M(I('session.DB').'goods');
		$glist=$goods->getField('idgoods,gname,idcat');
		$cat=M(I('session.DB').'categroy');
		$clist=$cat->getField('idcat,cat_name');
		$cdata=array();
		foreach ($rs as $key => $value) {
			$idcat=$glist[$key]['idcat'];
			if(!$cdata[$idcat]['cat'])
			   $cdata[$idcat]['categories']=$clist[$idcat];
			$cdata[$idcat]['ttl_y']+=$rs[$key]['profit'];
			$cdata[$idcat]['drilldown']['categories'][]=$glist[$key]['gname'];
			$cdata[$idcat]['drilldown']['y'][]=$rs[$key]['profit'];
		}
		$rsdata['ttl_profit']=$ttl_profit;
		$rsdata['data']=$cdata;
		
		echo json_encode($rsdata);
	}
	//供应商分析
	public function supply(){
		$purchase=M(I('session.DB').'purchase_all');
		$where['time']=array('like',date('Y-m').'%');
		$where['idgoods']=array('gt',0);
		$plist=$purchase->where($where)->field('idsupply,sum(number) as number,sum(sum) as sum')->order('sum desc')->group('idsupply')->page('0,10')->select();
		//print_r($plist);return;
		$data=array();
		$slist=F(I('session.DB').'supply');
		if(!$slist){
			$supply=M(I('session.DB').'supply');
			$slist=$supply->getField('idsupply,sname');
			F(I('session.DB').'supply',$slist);
		}
		
		foreach ($plist as $key => $value) {
			$temp=$value['idsupply'];
			//$temp=$supply->where($swhere)->field('sname')->find();
			$data['categories'][]=$slist[$temp];
			$data['number'][]=$value['number'];
			$data['sum'][]=$value['sum'];
		}
		//print_r($data);
		$this->assign('data',json_encode($data));
		$this->display("statistics/supply");
	}
	public function supplyAnalysis(){
		//echo "hello";return;
		$where['idgoods']=array('gt',0);
		if($_POST['idsupply']!=''){
			if($_POST['edate']&&$_POST['sdata'])
				$where['time']=array('between',array($_POST['sdate']." 00:00:00",$_POST['edate']." 23:59:59"));
			$where['idsupply']=$_POST['idsupply'];
			$purchase=M(I('session.DB').'purchase_all');
			$plist=$purchase->where($where)->field('sum(number) as number,sum(sum) as sum,date_format(time,"%Y-%m") as mytime')->order('mytime desc')->group('mytime')->page('0,13')->select();
			$data=array();
			foreach ($plist as $key => $value) {
				$data['categories'][]=$value['mytime'];
				$data['number'][]=$value['number'];
				$data['sum'][]=$value['sum'];
			}
			echo json_encode($data);
		}else{ 
			$where['time']=array('between',array($_POST['sdate']." 00:00:00",$_POST['edate']." 23:59:59"));
			$purchase=M(I('session.DB').'purchase_all');
			$plist=$purchase->where($where)->field('idsupply,sum(number) as number,sum(sum) as sum')->order('sum desc')->group('idsupply')->page('0,10')->select();
			//print_r($plist);return;
			$data=array();
			$slist=F(I('session.DB').'supply');
			if(!$slist){
				$supply=M(I('session.DB').'supply');
				$slist=$supply->getField('idsupply,sname');
				F(I('session.DB').'supply',$slist);
			}	
			foreach ($plist as $key => $value) {
				$temp=$value['idsupply'];
				//$temp=$supply->where($swhere)->field('sname')->find();
				$data['categories'][]=$slist[$temp];
				$data['number'][]=$value['number'];
				$data['sum'][]=$value['sum'];
			}
			echo json_encode($data);
		}
	}
	
	//客户分析
	public function member(){
		//echo "hello";return;
		$where['idgoods']=array('gt',0);
		$sale=M(I('session.DB').'sale_all');
		$where['time']=array('like',date('Y-m').'%');
		$plist=$sale->where($where)->field('idmember,sum(number) as number,sum(sum) as sum')->order('sum desc')->group('idmember')->page('0,10')->select();
		//print_r($plist);return;
		$data=array();
		$slist=F(I('session.DB').'member');
		if(!$slist){
			$supply=M(I('session.DB').'member');
			$slist=$supply->getField('idmmeber,mname');
			F(I('session.DB').'member',$slist);
		}
		
		foreach ($plist as $key => $value) {
			$temp=$value['idmember'];
			//$temp=$supply->where($swhere)->field('sname')->find();
			$data['categories'][]=$slist[$temp];
			$data['number'][]=$value['number'];
			$data['sum'][]=$value['sum'];
		}
		//print_r($data);return;
		$this->assign('data',json_encode($data));
		$this->display("statistics/member");
	}
	
	public function saleAnalysis(){
		//echo "hello";return;
		$where['idgoods']=array('gt',0);
		if($_POST['idmember']!=''){
			if($_POST['edate']&&$_POST['sdata'])
				$where['time']=array('between',array($_POST['sdate']." 00:00:00",$_POST['edate']." 23:59:59"));
			$where['idmember']=$_POST['idmember'];
			$sale=M(I('session.DB').'sale_all');
			$plist=$sale->where($where)->field('sum(number) as number,sum(sum) as sum,date_format(time,"%Y-%m") as mytime')->order('mytime desc')->group('mytime')->page('0,13')->select();
			$data=array();
			foreach ($plist as $key => $value) {
				$data['categories'][]=$value['mytime'];
				$data['number'][]=$value['number'];
				$data['sum'][]=$value['sum'];
			}
			echo json_encode($data);
		}else{
			$where['time']=array('between',array($_POST['sdate']." 00:00:00",$_POST['edate']." 23:59:59"));
			$sale=M(I('session.DB').'sale_all');
			$plist=$sale->where($where)->field('idmember,sum(number) as number,sum(sum) as sum')->order('sum desc')->group('idmember')->page('0,10')->select();
			//print_r($plist);return;
			$data=array();
			$slist=F(I('session.DB').'member');
			if(!$slist){
				$supply=M(I('session.DB').'member');
				$slist=$supply->getField('idmember,mname');
				F(I('session.DB').'member',$slist);
			}
			foreach ($plist as $key => $value) {
				$temp=$value['idmember'];
				//$temp=$supply->where($swhere)->field('sname')->find();
				$data['categories'][]=$slist[$temp];
				$data['number'][]=$value['number'];
				$data['sum'][]=$value['sum'];
			}
			echo json_encode($data);
		}
	}
	
	public function fee(){
		$this->display("statistics/fee");
	}
	public function feeTable(){
		$data=$_POST?$_POST:$_GET;
		//print_r($data); return;
		$bank=M(I('session.DB').'bank_detail');
		if($data['sdate']&&$data['edate']){
			$where['time']=array('between',array($data['sdate']." 00:00:00",$data['edate']." 23:59:59"));
			$info=$data['sdate']."到".$data['edate'];
			$params['edate']=$data['edate'];
			$params['sdate']=$data['sdate'];
		}else{
			$where['time']=array('like',date('Y-m').'%');
			$info=date('Y-m');
		}
		$where['idsubject']=array('neq',0);
		$p=$data['p']?$data['p']:0;
		if($data['summary']){
			$where['summary']=array('like','%'.$data['summary'].'%');
			$params['summary']=$data['summary'];
		}
		$count=$bank->where($where)->count();
		$Page=specialpage($count, $params);
		$blist=$bank->where($where)->field('time,sum,user,summary')->page($p.',10')->select();
		if($data['summary']){
			$where['summary']=array('like','费用:%'.$data['summary'].'%');
			$params['summary']=$data['summary'];
		}else{
			$where['summary']=array('like','费用:%');
		}
		$fee=$bank->where($where)->sum('sum');
		$fee=$fee*(-1);
		if($data['summary']){
			$where['summary']=array('like','收入:%'.$data['summary'].'%');
		}else{
			$where['summary']=array('like','收入:%');
		}
		$income=$bank->where($where)->sum('sum');	
		$this->assign('income',$income);
		$this->assign('fee',$fee);
		$this->assign('fee_table',$blist);
		$this->assign('info',$info);
		$show=$Page->show();
		$this->assign('page',$show);
		$this->display('statistics/feeTable');
	}
}
