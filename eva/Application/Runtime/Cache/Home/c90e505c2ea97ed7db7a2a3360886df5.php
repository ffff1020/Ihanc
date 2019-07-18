<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
$(document).ready(function(){
	
   $('input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea', '.form').iTextClear(); 
   $.post("/ihanc/eva/index.php/Home/Setting/catTable", function(result){
	    $("#cat_table").html(result);
	  });
});
$('#cat_form').ajaxForm({
	beforeSubmit: function(){return checkForm("#cat_form")},
	//async: true,
    success: function(data){ 
    	mynotice(data); 
    	if(data.status==1){ 
    		$.post("/ihanc/eva/index.php/Home/Setting/catTable", function(result){
	        $("#cat_table").html(result);
	        $('#cat_form input[name="cat_name"]').val();
	 		 });
    		}
    	},  
    dataType: 'json'
});
function cat_name(id){
	$('#cat_dialog_form input[name="cat_name"]').val($('#'+id).text());
	$('#cat_dialog_form input[name="idcat"]').val(id);
	$( "#cat_name" ).dialog({
	      height: 200,
	      width: 350,
	      modal: true,
	      buttons: {
	        "修改": catNameUpdate,
	        "取消": function() {
	        $(this).dialog( "close" );
	        }
	      },
	    });
}
function catNameUpdate(){
	$('#cat_dialog_form').ajaxForm({
		async: true,
	    success: function(data){
	    	if(data.status==1){
	    		$.get("/ihanc/eva/index.php/Home/Setting/catTable", function(result){
	    		    $("#cat_table").html(result);
	    		  });
	    		$( "#cat_name" ).dialog("close");
	    	}
	    		mynotice(data);
	    },
	    dataType: 'json'
	});
	$('#cat_dialog_form').submit();
}
</script>
       <h1 class="page-title">种类管理</h1>
                <div class="container_12 clearfix leading" id="cat_main">
                <section class="grid_12">
                    	<form class="form has-validation" id='cat_form' action="/ihanc/eva/index.php/Home/Setting/catUpdate" method="post">
                            <div class="clearfix">
                                <label for="form-name" class="form-label">种类名称: <em>*</em></label>
                                <div class="form-input"><input type="text" id="form-name" name="cat_name" required="required" placeholder="请输入种类名称" value=<?php echo ($list["cat_name"]); ?> >
                                </div>
                            </div>
                             <div class="form-action clearfix">
                                <button class="button" type="submit">添加</button>
                                <button class="button" type="reset">重置</button>
                            </div>
                        </form>
                    </section>
                    
                   <section class="portlet grid_12 leading" id="cat_table"></section>
                 <section class=" grid_12 leading" >
                 <div class="message info"><h6>温馨提示:点击分类名称可进行修改</h6></div>
                 </section>
                </div>
<div id="cat_name" title="修改种类名称" style="display:none">
 <form action="/ihanc/eva/index.php/Home/Setting/catUpdate" method="post" id="cat_dialog_form">
    <fieldset>
      <input type="hidden" name=idcat>
      <input type="text" name="cat_name" id="cat_name" class="text ui-widget-content ui-corner-all">
    </fieldset>
  </form>
</div>