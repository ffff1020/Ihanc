<?php if (!defined('THINK_PATH')) exit();?><script>
$('#p_page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#purchase_table").html(result);
	  });
	return false;
});
</script>

<table class="bordered" style="width:100%;font-size:12px" id="screditDetail_tb" >

    <thead> 
    <tr>
        <th>#</th> 
        <th>日期</th> 
    	<th>商品名称</th>
    	<th>价格</th>
    	<th>数量</th> 
    	<th>金额</th> 
    	<th>状态</th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($check_list)): $i = 0; $__LIST__ = $check_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo["cf"] == 1 and $vo["sum"] < 0): else: ?>
    <tr >
    <td><?php echo ($i); ?></td>
    <?php if($vo["idgoods"] > 0): ?><td><?php echo (substr($vo["time"],0,10)); ?></td>
    <td><?php echo ($vo["gname"]); ?></td>
    <td><?php echo ($vo["price"]); ?></td>
    <td><?php echo ($vo["number"]); ?></td>
    <td>￥<?php echo (number_format($vo["sum"])); ?></td>
   <?php if($vo["finish"] == 1 ): if($vo["cf"] > 1): ?><td>已结转</td><?php else: ?><td>已付</td><?php endif; ?>
    <?php else: ?>
    <td><font color='red'>未付</font></td><?php endif; ?>
    <?php elseif($vo["sum"] < 0): ?>
    <td><?php echo (substr($vo["time"],0,10)); ?>&nbsp;付款:</td>
    <td><?php echo ($vo["gname"]); ?></td>
    <td><?php echo ($vo["price"]); ?></td>
    <td><?php echo ($vo["number"]); ?></td>
    <td><font color="red">￥<?php echo (number_format($vo["sum"])); ?></font></td>
     <td><?php if($vo["cf"] > 1): ?>已结转<?php elseif($vo["finish"] == 1): ?>已销帐<?php endif; ?></td>
    <?php else: ?>
    <td><?php echo (substr($vo["time"],0,10)); ?>&nbsp;结转余额:</td>
    <td><?php echo ($vo["gname"]); ?></td>
    <td><?php echo ($vo["price"]); ?></td>
    <td><?php echo ($vo["number"]); ?></td>
    <td><font color="red">￥<?php echo (number_format($vo["sum"])); ?></font></td>
    <td> <?php if($vo["finish"] == 1): if($vo["cf"] > 1): ?>已结转<?php else: ?>已付款<?php endif; else: ?><font color='red'>未付</font><?php endif; ?></td><?php endif; ?>
    </tr><?php endif; endforeach; endif; else: echo "" ;endif; ?>
    <tr><td colspan="7" align="left">合计应付金额:￥<?php echo (number_format($sum)); ?></td></tr>
    </tbody>
</table>
<div class="green-black" style="float:right" id="p_page"><?php echo ($page); ?></div>