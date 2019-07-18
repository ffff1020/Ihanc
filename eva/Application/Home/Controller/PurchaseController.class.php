<?php
namespace Home\Controller;
use Think\Controller;
class PurchaseController extends Controller {
	public function _initialize(){
		if(!is_login()){
			$this->redirect('login/index');
		}
	}
	public function index(){
		$this->display("purchase/index");
	}
    public function supply(){
    	$this->display("purchase/supply");
 	}
 	public function supplyTable(){
 		if($_GET['key']) 
 		$where['sname|ssn']=array('like','%'.$_GET['key'].'%');
 		if($_POST){
 			$where['sname|ssn']=array('like','%'.$_POST['name'].'%');
 		}
 		$purchase=M(I('session.DB').'supply');
 		$params=$_POST['name']?$_POST['name']:$_GET['key'];
 		$Page =getpage($purchase, $where,10,$params);
 		$show = $Page->show();
 		$p=$_GET['p']?$_GET['p']:0;
 		$list=$purchase->where($where)->page($p.',10')->select();
 		$this->assign("page",$show);
 		$this->assign("supply_list",$list);
 		$this->display("purchase/supplyTable");
 	}
 	
 	public function supplyUpdate(){
 		$supply=M(I('session.DB').'supply');
 		if($_POST['idsupply']){
 			$rs=$supply->save($_POST);
 			if($rs){
 				$this->success("保存成功");
 			}else{
 				$this->error("保存失败");
 			}
 		}else{
 			$where['sname']=$_POST['sname'];
 			$check=$supply->where($where)->count();
 			if($check>0){
 				$this->error("保存失败,该名称已存在");
 			}else{
 				$rs=$supply->add($_POST);
 				if($rs){
 					$slist=$supply->getField('idsupply,sname');
 					F(I('session.DB').'supply',$slist);
 					$this->success("保存成功");
 				}else{ $this->error("保存失败");
 				};
 			}
 		}
 	}
 	
 	public function searchSupply(){
 		$where['sname|ssn']=array('like','%'.$_POST['key'].'%');
 		$supply=M(I('session.DB').'supply');
 		$sname=$supply->where($where)->limit(0,10)->getField("idsupply,sname");
 		if(!$sname) return;
 		 foreach ( $sname as $key => $value ) {  
         $str['sname'][] = urlencode ( $value );  
         $str['idsupply'][]=$key;
         }  
         echo urldecode ( json_encode ( $str) ); 
 	}
 	
 	public function searchGoods(){
 		$where['gname|gsn']=array('like','%'.$_POST['key'].'%');
 		$supply=M(I('session.DB').'goods');
 		$sname=$supply->where($where)->limit(0,10)->getField("idgoods,gname");
 		if(!$sname) return;
 		foreach ( $sname as $key => $value ) {
 			$str['gname'][] = urlencode ( $value );
 			$str['idgoods'][]=$key;
 		}
 		echo urldecode ( json_encode ( $str) );
 	}
 	public function purchase(){
 		$where['inorder']=IN.date("Ymd").'-1';
 		$purchase=M(I('session.DB').'purchase_all');
 		$order=1;
 		while ($purchase->where($where)->count()){
 			$order++;
 			$where['inorder']=IN.date("Ymd").'-'.$order;
 		}
 		$order=$where['inorder'];
 		$this->assign('order',$order);
 		$staff=M(I('session.DB').'staff');
 		$stafflist=$staff->getField('idstaff,staff_name');
 		$temp="<select name='idstaff'>";
 		foreach ($stafflist as $key => $value) {
 			$temp=$temp."<option value='".$key."'>".$value."</option>";
 		}
 		$temp=$temp.'</select>';
 		$this->assign('stafflist',$temp);
 		$cat=M(I('session.DB').'categroy');
 		$list=$cat->select();
 		$this->assign("cat_list",$list);
 		
 		$this->display("purchase/purchase");
 	}
 	
