<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
$(document).ready(function(){
    $('input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea', '.form').iTextClear();
    //载入产品表格
    $.post("/eva/index.php/home/setting/goodsTable", function(result){
	    $("#goods_table").html(result);
	  });
});
    //post表单,添加修改产品
    $('#goods_form').ajaxForm({
    	beforeSubmit: function(){return checkForm("#goods_form")},
    	async: true,
        success: function(data){ 
        	mynotice(data); 
        	if(data.status==1){ 
        		$.get("/eva/index.php/home/setting/goodsTable", function(result){
    	        $("#goods_table").html(result);
    	        $('#goods_form input').val('');
    	 		 });
        		}
        	},  // post-submit callback
        dataType: 'json'
    
});
    //生成产品编码
function make_py(){
	var str = document.getElementById("gname").value;
	var py=makePy(str);
	document.getElementById("gsn").value=py;
}
//搜索框的动画设置    
$('#searchform .searchcontainer').find('input[type=button]').click(function(){ 
    $('#s').val($('#s').attr('placeholder'));
    $('.searchbox').animate({marginRight: 0}).next().fadeOut();
});
</script>
                <h1 class="page-title">产品管理</h1>
                <div class="container_12 clearfix leading">
                    <div class="grid_12">
                    	<form class="form has-validation" id="goods_form" action="/eva/index.php/home/setting/goodsUpdate" method="post">
							<input type="hidden" name='idgoods' id="idgoods">
                            <div class="clearfix">

                                <label for="form-name" class="form-label">产品名称<em>*</em></label>

                                <div class="form-input"><input type="text" id="gname" name="gname" required="required" placeholder="请输入产品名称" onblur="make_py();" /></div>

                            </div>

                            <div class="clearfix">

                                <label for="form-email" class="form-label">产品编码<em>*</em></label>

                                <div class="form-input"><input type="text" id="gsn" name="gsn" required="required" placeholder="系统直接生成拼音首字母,也可修改" /></div>

                            </div>

                            <div class="clearfix">
                                <label for="form-birthday" class="form-label">产品种类<em>*</em></label>
                                <div class="form-input" id='idcat'>
                                <p>
                                <select name="idcat" >
                                <?php if(is_array($cat_list)): $i = 0; $__LIST__ = $cat_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value=<?php echo ($vo["idcat"]); ?>><?php echo ($vo["cat_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select></p>
                                </div>
                            </div>
                            <div class="form-action clearfix">

                                <button class="button" type="submit">保存</button>

                                <button class="button" type="reset">重置</button>

                            </div>

                        </form>
                    </div>
                </div>
                <div class="container_12 clearfix leading" style="width:80%;">
                 <div class="grid_6" id="searchform">
                        <div class="searchcontainer" >
                            <div class="searchbox">
                                <input type="text" id="s"  autocomplete="off" placeholder="请输入产品名称或编码进行搜索" style=" position: absolute;top: 7px;">
                            </div>
                             <input type="button" value="取消" style="display:none; position: absolute;right: 25px;top: 10px;"/>
                        </div>
                  </div>
                  </div>
                <div class="clearfix"></div>
                <div class="container_12 clearfix leading">
                 <div class="grid_12" id="goods_table"></div>
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
		  $.post("/eva/index.php/home/setting/goodsTable", {name:$(this).val()},function(result){
			    $("#goods_table").html(result);
			  });
	  }
  });
  </script>