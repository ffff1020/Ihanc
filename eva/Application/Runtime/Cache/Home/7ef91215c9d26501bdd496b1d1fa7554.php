<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
<HTML> 
<HEAD> 
<TITLE>瀚盛销售管理系统水产专用版</TITLE> 
<META NAME="Generator" CONTENT="EditPlus"> 
<META NAME="Author" CONTENT=""> 
<META NAME="Keywords" CONTENT=""> 
<META NAME="Description" CONTENT=""> 
<script type="text/javascript">   
     var HKEY_Root,HKEY_Path,HKEY_Key;    
     HKEY_Root="HKEY_CURRENT_USER";    
     HKEY_Path="\\Software\\Microsoft\\Internet Explorer\\PageSetup\\";    
         //设置网页打印的页眉页脚为空    
			 function PageSetup_Null()   
			  {    
				try {    
						var Wsh=new ActiveXObject("WScript.Shell");    
				HKEY_Key="header";    
				Wsh.RegWrite(HKEY_Root+HKEY_Path+HKEY_Key,"");    
				HKEY_Key="footer";    
				Wsh.RegWrite(HKEY_Root+HKEY_Path+HKEY_Key,"");    
				}  catch(e){}    
			  }
      //恢复网页打印的页眉页脚   
function SetupPage() 
{ 
     try{ 
   var RegWsh = new ActiveXObject("WScript.Shell"); 
   hkey_key="header" 
   RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"&w&b页码，&p/&P") 
   hkey_key="footer" 
   RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"&b&d") //去掉了&u 因为我不想显示当前打印页的网址 
   hkey_key="margin_bottom"; 
   RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"0.0"); //0.39相当于把页面设置里面的边距设置为10 
   hkey_key="margin_left"; 
   RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"0.0"); 
   hkey_key="margin_right"; 
   RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"0.0"); 
   hkey_key="margin_top"; 
   RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"0.0"); 
     }
     catch(e){
     alert(e);
     } 
}
 
function printsetup(){ 
// 打印页面设置 
wb.execwb(8,1); 
} 
function printpreview(){ 
// 打印页面预览 
PageSetup_Null(); 
wb.execwb(7,1); 
 
} 
 
function printit() 
{ 
PageSetup_Null(); 
wb.execwb(6,6) ;
}
     function doPrint() {
         if(!!window.ActiveXObject || "ActiveXObject" in window){
             try {
                 var myDoc = {
                     settings:{
                         topMargin:100,   //100,代表10毫米
                         leftMargin:20,
                         bottomMargin:500,
                         rightMargin:10},
                     documents: document,
                     /*
                      要打印的div 对象在本文档中，控件将从本文档中的 id 为 'page1' 的div对象，
                      作为首页打印id 为'page2'的作为第二页打印            */
                     copyrights: '杰创软件拥有版权  www.jatools.com' // 版权声明,必须
                 };
                 document.getElementById("jatoolsPrinter").print(myDoc, false); // 直接打印，不弹出打印机设置对话框
                 window.close();
             }catch (e) {
                 alert(e.name + ": " + e.message + "。可能是没有安装相应的控件或插件,请下载驱动程序!");
                 //console.log(e);
                 window.open('/ihanc/eva/Application/Home/View/public/lib/jatoolsPrinter_free.zip','_self');
             }
         }else {
             window.print();
             window.close();
         }
     }
</script>  
<style type="text/css" media=print> 
.noprint{display : none; } 
</style> 
 
</HEAD> 
 
<BODY> 
<input type=button name=button_print value="打印" class="noprint" onClick="javascript:doPrint()">
<input type=button name=button_setup value="打印页面设置" class="noprint" onClick="javascript:printsetup();"> 
<input type=button name=button_show value="打印预览" class="noprint" onClick="javascript:printpreview();">
<OBJECT  ID="jatoolsPrinter" CLASSID="CLSID:B43D3361-D075-4BE2-87FE-057188254255"
         codebase="jatoolsPrinter.cab#version=8,6,0,0" height=0></OBJECT>
