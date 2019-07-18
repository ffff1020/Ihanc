<?php
namespace Home\Controller;
use Think\Controller;
class FinanceController extends Controller {
	public function _initialize(){
		if(!is_login()){
			$this->redirect('login/index');
		}
	}
	
	public function bank(){
		$bank=M(I('session.DB').'bank');
		$blist=$bank->select();
		$this->assign('bank_list',$blist);
		$this->display("finance/bank");
	}
	
	public function bankAdd(){
		$bank=M(I('session.DB').'bank');
		$where['bname']=$_POST['bname'];
		$check=$bank->where($where)->count();
		if($check){
			$this->error("该银行名称已经存在");
			return;
		}
		$rs=$bank->add($_POST);
		if($rs){
			$data['sum']=$_POST['sum'];
			$data['idbank']=$rs;
			$data['user']=cookie('nick');
			$data['summary']="初始金额";
			$data['balance']=$data['sum'];
			$bank_detail=M(I('session.DB').'bank_detail');
			$rs1=$bank_detail->add($data);
			//$this->success($rs1);return;
			if($rs1){
				$this->success("添加银行成功");
				mylog("添加银行".$_POST['gname'].$_POST['sum']);
			}else{
				$this->error("添加银行明细失败,请联系管理员");
				mylog("添加银行明细失败,idbank:".$rs);
			}
		}else 
			$this->error("添加银行失败");
	}
	
	public function bankDetail(){
		$bank_detail=M(I('session.DB').'bank_detail');
		$p=$_GET['p']?$_GET['p']:0;
		if($_POST){
			$params=$_POST;
		}else{
			$params['idbank']=$_GET['idbank'];
			$params['sdate']=$_GET['sdate'];
			$params['edate']=$_GET['edate'];
			$params['summary']=$_GET['summary'];
		}
		//print_r($params);
		$where['idbank']=$params['idbank'];
		if($params['summary'])
			$where['summary']=array('like','%'.$params['summary'].'%');
		if($params['sdate']&&$params['edate'])
			$where['time']=array('between',array($params['sdate']." 00:00:00",$params['edate']." 23:59:59"));
		$count=$bank_detail->where($where)->count();
		$Page=specialpage($count, $params);
		$show=$Page->show();
		$blist=$bank_detail->where($where)->page($p.',10')->order('time desc')->select();
		//echo $bank_detail->where($where)->fetchSql(true)->page($p.',10')->order('time desc')->select();
		$this->assign('bank_list',$blist);
		$this->assign('page',$show);
		$this->assign('idbank',$params['idbank']);
		$this->assign('edate',$params['edate']);
		$this->assign('sdate',$params['sdate']);
		$this->assign('summary',$params['summary']);
		$this->display("finance/bankDetail");
	}
	
	public function transfer(){
		$bank=M(I('session.DB').'bank');
		$blist=$bank->getField('idbank,bname,sum');
		$this->assign('bank_list',$blist);
		if($_POST){
			$bankdetail=M(I('session.DB').'bank_detail');
			$data['time']=date("Y-m-d H:i:s");
			$data['user']=cookie('nick');
			//转出银行记录:
			$out_id=$data['idbank']=$_POST['out_bank'];
			$in_id=$_POST['in_bank'];
			$data['sum']=(-1)*$_POST['sum'];
			$data['balance']=$blist[$out_id]['sum']-$_POST['sum'];
			$data['summary']="转到".$blist[$in_id]['bname'];
			$rs=$bankdetail->add($data);
			if($rs){
				//转入银行:
				$data['idbank']=$_POST['in_bank'];
				$data['sum']=$_POST['sum'];
				$data['balance']=$blist[$in_id]['sum']+$_POST['sum'];
				$data['summary']="从".$blist[$out_id]['bname']."转入";
				$rs1=$bankdetail->add($data);
				if($rs1){
					mylog("从".$blist[$out_id]['bname']."转入".$blist[$in_id]['bname']."金额:".$_POST['sum']);
					$this->success("转账成功!");
					return;
				}else{
					mylog("已经付出行".$blist[$out_id]['bname']."扣款,收款行".$blist[$in_id]['bname']."插入失败!");
					$this->error("操作失败,请联系管理");
					return;
				}
				
			}
			mylog("没有扣款,转账失败!");
			$this->error("操作失败,请重新转账");
			return;
		}
		
		$this->display("finance/transfer");
	}
	
	public function subAdd(){
		$cat=M(I('session.DB').'subject');
		if($_POST['idsubject']){
			$rs=$cat->save($_POST);
			if($rs){
				$this->success("保存成功");
					
			}else{
				$this->error("保存失败");
			}
		}else{
			
			$where['subject']=$_POST['subject'];
			$check=$cat->where($where)->count();
			//$this->success($_POST['subject'].$_POST['cat']);return;
			if($check>0){
				$this->error("保存失败,该名称已存在");
			}else{
				$rs=$cat->add($_POST);
				$this->success("保存成功");
			}
		}
	}
	public function subject(){
		$this->display("finance/subject");
	}
	public function subTable(){
		$sub=M(I('session.DB').'subject');
		$where['cat']=1;
		$sub_list_1=$sub->where($where)->field('idsubject,subject')->select();
		$where['cat']=2;
		$sub_list_2=$sub->where($where)->field('idsubject,subject')->select();
		$this->assign('sub_list_1',$sub_list_1);
		$this->assign('sub_list_2',$sub_list_2);
		$this->display("finance/subTable");
	}
	