 	public function purchaseTable(){
 		$purchase=M(I('session.DB').'purchase_all');
 		$where['idgoods']=array('gt',0); //查询进货信息,必须有idgoods,付款记录没有idgoods
 		$where['time']=array('like','%'.date("Y-m-d").'%');
 		if($_POST){
 			if($_POST['idsupply'])
 				$params['idsupply']=$where['idsupply']=$_POST['idsupply'];
 			if($_POST['sdate']&&$_POST['edate']){
 		    	$where['time']=array('between',array($_POST['sdate']." 00:00:00",$_POST['edate']." 23:59:59"));
 		   		$params['sdate']=$_POST['sdate'];
 		    	$params['edate']=$_POST['edate'];
 			}
 		}elseif($_GET){
 			if($_GET['idsupply'])
 				$params['idsupply']=$where['idsupply']=$_GET['idsupply'];
 			if($_GET['sdate']&&$_GET['edate']){
 				$where['time']=array('between',array($_GET['sdate']." 00:00:00",$_GET['edate']." 23:59:59"));
 				$params['sdate']=$_GET['sdate'];
 				$params['edate']=$_GET['edate'];
 			}
 		}
 		$count=$purchase->where($where)->field('count(distinct inorder) as count')->select();
 		$count=$count['0']['count'];
 		//echo $purchase->fetchSql(true)->field('inorder')->distinct(true)->count();
 		$Page =specialpage($count, $params);
 		$show = $Page->show();
 		$p=$_GET['p']?$_GET['p']:0;
 		$plist=$purchase->where($where)->field("sum(number) as ttl_num,inorder,sum(sum) as ttl_sum")->group('inorder')->order('time desc')->page($p.',10')->select();
 		$freight=M(I('session.DB').'freight');
 		foreach ($plist as $key => $value) {
 			$fwhere['inorder']=$value['inorder'];
 			$plist[$key]['ttl_fee']=$freight->where($fwhere)->sum('freight');
 		}
 		
 		$this->assign('purchase_list',$plist);
 		$this->assign("page",$show);
 		$this->display("purchase/purchaseTable");
 	}
 	//更新页面的合计金额
 	public function purchaseTableUpdate(){
 		//echo "hello";return;
 		$purchase=M(I('session.DB').'purchase');
 		$where['idgoods']=array('gt',0);
 		$where['inorder']=$_POST['inorder'];
 		$rs=$purchase->where($where)->field("sum(number) as ttl_num,sum(sum) as ttl_sum")->find();
 		echo json_encode($rs);
 	}
 	//删除进货记录
 	public function purchaseDel(){
 		$purchase=M(I('session.DB').'purchase');
 		$idpurchase=$_POST['idpurchase'];
 		$rs=$purchase->field('idgoods,inorder')->find($idpurchase);
 		$where['idgoods']=$rs['idgoods'];
 		$where['inorder']=$rs['inorder'];
 		$sale=M(I('session.DB').'sale');
 		$count=$sale->where($where)->count();
 		//echo $count;return;
 		if($count>0){
 			$this->error("该产品已经销售,不能删除");
 			return ;
 		}
 		$rs=$purchase->delete($idpurchase);
 		//echo $rs;
 		if($rs){
 			mylog("删除进货单idpurchase为:".$idpurchase);
 			$purchase=M(I('session.DB').'purchase_finish');
 			$purchase->delete($idpurchase);
 			$this->success("该进货单删除成功");
 			return;
 		}else {
 			$this->error("该进货单删除失败");
 			return ;
 		}
 	}
 	public function purchaseDetail(){
 		$where['inorder']=array('like',$_GET['inorder']);
 		$purchase=M(I('session.DB').'purchase_all');
 		$list=$purchase->where($where)->field("idsupply,idgoods,price,number,sum,idsupply,finish,idpurchase,inorder")->select();
 		$glist=F(I('session.DB').'goods');
 		if(!$glist){
 			$goods=M(I('session.DB').'goods');
 			$glist=$goods->getField('idgoods,gname');
 			F(I('session.DB').'goods',$glist);
 		}
 		$slist=F(I('session.DB').'supply');
 		if(!$slist){
 		 	 $supply=M(I('session.DB').'supply');
 		 	 $slist=$supply->getField('idsupply,sname');
 		 	 F(I('session.DB').'supply',$slist);
 		}
 		foreach ($list as $key => $value) {
 			$gid=$value['idgoods'];
 			$sid=$value['idsupply'];
 			$list[$key]['gname']=$glist[$gid];
 			$list[$key]['sname']=$slist[$sid];
 		}
 		$this->assign('pur_detail',$list);
 		
 		$freight=M(I('session.DB').'freight');
 		$flist=$freight->where($where)->field("idfreight,subject,freight,idstaff,status,remark,inorder")->select();
 		$staff=M(I('session.DB').'staff');
 		$staff_list=$staff->getField('idstaff,staff_name');
 		foreach ($flist as $key=>$value){
 			$idstaff=$value['idstaff'];
 			$flist[$key]['staff_name']=$staff_list[$idstaff];
 		}
 		$this->assign("freight_list",$flist);
 		
 		$this->display("purchase/purchaseDetail");
 	}
 	
