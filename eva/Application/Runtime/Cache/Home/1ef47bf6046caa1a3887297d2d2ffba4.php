<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
$(document).ready(function(){
   $('input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea', '.form').iTextClear(); 
   $.post("/ihanc/eva/index.php/Home/Setting/staffTable", function(result){
	    $("#staff_table").html(result);
	  });
});
$('#staff_form').ajaxForm({
	beforeSubmit: function(){return checkForm("#staff_form")},
	async: true,
    success: function(data){ 
    	mynotice(data); 
    	if(data.status==1){ 
    		$.get("/ihanc/eva/index.php/Home/Setting/staffTable", function(result){
	        $("#staff_table").html(result);
	        $('#staff_form input[name="staff_name"]').val();
	 		 });
    		}
    	},  
    dataType: 'json'
});
function staff_name(id){
	$('#staff_dialog_form input[name="staff_name"]').val($('#'+id).text());
	$('#staff_dialog_form input[name="idstaff"]').val(id);
	$( "#staff_name" ).dialog({
	      height: 200,
	      width: 350,
	      modal: true,
	      buttons: {
	        "修改": staffNameUpdate,
	        "取消": function() {
	        $(this).dialog( "close" );
	        }
	      },
	    });
}
function staffNameUpdate(){
	$('#staff_dialog_form').ajaxForm({
		async: true,
	    success: function(data){
	    	if(data.status==1){
	    		$.get("/ihanc/eva/index.php/Home/Setting/staffTable", function(result){
	    		    $("#staff_table").html(result);
	    		  });
	    		$( "#staff_name" ).dialog("close");
	    	}
	    		mynotice(data);
	    },
	    dataType: 'json'
	});
	$('#staff_dialog_form').submit();
}
</script>
       <h1 class="page-title">员工管理</h1>
                <div class="container_12 clearfix leading" id="staff_main">
                <section class="grid_12">
                    	<form class="form has-validation" id='staff_form' action="/ihanc/eva/index.php/Home/Setting/staffUpdate" method="post">
                            <div class="clearfix">
                                <label for="form-name" class="form-label">员工姓名: <em>*</em></label>
                                <div class="form-input"><input type="text" id="form-name" name="staff_name" required="required" placeholder="请输入员工姓名" value=<?php echo ($list["staff_name"]); ?> >
                                </div>
                            </div>
                             <div class="form-action clearfix">
                                <button class="button" type="submit">添加</button>
                                <button class="button" type="reset">重置</button>
                            </div>
                        </form>
                    </section>
                    
                   <section class="portlet grid_12 leading" id="staff_table"></section>
                 <section class=" grid_12 leading" >
                 <div class="message info"><h6>温馨提示:点击员工姓名可进行修改</h6></div>
                 </section>
                </div>
<div id="staff_name" title="修改员工姓名" style="display:none">
 <form action="/ihanc/eva/index.php/Home/Setting/staffUpdate" method="post" id="staff_dialog_form">
    <fieldset>
      <input type="hidden" name=idstaff>
      <input type="text" name="staff_name" id="staff_name" class="text ui-widget-content ui-corner-all">
    </fieldset>
  </form>
</div>