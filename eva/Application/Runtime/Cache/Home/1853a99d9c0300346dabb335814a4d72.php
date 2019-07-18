<?php if (!defined('THINK_PATH')) exit();?><style>
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
	   $('input[type=text]').iTextClear(); 
	   $.get("/ihanc/eva/index.php/Home/Sale/mcreditTable", function(result){
	    $("#mcredit_table").html(result);
	  });
	});
$('a.iTextClearButton').click(function(){
	 $.get("/ihanc/eva/index.php/Home/Sale/mcreditTable", function(result){
		    $("#mcredit_table").html(result);
		  });
});
function print_all(){
	window.open("/ihanc/eva/index.php/Home/Sale/printAll");
}
function getmember(obj){
	 var tx=$(obj); 
	// var index=0;
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
   var tp=tx.offset().top+45;
	 if ($.trim(tx.val()).length == 0){
 	        $('#result').hide();
	        $("#sale_list_form input[name='idmember']").val('');
	        $.get("/ihanc/eva/index.php/Home/Sale/mcreditTable", function(result){
	    	    $("#mcredit_table").html(result);
	    	  });
             return;
          }
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
	    getinfo(idmember);
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
	getinfo(id);
}
function getinfo(id){
	$.get("/ihanc/eva/index.php/Home/Sale/mcreditTable",{'idmember':id},function(result){
		$("#mcredit_table").html(result);
	});
}

</script>
<div id="mcredit_detail"></div>
<div class="container_12 clearfix leading" >
<h1 class="page-title">应收管理</h1>
<div class="grid_12">
<form class="form" action="/ihanc/eva/index.php/Home/Sale/screditTable" id="sale_list_form" method="post" onkeydown="if(event.keyCode==13){return false;}">
                    <div class="clearfix">
                      <label for="form-birthday" class="form-label">客户姓名:</label>
                      <div class="form-input"><input type="text" name="mname" required onkeyup='getmember(this)' onkeydown='selectmember(this)' /></div>
                     <input type="hidden" name="idmember">
  </div>
</form>
<br/>
<h6>应收款合计金额为:<em><font color='red'>￥<?php echo (number_format($ttl)); ?></font></em>元&nbsp;&nbsp;&nbsp;&nbsp;<a class="button" onclick='print_all()'>打印应收全报表</a></h6>
<div class="grid_12"></div>
 <div class="clearfix"></div><p></p><p></p>
<div id="mcredit_table"><img src="/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif">&nbsp;&nbsp;加载中,请稍候...</div>
<div id="result" ></div>
</div>
</div>
<section class=" grid_12 leading" style="width:98%">
  <div class="message info"><h6>温馨提示:双击客户姓名,可查看明细,进行收款</h6></div>
                 </section>