 	public function purchaseAdd(){
 		$intime=date("Y-m-d H:i:s");
 		$pdata=$_POST['data'];
 		$inorder=$_POST['inorder'];
 		for ($i = 0; $i < sizeof($pdata); $i++) {
 			$pdata[$i]['inorder']=$inorder;
 			$pdata[$i]['time']=$intime;
 			$pdata[$i]['user']=cookie('nick');
 		}
 		$purchase=M(I('session.DB').'purchase');
 		$rs=$purchase->addAll($pdata);
 		if(!$rs){
 			echo "保存失败,请重新建进货单!";
 			mylog("保存失败,请重新建进货单!");
 			return;
 		}
 		//添加到库存stock表
 		$where['inorder']=$_POST['inorder'];
 		$slist=$purchase->where($where)->field('idgoods,sum(number) as stock,inorder')->group('idgoods')->select();
 		$stock=M(I('session.DB').'stock');
 		$rs=$stock->addAll($slist);
 		if(!$rs){
 			echo "库存保存失败,请联系管理员!请记录进货单号:".$inorder;
 			mylog("库存保存失败,请联系管理员!请记录进货单号:".$inorder);
 			return;
 		}
 		
 		$fee=$_POST['freight'];
 		if(sizeof($fee)>0){
 			for ($i = 0; $i < sizeof($fee); $i++) {
 				$fee[$i]['inorder']=$inorder;
 				$fee[$i]['time']=$intime;
 				$fee[$i]['user']=cookie('nick');
 			}
 			$freight=M(I('session.DB').'freight');
 			$rs=$freight->addAll($fee);
 			if(!$rs){
 				echo "进货费用保存失败,请联系管理员!请记录进货单号:".$inorder;
 				mylog("进货费用保存失败,请联系管理员!请记录进货单号:".$inorder);
 				return;
 			}
 		}
 		mylog("新建进货单"+$inorder);
 		echo "新建进货单成功!";
 	}
    public function purchaseUpdate(){
    	//print_r($_POST);return;
    	$purchase=M(I('session.DB').'purchase');
    	$where['idpurchase']=$_POST['idpurchase'];
    	$rs=$purchase->where($where)->save($_POST);
    	//echo $rs;return;
    	if($rs){
    		//数据库中的stock表进行自动更新,不必update
    		mylog("进货单".$_POST['idpurchase']."修改成功");
    		$this->success("进货单修改成功");
    		return;
    	}
    	mylog("进货单".$_POST['idpurchase']."修改失败");
    	$this->error("进货单修改失败");
    }	
    
    public function freightUpdate(){
    	//print_R($_POST);return;
    	$freight=M(I('session.DB').'freight');
    	$where['idfreight']=$_POST['idfreight'];
    	$rs=$freight->where($where)->save($_POST);
    	//echo $rs;return ;
    	if($rs){
    		mylog("进货单费用".$_POST['idfreight']."修改成功");
    		$this->success("进货单费用修改成功");
    		return;
    	}
    	mylog("进货单费用".$_POST['idfreight']."修改失败");
    	$this->error("进货单费用修改失败");
    }
    public function purchaseTableFreightUpdate(){
    	$freight=M(I('session.DB').'freight');
    	$where['inorder']=$_POST['inorder'];
    	$fee=$freight->where($where)->sum('freight');
    	echo $fee;
    }
    public function freightDel(){
    	$freight=M(I('session.DB').'freight');
    	$rs=$freight->delete($_POST['idfreight']);
    	if($rs){
    		mylog("删除进货费用idfreight:".$_POST['idfreight']);
    		$this->success("删除进货运费成功");
    		return;
    	}
    	$this->error("删除进货运费失败");
    }
    public function scredit(){
    	$this->display("purchase/scredit");
    }
    public function screditTable(){
    	$purchase=M(I('session.DB').'purchase');
    	$ttl=$purchase->sum(sum);
    	$ttl="应付款合计金额为:<em><font color='red'>￥".number_format($ttl)."</font></em>元";
    	$this->assign('ttl',$ttl);
    	$p=$_GET['p']?$_GET['p']:0;
    	if($_GET['idsupply']){
    		$count=1;
    		$plist[0]['sum']=$purchase->where($_GET)->sum('sum');
    		$supply=M(I('session.DB').'supply');
    		$sname=$supply->field('sname')->find($_GET['idsupply']);
    		$plist[0]['sname']=$sname['sname'];
    		$plist[0]['idsupply']=$_GET['idsupply'];
    	}else{
    		$count=$purchase->field('count(distinct idsupply) as count')->find();
    		$count=$count['count'];
    		$table[I('session.DB').'purchase']='purchase';
    		$table[I('session.DB').'supply']='supply';
    		$plist=$purchase->table($table)->where('purchase.idsupply=supply.idsupply')->field('purchase.idsupply as idsupply,sum(purchase.sum) as sum,supply.sname as sname')->group('idsupply')->page($p.',10')->select();
    	}
    	$Page=specialpage($count);
    	$show = $Page->show();
    	$this->assign("page",$show);
    	$this->assign("scredit_list",$plist);
    	$this->display("purchase/screditTable");
    }
    
