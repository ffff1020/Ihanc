<?php if (!defined('THINK_PATH')) exit();?><script>
$("#sale_report_table tbody tr").click(function(){
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	var divhtml="<div id='sale_table'></div>";  
	$('#main-content').append(divhtml);              
	var title=$(this).find("td:nth-child(1)").text()+"今日销售明细单";
	$.get("/ihanc/eva/index.php/Home/Sale/saleTable",
		{'idgoods':$(this).attr('id')},
		function(result){
			$('#dingwei').remove();
			$('#sale_table').html(result);
			$('#sale_table').dialog({
				 width:700,
				 maxHeight:700,
				 title:title,
				 modal: true,
			     close: function () { $(this).remove(); },// html('');
			     buttons: {
			    	 "取消": function() { $(this).remove();},
			      },
			});
	});
});
</script>

<table class="bordered" style="width:100%;font-size:12px" id="sale_report_table" > 
<thead>
<tr>
<th>商品名称</th>
<th>赊出数量</th>
<th>赊出金额</th>
<th>销售数量合计</th>
<th>销售金额合计</th>
</tr>
</thead>
<tbody>
<?php if(is_array($sinfo)): $i = 0; $__LIST__ = $sinfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo["idgoods"] > 0): ?><tr id=<?php echo ($vo["idgoods"]); ?> title="点击查看<?php echo ($vo["gname"]); ?>销售信息">
<td><?php echo ($vo["gname"]); ?></td>
<td><?php echo (number_format($vo['cnum'],2)); ?></td>
<td>￥<?php echo (number_format($vo['csum'])); ?></td>
<?php $paid = $paid+$vo['sum']-$vo['csum']; ?>
<td><?php echo (number_format($vo['number'],2)); ?></td>
<td>￥<?php echo (number_format($vo['sum'])); ?></td>
</tr><?php endif; endforeach; endif; else: echo "" ;endif; ?>
</tbody>
<tr>

<td>已收总金额:<font color="red">￥<?php echo (number_format($cash)); ?></font></td>
<td>已收欠款:<font color="blue">￥<?php echo (number_format($cash-$paid)); ?></font></td>
<td>现金付款:<font color="blue">￥<?php echo (number_format($paid)); ?></font></td>
<td>合计数量:<?php echo (number_format($ttl_num)); ?></td>
<td>营业额:￥<?php echo (number_format($ttl_sum)); ?></td>
</tr>



</table>