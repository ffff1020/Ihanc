<?php if (!defined('THINK_PATH')) exit();?><script>
$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#fee_table").html(result);
	  });
	return false;
});
</script>
<div class="clearfix" style="text-align:center"><h4><?php echo ($info); ?>费用分析统计表</h4></div>

<div ><h5>费用合计:<font color="red">￥<?php echo (number_format($fee)); ?></font>&nbsp;&nbsp;
收入合计:￥<?php echo (number_format($income)); ?></h5></div>

<table class="bordered" style="width:100%;font-size:12px"  > 
    <thead> 
    <tr>
        <th >#</th> 
    	<th>日期</th> 
    	<th>金额</th> 
    	<th>摘要</th> 
    	<th>操作人</th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($fee_table)): $i = 0; $__LIST__ = $fee_table;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr >
    <td><?php echo ($i); ?></td>
    <td><?php echo ($vo["time"]); ?></td>
    <?php if($vo["sum"] < 0): ?><td><font color="red">￥<?php echo (number_format($vo["sum"])); ?></font></td>
    <?php else: ?><td>￥<?php echo (number_format($vo["sum"])); ?></td><?php endif; ?>
     
    <td><?php echo ($vo["summary"]); ?></td>
    <td><?php echo ($vo["user"]); ?></td>
    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
    </table>
     <div class="green-black" style="float:right" id="page"><?php echo ($page); ?></div>