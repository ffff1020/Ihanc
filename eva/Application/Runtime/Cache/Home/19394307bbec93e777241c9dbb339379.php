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
wb.execwb(6,6) 

} 
 
</script>  
<style type="text/css" media=print> 
.noprint{display : none; } 
</style> 
 
</HEAD> 
 
<BODY> 
<input type=button name=button_print value="打印" class="noprint" onClick="javascript:printit()"> 
<input type=button name=button_setup value="打印页面设置" class="noprint" onClick="javascript:printsetup();"> 
<input type=button name=button_show value="打印预览" class="noprint" onClick="javascript:printpreview();"> 
<br />
<br />

<OBJECT classid="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2" height=0 id=wb name=wb width=0></OBJECT> 

<div style=" text-align:center;"><font size="4px"><b><?php echo ($info["info_name"]); ?></b></font></div>
<div style=" text-align:center;"><font size="3px">客户对账单</font></div>
<br /><br />
客户:&nbsp;<?php echo ($mname); echo ($_GET['mname']); ?>&nbsp;&nbsp;&nbsp;&nbsp;<br />
日期:&nbsp;<?php echo date('Y-m-d H:i:s');?><div>
===============================

<table width="280px">
<tr>
<td style="padding-left:0px" width="15%" align="center">日期</td>
<td style="padding-left:0px" width="20%" align="center">品名</td>
<td style="padding-left:0px" width="15%" align="center">数量</td>
<td style="padding-left:0px" width="15%" align="center">单价</td>
<td style="padding-left:0px" width="35%" align="center">总价</td>
</tr>
 <?php if(is_array($slist)): $i = 0; $__LIST__ = $slist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo["cf"] == 1 and $vo["sum"] < 0): else: ?>
          <tr>
 <td style="padding-left:0px;font-size:14px"  align="left"><?php echo (substr($vo["time"],5,5)); ?></td>
 <?php if($vo["idgoods"] > 0): ?><td style="padding-left:0px;font-size:12px"  align="left"><B><?php echo ($vo["gname"]); ?></B> </td>
<?php elseif($vo["cf"] == 0): ?> 
<td style="padding-left:0px;font-size:12px"  align="center"><B>付款 </B></td>
<?php else: ?>
<td style="padding-left:0px;font-size:12px"  align="center"><B>结转余额</B> </td><?php endif; ?>
<td style="padding-left:0px"  align="center"><?php echo ($vo["number"]); ?></td>
<td style="padding-left:0px"  align="center"><?php echo ($vo["price"]); ?></td>
<?php if($vo["finish"] == 1 and $vo["idgoods"] > 0): ?><td style="padding-left:0px"  align="center">￥<?php echo (number_format($vo["sum"])); ?><font size='2px'>&nbsp;已付</font></td>   
<?php else: ?>
 <td style="padding-left:0px"  align="center">￥<?php echo (number_format($vo["sum"])); ?></td><?php endif; ?>
          </tr><?php endif; endforeach; endif; else: echo "" ;endif; ?>
</table>
</div>
---------------------------------------------------<br />
数量合计: &nbsp;<?php echo (number_format($number,2)); ?><br/>
金额合计：￥<?php echo (number_format($sum)); ?><br/>
<?php if($credit > 0): ?>应收累计：￥<?php echo ($credit); ?><br/><?php endif; ?>
===============================<br />
<br/>
&nbsp;&nbsp;&nbsp;&nbsp;谢谢您的惠顾，欢迎您再次光临！<br />
&nbsp;&nbsp;<br />
电话:<?php echo ($info["info_tel"]); ?> <br/>
地址:<?php echo ($info["info_add"]); ?><br/>
<br/>
本软件由&nbsp;<em><b>泸州瀚盛渔需用品</b></em>&nbsp;开发<br/>
技术支持及私人定制请扫描二维码<br/>
<img alt="" src="/ihanc/eva/Application/Home/View/public/images/pcode.jpg">

</BODY> 
</HTML>