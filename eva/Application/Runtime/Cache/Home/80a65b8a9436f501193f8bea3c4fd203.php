<?php if (!defined('THINK_PATH')) exit();?>  <script>
$(document).ready(function(){
   $.get("/ihanc/eva/index.php/Home/Statistics/profitTable", function(result){
    $("#profit_table").html(result);
  });
  // showpie();
});

$('#profit_form').ajaxForm({
	beforeSubmit: check,
	//async: true,
    success: function(data){ 
    	$("#profit_table").html(data);
    	},  
   // dataType: 'json'
});
function check(){
	var sdate=$('#profit_form input[name="sdate"]').val();
	var edate=$('#profit_form input[name="edate"]').val();
	if(sdate&&edate){
		var start=new Date(sdate.replace("-", "/").replace("-", "/"));   
	    var end=new Date(edate.replace("-", "/").replace("-", "/"));  
	    if(end<start){  
	    	alert("结束日期必须大于开始日期!");
	    	$('#sale_report_form input[name="edate"]').val('');
	        return false;  
	    } 
	   // $("#sale_report_table").html('');
	    return true;  
	}
	return false;
}


</script>
   <h1 class="page-title">营业统计报表</h1>
                <div class="container_12 clearfix leading"> 
                <div class="grid_12">
               
<form class="form" action="/ihanc/eva/index.php/Home/Statistics/profitTable" id="profit_form" method="post" >
 <div class="clearfix">

                                <label for="form-birthday" class="form-label">开始日期</label>

                                <div class="form-input"><input type="date" name="sdate" required  /></div>

                            </div>
                             <div class="clearfix">

                                <label for="form-birthday" class="form-label">结束日期</label>

                                <div class="form-input"><input type="date" name="edate" required value=<?php echo date("Y-m-d");?> /></div>

                            </div>
                            <div class="form-action clearfix">

                                <button class="button" type="submit" >查询</button>
								<a class="button" onclick="showpie()">显示各产品毛利</a>

                            </div>
</form></div><p>
  <div id="profit_table"><img alt="" src="/ihanc/eva/Application/Home/View/public/images/ajax-loader-ffffff.gif">加载中...</div>
                    </div>
                     <section class=" grid_12 leading" style="width:98%">
                 <div class="message info"><h6>温馨提示:双击表格,可查看该单产品毛利明细</h6></div>
                 </section>
