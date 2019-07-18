<?php
namespace Home\Controller;
use Think\Controller;
class StockController extends Controller {
	public function _initialize(){
		if(!is_login()){
			$this->redirect('login/index');
		}
	}
	
	public function index(){
		$stock=M(I('session.DB').'stock');
		$slist=$stock->select();
		$glist=F(I('session.DB').'goods');
		if(!$glist){
			$goods=M(I('session.DB').'goods');
			$glist=$goods->getField('idgoods,gname')->select();
			F(I('session.DB').'goods',$glist);
		}
		for ($i = 0; $i < sizeof($slist); $i++) {
			$temp=$slist[$i]['idgoods'];
			$slist[$i]['gname']=$glist[$temp];
			//$slist[$i]['number']=number_format($slist[$i]['number'], 2, '.', '');
		}
		$this->assign('stock_list',$slist);
		$this->display("stock/index");
	}
	
	public function stockUpdate(){
		$stock=M(I('session.DB').'stock');
		//$rs=$stock->fetchSql(true)->save($_POST);
		//echo $rs;return;
		$rs=$stock->save($_POST);
		if($rs){
			$this->success("库存保存成功");
			mylog("修改库存id".$_POST['idstock']."数量为:".$_POST['stock']);
		}else 
			$this->error("库存修改失败");
	}
	
	public function stockDel(){
		$stock=M(I('session.DB').'stock');
		$rs=$stock->where($_POST)->delete();
		if($rs){
			$this->success("库存删除成功");
			mylog("删除库存id".$_POST['idstock']);
		}else
			$this->error("库存删除失败");
	}
}