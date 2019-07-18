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

function check(){
	var sdate=$('#sale_list_form input[name="sdate"]').val();
	var edate=$('#sale_list_form input[name="edate"]').val();
	var idmember=$('#sale_list_form input[name="idmember"]').val();
	if(sdate&&edate){
		var start=new Date(sdate.replace("-", "/").replace("-", "/"));   
	    var end=new Date(edate.replace("-", "/").replace("-", "/"));  
	    if(end<start){  
	    	alert("结束日期必须大于开始日期!");
	    	$('#sale_list_form input[name="edate"]').val('');
	        return;
	    }  
	}
	if(idmember=='') {alert("请输入客户");return ;}
	window.open("/ihanc/eva/index.php/Home/Sale/salecheck/mname/"+$('#sale_list_form input[name="mname"]').val()+"/idmember/"+idmember+"/edate/"+edate+"/sdate/"+sdate);
}
</script>
<h1 class="page-title">客户对账单</h1>
<div class="container_12 clearfix leading">
<div class="grid_12">
<form class="form" action="/ihanc/eva/index.php/Home/Sale/salecheck" id="sale_list_form" method="post" onkeydown="if(event.keyCode==13){return false;}">
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

                                <a class="button" onclick="check()">查询</a>


                            </div>
</form>
<div style="min-height:100px"></div>
<div class="grid_12"></div>

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
	   $.post('/ihanc/eva/index.php/Home/Sale/searchMember',{'key':tx.val()},function(data){
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