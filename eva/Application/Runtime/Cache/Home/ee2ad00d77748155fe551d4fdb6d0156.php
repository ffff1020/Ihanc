<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
$(document).ready(function(){
    $('input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea', '.form').iTextClear();
  });
 $('#fee_form').ajaxForm({
		beforeSubmit: function(){
			var sum=$('#fee_form input[name="sum"]').val();
			if(sum==''){
				alert("请输入产生费用金额!");
				return false;
			}
			if(isNaN(sum)){
				alert("请在费用金额中输入正确的数字!");
				$('#fee_form input[name="sum"]').val('');
				return false;
			}
		},
		async: true,
	    success: function(data){ 
	    	//alert(data);return;
	    	mynotice(data); 
	    	if(data.status==1){ 
	    		 $('#fee_form input[name="sum"]').val('');
	    		 $('#fee_form textarea').val('');
	    		}
	    	},  
	    dataType: 'json'
	});
</script>
                <h1 class="page-title">费用管理</h1>
                <div class="container_12 clearfix leading">
                    <div class="grid_12">
                    	<form class="form has-validation" id="fee_form" action="/ihanc/eva/index.php/Home/Finance/feeAdd" method="post">
                           <div class="clearfix">
                                <label for="form-birthday" class="form-label">会计科目<em>*</em></label>
                                <div class="form-input" >
                                <select name="idsubject" >
                                <?php if(is_array($subject_list)): $i = 0; $__LIST__ = $subject_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value=<?php echo ($vo["idsubject"]); ?>><?php echo ($vo["subject"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                                </div>
                            </div>

                   
                            <div class="clearfix">
                                <label for="form-birthday" class="form-label">金额:<em>*</em></label>
                                <div class="form-input" >
                               <input type="number" name="sum"/>
                                </div>
                            </div>
                            
                             <div class="clearfix">
                                <label for="form-birthday" class="form-label">付款银行<em>*</em></label>
                                <div class="form-input" >
                                <select name="idbank" >
                                <?php if(is_array($bank_list)): $i = 0; $__LIST__ = $bank_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value=<?php echo ($vo["idbank"]); ?>><?php echo ($vo["bname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                                </div>
                            </div>
                            
                            <div class="clearfix">
                                <label for="form-birthday" class="form-label">备注:</label>
                                <div class="form-input" >
                                 <textarea rows="3" cols="20" name="summary"> </textarea>
                                </div>
                            </div>
                            
                            <div class="form-action clearfix">

                                <button class="button" type="submit">保存</button>

                                <button class="button" type="reset">重置</button>

                            </div>

                        </form>
                    </div>
                </div>