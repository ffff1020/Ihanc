<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"> 
<HTML> 
<HEAD> 
<TITLE> 瀚盛管理系统水产专用版</TITLE> 
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
<font size="4px" ><B>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ($info["info_name"]); ?></font></B><br /><br />
<table>
<tr>
<?php $var = '1'; ?>
<?php if(is_array($rs)): $i = 0; $__LIST__ = $rs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo["ttl_sum"] > 0): $var = '0'; ?>
<tr></tr><tr></tr>
<tr><td><b><?php echo ($vo["mname"]); ?></b></td><td>合计:￥<?php echo ($vo['ttl_sum']+$vo['ttl_paid']); ?></td><td></td></tr><?php endif; ?>
<?php if($vo["idgoods"] > 0): ?><td><?php echo ($vo["time"]); ?>&nbsp;<?php echo ($vo["gname"]); ?>&nbsp;<?php echo ($vo["number"]); ?>*<?php echo ($vo["price"]); ?>=<?php echo ($vo["sum"]); ?></td>
<?php else: ?>
<td><?php echo ($vo["time"]); ?>&nbsp;<?php if($vo["cf"] == 1): ?><b>结转:</b><?php else: ?><b>付款:</b><?php endif; echo ($vo["sum"]); ?></td><?php endif; ?>
<?php $var = $var=($var+1)%3; ?>
<?php if($var == 0): ?></tr><tr><?php endif; endforeach; endif; else: echo "" ;endif; ?>
</tr>
</table>

</BODY> 
</HTML>