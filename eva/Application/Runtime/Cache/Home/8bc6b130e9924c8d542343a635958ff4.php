<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
$(document).ready(function(){
    $('input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea', '.form').iTextClear();
});
$('#info_form').ajaxForm({
	beforeSubmit: function(){return checkForm("#info_form")},
	async: true,
    success:  mynotice,  // post-submit callback
    dataType: 'json'
});

</script>
       <h1 class="page-title">公司信息</h1>
                <div class="container_12 clearfix leading">
                <div class="grid_12">
                    	<form class="form has-validation" id="info_form" action="/eva/index.php/home/setting/infoUpdate" method="post">
                            <div class="clearfix">
                                <label for="form-name" class="form-label">公司名称: <em>*</em></label>
                                <div class="form-input"><input type="text" id="form-name" name="info_name" required="required" placeholder="请输入公司名称" value=<?php echo ($info_list["info_name"]); ?> >
                                </div>
                            </div>
                            <div class="clearfix">
                                <label for="form-email" class="form-label">公司电话 <em>*</em></label>
                                <div class="form-input"><input type="number" id="form-number" name="info_tel" required="required" placeholder="请输入公司电话" value=<?php echo ($info_list["info_tel"]); ?> >
                                </div>
                            </div>
                            <div class="clearfix">
                                <label for="form-email" class="form-label">公司地址 <em>*</em></label>
                                <div class="form-input"><input type="text" id="form-add" name="info_add" required="required" placeholder="请输入公司地址" value=<?php echo ($info_list["info_add"]); ?> >
                                </div>
                            </div>
                            <div class="clearfix">
                                <label for="form-email" class="form-label">打印设置 </label>
                                <div class="form-input"><input type="text"  name="print" required="required" placeholder="分页打印行数，连续打印为 0" value=<?php echo ($info_list["print"]); ?> >
                                </div>
                            </div>
                             <div class="form-action clearfix">
                                <button class="button" type="submit">保存</button>
                                <button class="button" type="reset">重置</button>
                            </div>
                        </form>
                    </div>
                </div>