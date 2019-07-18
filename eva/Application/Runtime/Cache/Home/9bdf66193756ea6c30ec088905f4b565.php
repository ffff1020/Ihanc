<?php if (!defined('THINK_PATH')) exit();?><table id="oTable" style="width:100%;color:white;border:none;border-collapse:collapse;z-index:-1">
<?php if(is_array($rs)): $i = 0; $__LIST__ = $rs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo["finish"] == 0): ?><tr id='idsale<?php echo ($vo["idsale"]); ?>' style='color:rgb(255,0,0);font-weight:bold'>
<?php else: ?><tr><?php endif; ?>
<td width="7%" style="text-align:center;"><?php echo ($i); ?>
<input type="hidden" name='idmember' value="<?php echo ($vo["idmember"]); ?>" />
<input type="hidden" name='inorder' value="<?php echo ($vo["inorder"]); ?>" />
<input type="hidden" name='idgoods' value="<?php echo ($vo["idgoods"]); ?>" />
</td>
<td width="23%" ><?php echo ($vo["mname"]); ?></td>
<td width="10%" ><?php echo ($vo["gname"]); ?></td>
<td width="30%" ><?php echo (number_format($vo["number1"],1)); ?>-<?php echo (number_format($vo["number2"],1)); ?>=<?php echo (number_format($vo["number"],1)); ?></td>
<td width="15%">￥<?php echo (number_format($vo["price"],1)); ?></td>
<td width="15%">￥<?php echo ($vo["sum"]); ?></td>
</tr><?php endforeach; endif; else: echo "" ;endif; ?>
</table>