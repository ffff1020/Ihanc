<?php if (!defined('THINK_PATH')) exit();?><table id="number1" style="width:100%;color:white;border:none;border-collapse:collapse;z-index:-1">
<?php if(is_array($rs)): $i = 0; $__LIST__ = $rs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idintime"]); ?>>
<td width="10%" style="text-align:center;"><?php echo ($i); ?></td>
<td width="23%" ><?php echo ($vo["mname"]); ?></td>
<td width="15%" ><?php echo ($vo["gname"]); ?></td>
<td width="15%" >￥<?php echo (number_format($vo["price"],2)); ?></td>
<td width="20%"   style="text-align:center;"><?php echo (number_format($vo["number1"],1)); ?></td>
<td width="18%" ><?php echo (substr($vo["time1"],10)); ?></td>
</tr><?php endforeach; endif; else: echo "" ;endif; ?>
</table>