    public function screditDetail(){
    	$purchase=M(I('session.DB').'purchase');
    	$plist=$purchase->where($_GET)->field('user',true)->order('idpurchase')->select();
    	$glist=F(I('session.DB').'goods');
    	if(!$glist){
    		$goods=M(I('session.DB').'goods');
    		$glist=$goods->getField('idgoods,gname');
    		F(I('session.DB').'goods',$glist);
    	}
    	foreach ($plist as $key => $value) {
    		$temp=$value['idgoods'];
    		$plist[$key]['gname']=$glist[$temp];
    		//$sum+=$value['sum'];
    	}
    	$this->assign('idsupply',$_GET['idsupply']);
    	//$this->assign('ttl',$sum);
    	$this->assign('scredit_detail_list',$plist);
    	$bank=M(I('session.DB').'bank');
    	$blist=$bank->field('idbank,bname')->order('weight desc')->select();
    	$this->assign('banklist',$blist);
    	$this->display("purchase/screditDetail");
    }
    /*逐笔结转*/
    function screditCf(){
    	//print_r($_GET);return;
    	$where['idpurchase']=array('in',$_GET['idpurchase']);
    	$where['idsupply']=$_GET['idsupply'];
    	$purchase=M(I('session.DB').'purchase');
    	$data['idsupply']=$_GET['idsupply'];
    	$data['user']=cookie('nick');
    	$data['sum']=$purchase->where($where)->sum('sum');
    	$data['cf']=1;
    	$data['remark']=$_GET['remark'];
    	$cfid=$purchase->add($data);
    	$data['sum']=$data['sum']*(-1);
    	$mcfid=$purchase->add($data);
    	$sdata['cf']=$cfid;
    	$rs=$purchase->where($where)->save($sdata);
    	if($rs){
    		$purchase->where($where)->delete();
    		$purchase->delete($mcfid);
    		$this->success("结转成功");
    		return;
    	}
    	mylog("结转错误,不能更新逐笔账单的cf!");
    	$this->error("结转错误,请联系管理员");
    }
    function screditPayment(){
    	$time=date('Y-m-d H:i:s');
    	/*A.$_post['idpurchase']的对应的sum总和与付款的金额相匹配,
    	 *   1.在purchase表中插入付款-sum,获取插入的idpurchase
    	 *   2.在bank_detail表中插入-sum
    	 *   3.将获得的idpurchase和$_post['idpurchase'],在purchase表中删除
    	 *B.$_post['idpurchase']为空,客户付款与idpurchase无关
    	 *	 case1:付款的金额==该客户总共的欠款
    	 *			1:在purchase表中插入付款-sum
    	 *          2.在bank_detail表中插入-sum
    	 *			3:在purchase表中删除所有idsupply的记录
    	 *	 case2:付款金额!=该客户总共的欠款,
    	 *          if:$_POST['cf']==0;
    	 *				1:在purchase表中插入付款-sum
    	 *         		2.在bank_detail表中插入-sum
    	 *          if:$_POST['cf']==1;
    	 *          	1:在purchase表中插入付款-sum
    	 *          	2.在bank_detail表中插入-sum
    	 *          	3.计算该客户的sum(sum),即付款后的总欠款scredit_sum
    	 *          	4.在purchase表中插入-credit_balance,cf=1 的记录
    	 *          	5.在purchase表中删除所有idsupply的记录
    	 *          	6.在purchase表中插入credit_balance,cf=1 的记录
    	 */
    	$purchase=M(I('session.DB').'purchase');
    	$bank_detail=M(I('session.DB').'bank_detail');
    	if($_POST['idpurchase']){  
    		//逐笔付款
    		$where['idpurchase']=array('in',$_POST['idpurchase']);
    		$where['idsupply']=$_POST['idsupply'];
    		$scredit_sum=$purchase->where($where)->sum('sum');
    		if($scredit_sum!=$_POST['sum']){
    			mylog("_post['idpurchase']中的付款总额与系统不符!");
    			$this->error("系统错误,请重新付款");
    			return;
    		}
    		$pdata['user']=cookie('nick');
    		$pdata['time']=$time;
    		$pdata['sum']=(-1)*$_POST['sum'];
    		$pdata['idsupply']=$_POST['idsupply'];
    		$insert_idpurchase=$purchase->add($pdata);
    		if(!$insert_idpurchase){
    			mylog("应付款插入purchase表错误.");
    			$this->error("系统错误,请重新付款");
    			return;
    		}
    		
    		$bank=M(I('session.DB').'bank');
    		$balance=$bank->field('sum')->find($_POST['idbank']);
    		$pdata['idbank']=$_POST['idbank'];
    		$pdata['balance']=$balance['sum']-$_POST['sum'];
    	    $slist=F(I('session.DB').'supply');
    	    if(!$slist){
    			$supply=M(I('session.DB').'supply');
    			$slist=$supply->getField('idsupply,sname');
    			F(I('session.DB').'supply',$slist);
    	    }
    	    $idsupply=$_POST['idsupply'];
    		$pdata['summary']="付款给供应商:".$slist[$idsupply];
    		$rs=$bank_detail->add($pdata);
    		if(!$rs){
    			mylog("应付款已经插入purchase表,插入bank_detail表错误.");
    			$this->error("系统错误,请联系管理员");
    			return;
    		}
    		  $rs=$purchase->where("idpurchase=$insert_idpurchase")->delete();
    		if($rs){
    			$result=$purchase->where($where)->delete();
    			if($result){
    				mylog("付款给供应商".$slist[$idsupply].",金额:".$_POST['sum']);
    				$this->success("付款给".$slist[$idsupply].",金额:".$_POST['sum']);
    				return;
    			}
    		}
    		mylog("purchase表中删除付款的记录发生错误.");
    		$this->error("系统错误,请联系管理员");
    		return;
    	}else{
    		//非逐笔付款
    		$where['idsupply']=$_POST['idsupply'];
    		$scredit_sum=$purchase->where($where)->sum('sum');
    		if($scredit_sum==$_POST['sum']){
    			//将以前的欠款付清
    			$pdata['user']=cookie('nick');
    			$pdata['time']=$time;
    			$pdata['sum']=(-1)*$_POST['sum'];
    			$pdata['idsupply']=$_POST['idsupply'];
    			$insert_idpurchase=$purchase->add($pdata);
    			if(!$insert_idpurchase){
    				mylog("应付款插入purchase表错误.");
    				$this->error("系统错误,请重新付款");
    				return;
    			}
    			$bank=M(I('session.DB').'bank');
    			$balance=$bank->field('sum')->find($_POST['idbank']);
    			$pdata['idbank']=$_POST['idbank'];
    			$pdata['balance']=$balance['sum']-$_POST['sum'];
    			$slist=F(I('session.DB').'supply');
    			if(!$slist){
    				$supply=M(I('session.DB').'supply');
    				$slist=$supply->getField('idsupply,sname');
    				F(I('session.DB').'supply',$slist);
    			}
    			$idsupply=$_POST['idsupply'];
    			$pdata['summary']="付款给供应商:".$slist[$idsupply];
    			$rs=$bank_detail->add($pdata);
    			if(!$rs){
    				mylog("应付款已经插入purchase表,插入bank_detail表错误.");
    				$this->error("系统错误,请联系管理员");
    				return;
    			}
    			$rs=$purchase->where($where)->delete();
    			if($rs){
    					mylog("付款给供应商".$slist[$idsupply].",金额:".$_POST['sum']);
    					$this->success("付款给".$slist[$idsupply].",金额:".$_POST['sum']);
    					return;
    			}
    			mylog("purchase表中删除付款的记录发生错误.");
    			$this->error("系统错误,请联系管理员");
    			return;
    		}else{
    			if($_POST['sum']!=0){
    				$pdata['user']=cookie('nick');
    				$pdata['time']=$time;
    				$pdata['sum']=(-1)*$_POST['sum'];
    				$pdata['idsupply']=$_POST['idsupply'];
    				$insert_idpurchase=$purchase->add($pdata);
    				if(!$insert_idpurchase){
    					mylog("应付款插入purchase表错误.");
    					$this->error("系统错误,请重新付款");
    					return;
    				}
    				
    				$bank=M(I('session.DB').'bank');
    				$balance=$bank->field('sum')->find($_POST['idbank']);
    				$pdata['idbank']=$_POST['idbank'];
    				$pdata['balance']=$balance['sum']-$_POST['sum'];
    				$slist=F(I('session.DB').'supply');
    				if(!$slist){
    					$supply=M(I('session.DB').'supply');
    					$slist=$supply->getField('idsupply,sname');
    					F(I('session.DB').'supply',$slist);
    				}
    				$idsupply=$_POST['idsupply'];
    				$pdata['summary']="付款给供应商:".$slist[$idsupply];
    				$rs=$bank_detail->add($pdata);
    				if(!$rs){
    					mylog("应付款已经插入purchase表,插入bank_detail表错误.");
    					$this->error("系统错误,请联系管理员");
    					return;
    				}
    			}
    				if($_POST['cf']==0){
    					mylog("付款给供应商".$slist[$idsupply].",金额:".$_POST['sum']);
    					$this->success("付款给:".$slist[$idsupply].",金额:".$_POST['sum']);
    				return;
    				}
    			    //进行结转,
    				$scredit_sum=$purchase->where($where)->sum('sum');
    				$cfdata['user']=cookie('nick');
    				$cfdata['time']=$time;
    				$cfdata['sum']=(-1)*$scredit_sum;
    				$cfdata['idsupply']=$_POST['idsupply'];
    				$cfdata['cf']=1;
    				$idcf=$purchase->add($cfdata);
    				if($idcf){
    					//$purchase->where($where)->delete();
    					$cfdata['sum']=$scredit_sum;
    					$rs=$purchase->add($cfdata);
    					if($rs){
    						$where['idpurchase']=array("not in",array($rs,$idcf));
    						$cf['cf']=$rs;
    						$purchase->where($where)->save($cf);
    						$where['idpurchase']=array("neq",$rs);
    						$purchase->where($where)->delete();
    						//$purchase->delete($idcf);
    						mylog("付款给供应商".$slist[$idsupply].",金额:".$_POST['sum']);
    						$this->success("付款给:".$slist[$idsupply].",金额:".$_POST['sum']);
    						return;
    					}
    				}
    				mylog("结转发生错误,没有在purchase表中进行结转插入.");
    				$this->error("结转失败,请联系管理员.");
    				return;
    		}
    	}
    }
    