	public function  fee(){
		$bank=M(I('session.DB').'bank');
		$sub=M(I('session.DB').'subject');
		$subject_list=$sub->where('cat=1')->select();
		$this->assign('subject_list',$subject_list);
		$bank_list=$bank->Field('idbank,bname')->order('weight desc')->select();
		$this->assign('bank_list',$bank_list);
		$this->display("finance/fee");
	}
	
	public function feeAdd(){
		//echo "hello";return;
		$bank=M(I('session.DB').'bank');
		$b=$bank->field('sum')->find($_POST['idbank']);
		$feedata=$_POST;
		$feedata['balance']=$b['sum']-$_POST['sum'];
		$feedata['sum']=(-1)*$_POST['sum'];
		$feedata['user']=cookie('nick');
		$subject=M(I('session.DB').'subject');
		$subname=$subject->field('subject')->find($_POST['idsubject']);
		//echo $subname;return;
		$feedata['summary']="费用:".$subname['subject'].$feedata['summary'];
		$feedata['time']=date('Y-m-d H:i:s');
		$bank_detail=M(I('session.DB').'bank_detail');
		$rs=$bank_detail->add($feedata);
		
		if($rs){
			mylog($feedata['summary']);
			$this->success("费用保存成功");
			return;
		}
		$this->success("费用保存失败");
		return;
	}
	
	public function  income(){
		$bank=M(I('session.DB').'bank');
		$sub=M(I('session.DB').'subject');
		$subject_list=$sub->where('cat=2')->select();
		$this->assign('subject_list',$subject_list);
		$bank_list=$bank->Field('idbank,bname')->order('weight desc')->select();
		$this->assign('bank_list',$bank_list);
		$this->display("finance/income");
	}
	
	public function incomeAdd(){
		//echo "hello";return;
		$bank=M(I('session.DB').'bank');
		$b=$bank->field('sum')->find($_POST['idbank']);
		$incomedata=$_POST;
		$incomedata['balance']=$b['sum']+$_POST['sum'];
		//$incomedata['sum']=(-1)*$_POST['sum'];
		$incomedata['user']=cookie('nick');
		$subject=M(I('session.DB').'subject');
		$subname=$subject->field('subject')->find($_POST['idsubject']);
		//echo $subname;return;
		$incomedata['summary']="收入:".$subname['subject'].$incomedata['summary'];
		$incomedata['time']=date('Y-m-d H:i:s');
		$bank_detail=M(I('session.DB').'bank_detail');
		$rs=$bank_detail->add($incomedata);
	
		if($rs){
			mylog($incomedata['summary']);
			$this->success("收入保存成功");
			return;
		}
		$this->success("收入保存失败");
		return;
	}
	
	public function  staff(){
		$bank=M(I('session.DB').'bank');
		$sub=M(I('session.DB').'staff');
		$staff_list=$sub->select();
		$this->assign('staff_list',$staff_list);
		$bank_list=$bank->Field('idbank,bname')->order('weight desc')->select();
		$this->assign('bank_list',$bank_list);
		$this->display("finance/staff");
	}
	
	public function freightAdd(){
		//echo "hello";return;
		$bank=M(I('session.DB').'bank');
		$b=$bank->field('sum')->find($_POST['idbank']);
		$freightdata=$_POST;
		$freightdata['balance']=$b['sum']-$_POST['sum'];
		$freightdata['sum']=(-1)*$_POST['sum'];
		$freightdata['user']=cookie('nick');
		$staff=M(I('session.DB').'staff');
		$name=$staff->field('staff_name')->find($_POST['idstaff']);
		//echo $subname;return;
		$freightdata['summary']="预支:".$name['staff_name'].$_POST['summary'];
		$fdata['time']=$freightdata['time']=date('Y-m-d H:i:s');
		$bank_detail=M(I('session.DB').'bank_detail');
		$rs=$bank_detail->add($freightdata);
		if($rs){
			mylog("银行扣款".$freightdata['summary']);
			$fdata['freight']=$_POST['sum']*(-1);
			$fdata['subject']="预支";
			$fdata['idstaff']=$_POST['idstaff'];
			$fdata['remark']=$_POST['summary'];
			$fdata['user']=cookie('nick');
			$freight=M(I('session.DB').'freight');
			$rs=$freight->add($fdata);
			if($rs){
				//$this->success($rs);
				$this->success("员工预支保存成功");
				return;
			}else{
				$this->error("员工预支失败,请联系管理员");
			}
		}
		$this->error("员工预支失败");
		return;
	}
	public function delBank(){
		//print_r($_GET);return;
		if($_GET['idbank']=='') {
			$this->error("删除错误，请重新操作");
			return;
		}
		$bank=M(I('session.DB').'bank');
		$rs=$bank->where($_GET)->delete();
		//$rs=1;
		if($rs) {
			mylog("删除银行成功,idbank:".$_GET['idbank']);
			$this->success("删除成功");
		}else 
			$this->error("删除失败");
			mylog("删除银行失败,idbank:".$_GET['idbank']);
	}
	public function setBank(){
		if($_GET['idbank']=='') {
			$this->error("设置默认银行错误，请重新操作");
			return;
		}
		$bank=M(I('session.DB').'bank');
		$rs=$bank->where("weight > 0")->setField("weight",0);
		$rs=$bank->where($_GET)->setField("weight",1);
		//$rs=1;
		if($rs) {
			$this->success("设置默认银行成功");
		}else
			$this->error("设置默认银行失败");
	}
}