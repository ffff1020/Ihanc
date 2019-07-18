<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
$(document).ready(function(){
    $('input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea', '.form').iTextClear();
  });
 $('#transfer_form').ajaxForm({
		beforeSubmit: function(){
			var sum=$('#transfer_form input[name="sum"]').val();
			if(sum==''){
				alert("请输入转账金额!");
				return false;
			}
			if(isNaN(sum)){
				alert("请在转账金额中输入正确的数字!");
				$('#transfer_form input[name="sum"]').val('');
				return false;
			}
		},
		async: true,
	    success: function(data){ 
	    	mynotice(data); 
	    	if(data.status==1){ 
	    		 $('#transfer_form input[name="sum"]').val('');
	    		}
	    	},  
	    dataType: 'json'
	});
</script>
                <h1 class="page-title">银行间转账</h1>
                <div class="container_12 clearfix leading">
                    <div class="grid_12">
                    	<form class="form has-validation" id="transfer_form" action="/ihanc/eva/index.php/Home/Finance/transfer" method="post">
                           <div class="clearfix">
                                <label for="form-birthday" class="form-label">转出银行<em>*</em></label>
                                <div class="form-input" >
                                <select name="out_bank" >
                                <?php if(is_array($bank_list)): $i = 0; $__LIST__ = $bank_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value=<?php echo ($key); ?>><?php echo ($vo["bname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                                </div>
                            </div>

                            <div class="clearfix">
                                <label for="form-birthday" class="form-label">转入银行<em>*</em></label>
                                <div class="form-input" >
                                
                                <select name="in_bank" >
                                <?php if(is_array($bank_list)): $i = 0; $__LIST__ = $bank_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key > 1): ?><option value=<?php echo ($key); ?> selected="selected"><?php echo ($vo["bname"]); ?></option>
                                <?php else: ?>
                                <option value=<?php echo ($key); ?>><?php echo ($vo["bname"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                                </div>
                            </div>
                            <div class="clearfix">
                                <label for="form-birthday" class="form-label">金额:<em>*</em></label>
                                <div class="form-input" >
                               <input type="number" name="sum"/>
                                </div>
                            </div>
                            <div class="form-action clearfix">

                                <button class="button" type="submit">保存</button>

                                <button class="button" type="reset">重置</button>

                            </div>

                        </form>
                    </div>
                </div>