    public function check(){
    	$this->display("purchase/check");
    }
    
    public function purchaseCheck(){
    	$data=$_POST?$_POST:$_GET;
    	if($data['edate']&&$data['sdate']){
    		$where['time']=array('between',array($data['sdate']." 00:00:00",$data['edate']." 23:59:59"));
    		$params['edate']=$data['edate'];
    		$params['sdate']=$data['sdate'];
    	}else{
    		$where['time']=array("like",'%'.date("Y-m").'%');
    	}
    	$p=$data['p']?$data['p']:0;
    	$params['idsupply']=$where['idsupply']=$data['idsupply'];
    	$purchase=M(I('session.DB').'purchase_all');
    	$count=$purchase->where($where)->count();
    	$ttl=$purchase->where($where)->sum('sum');
    	$check_list=$purchase->where($where)->field("time,idsupply,idgoods,number,price,sum,cf,finish")->order('idpurchase desc')->page($p.',10')->select();
    	$Page=specialpage($count, $params);
    	$show=$Page->show();
    	$glist=F(I('session.DB').'goods');
    	if(!$glist){
    		$goods=M(I('session.DB').'goods');
    		$glist=$goods->getField('idgoods,gname');
    		F(I('session.DB').'goods',$glist);
    	}
    	foreach ($check_list as $key => $value) {
    		$temp=$value['idgoods'];
    		$check_list[$key]['gname']=$glist[$temp];
    		//if($value['finish']==0) $ttl+=$value['sum'];
    	}
    	$this->assign('page',$show);
    	$this->assign('sum',$ttl);
    	$this->assign('check_list',$check_list);
    	$this->display("purchase/purchaseCheck");
    }
    
