<?php if (!defined('THINK_PATH')) exit();?><script type="text/javascript" src="/ihanc/eva/Application/Home/View/public/js/ukey.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	load();
	$('input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea', '.form').iTextClear();
});
$('#pwd_form').ajaxForm({
	beforeSubmit: check,
	async: true,
    success:  function(data){
    	mynotice(data);
    	$('#btn').click();	
    },  // post-submit callback
    dataType: 'json'
});
function check(){
	var pwd=$('#pwd_form input[name=pwd]').val();
	var pwd1=$('#pwd_form input[name=pwd1]').val();
	var pwd0=$('#pwd_form input[name=pwd0]').val();
	if(pwd0==''){
		var obj={info:"请输入原密码"};
		mynotice(obj);
		return false;
	}
	if(pwd==''){
		var obj={info:"请输入新密码"};
		mynotice(obj);
		return false;
	}
	if(pwd1==pwd) return true;
	var obj={info:"两次输入的密码不同,请重输"};
	mynotice(obj);
	$('#pwd_form input[name=pwd]').val('');
	$('#pwd_form input[name=pwd1]').val('');
	return false;
}
</script>
<object id="plugin0" type="application/npsyunew3-plugin"  style="width:0;height:0"></object>
       <h1 class="page-title">修改密码</h1>
                <div class="container_12 clearfix leading">
                <div class="grid_12">
                    	<form class="form has-validation" id="pwd_form" action="/ihanc/eva/index.php/Home/Setting/pwdUpdate" method="post">
                           <input type="hidden" name="name" id="name">
                            <div class="clearfix">
                                <label for="form-name" class="form-label">原密码: <em>*</em></label>
                                <div class="form-input"><input type="password"  name="pwd0" required="required"  >
                                </div>
                            </div>
                            <div class="clearfix">
                                <label for="form-email" class="form-label">新密码 <em>*</em></label>
                                <div class="form-input"><input type="password" name="pwd" required="required"  >
                                </div>
                            </div>
                            <div class="clearfix">
                                <label for="form-email" class="form-label">新密码再一次 <em>*</em></label>
                                <div class="form-input"><input type="password"  name="pwd1" required="required"  >
                                </div>
                            </div>
                             <div class="form-action clearfix">
                                <button class="button" type="submit">保存</button>
                                <button class="button" type="reset" id="btn">重置</button>
                            </div>
                        </form>
                    </div>
                </div>