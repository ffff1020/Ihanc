<?php if (!defined('THINK_PATH')) exit();?>
<html>   
<head>   
<title>瀚盛销售管理系统水产专用版</title>   
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">   
<!--media=print 这个属性可以在打印时有效-->   
<style media=print>   
.Noprint{display:none;}   
.PageNext{page-break-after: always;}   
</style>   
 
<style> 
table
{
border-collapse:collapse;
}  
table, th, td
{
border: 1px solid black;
}
 
</style>   
 
</head>   
 
<body >  
<center class="Noprint" >   
<p>   
<OBJECT id=WebBrowser classid=CLSID:8856F961-340A-11D0-A96B-00C04FD705A2 height=0 width=0>   
</OBJECT>   
<input type=button value=打印 onclick=document.all.WebBrowser.ExecWB(6,1)>    
<input type=button value=页面设置 onclick=document.all.WebBrowser.ExecWB(8,1)>   
<input type=button value=打印预览 onclick=document.all.WebBrowser.ExecWB(7,1)>   
<br/>   
</p>   
</center> 
<?php if(is_array($slist)): $i = 0; $__LIST__ = $slist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if(($i%$print) == 1): if($i > 1): ?><div class="PageNext"></div><?php endif; ?>
<div style=" text-align:center;"><font size="4px"><b><?php echo ($info["info_name"]); ?></b></font></div>
<div style=" text-align:center;"><font size="3px">销售单</div>
<br />
客户:&nbsp;<?php echo ($_GET['mname']); echo ($mname); ?>&nbsp;&nbsp;&nbsp;&nbsp;<br />
打印日期:&nbsp;<?php echo ($time); ?> &nbsp;&nbsp;
<table width="280px">
<tr>
<td  width="15%" align="center" >日期</td>
<td  width="20%" align="center">品名</td>
<td  width="15%" align="center">数量</td>
<td  width="15%" align="center">单价</td>
<td  width="35%" align="center">总价</td>
</tr><?php endif; ?>
 <tr>
 <td style="padding-left:0px;font-size:14px"  align="left"><?php echo (substr($vo["time"],5,5)); ?></td>
<td style="padding-left:0px;font-size:12px"  align="left"><B><?php echo ($vo["gname"]); ?></B> </td>
<td style="padding-left:0px"  align="center"><?php echo ($vo["number"]); ?></td>
<td style="padding-left:0px"  align="center"><?php echo ($vo["price"]); ?></td>
<td style="padding-left:0px"  align="center">￥<?php echo (number_format($vo["sum"])); ?><font size='2px'>&nbsp;</td>   
</tr>        
<?php if(($i%$print) == 0): ?></table>
<br/><br/>
<center>第<?php echo (ceil($i/$print)); ?>页/共<?php echo (ceil($len/$print)); ?>页</center><?php endif; endforeach; endif; else: echo "" ;endif; ?>
<?php if($i%$print > 5): ?></table><br/><br/>
<center>第<?php echo (ceil($i/$print)); ?>页/共<?php echo (ceil($len/$print)); ?>页</center><?php endif; ?>
<?php if((($i%$print) == 0) or ($i%$print) > 5): ?><div class="PageNext"></div>
<div style=" text-align:center;"><font size="4px"><b><?php echo ($info["info_name"]); ?></b></font></div>
<div style=" text-align:center;"><font size="3px">销售单</div>
<br />
客户:&nbsp;<?php echo ($_GET['mname']); echo ($mname); ?>&nbsp;&nbsp;&nbsp;&nbsp;<br />
打印日期:&nbsp;<?php echo date('Y-m-d H:i:s');?> &nbsp;&nbsp;
<table width="280px">
<tr>
<td  width="15%" align="center" >日期</td>
<td  width="20%" align="center">品名</td>
<td  width="15%" align="center">数量</td>
<td  width="15%" align="center">单价</td>
<td  width="35%" align="center">总价</td>
</tr><?php endif; ?>
<tr><td colspan='3'>合计数量：<?php echo ($number); ?></td><td colspan='2'>合计金额：￥<?php echo (number_format($sum)); ?></td></tr>
<?php if($credit > 0): ?><tr><td colspan='5'>累计应收：<?php echo (number_format($credit)); ?></td></tr><?php endif; ?>
</table>
<div>
<br/>
客户签名：<br/><br/>
谢谢您的惠顾，欢迎您再次光临！<br />
电话:<?php echo ($info["info_tel"]); ?> <br/>
地址:<?php echo ($info["info_add"]); ?><br/>
<div style="width:280px">
<img style="display:inline" alt="" src="/ihanc/eva/Application/Home/View/public/images/pcode.jpg">
<div style="display:inline ;width:60%">本软件由&nbsp;<em><b>泸州瀚盛渔需用品</b></em>&nbsp;开发
技术支持及私人定制请扫描二维码<br/><br/></div>
<center>第<?php echo (ceil($len/$print)); ?>页/共<?php echo (ceil($len/$print)); ?>页</center>
</div>
</body>   
</html>