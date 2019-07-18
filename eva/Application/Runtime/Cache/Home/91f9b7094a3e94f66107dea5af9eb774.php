<?php if (!defined('THINK_PATH')) exit();?>  <script>
$(document).ready(function(){
   $.get("/ihanc/eva/index.php/Home/Statistics/feeTable", function(result){
    $("#fee_table").html(result);
  });
 
});

$('#fee_form').ajaxForm({
	beforeSubmit: check,
	async: true,
    success: function(data){ 
    	//alert(data);
    	$("#fee_table").html(data);
    	},  
   // dataType: 'json'
});
function check(){
	var sdate=$('#fee_form input[name="sdate"]').val();
	var edate=$('#fee_form input[name="edate"]').val();
	if(sdate&&edate){
		var start=new Date(sdate.replace("-", "/").replace("-", "/"));   
	    var end=new Date(edate.replace("-", "/").replace("-", "/"));  
	    if(end<start){  
	    	alert("结束日期必须大于开始日期!");
	    	$('#fee_form input[name="edate"]').val('');
	        return false;  
	    } 
	   // $("#sale_report_table").html('');
	    return true;  
	}
	return false;
}


</script>
 
 <h1 class="page-title">费用分析统计报表</h1>
                <div class="container_12 clearfix leading"> 
                <div class="grid_12">
               
<form class="form" action="/ihanc/eva/index.php/Home/Statistics/feeTable" id="fee_form" method="post" >
 <div class="clearfix">

                                <label for="form-birthday" class="form-label">开始日期</label>

                                <div class="form-input"><input type="date" name="sdate" required  /></div>

                            </div>
                             <div class="clearfix">

                                <label for="form-birthday" class="form-label">结束日期</label>

                                <div class="form-input"><input type="date" name="edate" required value=<?php echo date("Y-m-d");?> /></div>

                            </div>
                            <div class="clearfix">

                                <label for="form-birthday" class="form-label">关键字</label>

                                <div class="form-input"><input type="text" name="summary"/></div>

                            </div>
                            <div class="form-action clearfix">

                                <button class="button" type="submit" >查询</button>
								
                            </div>
</form></div><p>
  <div id="fee_table"><img alt="" src="/ihanc/eva/Application/Home/View/public/images/ajax-loader-ffffff.gif">加载中...</div>
                    </div>