    public function checkPrint(){
    	$data=$_GET;
    	if($data['edate']&&$data['sdate']){
    		$where['time']=array('between',array($data['sdate']." 00:00:00",$data['edate']." 23:59:59"));
    		//$time=$data['sdate'].'-'.$data['edate'];
    	}else{
    		$where['time']=array("like",'%'.date("Y-m").'%');
    		//$time=date("Y-m");
    	}
    	$where['idsupply']=$data['idsupply'];
    	$purchase=M(I('session.DB').'purchase_all');
    	$check_list=$purchase->where($where)->field("time,idsupply,idgoods,number,price,sum,cf,finish")->order('idpurchase')->select();
    	$glist=F(I('session.DB').'goods');
    	if(!$glist){
    		$goods=M(I('session.DB').'goods');
    		$glist=$goods->getField('idgoods,gname');
    		F(I('session.DB').'goods',$glist);
    	}
    	$ttl_num=0;
    	$ttl_sum=0;
    	$ttl_paid=0;
    	foreach ($check_list as $key => $value) {
    		$temp=$value['idgoods'];
    		$check_list[$key]['gname']=$glist[$temp];
    		if($temp){
    			$ttl_num+=$value['number'];
    			$ttl_sum+=$value['sum'];
    		}else{
    			$ttl_paid+=$value['sum'];
    		}
    		
    	}
    	$info=M(I('session.DB').'info');
    	$info=$info->find(1);
    	$this->assign('info',$info);
    	$this->assign('ttl_num',$ttl_num);
    	$this->assign('ttl_sum',$ttl_sum);
    	$this->assign('ttl_paid',$ttl_paid*(-1));
    	$this->assign('check_list',$check_list);
    	$this->display("purchase/checkprint");
    }
    
