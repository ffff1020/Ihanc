<?php
namespace Home\Controller;
use Think\Controller;
class SettingController extends Controller {
	public function _initialize(){
		if(!is_login()){
			$this->redirect('login/index');
		}
	}
	//公司信息
    public function index(){
    	$info=M(I('session.DB').'info');
    	$list=$info->find(1);
    	//print_r($list);
    	$this->assign('info_list',$list);
    	$this->display("setting/index");
 	}
 	//公司信息update
 	public function infoUpdate(){
 		$info=M(I('session.DB').'info');
 		$rs=$info->where('idinfo=1')->save($_POST);
 		if($rs){
 			$this->success("保存成功");
 		}else{
 			$this->error("保存失败");
 		}
 	}
 	//种类设置
 	public function cat(){
 		$this->display("setting/cat");
 	}
 	public function catTable(){
 		$cat=M(I('session.DB').'categroy');
 		$list=$cat->select();
 		$this->assign("cat_list",$list);
 		$this->display("setting/catTable");
 	}
 	public function catUpdate(){
 		//$this->success("保存成功");
 		$cat=M(I('session.DB').'categroy');
 		if($_POST['idcat']){
 			$rs=$cat->save($_POST);
 			if($rs){
 				$this->success("保存成功");
 				
 			}else{
 				$this->error("保存失败");
 			}
 		}else{
 		$where['cat_name']=$_POST['cat_name'];
 		$check=$cat->where($where)->count();
 		if($check>0){
 			$this->error("保存失败,该名称已存在");
 		}else{
 			$rs=$cat->add($_POST);
 			$this->success("保存成功");
 		}
 		}
 	}
 	public function goods(){
 		$cat=M(I('session.DB').'categroy');
 		$list=$cat->select();
 		$this->assign("cat_list",$list);
 		$this->display("setting/goods");
 	}
 	public function goodsTable(){
 		if($_GET['key']) 
 		$where['gname|gsn']=array('like','%'.$_GET['key'].'%');
 		if($_POST){
 			$where['gname|gsn']=array('like','%'.$_POST['name'].'%');
 		}
 		$goods=M(I('session.DB').'goods');
 		$params=$_POST['name']?$_POST['name']:$_GET['key'];
 		$Page =getpage($goods, $where,10,$params);
 		$show = $Page->show();
 		$p=$_GET['p']?$_GET['p']:0;
 		$list=$goods->where($where)->order('idcat')->page($p.',10')->select();
 		$cat=M(I('session.DB').'categroy');
 		$catlist=$cat->getField("idcat,cat_name");
 		for ($i = 0; $i < sizeof($list); $i++) {
 			$idcat=$list[$i]['idcat'];
 			$list[$i]['idname']=$catlist[$idcat];
 		}
 		$this->assign("page",$show);
 		$this->assign("goods_list",$list);
 		$this->display("setting/goodsTable");
 	}
 	public function goodsUpdate(){
 		$goods=M(I('session.DB').'goods');
 		if($_POST['idgoods']){
 			$rs=$goods->save($_POST);
 			if($rs){
 				$goodslist=$goods->getField('idgoods,gname');
 				F(I('session.DB').'goods',$goodslist);
 				$this->success("保存成功");
 			}else{
 				$this->error("保存失败");
 			}
 		}else{
 			$where['gname']=$_POST['gname'];
 			$check=$goods->where($where)->count();
 			if($check>0){
 				$this->error("保存失败,该名称已存在");
 			}else{
 				$rs=$goods->add($_POST);
 				if($rs){
 					$goodslist=$goods->getField('idgoods,gname');
 					F(I('session.DB').'goods',$goodslist);
 				    $this->success("保存成功");
 				}else{ $this->error("保存失败");};
 			}
 		}
 	}
		//员工设置
 	public function staff(){
 		$this->display("setting/staff");
 	}
 	public function staffTable(){
 		$staff=M(I('session.DB').'staff');
 		$list=$staff->select();
 		$this->assign("staff_list",$list);
 		$this->display("setting/staffTable");
 	}
 	public function staffUpdate(){
 		//$this->success("保存成功");
 		$staff=M(I('session.DB').'staff');
 		if($_POST['idstaff']){
 			$rs=$staff->save($_POST);
 			if($rs){
 				$this->success("保存成功");
 			}else{
 				$this->error("保存失败");
 			}
 		}else{
 		$where['staff_name']=$_POST['staff_name'];
 		$check=$staff->where($where)->count();
 		if($check>0){
 			$this->error("保存失败,该名称已存在");
 		}else{
 			$rs=$staff->add($_POST);
 			$this->success("保存成功");
 		}
 		}
 	}
 	
