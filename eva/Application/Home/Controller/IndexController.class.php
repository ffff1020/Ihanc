<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
	public function _initialize(){
		if(!is_login()){
			$this->redirect('login/index');
		}
	}
    public function index(){
    	$this->display("index/index");
 	}
 	
 	public function logOut(){
 		$log=M('user_log','','DB_CONFIG1');
 		$ldata['otime']=date('Y-m-d H:i:s');
 		$where['iduser_log']=I('cookie.log_id');
 		$log->where($where)->save($ldata);
 		cookie(null);
 		session(null);
 		$this->redirect('login/index','',0,'');
 	}
 	
 	public function dashboard(){
 		$date=date('d');
 		if($date==1){
 			$asset=M(I('session.DB').'asset');
 			$where['time']=date("Y-m",strtotime('-1 month'));
 			$rs=$asset->where($where)->count();
 			$info=M(I('session.DB').'info');
 			$infoview=$info->find(1);
 			if(($rs<1)&&($infoview['info']==1)){
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
 			$data['time']=date("Y-m",strtotime('-1 month'));
 			$asset=M(I('session.DB').'asset');
 			$rs=$asset->add($data);
 			//echo $rs;
 			}
 		}
 		$sale=M(I('session.DB').'sale');
 		$tlist=$sale->field('time,idmember,sum(sum) as sum')->group('idmember')->order('time')->page('0,10')->select();
 		$slist=$sale->field('time,idmember,sum(sum) as sum')->group('idmember')->order('sum desc')->page('0,10')->select();
 		
 		$mlist=F(I('session.DB').'member');
 		if(!$mlist){
 			$member=M(I('session.DB').'member');
 			$mlist=$member->getField('idmember,mname');
 			F(I('session.DB').'member',$mlist);
 		}
 		for ($i = 0; $i < 10; $i++) {
 			$temp=$tlist[$i]['idmember'];
 			$tlist[$i]['mname']=$mlist[$temp];
 			$temp=$slist[$i]['idmember'];
 			$slist[$i]['mname']=$mlist[$temp];
 		}
 		
 		$this->assign('slist',$slist);
 		$this->assign('tlist',$tlist);
 		
 		$startdate=strtotime(date('Y-m-d'));
 		$user=M('user');
 		$str=I('session.DB');
 		$str=substr($str,6,-1);
 		$where['db']=$str;
 		$edate=$user->where($where)->field('etime')->find();
 		//print_r($edate);
 		$enddate=strtotime($edate['etime']);
 		$days=round(($enddate-$startdate)/86400);
 		//if($days<10){
 		    $this->assign('days',$days);
 		//}
 		$this->display("index/dashboard");
 	}
 	
}