    public function freight(){
    	$freight=M(I('session.DB').'freight');
    	$where['status']=0;
    	$freightlist=$freight->where($where)->field('idstaff,sum(freight) as freight')->group('idstaff')->select();
    	$staff=M(I('session.DB').'staff');
    	$stafflist=$staff->getField('idstaff,staff_name');
    	for ($i = 0; $i < sizeof($freightlist); $i++) {
    		$temp=$freightlist[$i]['idstaff'];
    		$freightlist[$i]['sname']=$stafflist[$temp];
    		$ttl+=$freightlist[$i]['freight'];
    	}
    	$ttl="员工垫付费用合计金额为:<em><font color='red'>￥".number_format($ttl)."</font></em>元";
    	$this->assign('ttl',$ttl);
    	$this->assign('freight_list',$freightlist);
    	$this->display("purchase/freight");
    }
    
    public function freightDetail(){
    	$freight=M(I('session.DB').'freight');
    	$where['status']=0;
    	$where['idstaff']=$_GET['idstaff'];
    	$freight_detail_list=$freight->where($where)->select();
    	$this->assign('freight_detail_list',$freight_detail_list);
    	$bank=M(I('session.DB').'bank');
    	$banklist=$bank->select();
    	$this->assign('banklist',$banklist);
    	$this->assign('idstaff',$_GET['idstaff']);
    	$this->display("purchase/freightDetail");
    }
    
    public function freightPayment(){
    	//print_r($_POST);return;
    	$freight=M(I('session.DB').'freight');
    	$bank=M(I('session.DB').'bank');
    	$balance=$bank->find($_POST['idbank']);
    	$time=date("Y-m-d H:i:s");

    	//插入freight表
    	$data['idstaff']=$where['idstaff']=$_POST['idstaff'];
    	$data['time']=$time;
    	$data['freight']=(-1)*$_POST['sum'];
    	$data['user']=cookie('nick');
    	$newid=$freight->add($data);
    	if(!$newid){
    		mylog("purchase-freightPayment-freight,不能插入freight");
    		$this->error("付款失败,请重新付款");
    		return;
    	}
    	//插入bank;
    	$bdata['time']=$time;
    	$bdata['idbank']=$_POST['idbank'];
    	$bdata['sum']=(-1)*$_POST['sum'];
    	$bdata['summary']="付款给员工:".$_POST['staff_name'];
    	$bdata['user']=cookie('nick');
    	$bdata['balance']=$balance['sum']-$_POST['sum'];
    	$bank_detail=M(I('session.DB').'bank_detail');
    	$rs=$bank_detail->add($bdata);
    	if(!$rs){
    		mylog("purchase-freightPayment-bank_detail,不能插入银行");
    		$this->error("付款失败,请重新付款");
    		return;
    	}
    	
    	if($_POST['idfreight']){
    		$where['idfreight']=array('in',$_POST['idfreight']);
    		$check=$freight->where($where)->sum('freight');
    	 	if($check!=$_POST['sum']){
    			mylog("purchase-freightPayment-check,付款金额不等于选择的idfreight");
    			$this->error("已经付款,没有删除员工垫付表,请联系管理员");
    			return;
    		}
    		$status['status']=1;
    		$rs=$freight->where($where)->save($status);
    		if(!$rs){
    			mylog("purchase-freightPayment-delete,删除idfreigh失败");
    			$this->error("已经付款,没有删除员工垫付表,请联系管理员");
    			return;
    		}
    		$status['idfreight']=$newid;
    		$rs=$freight->save($status);
    		if(!$rs){
    			mylog("purchase-freightPayment-delete,删除newid-freigh失败");
    			$this->error("已经付款,没有删除员工垫付表,请联系管理员");
    			return;
    		}
    	}else{
    		$status['status']=1;
    		$check['idstaff']=$_POST['idstaff'];
    		$sum=$freight->where($check)->sum('freight');
    		if($sum==0)
    			$rs=$freight->where($check)->save($status);
    		if(!$rs){
    			mylog("purchase-freightPayment-delete,删除freight失败");
    			$this->error("已经付款,没有删除员工垫付表,请联系管理员");
    			return;
    		}
    	}
    	mylog("员工垫付款付款成功");
    	$this->success("付款给员工成功");
    	
    }
    
