<?php
namespace Home\Controller;
use Think\Controller;
class SaleController extends Controller {
	public function _initialize(){
		if(!is_login()){
			$this->redirect('login/index');
		}
	}
	public function index(){
		$this->display("sale/index");
	}
	public function member(){
		$this->display("sale/member");
	}
	public function memberTable(){
		if($_GET['key'])
			$where['mname|msn']=array('like','%'.$_GET['key'].'%');
		if($_POST){
			$where['mname|msn']=array('like','%'.$_POST['name'].'%');
		}
		$sale=M(I('session.DB').'member');
		$params=$_POST['name']?$_POST['name']:$_GET['key'];
		$Page =getpage($sale, $where,10,$params);
		$show = $Page->show();
		$p=$_GET['p']?$_GET['p']:0;
		$list=$sale->where($where)->page($p.',10')->select();
		$this->assign("page",$show);
		$this->assign("member_list",$list);
		$this->display("sale/memberTable");
	}
	public function memberUpdate(){
		$member=M(I('session.DB').'member');
		if($_POST['idmember']){
			$rs=$member->save($_POST);
			if($rs){
				$this->success("保存成功");
			}else{
				$this->error("保存失败");
			}
		}else{
			$where['mname']=$_POST['mname'];
			$check=$member->where($where)->count();
			if($check>0){
				$this->error("保存失败,该名称已存在");
			}else{
				$rs=$member->add($_POST);
				if($rs){
					$mlist=$member->getField('idmember,mname');
					F(I('session.DB').'member',$mlist);
					$this->success("保存成功");
				}else{ $this->error("保存失败");
				};
			}
		}
	}
	
	public function sale(){
		$bank=M(I('session.DB').'bank');
		$blist=$bank->field('idbank,bname')->order('weight desc')->select();
		$this->assign('bank_list',$blist);
		$this->display("sale/sale");
	}
	
	public function searchMember(){
		
		$where['mname|msn']=array('like','%'.$_POST['key'].'%');
		$member=M(I('session.DB').'member');
		$sname=$member->where($where)->limit(0,10)->getField("idmember,mname");
		if(!$sname) return;
		foreach ( $sname as $key => $value ) {
			$str['mname'][] = urlencode ( $value );
			$str['idmember'][]=$key;
		}
		echo urldecode ( json_encode ( $str) );
	}
	
	public function getCredit(){
		//echo "hello";return;
		if($_GET){
			$sale=M(I('session.DB').'sale');
			$credit=$sale->where($_GET)->sum('sum');
			echo $credit;
		}
	}
	public function searchStockGoods(){
		$where['gname|gsn']=array('like','%'.$_POST['key'].'%');
		$stockgoods=M(I('session.DB').'stock_goods_view');
		$list=$stockgoods->where($where)->field('gsn',true)->select();
		if(!$list) return;
		foreach ( $list as $key => $value ) {
			$str['gname'][] = urlencode ( $value['gname'] );
			$str['idgoods'][]=$value['idgoods'];
			$str['inorder'][]=$value['inorder'];
			$str['stock'][]=number_format($value['stock'],2);
		}
		echo urldecode ( json_encode ( $str) );
	}
	
	public function saleAdd(){
		//print_r($_POST);return;
		$time=date("Y-m-d H:i:s");
		if(!$_POST['idmember']){$this->error("系统错误,请刷新系统");return;};
		if($_POST['idgoods']&&$_POST['inorder']&&$_POST['sum']&&$_POST['number']&&$_POST['price']){
			$data=$_POST;
			//$data['sum']=(-1)*$data['sum'];
			$data['time']=$time;
			$data['user']=cookie('nick');
			$sale=M(I('session.DB').'sale');
			$rs=$sale->add($data);
			if(!$rs){
				mylog("系统插入sale表错误!");
				$this->error("系统错误,请刷新系统");
				return;
			}
		}
		if($_POST['paid']){
			//非逐笔收款
			$bdata['mname']=$_POST['mname'];
			$bdata['idmember']=$_POST['idmember'];
			$bdata['sum']=$_POST['paid'];
			$bdata['time']=$time;
			$bdata['idbank']=$_POST['idbank'];
			$result=$this->commonCollect($bdata);
			switch ($result) {
				case 1:
				      mylog("银行收款".$_POST['mname'].$_POST['sum'].'未成功!');
				      if($rs){
				      	$info="销售单保存成功,";
				      }
				      $this->error($info."收款失败,重新收款");
				      return;
				break;
				
				case 2:
					mylog($_POST['idmember']."银行收款成功!,为在sale表中插入收款");
					$bank_detail=M(I('session.DB').'bank_detail');
					$where['time']=$time;
					$bankrs=$bank_detail->delete($where);
					if($rs){
						$info="销售单保存成功,";
					}
					if(!$bankrs){
						mylog($_POST['idmember']."银行收款成功!,为在sale表中插入收款,不能删除bank_detail表的记录");
						$this->error("系统错误,请联系管理员");
						return;
					}
					$this->error($info."收款失败,重新收款");
					return;
					break;
				
				case 3:
					mylog($_POST['idmember'].$_POST['mname']."销售单保存成功,"."收款".$_POST['paid']);
					if($rs){
						$info="销售单保存成功,";
					}
					$this->success($info.$_POST['mname']."收款￥".number_format($_POST['paid']));
					return;
					break;
				default:
				break;
			}
		}
		mylog($_POST['idmember'].$_POST['mname']."销售单保存成功");
		$this->success("销售单保存成功");
	}
	//非逐笔收款且不结转
	public function commonCollect($bdata){
		$bank=M(I('session.DB').'bank');
		$idbank=$bdata['idbank'];
		$balance=$bank->field('sum')->find($idbank);
		$bdata['user']=cookie('nick');
		$bdata['summary']="客户付款:".$bdata['mname'];
		$bdata['balance']=$bdata['sum']+$balance['sum'];
		$bank_detail=M(I('session.DB').'bank_detail');
		$brs=$bank_detail->add($bdata);
		if(!$brs){
			return 1;     //付款银行未插入
		}
		
		$sale=M(I('session.DB').'sale');
		$bdata['sum']=(-1)*$bdata['sum'];
		$bdata['idgoods']=0;
		$srs=$sale->add($bdata);
		if(!$srs){
			return 2;  //银行插入后,在sale表中插入不成功.
		}	
		$where['idmember']=$bdata['idmember'];
		$sum=$sale->where($where)->sum('sum');
		if($sum==0){
			$rs=$sale->where($where)->delete();
		}
		return 3;  //成功
	}
	
