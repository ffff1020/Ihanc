<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function mylog($user,$mdb,$remark){
    $logTable=$mdb.'.log';
    $data['user']=$user;
    $data['remark']=$remark;
    $rs=\think\Db::table($logTable)
        ->insert($data);
    return $rs;
}
function insertAsset($mdb){
    $time=date("Y-m",strtotime("-1 month"));
    $assetTable=$mdb.".asset";
    $rs=\think\Db::table($assetTable)
        ->where("time","like",$time)->find();
    if($rs==null){
        $data['time']=$time;
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
        $rs=\think\Db::table($assetTable)->insert($data);
    }
}

?>