    public function cfPrint(){
    	$purchase=M(I('session.DB').'purchase_all');
    	$where['cf']=$_GET['idpurchase'];
    	$plist=$purchase->where($where)->field('idgoods,sum,number,price,cf,time')->select();
    	$glist=F(I('seesion.DB').'goods');
    	if(!$glist){
    		$goods=M(I('session.DB').'goods');
    		$glist=$goods->getField('idgoods,gname');
    		F(I('seesion.DB').'goods',$glist);
    	}
    	$sum=0;
    	for ($i = 0; $i < sizeof($plist); $i++) {
    		$temp=$plist[$i]['idgoods'];
    		$plist[$i]['gname']=$glist[$temp];
    		$sum+=$plist[$i]['sum'];
    	}
    	//print_r($plist);
    	$info=$purchase->where($_GET)->field('remark,time,idsupply')->find();
    	$supply=M(I('session.DB').'supply');
    	$sname=$supply->Field('sname')->find($info['idsupply']);
    	$info['sname']=$sname['sname'];
    	$this->assign('plist',$plist);
    	$this->assign('ttl_sum',$sum);
    	$this->assign('info',$info);
    	$this->display('purchase/cfprint');
    	
    }
    public function purchaseprint(){
    	if(!$_GET) return;
    	$info=M(I('session.DB').'info');
    	$list=$info->find(1);
    	$this->assign('info',$list);
    	if($_GET['idpurchase']){
    		$purchase=M(I('session.DB').'purchase');
    		$where['idpurchase']=array('in',$_GET['idpurchase']);
    		$plist=$purchase->where($where)->field('price,number,sum,idgoods,time,cf')->order('idpurchase')->select();
    		$number=$sum=0;
    		$supply=M(I('session.DB').'supply');
    		$mlist=$supply->where($_GET)->field('sname')->find();
    		$this->assign('sname',$mlist['sname']);
    		$glist=F(I('session.DB').'goods');
    		if(!$glist){
    			$goods=M(I('session.DB').'goods');
    			$glist=$goods->getField('idgoods,gname');
    			F(I('session.DB').'goods',$glist);
    		}
    		foreach ($plist as $key => $value) {
    			$temp=$value['idgoods'];
    			$plist[$key]['gname']=$glist[$temp];
    			$sum+=$value['sum'];
    			$number+=$value['number'];
    		}
    		$this->assign('plist',$plist);
    		$this->assign('sum',$sum);
    		$this->assign('number',$number);
    		$this->display("purchase/purchaseprint");
    		return;
    	}
    	$supply=M(I('session.DB').'supply');
    	$mlist=$supply->where($_GET)->field('sname')->find();
    	$this->assign('sname',$mlist['sname']);
    	$glist=F(I('session.DB').'goods');
    	if(!$glist){
    		$goods=M(I('session.DB').'goods');
    		$glist=$goods->getField('idgoods,gname');
    		F(I('session.DB').'goods',$glist);
    	}
    	$purchase=M(I('session.DB').'purchase');
    	$plist=$purchase->where($_GET)->field('price,number,sum,idgoods,time,cf')->order('idpurchase')->select();
    	$number=$sum=0;
    	foreach ($plist as $key => $value) {
    		$temp=$value['idgoods'];
    		$plist[$key]['gname']=$glist[$temp];
    		$sum+=$value['sum'];
    		$number+=$value['number'];
    	}
    	$this->assign('plist',$plist);
    	$this->assign('sum',$sum);
    	$this->assign('number',$number);
    	$this->display("purchase/purchaseprint");
    }
    public function psale(){
    	$this->display("purchase/psale");
    }
    public function psaleAdd(){       //即买即卖添加记录
    	$where['inorder']="JIN".date("Ymd").'-1';
 		$purchase=M(I('session.DB').'purchase_all');
 		$order=1;
 		while ($purchase->where($where)->count()){
 			$order++;
 			$where['inorder']="JIN".date("Ymd").'-'.$order;
 		}
 		$inorder=$where['inorder'];
 		$intime=date("Y-m-d H:i:s");
 		$pdata=$_POST['pdata'];
 		for ($i = 0; $i < sizeof($pdata); $i++) {
 			$pdata[$i]['inorder']=$inorder;
 			$pdata[$i]['time']=$intime;
 			$pdata[$i]['user']=cookie('nick');
 		}
 		$purchase=M(I('session.DB').'purchase');
 		$rs=$purchase->addAll($pdata);
 		if(!$rs){
 			echo "保存失败,请重新建即买即卖单!";
 			mylog("即买即卖保存失败,请重新建即买即卖单!");
 			return;
 		}
 		$sdata=$_POST['sdata'];
 		for ($i = 0; $i < sizeof($sdata); $i++) {
 			$sdata[$i]['inorder']=$inorder;
 			$sdata[$i]['time']=$intime;
 			$sdata[$i]['user']=cookie('nick');
 		}
 		$sale=M(I('session.DB').'sale');
 		$rs=$sale->addAll($sdata);
 		if(!$rs){
 			echo "保存销售单失败,请联系管理员!";
 			mylog("即买即卖，销售单保存失败！");
 			return;
 		}
 		echo(0);
 		mylog("即买即卖保存成功！");
    }
    
}