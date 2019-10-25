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
function goEasy($channel,$content){
    //请求地址
    $uri = "http://goeasy.io/goeasy/publish";
    // 参数数组
    $data = [
        'appkey'  => "BC-ac73259b70bd452987726d46482efeba",
        'channel' => $channel,
        'content' =>$content
    ];
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $uri );//地址
    curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );//post传输的数据。
    $return = curl_exec ( $ch );
    curl_close ( $ch );
    return $return;
}

?>