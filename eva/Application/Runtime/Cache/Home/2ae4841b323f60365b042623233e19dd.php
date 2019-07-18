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
	var mydata='<?php echo ($data); ?>';
	var data=JSON.parse(mydata);
	var number=[];
	var sum=[];
	for(i=0;i<data.number.length;i++){
		number.push(parseInt(data.number[i]));
		sum.push(parseInt(data.sum[i]));
	}
	$('#container').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: '本月供应商进货金额前十'
        },
      
        xAxis: {
            categories: data.categories
        },
        yAxis: {
            min: 0,
            title: {
                text: '进货金额元/进货数量斤'
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
$('#purchase_list_form').ajaxForm({
	beforeSubmit: check,
	async: false,
    success: function(mydata){ 
    	var data=JSON.parse(mydata);
    	var idsupply=$('#purchase_list_form input[name="idsupply"]').val();
    	if(idsupply==''){
    	var number=[];
    	var sum=[];
    	for(i=0;i<data.number.length;i++){
    		number.push(parseInt(data.number[i]));
    		sum.push(parseInt(data.sum[i]));
    	}
    	$('#container').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: '本期供应商进货金额前十'
            },
            subtitle:{
            	text: $('#purchase_list_form input[name="sdate"]').val()+'到'+$('#purchase_list_form input[name="edate"]').val()
            },
            xAxis: {
                categories: data.categories
            },
            yAxis: {
                min: 0,
                title: {
                    text: '进货金额元/进货数量斤'
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
    		 var title="供应商:"+$('#purchase_list_form input[name="sname"]').val()+"进货分析";
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
    	           // subtitle:{
    	           // 	text: $('#purchase_list_form input[name="sdate"]').val()+'到'+$('#purchase_list_form input[name="edate"]').val()
    	           // },
    	            xAxis: {
    	                categories: data.categories
    	            },
    	            yAxis: {
    	                min: 0,
    	                title: {
    	                    text: '进货金额元/进货数量斤'
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
	var sdate=$('#purchase_list_form input[name="sdate"]').val();
	var edate=$('#purchase_list_form input[name="edate"]').val();
	var idsupply=$('#purchase_list_form input[name="idsupply"]').val();
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
	if(idsupply==''){alert("请输入供应商!"); return false;}
    return true;
}
</script>
<h1 class="page-title">供应商分析统计</h1>
<div class="container_12 clearfix leading">
<div class="grid_12">
<form class="form" action="/ihanc/eva/index.php/Home/Statistics/supplyAnalysis" id="purchase_list_form" method="post" onkeydown="if(event.keyCode==13){return false;}">
 <div class="clearfix">

                                <label for="form-birthday" class="form-label">开始日期</label>

                                <div class="form-input"><input type="date" name="sdate" required  /></div>

                            </div>
                             <div class="clearfix">

                                <label for="form-birthday" class="form-label">结束日期</label>

                                <div class="form-input"><input type="date" name="edate" required value=<?php echo date("Y-m-d");?> /></div>

                            </div>
                            <div class="clearfix">

                                <label for="form-birthday" class="form-label">供应商</label>

                                <div class="form-input"><input type="text" name="sname" required onkeyup='getsupply(this)' onkeydown='selectSupply(this)' /></div>
                                <input type="hidden" name="idsupply">

                            </div>
                            <div class="form-action clearfix">

                                <button class="button" type="submit">查询</button>


                            </div>
</form>
<div class="grid_12"></div>
 <div class="clearfix"></div><p></p><p></p>
<div id="container"></div>

<div id="result" ></div>
</div>
</div>

<script>
function getsupply(obj){
	//alert('/ihanc/eva/index.php/home/purchase/searchSupply');
	 var tx=$(obj); 
	// var index=0;
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
    var tp=tx.offset().top+45;
	 if ($.trim(tx.val()).length == 0){
  	        $('#result').hide();
	        $("#purchase_list_form input[name='idsupply']").val('');
           return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/ihanc/eva/index.php/home/purchase/searchSupply',{'key':tx.val()},function(data){
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.sname.length;
 		    for (var i = 0; i < len; i++) {
 			 trs+='<li style="list-style-type:none;"><a href="javascript: fillsupply('+obj.idsupply[i]+',\''+obj.sname[i]+'\')" title='+obj.idsupply[i]+'>'+obj.sname[i]+'</a></li>';    
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
function selectSupply(obj){
	var tx=$(obj); 
	var key = event.keyCode;  
   if (key == 13){
 	 var idsupply= $("#result li:eq(" + index + ") a").attr('title');
 	    tx.val($("#result li:eq(" + index + ") a").text()); 
 	    $("#purchase_list_form input[name='idsupply']").val(idsupply);
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
function fillsupply(id,value){
	$("#purchase_list_form input[name='sname']").val(value);
	$("#purchase_list_form input[name='idsupply']").val(id);
	$('#result').hide();
}

</script>