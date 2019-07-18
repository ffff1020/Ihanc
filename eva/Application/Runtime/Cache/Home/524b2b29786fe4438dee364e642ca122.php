<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
$(document).ready(function(){
   $('input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea', '.form').iTextClear(); 
   $.get("/ihanc/eva/index.php/Home/Finance/subTable", function(result){
	    $("#sub_table").html(result);
	  });
});
$('#sub_form').ajaxForm({
	beforeSubmit: function(){return checkForm("#sub_form")},
	async: true,
    success: function(data){ 
    	mynotice(data); 
    	if(data.status==1){ 
    		 $('#sub_form input[name="subject"]').val('');
    		$.get("/ihanc/eva/index.php/Home/Finance/subTable", function(result){
	        $("#sub_table").html(result);
	       
	 		 });
    		}
    	},  
    dataType: 'json'
});
function sub_name(id){
	$('#sub_dialog_form input[name="subject"]').val($('#'+id).text());
	$('#sub_dialog_form input[name="idsubject"]').val(id);
	$( "#sub_name" ).dialog({
	      height: 200,
	      width: 350,
	      modal: true,
	      buttons: {
	        "修改": subNameUpdate,
	        "取消": function() {
	        $(this).dialog( "close" );
	        }
	      },
	    });
}
function subNameUpdate(){
	$('#sub_dialog_form').ajaxForm({
		async: true,
	    success: function(data){
	    	if(data.status==1){
	    		$.get("/ihanc/eva/index.php/Home/Finance/subTable", function(result){
	    		    $("#sub_table").html(result);
	    		  });
	    		$( "#sub_name" ).dialog("close");
	    	}
	    		mynotice(data);
	    },
	    dataType: 'json'
	});
	$('#sub_dialog_form').submit();
}
</script>
       <h1 class="page-title">会计科目管理</h1>
                <div class="container_12 clearfix leading" id="sub_main">
                <section class="grid_12">
                    	<form class="form has-validation" id='sub_form' action="/ihanc/eva/index.php/Home/Finance/subAdd" method="post">
                            <div class="clearfix">
                                <label for="form-name" class="form-label">科目名称: <em>*</em></label>
                                <div class="form-input"><input type="text" id="form-name" name="subject" required="required" placeholder="请输入会计科目名称" >
                                </div>
                            </div>
                             <div class="clearfix">
                                <label for="form-name" class="form-label">科目类型: <em>*</em></label>
                                <div class="form-input">
                                <select name="cat">
                                <option value=1>支出类</option>
                                <option value=2>收入类</option>
                                </select>
                                </div>
                            </div>
                             <div class="form-action clearfix">
                                <button class="button" type="submit">添加</button>
                                <button class="button" type="reset">重置</button>
                            </div>
                        </form>
                    </section>
                    
                   <section class="portlet grid_12 leading" id="sub_table"></section>
                 <section class=" grid_12 leading" >
                 <div class="message info"><h6>温馨提示:点击科目名称可进行修改</h6></div>
                 </section>
                </div>
<div id="sub_name" title="修改科目名称" style="display:none">
 <form action="/ihanc/eva/index.php/Home/Finance/subAdd" method="post" id="sub_dialog_form">
    <fieldset>
      <input type="hidden" name=idsubject>
      <input type="text" name="subject" id="subject" class="text ui-widget-content ui-corner-all">
    </fieldset>
  </form>
</div>