<?php if (!defined('THINK_PATH')) exit();?>
<section class="portlet grid_12 leading"> 
                        <header>
                            <h2><?php echo ($info); ?></h2>
                        </header>
                        <section >
                            <table class="full" style="font-size:12px;width:90%">
                                <tbody> 
                             
 								<?php if(is_array($sale_report)): $i = 0; $__LIST__ = $sale_report;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
 								<td id=<?php echo ($vo["idgoods"]); ?>><a onclick='compare(<?php echo ($vo["idgoods"]); ?>)'><?php echo ($vo["gname"]); ?></a>平均价格:￥<?php echo ($vo["price"]); ?></td>
 								<td style="width:70%"><div  class="progress"><span style="width:<?php echo ($vo["percent"]); ?>%;"><b><?php echo ($vo["percent"]); ?>%</b></span></div></td> 
                                <td style="width:60px" class="ar"><?php echo (number_format($vo["number"])); ?>/<?php echo ($ttl); ?></td>
 								</tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody> 
 
                            </table>
                        </section></section> 
              
 <script>

 function compare(idgoods){
	// alert( $('#goods').length);
	 $('#goods').length && $('#goods').remove();
	 var html='<div id="goods" ><fieldset><div id="chart2" style="width:60%;">haha</div></fieldset></div>';
	 $('body').append(html);
	// $('#chart2').empty();   
	 var ticks=new Array();
     var number = new Array();
     var ymax=0;
	 $.get("/eva/index.php/Home/Statistics/compare",{'idgoods':idgoods},function(result){
		    var data=$.parseJSON(result);
		   // len=data.number.length;
		    for (var i = 0; i < 13; i++) {
		    	if(data.number[i]){
		    	number.push(parseInt(data.number[i]));
		    //	if(parseInt(data.number[i])>ymax) ymax=data.number[i];
		    	ticks.push(data.ticks[i]);
		    	}else
		    		ticks.push(i);
		    }

		    var title=$('#'+idgoods).find('a').html()+"--销量统计图表";
		    $('#chart2').highcharts({
		        title: {
		            text: title,
		            x: -20 //center
		        },
		        
		        xAxis: {
		            categories: ticks
		        },
		        yAxis: {
		            title: {
		                text: '月销售数量 (斤)'
		            },
		            plotLines: [{
		                value: 0,
		                width: 1,
		                color: '#808080'
		            }]
		        },
		        tooltip: {
		            valueSuffix: '斤'
		        },
		       
		        series: [{
		            name: $('#'+idgoods).find('a').html(),
		            data: number
		        }]
		    });
		    
		    
		    $('#goods').dialog({
				 width:800,
				 maxHeight:600,
				 title:title,
				 modal: true,
				 close:function(){$(this).remove();},
			     buttons: {
			    	 "关闭": function() { $(this).dialog( "close" ); },
			        
			      },
			});
		});
	
 }


 </script>