 	public function pwd(){
 		$this->display("setting/pwd");
 	}
 	
 	public function pwdUpdate(){
 		$user=M('user');
 		$where['name']=$_POST['name'];
 		$where['pwd']=md5($_POST['pwd0']);
 		$rs=$user->where($where)->select();
 		if(!$rs){
 			$this->error("原密码输入错误,请重新输入");
 			return;
 		}
 		$data['pwd']=md5($_POST['pwd']);
 		$rs=$user->where("name='".$_POST['name']."'")->save($data);
 		//$this->success($rs);
 		//return;
 		if($rs){
 			mylog("密码修改");
 			$this->success("密码修改成功");
 			return;
 		}
 		$this->error("密码修改失败,请重新修改");
 	}
 	//系统初期数据录入
 	public function start(){
 		$info=M(I('session.DB').'info');
 		$start=$info->find(1);
 		$this->assign('info',$start['info']);
 		$cat=M(I('session.DB').'categroy');
 		$catlist=$cat->field('idcat,cat_name')->select();
 		$this->assign('cat_list',$catlist);
 		$this->display("setting/start");
 		
 	}
 	public function Add(){
 		
 		$time=date("Y-m-d H:i:s");
 		if($_POST['scredit']){
 			$data=$_POST['scredit'];
 			for ($i = 0; $i < sizeof($data); $i++) {
 				//$data[$i]['inorder']="初期建帐".$time;
 				$data[$i]['time']=date("Y-m-d")."00:00:00";
 				$data[$i]['cf']=1;
 				$data[$i]['user']=cookie('nick');
 			}
 			//print_r($data);return;
 			$purchase=M(I('session.DB').'purchase');
 			$rs=$purchase->addAll($data);
 			if(!$rs){
 				echo ('录入初始数据错误,请重新录入');
 				return;
 			}
		}
		
 		if($_POST['mcredit']){
 			$data=array();
 			$data=$_POST['mcredit'];
 			for ($i = 0; $i < sizeof($data); $i++) {
 				//$data[$i]['inorder']="初期建帐".$time;
 				$data[$i]['time']=date("Y-m-d")."00:00:00";
 				$data[$i]['cf']=1;
 				$data[$i]['user']=cookie('nick');
 			}
 			$sale=M(I('session.DB').'sale');
 			$rs=$sale->addAll($data);
 			if(!$rs){
 				echo ('录入初始数据错误,请重新录入');
 				return;
 			}
 		}
 		if($_POST['stock']){
 			$data=array();
 			$data=$_POST['stock'];
 			for ($i = 0; $i < sizeof($data); $i++) {
 				$data[$i]['inorder']="初期建帐".$time;
 				$data[$i]['time']=$time;
 				$data[$i]['user']=cookie('nick');
 				$data[$i]['cf']=0;
 				$data[$i]['idsupply']=0;
 			}
 			$purchase_all=M(I('session.DB').'purchase');
 			$rs=$purchase_all->addAll($data);
 			//echo $rs;return;
 			if(!$rs){
 				echo ('录入初始数据错误,请重新录入');
 				return;
 			}
 			$where['inorder']="初期建帐".$time;
 			$where['idgoods']=array('gt',0);
 			$slist=$purchase_all->where($where)->field('idgoods,sum(number) as stock,inorder')->group('idgoods')->select();
 			$stock=M(I('session.DB').'stock');
 			$rs=$stock->addAll($slist);
 			if(!$rs){
 				echo ('录入初始数据错误,请重新录入');
 				return;
 			}
 			$pwhere['time']=$time;
 			$purchase_all->where($pwhere)->delete();
 		}
 		
 		echo ("录入初始数据成功");
 		return;
 	}
 	public function complete(){
 		//echo "hello";return;
 		$info=M(I('session.DB').'info');
 		$where['idinfo']=1;
 		$rs=$info->where($where)->save($_GET);
 		$stock=M(I('session.DB').'stock_sum_view');
 		$stocklist=$stock->sum('sum');
 		$data['stock']=$stocklist;
 		//print_r($stocklist);return;
 		$bank=M(I('session.DB').'bank');
 		$data['bank']=$bank->sum('sum');
 		$sale=M(I('session.DB').'sale');
 		$data['scredit']=$sale->sum('sum');
 		$purchase=M(I('session.DB').'purchase');
 		$data['mcredit']=$purchase->sum('sum');
 		$freight=M(I('session.DB').'freight');
 		$data['freight']=$freight->where('status=0')->sum('freight');
 		$data['time']="初期建账";
 		//print_r($data);
 		$asset=M(I('session.DB').'asset');
 		$rs1=$asset->add($data);
 		
 		echo $rs;
 	}
}
?>