<script>
function showpie(){
	   var sdate=$('#profit_form input[name=sdate]').val();	
	   var edate=$('#profit_form input[name=edate]').val();
	   if(sdate&&edate){
			var start=new Date(sdate.replace("-", "/").replace("-", "/"));   
		    var end=new Date(edate.replace("-", "/").replace("-", "/"));  
		    if(end<start){  
		    	alert("结束日期必须大于开始日期!");
		    	$('#sale_report_form input[name="edate"]').val('');
		        return false;  
		    } 
		    var title=sdate+"到"+edate+"毛利百分比饼状图";
	   }else var title="本月毛利百分比饼状图";
	   var html='<div id="ttl_profit_chart" ><fieldset><div id="chart" ></div></fieldset></div>';
		 $('body').append(html); 
		var ttl;
		var chart_data;
		 $.ajax({
			 type:"POST",
			 data:{sdate:sdate,edate:edate},
			 url:"/ihanc/eva/index.php/Home/Statistics/profitpie",
			 dataType: "json",
			 async: false,
			 success: function(data){
				if(data.info==1){ alert("您输入的时间段没有进货,不能查询!");return;}
				// if(data.info){ alert(data.info);return;}
				ttl=data.ttl_profit;
				chart_data=data.data;
			 },
		});
		var colors = Highcharts.getOptions().colors;
		var cdata=[];
		var gdata=[];
		var i=0;
		 $.each(chart_data,function(n,value) { 
	           cdata.push({
	        	   name: value.categories,
	        	   y: value.ttl_y,
	        	   color: colors[i]
	        	  });
	          
	        var drilldown=value.drilldown;
	        	 for (var j = 0; j <drilldown.categories.length; j++) {
	        		 var brightness = 0.2 - (j / drilldown.categories.length) / 5 ;
	        		 gdata.push({
	        		     name: drilldown.categories[j],
	        		     y:drilldown.y[j],
	        		     color: Highcharts.Color(colors[i]).brighten(brightness).get()
	        		 });
	        		}
	          i++;
	     });
		
		 $('#ttl_profit_chart').dialog({
			 width:600,
			 maxHeight:600,
			 title:title,
			 modal: true,
			 close:function(){$(this).remove();},
		     buttons: {
		    	 "关闭": function() { $(this).dialog( "close" ); },
		      },
		});


var categories = ['MSIE', 'Firefox', 'Chrome', 'Safari'],
name = 'Browser brands',
data = [{
     y: 55.11,
     color: colors[0],
     drilldown: {
         name: 'MSIE versions',
         categories: ['MSIE 6.0', 'MSIE 7.0', 'MSIE 8.0', 'MSIE 9.0'],
         data: [10.85, 7.35, 33.06, 2.81],
         color: colors[0]
     }
 }, {
     y: 21.63,
     color: colors[1],
     drilldown: {
         name: 'Firefox versions',
         categories: ['Firefox 2.0', 'Firefox 3.0', 'Firefox 3.5', 'Firefox 3.6', 'Firefox 4.0'],
         data: [0.20, 0.83, 1.58, 13.12, 5.43],
         color: colors[1]
     }
 }, {
     y: 11.94,
     color: colors[2],
     drilldown: {
         name: 'Chrome versions',
         categories: ['Chrome 5.0', 'Chrome 6.0', 'Chrome 7.0', 'Chrome 8.0', 'Chrome 9.0',
             'Chrome 10.0', 'Chrome 11.0', 'Chrome 12.0'],
         data: [0.12, 0.19, 0.12, 0.36, 0.32, 9.91, 0.50, 0.22],
         color: colors[2]
     }
 }, {
     y: 7.15,
     color: colors[3],
     drilldown: {
         name: 'Safari versions',
         categories: ['Safari 5.0', 'Safari 4.0', 'Safari Win 5.0', 'Safari 4.1', 'Safari/Maxthon',
             'Safari 3.1', 'Safari 4.1'],
         data: [4.55, 1.42, 0.23, 0.21, 0.20, 0.19, 0.14],
         color: colors[3]
     }
 }, {
     y: 2.14,
     color: colors[4],
     drilldown: {
         name: 'Opera versions',
         categories: ['Opera 9.x', 'Opera 10.x', 'Opera 11.x'],
         data: [ 0.12, 0.37, 1.65],
         color: colors[4]
     }
 }];


//Build the data arrays
var browserData = [];
var versionsData = [];
for (var i = 0; i < data.length; i++) {
if(i<(data.length-1)){
//add browser data
browserData.push({
 name: categories[i],
 y: data[i].y,
 color: data[i].color
});
}
//add version data
for (var j = 0; j < data[i].drilldown.data.length; j++) {
 var brightness = 0.2 - (j / data[i].drilldown.data.length) / 5 ;
 versionsData.push({
     name: data[i].drilldown.categories[j],
     y: data[i].drilldown.data[j],
     color: Highcharts.Color(data[i].color).brighten(brightness).get()
 });
}
}

//Create the chart
$('#chart').highcharts({
chart: {
 type: 'pie'
},
title: {
 text: title+"本期毛利为:￥"+ttl
},
yAxis: {
 title: {
     text: '毛利百分比'
 }
},
plotOptions: {
 pie: {
     shadow: false,
     center: ['50%', '50%']
 }
},
tooltip: {
 valueSuffix: '元'
},
series: [{
 name: '种类',
 data: cdata,
 size: '60%',
 dataLabels: {
     formatter: function() {
         return this.y > 5 ? this.point.name : null;
     },
     color: 'white',
     distance: -30
 }
},
{
 name: '产品',
 data:  gdata,
 size: '80%',
 innerSize: '60%',
 dataLabels: {
     formatter: function() {
         // display only if larger than 1
         return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.percentage.toFixed(2) +'%'  : null;
     }
 }
}]
});
}
</script>