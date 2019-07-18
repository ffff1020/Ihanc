<?php if (!defined('THINK_PATH')) exit();?>
 <h1 class="page-title">销售数量统计报表</h1>
                <div class="container_12 clearfix leading"> 
                <div class="grid_12">
              <table  class="bordered" style="width:100%;font-size:12px">
    <thead> 
    <tr>
        <th>库存总额</th> 
    	<th>银行金额</th> 
    	<th>应收款总额</th> 
    	<th>应付款总额</th>
    	<th>总资产</th> 
    </tr>
    </thead>
    <tbody>
    <tr>
    <?php if(is_array($asset_list)): $i = 0; $__LIST__ = $asset_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><td>￥<?php echo (number_format($vo)); ?></td><?php endforeach; endif; else: echo "" ;endif; ?>
    <td>￥<?php echo (number_format($ttl)); ?></td>
    </tr>
    </tbody>
              </table> <br><br>
              <div id="container" style="min-width:500px;height:400px"></div> 
                </div>
                </div>
<script>
$(document).ready(function(){
	var mytime=new Array();
	var bank=new Array();
	var mcredit=new Array();
	var scredit=new Array();
	var stock=new Array();
	$.ajax({
		 type:"GET",
		 url:"/ihanc/eva/index.php/Home/Statistics/mytest",
		 dataType: "json",
		 async: false,
		 success: function(data){
			var len=data.length;
			for(var i=0;i<len;i++){
				//alert(data[i]['bank']);
				bank.push(parseInt(data[i]['bank']));
				mcredit.push(parseInt(data[i]['mcredit']));
				var temp=parseInt(data[i]['scredit'])+parseInt(data[i]['freight']);
				temp=(-1)*temp;
				scredit.push(temp);
				stock.push(parseInt(data[i]['stock']));
				mytime.push(data[i]['time']);
			}
		 }
	});
	//alert(mytime);
    $('#container').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: '历史资产报表'
        },
        xAxis: {
            categories: mytime
        },
        yAxis: {
            //min: 0,
            title: {
                text: '总资产金额'
            },
            stackLabels: {
                enabled: true,
                style: {
                    fontWeight: 'bold',
                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                }
            }
        },
        legend: {
            align: 'right',
            x: -70,
            verticalAlign: 'top',
            y: 20,
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
            borderColor: '#CCC',
            borderWidth: 1,
            shadow: false
        },
        tooltip: {
            formatter: function() {
                return '<b>'+ this.x +'</b><br/>'+
                    this.series.name +': '+ Math.round(this.y/this.point.stackTotal*100) +'%<br/>'
                   ;
            }
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                    shadow: false
                }
            }
        },
        series: [{
            name: '银行金额',
            data: bank
        }, {
            name: '应收金额',
            data: mcredit
        }, {
            name: '应付金额',
            data: scredit
        }, {
            name: '库存金额',
            data: stock
        }]
    });
});				
</script>