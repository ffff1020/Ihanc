<?php if (!defined('THINK_PATH')) exit();?>
<style>
#result{border:1px solid #817FB2; display:none;background-color: #F1F19B;z-index:999;float:left;}
#result li{background:#FFF; text-align:left;}
#result li.cls{text-align:right;}
#result ul{padding:0px; margin:0px;} 
#result li a{display:block; padding:4px;margin:0px; cursor:pointer; color:#666;text-decoration:none;font-size:12px}
#result li a:hover{background:#D8D8D8; text-decoration:none; color:#000;}
#result li a.try{background:#D8D8D8; text-decoration:none; color:#000;}
</style>
<script>
$(document).ready(function(){
	//return;
	var mydata='<?php echo ($data); ?>';
	var data=JSON.parse(mydata);
	//alert(data.categories);
	var number=[];
	var sum=[];
	for(i=0;i<data.number.length;i++){
		number.push(parseInt(data.number[i]));
		sum.push(parseInt(data.sum[i]));
	}
//	alert(number);
	$('#container2').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: '本月客户销售金额前十'
        },
      
        xAxis: {
            categories: data.categories
        },
        yAxis: {
            min: 0,
            title: {
                text: '销售金额元/销售数量斤'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
        	 name: '数量',
             data: number
        },{
            name: '金额',
            data: sum
        }]
    });
});