<OBJECT classid="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2" height=0 id=wb name=wb width=0></OBJECT>
<div id='page1'>
<div style=" text-align:center;"><font size="4px"><b><?php echo ($info["info_name"]); ?></b></font></div>
<div style=" text-align:center;"><font size="3px">
<?php if(empty($_GET['sdate'])): echo date('Y-m-d');?>
<?php else: ?> 
<?php echo (substr($_GET['sdate'],5,5)); ?>到<?php echo (substr($_GET['edate'],5,5)); endif; ?> 
客户对账单</font></div>
<br /><br />
客户:&nbsp;<?php echo ($_GET['mname']); echo ($mname); ?>&nbsp;&nbsp;&nbsp;&nbsp;<br />
打印日期:&nbsp;<?php echo date('Y-m-d H:i:s');?><div>
    <hr style=" height:2px;border:none;border-top:2px dashed;" />

<table style="width: 100%">
<tr>
<td style="padding-left:0px; width:15% ;text-align:center" >日期</td>
<td style="padding-left:0px" width="30%" align="center">品名</td>
<td style="padding-left:0px" width="15%" align="center">数量</td>
<td style="padding-left:0px" width="15%" align="center">单价</td>
<td style="padding-left:0px" width="25%" align="center">总价</td>
</tr>
 <?php if(is_array($slist)): $i = 0; $__LIST__ = $slist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo["cf"] == 1 and $vo["sum"] < 0 and $vo["idgoods"] == null): else: ?>
          <tr>
 <td style="padding-left:0px;font-size:13px"  align="left"><?php echo (substr($vo["time"],5,5)); ?></td>
 <?php if($vo["idgoods"] > 0): ?><td style="padding-left:0px;font-size:12px"  align="left"><B><?php echo ($vo["gname"]); ?></B> </td>
<?php elseif($vo["idgoods"] == 0 and $vo["idgoods"] != null): ?>
  <td style="padding-left:0px;font-size:12px"  align="center"><B> 付款</B></td>
<?php else: ?>
<td style="padding-left:0px;font-size:12px"  align="center"><B>结转余额</B> </td><?php endif; ?>
<td style="padding-left:0px"  align="center"><?php echo ($vo["number"]); ?></td>
<td style="padding-left:0px"  align="center"><?php echo ($vo["price"]); ?></td>
<td style="padding-left:0px"  align="center">￥<?php echo (number_format($vo["sum"])); ?><font size='2px'>&nbsp;
<?php if($vo["finish"] == 1 ): if($vo["cf"] > 1 ): ?>结转<?php elseif($vo["cf"] == 1 or $vo["idgoods"] > 0): ?>已付<?php endif; ?></font></td><?php endif; ?>
</tr>        
<?php if($vo["remark"] == ''): else: ?>
<tr><td colspan='5'>备注:<?php echo ($vo["remark"]); ?></td></tr><?php endif; endif; endforeach; endif; else: echo "" ;endif; ?>
</table>
</div>
    <hr style=" height:1px;border:none;border-top:1px dashed;" />
数量合计：<?php echo (number_format($ttl_num,2)); ?><br/>
金额合计：￥<?php echo (number_format($ttl_sum)); ?><br/>
付款合计：￥<?php echo (number_format($ttl_paid)); ?><br/>
    <hr style=" height:2px;border:none;border-top:2px dashed;" />
<br/>
<p style="text-align: center">谢谢您的惠顾，欢迎您再次光临！</p>
&nbsp;&nbsp;<br />
电话:<?php echo ($info["info_tel"]); ?> <br/>
地址:<?php echo ($info["info_add"]); ?><br/>
<br/>

本软件由&nbsp;<em><b>泸州瀚盛渔需用品</b></em>&nbsp;开发<br/>
技术支持及私人定制请扫描二维码<br/>
<img alt="" src="/ihanc/eva/Application/Home/View/public/images/pcode.jpg">
</div>
</BODY> 
</HTML>