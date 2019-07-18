<?php if (!defined('THINK_PATH')) exit();?><script>

$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#profit_table").html(result);
	  });
	return false;
});
$('#profitdetail tbody tr').click(function(){
	var id=$(this).attr('id');
	var sum=$('#'+id).find('td:nth-child(5)').text();
	if(sum=='0.00'){ return;}
	 var categories=new Array();
     var series = new Array();
	$.ajax({
		 type:"POST",
		 data:{inorder:id},
		 url:"/ihanc/eva/index.php/Home/Statistics/profitDetail",
		 dataType: "json",
		 async: false,
		 success: function(data){
			 categories=data.categories;
			 series=data.series;
			
		 },
	});
	// return;
	 $('#profit_chart').length && $('#profit_chart').remove();
	 var html='<div id="profit_chart" ><fieldset><div id="chart2" style="width:60%;">haha</div></fieldset></div>';
	 $('body').append(html); 
	 $('#chart2').highcharts({
	        chart: {
	            type: 'column'
	        },
	        title: {
	            text: id+"毛利明细图表"
	        },
	        xAxis: {
	            categories: categories
	        },
	        yAxis: {
	            title: {
	                text: '毛利 (元)'
	            },
	        },
	        credits: {
	            enabled: false
	        },
	        tooltip: {
	            valueSuffix: '元'
	        },
	        series: [{
	            name: '毛利',
	            data: series
	        }],
	        legend: {
	            enabled: false
	        },
	    });
	 
	 $('#profit_chart').dialog({
		 width:800,
		 maxHeight:600,
		 title:id+"毛利明细图表",
		 modal: true,
		 close:function(){$(this).remove();},
	     buttons: {
	    	 "关闭": function() { $(this).dialog( "close" ); },
	      },
	});
	 
});
</script>
<div class="clearfix" style="text-align:center"><h4><?php echo ($info); ?>营业统计表</h4></div>
<table class="bordered" style="width:100%;font-size:12px" id="profitdetail" > 
    <thead> 
    <tr>
        <th width="13%">入库单号</th> 
    	<th>进货数量</th> 
    	<th>进货金额</th> 
    	<th>进货费用</th> 
    	<th>销售数量</th>
    	<th>销售金额</th>
    	<th>库存数量</th>
    	<th>库存金额</th>
    	<th>损耗数量</th>
    	<th>此单毛利</th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($profit_table)): $i = 0; $__LIST__ = $profit_table;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id='<?php echo ($vo["inorder"]); ?>'>
    <td><?php echo ($vo["inorder"]); ?></td>
    <td><?php echo (number_format($vo["number"],2)); ?></td>
    <td>￥<?php echo (number_format($vo["sum"])); ?></td>
    <td>￥<?php echo (number_format($vo["freight"])); ?></td>
    <td><?php echo (number_format($vo["s_num"],2)); ?></td>
    <td>￥<?php echo (number_format($vo["s_sum"])); ?></td>
    <td><?php echo (number_format($vo["stock"],2)); ?></td>
     <td>￥<?php echo (number_format($vo["stock_sum"])); ?></td>
     <?php $temp_num = $vo['number']-$vo['s_num']; ?>
     <?php $temp_num = $temp_num-$vo['stock']; ?>
     <?php if($temp_num > 0): ?><td><font color='#FFA500'>折称:<?php echo (number_format($temp_num,2)); ?></font></td>
     <?php else: ?>
     <td>涨称:<?php echo (number_format($temp_num,2)); ?></td><?php endif; ?>
      <?php $temp_sum = $vo['stock_sum']+$vo['s_sum']; ?>
     <?php $temp_sum = $temp_sum-$vo['sum']-$vo['freight']; ?>
     <?php if($temp_sum > 0): ?><td><font color='green'>盈:￥<?php echo (number_format($temp_sum,2)); ?></font></td>
     <?php else: ?>
     <td><font color='red'>亏:￥<?php echo (number_format($temp_sum,2)); ?></font></td><?php endif; ?>
    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
    </table>
     <div class="green-black" style="float:right" id="page"><?php echo ($page); ?></div>