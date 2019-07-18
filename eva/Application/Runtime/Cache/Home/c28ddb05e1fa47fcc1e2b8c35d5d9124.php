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
   $.get("/ihanc/eva/index.php/Home/Purchase/purchaseTable", function(result){
    $("#purchase_table").html(result);
  });
});

$('#purchase_list_form').ajaxForm({
	beforeSubmit: check,
	async: true,
    success: function(data){ 
    	$("#purchase_table").html(data);
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
	    }  
	    return true;  
	}
	if(idsupply) return true;
	alert("请输入供应商,或者输入开始日期和结束日期!");
	return false;
}
</script>
<h1 class="page-title">历史进货单</h1>
<div class="container_12 clearfix leading">
<div class="grid_12">
<form class="form" action="/ihanc/eva/index.php/Home/Purchase/purchaseTable" id="purchase_list_form" method="post" onkeydown="if(event.keyCode==13){return false;}">
 <div class="clearfix">

                                <label for="form-birthday" class="form-label">开始日期</label>

                                <div class="form-input"><input type="date" name="sdate"   /></div>

                            </div>
                             <div class="clearfix">

                                <label for="form-birthday" class="form-label">结束日期</label>

                                <div class="form-input"><input type="date" name="edate"  value=<?php echo date("Y-m-d");?> /></div>

                            </div>
                            <div class="clearfix">

                                <label for="form-birthday" class="form-label">供应商</label>

                                <div class="form-input"><input type="text" name="sname"  onkeyup='getsupply(this)' onkeydown='selectSupply(this)' /></div>
                                <input type="hidden" name="idsupply">

                            </div>
                            <div class="form-action clearfix">

                                <button class="button" type="submit">查询</button>


                            </div>
</form>
<div class="grid_12"></div>
 <div class="clearfix"></div><p></p><p></p>
<div id="purchase_table"></div>

<div id="result" ></div>
</div>
</div>

<script>
function getsupply(obj){
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
	   $.post('/ihanc/eva/index.php/Home/Purchase/searchSupply',{'key':tx.val()},function(data){
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