	public function getStock(){
		$stock=M(I('session.DB').'stock');
		$number=$stock->where($_GET)->field('stock')->find();
		echo $number['stock'];
	}
	/*逐笔结转*/
	function mcreditCf(){
		//print_r($_GET);return;
		$where['idsale']=array('in',$_GET['idsale']);
		$where['idmember']=$_GET['idmember'];
		$sale=M(I('session.DB').'sale');
		$data['idmember']=$_GET['idmember'];
		$data['user']=cookie('nick');
		$data['sum']=$sale->where($where)->sum('sum');
		$data['cf']=1;
		$data['remark']=$_GET['remark'];
		$cfid=$sale->add($data);
		$data['sum']=$data['sum']*(-1);
		$mcfid=$sale->add($data);
		$sdata['cf']=$cfid;
		$rs=$sale->where($where)->save($sdata);
		if($rs){
			$sale->where($where)->delete();
			$sale->delete($mcfid);
			$this->success("结转成功");
			return;
		}
		mylog("结转错误,不能更新逐笔账单的cf!");
		$this->error("结转错误,请联系管理员");
	}
	
	public function mcreditDetail(){
		$sale=M(I('session.DB').'sale');
		$plist=$sale->where($_GET)->field('user',true)->order('idsale')->select();
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
		}
		$this->assign('idmember',$_GET['idmember']);
		$this->assign('ttl',$sum);
		$this->assign('mcredit_detail_list',$plist);
		$bank=M(I('session.DB').'bank');
		$blist=$bank->field('idbank,bname')->order('weight desc')->select();
		$this->assign('banklist',$blist);
		$this->display("sale/mcreditDetail");
	}
	
	public function mcreditPayment(){
		$time=date('Y-m-d H:i:s');
		//逐笔收款
		$sale=M(I('session.DB').'sale');
		$bank_detail=M(I('session.DB').'bank_detail');
		if($_POST['idsale']){
			$where['idsale']=array('in',$_POST['idsale']);
			$where['idmember']=$_POST['idmember'];
			$mcredit_sum=$sale->where($where)->sum('sum');
			if($mcredit_sum!=$_POST['sum']){
				mylog("_post['idsale']中的付款总额与系统不符!");
				$this->error("系统错误,请重新付款");
				return;
			}
			$pdata['user']=cookie('nick');
			$pdata['time']=$time;
			$pdata['sum']=(-1)*$_POST['sum'];
			$pdata['idmember']=$_POST['idmember'];
			$pdata['idgoods']=0;
			$insert_idsale=$sale->add($pdata);
			if(!$insert_idsale){
				mylog("逐笔收款,应收款插入sale表错误.");
				$this->error("系统错误,请重新付款");
				return;
			}
			
			$bank=M(I('session.DB').'bank');
			$balance=$bank->field('sum')->find($_POST['idbank']);
			$pdata['idbank']=$_POST['idbank'];
			$pdata['sum']=$_POST['sum'];
			$pdata['balance']=$balance['sum']+$_POST['sum'];
			$idmember=$_POST['idmember'];
			$pdata['summary']="客户逐笔付款:".$_POST['mname'];
			$rs=$bank_detail->add($pdata);
			if(!$rs){
				mylog("逐笔收款-应收款已经插入sale表,插入bank_detail表错误.");
				$this->error("系统错误,请联系管理员");
				return;
			}
			$rs=$sale->where("idsale=$insert_idsale")->delete();
			if($rs){
				$result=$sale->where($where)->delete();
				if($result){
					mylog("客户逐笔付款".$_POST['mname'].",金额:￥".$_POST['sum']);
					$this->success("收款".$_POST['mname'].",金额:￥".number_format($_POST['sum']));
					return;
				}
			}
			mylog("逐笔收款-sale表中删除付款的记录发生错误.");
			$this->error("系统错误,请联系管理员");
			return;			
		}else{
			//非逐笔付款,先在银行中插入该收入
			$bank=M(I('session.DB').'bank');
			$idbank=$_POST['idbank'];
			$balance=$bank->field('sum')->find($idbank);
			$bdata['user']=cookie('nick');
			$bdata['summary']="客户非逐笔付款:".$_POST['mname'];
			$bdata['balance']=$_POST['sum']+$balance['sum'];
			$bdata['sum']=$_POST['sum'];
			$bank_detail=M(I('session.DB').'bank_detail');
			$bdata['idbank']=$_POST['idbank'];
			$brs=$bank_detail->add($bdata);
			if(!$brs){
				mylog("非逐笔收款-银行插入未成功!");
				$this->error("系统错误,请刷新页面重新操作");
				return;
			}
			
			
			//插入sale表
			$sdata['user']=cookie('nick');
			$sdata['time']=$time;
			$sdata['sum']=(-1)*$_POST['sum'];
			$sdata['idmember']=$_POST['idmember'];
			$sdata['idgoods']=0;
			$insert_idsale=$sale->add($sdata);
			if(!$insert_idsale){
				mylog("非逐笔收款-银行收款-应收款插入sale表错误.");
				$this->error("系统错误,联系管理员");
				return;
			}
			$where['idmember']=$_POST['idmember'];
			$mcredit_sum=$sale->where($where)->sum('sum');
			if($mcredit_sum==0){
				$srs=$sale->where($where)->delete();
				if(!$srs){
					mylog("非逐笔收款-银行收款-应收款插入sale表-删除sale表错误.");
					$this->error("系统错误,联系管理员");
					return;
				}
			}elseif ($_POST['cf']==1){
				$sdata['sum']=(-1)*$mcredit_sum;
				$sdata['cf']=1;$sdata['idgoods']=NULL;
				$mcfrs=$sale->add($sdata);
				if(!$mcfrs){
					mylog("非逐笔收款-银行收款-应收款插入sale表-sale表中插入''cf'错误.");
					$this->error("系统错误,联系管理员");
					return;
				}
				
				$sdata['sum']=$mcredit_sum;
				$cfrs=$sale->add($sdata);
				if(!$cfrs){
					mylog("非逐笔收款-银行收款-应收款插入sale表-sale表中插入''cf'-插入'cf'错误.");
					$this->error("系统错误,联系管理员");
					return;
				}
				$where['idsale']=array('neq',$cfrs);
				$cfrs=$sale->where($where)->setField('cf',$cfrs);
				$cfrs=$sale->where($where)->delete();
				if(!$cfrs){
					mylog("非逐笔收款-银行收款-应收款插入sale表-sale表中插入''cf'-删除sale表错误.");
					$this->error("系统错误,联系管理员");
					return;
				}
			}
			mylog("非逐笔应收款成功.");
			$this->success("收款".$_POST['mname'].",金额:￥".number_format($_POST['sum']));
		}
	}
	
	public function saleprint(){
		if(!$_GET) return;
		$info=M(I('session.DB').'info');
		$list=$info->find(1);
		$this->assign('info',$list);
		if($_GET['idsale']){
			$sale=M(I('session.DB').'sale');
			$where['idsale']=array('in',$_GET['idsale']);
			$slist=$sale->where($where)->field('price,number,sum,idgoods,time,cf')->order('idsale')->select();
			$number=$sum=0;
			$member=M(I('session.DB').'member');
			$mlist=$member->where($_GET)->field('mname')->find();
		//	$cwhere['idmember']=$_GET['idmember'];
		//	$credit=$sale->where($cwhere)->sum('sum');
		//	$this->assign('credit',$credit);
			$this->assign('mname',$mlist['mname']);
			$glist=F(I('session.DB').'goods');
			if(!$glist){
				$goods=M(I('session.DB').'goods');
				$glist=$goods->getField('idgoods,gname');
				F(I('session.DB').'goods',$glist);
			}
			foreach ($slist as $key => $value) {
				$temp=$value['idgoods'];
				$slist[$key]['gname']=$glist[$temp];
				$sum+=$value['sum'];
				$number+=$value['number'];
			}
			
			$this->assign('slist',$slist);
			$this->assign('sum',$sum);
			$this->assign('number',$number);
			if($list['print']>0){
			$len=sizeof($slist)+1;
			if(($len%$list['print'])!=0){ 
			if((($len%$list['print'])*1.5)>$list['print']) $len=$len+1; 
			 }
			$this->assign('len',$len);
			$this->assign('print',$list['print']);
			$this->display("sale/saleprint_lodop");
			return;
		}
			$this->display("sale/saleprint");
			return;
		}
		$member=M(I('session.DB').'member');
		$mlist=$member->where($_GET)->field('mname')->find();
		$this->assign('mname',$mlist['mname']);
		$glist=F(I('session.DB').'goods');
		if(!$glist){
			$goods=M(I('session.DB').'goods');
			$glist=$goods->getField('idgoods,gname');
			F(I('session.DB').'goods',$glist);
		}
		$sale=M(I('session.DB').'sale');
		$slist=$sale->where($_GET)->field('price,number,sum,idgoods,time,cf')->order('idsale')->select();
		$number=$sum=0;
		foreach ($slist as $key => $value) {
			$temp=$value['idgoods'];
			$slist[$key]['gname']=$glist[$temp];
			$sum+=$value['sum'];
			$number+=$value['number'];
		}
		$this->assign('slist',$slist);
		$this->assign('sum',$sum);
		$this->assign('number',$number);
		if($list['print']>0){
			$len=sizeof($slist)+1;
		if(($len%$list['print'])!=0){ 
			if((($len%$list['print'])*1.5)>$list['print']) $len=$len+1; 
			 }
			$this->assign('len',$len);
			$this->assign('print',$list['print']);
			$this->display("sale/saleprint_lodop");
			return;
		}
		$this->display("sale/saleprint");
	}
	
	public function saleTable(){
		    $data=$_POST?$_POST:$_GET;
			$p=$data['p']?$data['p']:1;
			if($data['sdate']&&$data['edate']){
				$info=substr($data['sdate'],5)."至".substr($data['edate'],5);
				$params['sdate']=$data['sdate'];
				$params['edate']=$data['edate'];
				$where['time']=array('between',array($data['sdate']." 00:00:00",$data['edate']." 23:59:59"));
			}else{
				$info="今日";
				$where['time']=array('like','%'.date('Y-m-d').'%');
			}
			if($data['idmember']){
				$params['idmember']=$data['idmember'];
				$where['member.idmember']=$data['idmember'];
			}
			if($data['inorder']){
				$params['inorder']=$data['inorder'];
				$where['inorder']=$data['inorder'];
				unset($where['time']);
				$info=$where['inorder'];
			}
		$sale=M(I('session.DB').'sale_all');
		if($data['idgoods']>0){
			$where['idgoods']=$data['idgoods'];
			$params['idgoods']=$data['idgoods'];
		}else{
		    $where['idgoods']=array('gt',0);
		}
		$count=$sale->join("RIGHT JOIN ".I('session.DB').'member'." ON sale_all.idmember= member.idmember" )->where($where)->count();
		$slist=$sale->join("RIGHT JOIN ".I('session.DB').'member'." ON sale_all.idmember= member.idmember" )->where($where)->field('sale_all.time,sale_all.idgoods,sale_all.idmember,sale_all.number,sale_all.price,sale_all.sum,member.mname,finish,sale_all.idsale,sale_all.idintime')->order('idsale desc')->page($p.',10')->select();
		//echo $slist;return;
		$glist=F(I('session.DB').'goods');
		if(!$glist){
			$goods=M(I('session.DB').'goods');
			$glist=$goods->getField('idgoods,gname');
			F(I('session.DB').'goods',$glist);
		}
		$Page=specialpage($count, $params);
		$show=$Page->show();
		$this->assign("page",$show);
		$ttl_num=0;
		foreach ($slist as $key => $value) {
			$temp=$value['idgoods'];
			$slist[$key]['gname']=$glist[$temp];
			$ttl_num+=$value['number'];
		}
		$this->assign('ttl_num',$ttl_num);
		$this->assign("sale_list",$slist);
		$this->assign('info',$info."销售单");
		$this->display("sale/saleTable");
	}	
	
	public function saleModify(){
		$str=I('session.DB');
		$str=substr($str,6,-1);
		$condition['db']=$str;
		$admin=M('user');
		$condition['pwd']=md5($_GET['pwd']);
		$result=$admin->where($condition)->select();
		if(!$result){
			$this->error("密码输入错误,请重新输入");
			return;
		}
		$sale=M(I('session.DB').'sale');
		$data['number']=(float)$_GET['number'];
		$data['sum']=(int)$_GET['sum'];
		$data['price']=(float)$_GET['price'];
		$where['idsale']=$_GET['idsale'];
		$rs=$sale->where($where)->setField($data);
		//echo $sale->fetchSql(true)->where($where)->setField($data);
		//return;
		if($rs){
			mylog('idsale='.$_GET['idsale']."修改成功".'number:'.$_GET['number'].'sum:'.$_GET['sum'].'price:'.$_GET['price']);
			$this->success("修改成功!");
			return;
		}
		$this->error("修改失败,请刷新");
		return;
	}
	
	public function saleDel(){ 
		    $str=I('session.DB');
		    $str=substr($str,6,-1);
		    $condition['db']=$str;
		    $admin=M('user');
			$condition['pwd']=md5($_GET['pwd']);
			$result=$admin->where($condition)->select();
		if(!$result){
			$this->error("密码输入错误,请重新输入");
			return;
		}
		$sale=M(I('session.DB').'sale');
		$rs=$sale->delete($_GET['idsale']);
		//$this->success($rs);return;
		if($rs){
			$sale=M(I('session.DB').'sale_finish');
			$sale->delete($_GET['idsale']);
			mylog('idsale='.$_GET['idsale']."删除成功");
			$this->success("删除成功!");
			return;
		}
		$this->error("删除失败,请刷新");
		return;
	}
	
	public function mcredit(){
		$sale=M(I('session.DB').'sale');
		$ttl=$sale->sum('sum');
		$this->assign("ttl",$ttl);
		$this->display("sale/mcredit");
	}
	public function mcreditTable(){
		$sale=M(I('session.DB').'sale');
		$p=$_GET['p']?$_GET['p']:0;
		if($_GET['idmember']==''){
			$count=$sale->field('count(distinct idmember) as count')->find();
			$Page=specialpage($count['count']);
			$slist=$sale->join("left JOIN ".I('session.DB').'member'." ON sale.idmember= member.idmember" )->field('sale.idmember,sum(sale.sum) as sum,member.mname')->group('sale.idmember')->page($p.',10')->select();
		}else{
			$count=1;
			$Page=specialpage(1);
			$slist[0]['sum']=$sale->where($_GET)->sum('sum');
			$member=M(I('session.DB').'member');
			$mname=$member->field('mname')->find($_GET['idmember']);
			$slist[0]['mname']=$mname['mname'];
			$slist[0]['idmember']=$_GET['idmember'];
		}
		$this->assign("mcredit_list",$slist);
		$this->assign("page",$Page->show());
		$this->display("sale/mcreditTable");
	}
	
	public function saleReport(){
		$sale=M(I('session.DB').'sale_all');
		$where['time']=array('like','%'.date('Y-m-d').'%');
		$where['idgoods']=array('gt',0);
		$slist=$sale->where($where)->field('idgoods,sum(sum) as sum,sum(number) as number,finish')->group('idgoods,finish')->select();
		$glist=F(I('session.DB').'goods');
		if(!$glist){
			$goods=M(I('session.DB').'goods');
			$glist=$goods->getField('idgoods,gname');
			F(I('session.DB').'goods',$glist);
		}
		$temp=0;
		for ($i = 0; $i <sizeof($slist); $i++) {
			$ttl_sum+=$slist[$i]['sum'];
			$ttl_num+=$slist[$i]['number'];
			if($slist[$i]['finish']==0){
				$slist[$i]['cnum']=$slist[$i]['number'];
				$slist[$i]['csum']=$slist[$i]['sum'];
				//$slist[$i]['number']=$slist[$i]['sum']=0;
			}
			if($slist[$i]['idgoods']==$temp){
				$slist[$i]['idgoods']=0;
				$j=$i-1;
				if($slist[$j]['cnum']){
					$slist[$j]['number']=$slist[$i]['number']+$slist[$j]['cnum'];
					$slist[$j]['sum']=$slist[$i]['sum']+$slist[$j]['csum'];
				}else{
					$slist[$j]['cnum']=$slist[$i]['cnum'];
					$slist[$j]['csum']=$slist[$i]['csum'];
				}
			}else{
				$temp=$slist[$i]['idgoods'];
				$slist[$i]['gname']=$glist[$temp];
			}
			
		}
		$cash=$sale->query("SELECT sum(sum) as sum FROM ".I('session.DB')."sale_all WHERE `time` LIKE '%".date("Y-m-d")."%' AND idgoods=0 LIMIT 1 ");
		$this->assign('cash',$cash[0]['sum']*(-1));
		$this->assign('sinfo',$slist);
		$this->assign('ttl_sum',$ttl_sum);
		$this->assign('ttl_num',$ttl_num);
		$this->display('sale/salereport');
	}
	
	public function check(){
		$this->display("sale/check");
	}
	
	public function salecheck(){
		if($_GET['edate']&&$_GET['sdate']){
			$where['time']=array('between',array($_GET['sdate']." 00:00:00",$_GET['edate']." 23:59:59"));
			//$time=substr($_GET['sdate'],5)."到".substr($_GET['edate'],5);
		}else{
			$where['time']=array("like",'%'.date("Y-m-d").'%');
			//$time="今日";
		}
		if($_GET['time']) $where['time']=date("Y-m-d H:i:s", $_GET['time']);
		$where['idmember']=$_GET['idmember'];
		$sale=M(I('session.DB').'sale_all');
		$slist=$sale->where($where)->order('idsale')->select();
		$glist=F(I('session.DB').'goods');
		if(!$glist){
			$goods=M(I('session.DB').'goods');
			$glist=$goods->getField('idgoods,gname');
			F(I('session.DB').'goods',$glist);
		}
		for ($i = 0; $i <sizeof($slist); $i++) {
			$temp=$slist[$i]['idgoods'];
			if($temp){
			    $slist[$i]['gname']=$glist[$temp];
			    $ttl_sum+=$slist[$i]['sum'];
			}else $ttl_paid+=$slist[$i]['sum']*(-1);
			$ttl_num+=$slist[$i]['number'];
		}
		$this->assign('slist',$slist);
		$info=M(I('session.DB').'info');
		$info=$info->find(1);
		$this->assign('info',$info);
		$this->assign('ttl_num',$ttl_num);
		$this->assign('ttl_sum',$ttl_sum);
		$this->assign('ttl_paid',$ttl_paid);
		if($info['print']>0){
			$len=sizeof($slist)+1;
		if(($len%$info['print'])!=0){ 
			if((($len%$info['print'])*1.5)>$info['print']) $len=$len+1; 
			 } 
			$this->assign('len',$len);
			$this->assign('print',$info['print']);
			$this->display("sale/checkprint_lodop");
			return;
		}
		$this->display("sale/checkprint");
	}
	//客户要求按照赊账的笔数进行排序
	public function printAll(){
		$glist=F(I('session.DB').'goods');
		if(!$glist){
			$goods=M(I('session.DB').'goods');
			$glist=$goods->getField('idgoods,gname');
		}
		$modol=M();
		$ttl_sum=$ttl_paid=0;
		$rs=$modol->query("select mname,sum(sum) as sum,count(*) as mycount,sale.idmember as idmember from ".I('session.DB')."sale,".I('session.DB')."member where sale.idmember=member.idmember and sum<>0 group by sale.idmember order by mycount;");
		//$sale=M(I('session.DB').'sale');
		$i=0;
		foreach ($rs as $key => $value) {
			$printrs[$i]['mname']=$value['mname'];
			$printrs[$i]['ttl_sum']=$value['sum'];
			$ttl_paid=0;
			$temp=$modol->query("select DATE_FORMAT(time,'%m%d') as time,number,idgoods,price,sum,cf from ".I('session.DB')."sale where idmember=".$value['idmember']."  order by time;");
			foreach ($temp as $tkey => $tvalue) {
				$printrs[$i]['time']=$tvalue['time'];
				$printrs[$i]['number']=$tvalue['number'];
				$idgoods=$printrs[$i]['idgoods']=$tvalue['idgoods'];
				$printrs[$i]['price']=$tvalue['price'];
				$printrs[$i]['gname']=$glist[$idgoods];
				$printrs[$i]['sum']=$tvalue['sum'];
				$printrs[$i]['cf']=$tvalue['cf'];
				$i++;
			}
		}
		$this->assign('rs',$printrs);
		$this->display("sale/printAll");
	}
	
	
	/*public function printAll(){
		$glist=F(I('session.DB').'goods');
		if(!$glist){
			$goods=M(I('session.DB').'goods');
			$glist=$goods->getField('idgoods,gname');
		}
		$modol=M();
		$ttl_sum=$ttl_paid=0;
		$rs=$modol->query("select DATE_FORMAT(time,'%m%d') as time,mname,number,idgoods,price,sum,cf from ".I('session.DB')."sale,".I('session.DB')."member where sale.idmember=member.idmember order by sale.idmember,time ;");
		//echo $rs;return;
		for ($i = 0; $i < (sizeof($rs)+1); $i++) {
			$value=$rs[$i];
			if($i==0){
				$mytemp=$value['mname'];
			}else{
				if($mytemp!=$value['mname']){
					$rs[$i-1]['ttl_sum']=$ttl_sum;
					$rs[$i-1]['ttl_paid']=$ttl_paid;
					$mytemp=$value['mname'];
					$ttl_sum=$ttl_paid=0;
				}
			}
			if(($value['idgoods']>0)||($value['cf']==1)){
				$temp=$value['idgoods'];
				$rs[$i]['gname']=$glist[$temp];
				$ttl_sum+=$value['sum'];
			}else
				$ttl_paid+=$value['sum'];
		}

		//print_r($rs);
		$this->assign('rs',$rs);
		$this->display("sale/printAll");
	}*/
	
	public function getInprice(){
		$purchase=M(I('session.DB').'purchase_all');
		$rs=$purchase->where($_GET)->field('price')->find();
		$this->success("进价：￥".$rs['price']);
	}
	public function cfPrint(){
    	$sale=M(I('session.DB').'sale_all');
    	$where['cf']=$_GET['idsale'];
    	$slist=$sale->where($where)->order('time')->select();
        $glist=F(I('session.DB').'goods');
       //print_r($glist);
        if(!$glist){
            $goods=M(I('session.DB').'goods');
            $glist=$goods->getField('idgoods,gname');
            F(I('session.DB').'goods',$glist);
           // print_r($glist);echo 'find';
        }
    	$sum=0;
    	$num=0;
    	for ($i = 0; $i < sizeof($slist); $i++) {
    		$temp=$slist[$i]['idgoods'];
    		$slist[$i]['gname']=$glist[$temp];
    		$sum+=$slist[$i]['sum'];
    		$num+=$slist[$i]['number'];
    		//echo $glist[$temp].'-'.$slist[$i]['idgoods'];
    	}
    	$info=M(I('session.DB').'info');
    	$info=$info->find(1);
    	$this->assign('info',$info);
    	$myinfo=$sale->where($_GET)->field('remark,time,idmember')->find();
    	$member=M(I('session.DB').'member');
    	$mname=$member->field('mname')->find($myinfo['idmember']);
    	$this->assign('slist',$slist);
    	$this->assign('mname',$mname['mname']);
    	$time=$myinfo['remark'].'结转明细';
    	$this->assign('time',$time);
    	$this->assign('ttl_num',$num);
    	$this->assign('ttl_sum',$sum);
    	if($info['print']>0){
    		$len=sizeof($slist)+1;
    	if(($len%$info['print'])!=0){ 
			if((($len%$info['print'])*1.5)>$info['print']) $len=$len+1; 
			 }
    		$this->assign('len',$len);
    		$this->assign('print',$info['print']);
    		$this->display("sale/checkprint_lodop");
    		return;
    	}
    	$this->display("sale/checkprint");
    	
    }
   public function intime(){
   	$bank=M(I('session.DB').'bank');
   	$banklist=$bank->field('idbank,bname')->order('weight desc')->select();
   	$this->assign('bank',$banklist);
   	$this->display('sale/intime');
   }
   
   public function itPrice(){
  	 $stock=M(I('session.DB').'stock');
   	 $price=$stock->field('MAX(price1) as price1,MAX(price2) as price2')->where($_GET)->find();
   	 if(($price['price1']==0)&&($price['price2']==0)&&($_GET['idmember']>0)){ 
   	 	$sale=M(I('session.DB').'sale_all');
   	 	$where['idmember']=$_GET['idmember'];
   	 	$where['idgoods']=$_GET['idgoods'];
   	 	$temp=$sale->field('price as price1')->where($where)->order("idsale desc")->find();
   	 	if($temp['price1']==null) 
   	 		$temp['price1']=0;
   		echo json_encode($temp);
   	 }else{
   		   echo json_encode($price);
   	 }
   }
   public function inSale(){
   	$intime=M(I('session.DB').'intime');
   	$data=$_POST;
    $data['user']=cookie('nick');
   //	$data['user']='nick';
   	$idintime=$intime->add($data);
   	//echo $idintime;return;
   	if(!$idintime){$this->error('保存失败，请重新输入');return;}
   	if($_POST['number2']=='')	{$this->success();return;}
   	$data['number']=abs($_POST['number1']-$_POST['number2']);
   	$data['time']=date('Y-m-d H:i:s');
   	$data['idintime']=$idintime;
   	$sale=M(I('session.DB').'sale');
   	$idsale=$sale->add($data);
   	$tdata['idsale']=$idsale;
   	$where['idintime']=$idintime;
   	$rs=$intime->where($where)->save($tdata);
   	if($rs)
   		$this->success();
   }
   public function number1(){
   	$intime=M(I('session.DB').'intime');
   	$where['number2']=array('EXP','IS NULL');
   	$where['user']=cookie('nick');
   	$where['time1']=array('like','%'.date("Y-m-d").'%');
   	$rs=$intime->where($where)->select();
   //	echo $rs;return;
   	$mlist=F(I('session.DB').'member');
   	if(!$mlist){
   		$member=M(I('session.DB').'member');
   		$mlist=$member->getField('idmember,mname');
   		F(I('session.DB').'member',$mlist);
   	}
   	$glist=F(I('session.DB').'goods');
   	if(!$glist){
   		$goods=M(I('session.DB').'goods');
   		$glist=$goods->getField('idgoods,gname');
   		F(I('session.DB').'goods',$glist);
   	}
   	for ($i = 0; $i < sizeof($rs); $i++) {
   		$mtemp=$rs[$i]['idmember'];
   		$gtemp=$rs[$i]['idgoods'];
   		$rs[$i]['mname']=$mlist[$mtemp];
   		$rs[$i]['gname']=$glist[$gtemp];
   	}
   	$this->assign('rs',$rs);
   	$this->display('sale/number1');
   }
   public function updataIntime(){ //获取number2，加入sale表
   		$intime=M(I('session.DB').'intime');
   		$data=$intime->find($_POST['idintime']);
   		$data['number']=abs($data['number1']-$_POST['number2']);
   		$data['time']=$data['time1'];
   		$data['sum']=$_POST['sum'];
   		$sale=M(I('session.DB').'sale');
   		$indata['idsale']=$sale->add($data);
   		   		
   		$indata['number2']=$_POST['number2'];
   		$indata['time1']=$data['time1'];
   		$indata['time2']=date('Y-m-d H:i:s');
   		$where['idintime']=$_POST['idintime'];
   		$rs=$intime->where($where)->save($indata);
   		//echo $rs;return;
   		if($rs) $this->success();
   }
   public function intimeTable(){
    $sale=M(I('session.DB').'sale_all');
    $where['time']=array('like',date("Y-m-d").'%');
    $where['user']=cookie('nick');
    $where['idgoods']=array('gt',0);
    $rs=$sale->where($where)->order('idsale desc')->select();
   	$mlist=F(I('session.DB').'member');
   	if(!$mlist){
   		$member=M(I('session.DB').'member');
   		$mlist=$member->getField('idmember,mname');
   		F(I('session.DB').'member',$mlist);
   	}
   	$glist=F(I('session.DB').'goods');
   	if(!$glist){
   		$goods=M(I('session.DB').'goods');
   		$glist=$goods->getField('idgoods,gname');
   		F(I('session.DB').'goods',$glist);
   	}
   	$intime=M(I('session.DB').'intime');
   	for ($i = 0; $i < sizeof($rs); $i++) {
   		$mtemp=$rs[$i]['idmember'];
   		$gtemp=$rs[$i]['idgoods'];
   		$rs[$i]['mname']=$mlist[$mtemp];
   		$rs[$i]['gname']=$glist[$gtemp];
   		$data=$intime->field('number1,number2')->find($rs[$i]['idintime']);
   		//echo $rs[$i]['idintime'];
   		$rs[$i]['number1']=$data['number1'];
   		$rs[$i]['number2']=$data['number2'];
   	}
   	$this->assign('rs',$rs);
   	$this->display('sale/intimeTable');
   }
   function getIntimeInfo(){
   	   $intime=M(I('session.DB').'intime');
   	   $info=$intime->field('number1,time1,number2,time2,user')->find($_GET['idintime']);
   	   echo json_encode($info);
   }
   public function updateSale(){
   	 $sale=M(I('session.DB').'sale');
   	 if($_POST['idgoods']) $data['idgoods']=$_POST['idgoods'];
   	 if($_POST['idmember']) $data['idmember']=$_POST['idmember'];
   	 if($_POST['inorder']) $data['inorder']=$_POST['inorder'];
   	 $data['price']=$_POST['price'];
   	 $data['sum']=$_POST['sum'];
   	 $data['time']=date('Y-m-d H:i:s');
   	 $data['user']=cookie('nick');
   	 $where['idsale']=$_POST['idsale'];
   	 $rs=$sale->where($where)->save($data);
   	 if($rs){
   	 	$this->success();
   	 	mylog('修改销售单id为'.$_POST['idsale']);
   	 	return;
   	 }
   	 $this->error('修改失败');
   }
  public function updateNum1(){
  	$intime=M(I('session.DB').'intime');
  	$data=$intime->find($_POST['idintime']);
  	$data['number1']=$_POST['number1'];
  	$data['price']=$_POST['price'];
    $rs=$intime->save($data);
    if($rs) $this->success();
  }
  public function searchGoods(){
  		$where['gname|gsn']=array('like','%'.$_POST['key'].'%');
  		$supply=M(I('session.DB').'stock_goods_view');
  		$sname=$supply->where($where)->limit(0,10)->getField("idgoods,gname");
  		if(!$sname) return;
  		foreach ( $sname as $key => $value ) {
  			$str['gname'][] = urlencode ( $value );
  			$str['idgoods'][]=$key;
  		}
  		echo urldecode ( json_encode ( $str) );
  }
  public function  tsaleAdd(){
  	   $stock=M(I('session.DB').'stock');
  	   $sale=M(I('session.DB').'sale');
  	   $time=date("Y-m-d H:i:s");
  	   if($_POST['idbank']>0){
  	   	$ttl_sum=0;
  	   	foreach ($_POST['data'] as $key => $value) {
  	   		$where['idgoods']=$value['idgoods'];
  	   		$number=(float)$value['number'];
  	   		$where['stock']=array('gt',$number);
  	   		$rs=$stock->where($where)->field('inorder')->find();
  	   		if(!$rs){
  	   			unset($where['stock']);
  	   			$rs=$stock->where($where)->field('inorder')->order('stock desc')->find();
  	   		}
  	   		$value['inorder']=$rs['inorder'];
  	   		$value['time']=$time;
  	   		$value['user']=cookie('nick');
  	   		$ttl_sum+=$value['sum'];
  	   		$rs=$sale->add($value);
  	   		if(!$rs){
  	   			mylog("tsale系统插入sale表错误!");
  	   			echo 0;
  	   			return;
  	   		}
  	   	  }
  	   	   $data['sum']=(-1)*$ttl_sum;
  	   	   $data['time']=$time;
  	   	   $data['user']=cookie('nick');
  	   	   $data['idmember']=$_POST['data'][0]['idmember'];
  	   	   $data['idgoods']=0;
  	   	   $rs=$sale->add($data);
  	   	   if($rs){
  	   	   	 $swhere['idmember']=$data['idmember'];
  	   	   	 $swhere['time']=$data['time'];
  	   	   	 $rs=$sale->where($swhere)->delete();
  	   	   	// echo $sale->fetchSql(true)->where($where)->delete(); return;
  	   	   	 if($rs){
  	   	   	 	$bank=M(I('session.DB').'bank');
  	   	   	 	$balance=$bank->field("sum")->find($_POST['idbank']);
  	   	   	 	$bdata['balance']=$balance['sum']+$ttl_sum;
  	   	   	 	$bdata['idbank']=$_POST['idbank'];
  	   	   	 	$bdata['sum']=$ttl_sum;
  	   	   	 	$bdata['time']=$time;
  	   	   	 	$bdata['user']=cookie('nick');
  	   	   	 	$member=M(I('session.DB').'member');
  	   	   	 	$mname=$member->field("mname")->find($data['idmember']);
  	   	   	 	$bdata['summary']="批量录入客户付款：".$mname['mname'];
  	   	   	 	$bankdetail=M(I('session.DB').'bank_detail');
  	   	   	 	$rs=$bankdetail->add($bdata);
  	   	   	 	if($rs){
  	   	   	 		mylog("tsale系统插入sale表成功!");
  	   	   	 		echo strtotime($time);
  	   	   	 		return;
  	   	   	 	}
  	   	   	 	mylog("tsale系统bankdetail表插入错误!");
  	   	   	 	echo 0;
  	   	   	 	return;
  	   	   	 }
  	   	   	 mylog("paidsum-tsale系统delete-sale表错误!");
  	   	   	 echo 0;
  	   	   	 return;
  	   	   }
  	   	   mylog("paidsum-tsale系统插入sale表错误!");
  	   	   echo 0;
  	   	   return;
  	   }else{
  	        foreach ($_POST['data'] as $key => $value) {
  	   	   	 $where['idgoods']=$value['idgoods'];
  	   	   	 $number=(float)$value['number'];
  	   	   	 $where['stock']=array('gt',$number);
  	   	   	 $rs=$stock->where($where)->field('inorder')->find();
  	   	   	 if(!$rs){
  	   	    	unset($where['stock']);
  	   	    	$rs=$stock->where($where)->field('inorder')->order('stock desc')->find();
  	   	   	 }
  	   	    $value['inorder']=$rs['inorder'];
  	   	    $value['time']=$time;
  	   	    $value['user']=cookie('nick');
  	   	    $rs=$sale->add($value);
  	   	    if(!$rs){
  	   	    	mylog("tsale系统插入sale表错误!");
  	   	    	echo 0;
  	   	    	return;
  	   	    }
  	     }
  	   }
  	   mylog("tsale系统插入sale表成功!");
  	   echo strtotime($time);
  }
  public function tsale(){
  	$bank=M(I('session.DB').'bank');
  	$banklist=$bank->field('idbank,bname')->order('weight desc')->select();
  	$this->assign('bank',$banklist);
  	$this->display('sale/tsale');
  }
  
  public function tsaleprint(){
  	if(!$_GET) return;
  	if($_GET['time']) $where['time']=date("Y-m-d H:i:s", $_GET['time']);
  	//$this->assign('time',$where['time']);
  	$info=M(I('session.DB').'info');
  	$list=$info->find(1);
  	$this->assign('info',$list);
  	$glist=F(I('session.DB').'goods');
  	if(!$glist){
  		$goods=M(I('session.DB').'goods');
  		$glist=$goods->getField('idgoods,gname');
  		F(I('session.DB').'goods',$glist);
  	}
  	$sale=M(I('session.DB').'sale_all');
  	$where['idmember']=$_GET['idmember'];
  	$where['idgoods']=array('gt',0);
  	$slist=$sale->where($where)->field('price,number,sum,idgoods,time,cf')->order('idsale')->select();
  	$number=$sum=0;
  	foreach ($slist as $key => $value) {
  		$temp=$value['idgoods'];
  		$slist[$key]['gname']=$glist[$temp];
  		$sum+=$value['sum'];
  		$number+=$value['number'];
  	}
  //	$csale=M(I('session.DB').'sale');
  //	$cwhere['idmember']=$where['idmember'];
  //	$credit=$csale->where($cwhere)->sum('sum');
  	$this->assign('slist',$slist);
  	$this->assign('sum',$sum);
  	$this->assign('number',$number);
  //	$this->assign('credit',$credit);
  	if($list['print']>0){
  		$len=sizeof($slist)+1;
  	if(($len%$list['print'])!=0){ 
			if((($len%$list['print'])*1.5)>$list['print']) $len=$len+1; 
			 }
  		$this->assign('len',$len);
  		$this->assign('print',$list['print']);
  		$this->display("sale/single_print_lodop");
  		return;
  	}
  	$this->display("sale/single_print");
  }
  public function saleTablePrint(){
  	if(!$_GET) return;
  	$info=M(I('session.DB').'info');
  	$list=$info->find(1);
  	$this->assign('info',$list);
  	$glist=F(I('session.DB').'goods');
  	if(!$glist){
  		$goods=M(I('session.DB').'goods');
  		$glist=$goods->getField('idgoods,gname');
  		F(I('session.DB').'goods',$glist);
  	}
  	$sale=M(I('session.DB').'sale_all');
  	$rs=$sale->where($_GET)->find();
  	$where['idmember']=$rs['idmember'];
  	$where['idgoods']=array('gt',0);
  	$where['time']=array('eq',$rs['time']);
  	$slist=$sale->where($where)->field('price,number,sum,idgoods,time,cf,idsale')->order('idsale')->select();
  	$number=$sum=0;
  	foreach ($slist as $key => $value) {
  		$temp=$value['idgoods'];
  		$slist[$key]['gname']=$glist[$temp];
  		$sum+=$value['sum'];
  		$number+=$value['number'];
  	}
  	//$csale=M(I('session.DB').'sale');
  	//$cwhere['idmember']=$where['idmember'];
  	//$credit=$csale->where($cwhere)->sum('sum');
  	$this->assign('slist',$slist);
  	$this->assign('sum',$sum);
  	$this->assign('number',$number);
  	//$this->assign('credit',$credit);
  	if($list['print']>0){
  		$len=sizeof($slist)+1;
  	    if(($len%$list['print'])!=0){ 
			if((($len%$list['print'])*1.5)>$list['print']) $len=$len+1; 
			 }
  		$this->assign('len',$len);
  		$this->assign('print',$list['print']);
  		$this->display("sale/single_print_lodop");
  		return;
  	}
  	$this->display("sale/single_print");
  }
}