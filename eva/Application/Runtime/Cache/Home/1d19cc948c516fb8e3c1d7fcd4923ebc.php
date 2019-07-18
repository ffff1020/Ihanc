<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
$(document).ready(function(){
    $('input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea', '.form').iTextClear();
    //载入产品表格
    $.get("/ihanc/eva/index.php/Home/Purchase/supplyTable", function(result){
	    $("#supply_table").html(result);
	  });
});
//post表单,添加修改产品
$('#supply_form').ajaxForm({
	beforeSubmit: function(){return checkForm("#supply_form")},
	async: true,
    success: function(data){ 
    	mynotice(data); 
    	if(data.status==1){ 
    		$.get("/ihanc/eva/index.php/Home/Purchase/supplyTable", function(result){
	        $("#supply_table").html(result);
	        $('#supply_form input').val('');
	 		 });
    		}
    	},  // post-submit callback
    dataType: 'json'

});
//生成产品编码
function make_py(){
var str = document.getElementById("sname").value;
var py=makePy(str);
document.getElementById("ssn").value=py;
}
//搜索框的动画设置    
$('#searchform .searchcontainer').find('input[type=button]').click(function(){ 
    $('#s').val($('#s').attr('placeholder'));
    $('.searchbox').animate({marginRight: 0}).next().fadeOut();
});
</script>
<h1 class="page-title">供应商管理</h1>
                <div class="container_12 clearfix leading">
                    <div class="grid_12">
                    	<form class="form has-validation" id="supply_form" action="/ihanc/eva/index.php/Home/Purchase/supplyUpdate" method="post">
							<input type="hidden" name='idsupply' id="idsupply">
                            <div class="clearfix">
                                <label for="form-name" class="form-label">供应商姓名<em>*</em></label>
                                <div class="form-input"><input type="text" id="sname" name="sname" required="required" placeholder="请输入供应商姓名" onblur="make_py();" /></div>
                            </div>
                            <div class="clearfix">
                                <label for="form-email" class="form-label">供应商编码<em>*</em></label>
                                <div class="form-input"><input type="text" id="ssn" name="ssn" required="required" placeholder="系统直接生成拼音首字母,也可修改" /></div>
                            </div>
                            <div class="clearfix">
                                <label for="form-birthday" class="form-label">联系电话</label>
                                <div class="form-input" >
                                <input type="text" id="stel" name="stel"  />
                                </div>
                            </div>
                            <div class="form-action clearfix">

                                <button class="button" type="submit">保存</button>

                                <button class="button" type="reset">取消</button>

                            </div>

                        </form>
                    </div>
                </div>
                <div class="container_12 clearfix leading" style="width:80%;">
                 <div class="grid_6" id="searchform">
                        <div class="searchcontainer" >
                            <div class="searchbox">
                                <input type="text" id="s"  autocomplete="off" placeholder="请输入供应商姓名或编码进行搜索" style=" position: absolute;top: 7px;">
                            </div>
                             <input type="button" value="取消" style="display:none; position: absolute;right: 25px;top: 10px;"/>
                        </div>
                  </div>
                  </div>
                <div class="clearfix"></div>
                <div class="container_12 clearfix leading">
                 <div class="grid_12" id="supply_table"></div>
                 <section class=" grid_12 leading" style="width:98%">
                 <div class="message info"><h6>温馨提示:双击表格,可进行修改</h6></div>
                 </section>
                 </div>
<script>
  $('#s').focus(function(){
	  $('.searchbox').animate({marginRight: 70}).next().fadeIn();
  });
  $('#s').blur(function(){
	  $('.searchbox').animate({marginRight:0}).next().fadeOut();
  });
  $('#s').keyup(function(){
	  var key = event.keyCode;
	  if(((key>47)&&(key<106))||(key==8)||(key==32)){
		  $.post("/ihanc/eva/index.php/Home/Purchase/supplyTable", {name:$(this).val()},function(result){
			    $("#supply_table").html(result);
			  });
	  }
  });
  </script>