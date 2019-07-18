<?php
function is_login(){
	if (cookie('key')) {
		$id=cookie('id');
		$admin=M('user');
		$result=$admin->where("iduser='{$id}'")->select();
		$str=$result[0]['iduser'].$result[0]['name'].$result[0]['ltime'].$result[0]['ip'];
		if($str!=authcode(cookie('key'),'DECODE',C('COOKIE_KEY'))){
			cookie(null);
			return false;
		}
	}else{
		return false;
	}
	return true;
}

//登录cookie.key的加密函数
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0){
	if($operation == 'DECODE') {
		$string = str_replace('[a]','+',$string);
		$string = str_replace('[b]','&',$string);
		$string = str_replace('[c]','/',$string);
	}
	$ckey_length = 4;
	$key = md5($key ? $key : 'livcmsencryption ');
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {

			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		$ustr = $keyc.str_replace('=', '', base64_encode($result));
		$ustr = str_replace('+','[a]',$ustr);
		$ustr = str_replace('&','[b]',$ustr);
		$ustr = str_replace('/','[c]',$ustr);
		return $ustr;
	}
}

/**
 * TODO 基础分页的相同代码封装，使前台的代码更少
 * @param $m 模型，引用传递
 * @param $where 查询条件
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getpage(&$m,$where,$pagesize=10,$params){
	$m1=clone $m;//浅复制一个模型
	$count = $m->where($where)->count();//连惯操作后会对join等操作进行重置
	$m=$m1;//为保持在为定的连惯操作，浅复制一个模型
	$p=new Think\Page($count,$pagesize);
	$p->lastSuffix=false;
	$p->setConfig('header','第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页');
	$p->setConfig('prev','上一页');
	$p->setConfig('next','下一页');
	$p->setConfig('last','末页');
	$p->setConfig('first','首页');
	$p->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
	$p->parameter=I('get.');
	$p->parameter['key']=$params;
	$m->limit($p->firstRow,$p->listRows);

	return $p;
}
//数据日志
function mylog($summary){
	$log=M(I('session.DB').'log');
	$data['summary']=$summary;
	$data['user']=cookie('nick');
	$log->add($data);
}

function specialpage($count,$params){
	$pagesize=10;
	$p=new Think\Page($count,$pagesize);
	$p->lastSuffix=false;
	$p->setConfig('header','第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页');
	$p->setConfig('prev','上一页');
	$p->setConfig('next','下一页');
	$p->setConfig('last','末页');
	$p->setConfig('first','首页');
	$p->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
	$p->parameter=I('get.');
	//$p->parameter['key']=$params;
	foreach ($params as $key => $value) {
		$p->parameter[$key]=$value;
	}
	return $p;
}