$('#sale_list_form').ajaxForm({
	beforeSubmit: check,
	async: false,
    success: function(mydata){ 
    	//alert(mydata);
    	var data=JSON.parse(mydata);
    	var idsale=$('#sale_list_form input[name="idmember"]').val();
    	if(idsale==''){
    	var number=[];
    	var sum=[];
    	for(i=0;i<data.number.length;i++){
    		number.push(parseInt(data.number[i]));
    		sum.push(parseInt(data.sum[i]));
    	}
    	$('#container2').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: '本期客户销售金额前十'
            },
            subtitle:{
            	text: $('#sale_list_form input[name="sdate"]').val()+'到'+$('#sale_list_form input[name="edate"]').val()
            },
            xAxis: {
                categories: data.categories
            },
            yAxis: {
                min: 0,
                title: {
                    text: '销售金额元/销售数量斤'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
            	 name: '数量',
                 data: number
            },{
                name: '金额',
                data: sum
            }]
        });
    	}else{
    		 $('#goods').length && $('#goods').remove();
    		 var html='<div id="goods" ><fieldset><div id="chart2" style="width:60%;">haha</div></fieldset></div>';
    		 $('body').append(html);
    		 var title="供应商:"+$('#sale_list_form input[name="mname"]').val()+"进货分析";
    		    var number=[];
    	    	var sum=[];
    	    	for(i=0;i<data.number.length;i++){
    	    		number.push(parseInt(data.number[i]));
    	    		sum.push(parseInt(data.sum[i]));
    	    	}
    	    	$('#chart2').highcharts({
    	            chart: {
    	                type: 'column'
    	            },
    	            title: {
    	                text: title
    	            },
    	           
    	            xAxis: {
    	                categories: data.categories
    	            },
    	            yAxis: {
    	                min: 0,
    	                title: {
    	                    text: '销售金额元/销售数量斤'
    	                }
    	            },
    	            tooltip: {
    	                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
    	                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
    	                    '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
    	                footerFormat: '</table>',
    	                shared: true,
    	                useHTML: true
    	            },
    	            plotOptions: {
    	                column: {
    	                    pointPadding: 0.2,
    	                    borderWidth: 0
    	                }
    	            },
    	            series: [{
    	            	 name: '数量',
    	                 data: number
    	            },{
    	                name: '金额',
    	                data: sum
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
    	}
    	},  
   // dataType: 'json'
});

function check(){
	var sdate=$('#sale_list_form input[name="sdate"]').val();
	var edate=$('#sale_list_form input[name="edate"]').val();
	var idmember=$('#sale_list_form input[name="idmember"]').val();
	if(sdate&&edate){
		var start=new Date(sdate.replace("-", "/").replace("-", "/"));   
	    var end=new Date(edate.replace("-", "/").replace("-", "/"));  
	    if(end<start){  
	    	alert("结束日期必须大于开始日期!");
	    	$('#purchase_list_form input[name="edate"]').val('');
	        return false;
	    }else 
	    	return true; 
	}
	if(idmember==''){alert("请输入客户!"); return false;}
    return true;
}
</script>
<h1 class="page-title">客户分析统计</h1>
<div class="container_12 clearfix leading">
<div class="grid_12">
<form class="form" action="/ihanc/eva/index.php/Home/Statistics/saleAnalysis" id="sale_list_form" method="post" onkeydown="if(event.keyCode==13){return false;}">
 <div class="clearfix">

                                <label for="form-birthday" class="form-label">开始日期</label>

                                <div class="form-input"><input type="date" name="sdate" required  /></div>

                            </div>
                             <div class="clearfix">

                                <label for="form-birthday" class="form-label">结束日期</label>

                                <div class="form-input"><input type="date" name="edate" required value=<?php echo date("Y-m-d");?> /></div>

                            </div>
                            <div class="clearfix">

                                <label for="form-birthday" class="form-label">客户</label>

                                <div class="form-input"><input type="text" name="mname" required onkeyup='getmember(this)' onkeydown='selectmember(this)' /></div>
                                <input type="hidden" name="idmember">

                            </div>
                            <div class="form-action clearfix">

                                  <button class="button" type="submit">查询</button>


                            </div>
</form>
<div style="min-height:100px"></div>
<div class="grid_12"><div id="container2"></div></div>

 <div class="message info" style="margin-bottom:10px"><h6>温馨提示:不选择时间显示当月记录</h6></div>
 
<div id="result" ></div>
</div>
</div>

<script>
function getmember(obj){
	 var tx=$(obj); 
	// var index=0;
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
    var tp=tx.offset().top+45;
	 if ($.trim(tx.val()).length == 0){
  	        $('#result').hide();
	        $("#sale_list_form input[name='idmember']").val('');
           return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/ihanc/eva/index.php/home/sale/searchMember',{'key':tx.val()},function(data){
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.mname.length;
 		    for (var i = 0; i < len; i++) {
 			 trs+='<li style="list-style-type:none;"><a href="javascript: fillmember('+obj.idmember[i]+',\''+obj.mname[i]+'\')" title='+obj.idmember[i]+'>'+obj.mname[i]+'</a></li>';    
 	        }; 
     	$('#result').css({'width':tx.width()-10});
	    $('#result').html(trs).css('display','block');
	    $('#result').show().offset({top:tp,left:lf}); 
		}else{
			$('#result').hide();
		    tx.val('');
		    }
		 li = $("#result").find("li:eq(" + index + ")");
	     li.css("background", "#D8D8D8").siblings().css("background", "#fff"); 
		});
	 }
};
   var index=0;
   var li;
function selectmember(obj){
	var tx=$(obj); 
	var key = event.keyCode;  
   if (key == 13){
 	 var idmember= $("#result li:eq(" + index + ") a").attr('title');
 	    tx.val($("#result li:eq(" + index + ") a").text()); 
 	    $("#sale_list_form input[name='idmember']").val(idmember);
 	  $('#result').hide();
 	  return;
 	  };
   if (key == 38) {  
       index--; 
       if (index == 0) index = 9; 
   } ;
   if (key == 40) {  
       index++;  
       var len=$('#result li').length;
       if (index == len) index = 0;   
   }else index=0;
     li = $("#result").find("li:eq(" + index + ")");
	  li.css("background", "#D8D8D8").siblings().css("background", "#fff");
}
function fillmember(id,value){
	$("#sale_list_form input[name='mname']").val(value);
	$("#sale_list_form input[name='idmember']").val(id);
	$('#result').hide();
}

</script>