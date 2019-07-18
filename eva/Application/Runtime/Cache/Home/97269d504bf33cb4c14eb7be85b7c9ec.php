<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<!--[if IE 7 ]>   <html lang="en" class="ie7 lte8"> <![endif]--> 
<!--[if IE 8 ]>   <html lang="en" class="ie8 lte8"> <![endif]--> 
<!--[if IE 9 ]>   <html lang="en" class="ie9"> <![endif]--> 
<!--[if gt IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<head>
<meta charset="utf-8">
<!--[if lte IE 9 ]><meta http-equiv="X-UA-Compatible"content="IE=IE9"><![endif]-->

<!-- iPad Settings -->
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" /> 
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<!-- Adding "maximum-scale=1" fixes the Mobile Safari auto-zoom bug: http://filamentgroup.com/examples/iosScaleBug/ -->
<!-- iPad End -->

<title>瀚盛销售系统水产版</title>

<!-- iOS ICONS -->
<link rel="apple-touch-icon" href="touch-icon-iphone.png" />
<link rel="apple-touch-icon" sizes="72x72" href="touch-icon-ipad.png" />
<link rel="apple-touch-icon" sizes="114x114" href="touch-icon-iphone4.png" />
<link rel="apple-touch-startup-image" href="touch-startup-image.png">
<!-- iOS ICONS END -->

<!-- STYLESHEETS -->

<link rel="stylesheet" media="screen" href="/eva/Application/Home/View/public/css/reset.css" />
<link rel="stylesheet" media="screen" href="/eva/Application/Home/View/public/css/grids.css" />
<link rel="stylesheet" media="screen" href="/eva/Application/Home/View/public/css/style.css" />
<link rel="stylesheet" media="screen" href="/eva/Application/Home/View/public/css/ui.css" />
<link rel="stylesheet" media="screen" href="/eva/Application/Home/View/public/css/jquery.uniform.css" />
<link rel="stylesheet" media="screen" href="/eva/Application/Home/View/public/css/forms.css" />
<link rel="stylesheet" media="screen" href="/eva/Application/Home/View/public/css/themes/lightblue/style.css" />

<style type = "text/css">
    #loading-container {position: absolute; top:50%; left:50%;}
    #loading-content {width:800px; text-align:center; margin-left: -400px; height:50px; margin-top:-25px; line-height: 50px;}
    #loading-content {font-family: "Helvetica", "Arial", sans-serif; font-size: 18px; color: black; text-shadow: 0px 1px 0px white; }
    #loading-graphic {margin-right: 0.2em; margin-bottom:-2px;}
    #loading {background-color:#abc4ff; background-image: -moz-radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); background-image: -webkit-radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); background-image: -o-radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); background-image: -ms-radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); background-image: radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); height:100%; width:100%; overflow:hidden; position: absolute; left: 0; top: 0; z-index: 99999;}
</style>

<!-- STYLESHEETS END -->

<!--[if lt IE 9]>
<script src="/eva/Application/Home/View/public/js/html5.js"></script>
<script type="text/javascript" src="/eva/Application/Home/View/public/js/selectivizr.js"></script>
<![endif]-->
</head>

<body class="login" style="overflow: hidden;" >

    <div id="loading"> 

        <script type = "text/javascript"> 
            document.write("<div id='loading-container'><p id='loading-content'>" +
                           "<img id='loading-graphic' width='16' height='16' src='/eva/Application/Home/View/public/images/ajax-loader-abc4ff.gif' /> " +
                           "加载中...</p></div>");
        </script> 

    </div> 

    <div class="login-box">
    	<section class="login-box-top">
            <header>
                <h2 class="logo ac"></h2>
            </header>
            <section>
            
                <form  class="has-validation"   style="margin-top: 30px">
                   <div id="info" style="color: red"><?php echo ($info); ?></div>
                    <div class="user-pass">
                        <input type="text" id="username" class="full" value="" name="name" required="required" placeholder="请输入用户名"  required="required" />

                        <input type="password" id="password" class="full" value="" name="password" required="required"  />
                        <p>&nbsp;</p>
                        <div class="user-pass">
                      <label >请输入验证码：</label>
                            <input type="text" id="code" name="code"  style="font-size: large;width: 30%">
                            <input type="button" id="getCodeButton"  value="发送验证码" onclick="getCode()">
                        </div>


                    </div>
                    <p class="clearfix">
                        <input type="button" class="fr" value="登录" onclick="login()"/>
                    </p>
                </form>
            </section>

    	</section>
	</div>
    <form id="formID" method = 'post'  action = '/eva/index.php/Home/Login/telLogin' >
        <input type="hidden" name="authData"/>
    </form>
    <!-- MAIN JAVASCRIPTS -->

    <script>window.jQuery || document.write("<script src='/eva/Application/Home/View/public/js/jquery.min.js'>\x3C/script>")</script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.tools.min.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.uniform.min.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.easing.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.ui.totop.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/global.js"></script>
    <script src="/eva/Application/Home/View/public/js/rsa.js"></script>
    <script src="/eva/Application/Home/View/public/js/jsbn.js"></script>


    <!-- MAIN JAVASCRIPTS END -->

    <!-- LOADING SCRIPT -->
    <script>
    $(window).load(function(){
    	$('#dingwei').remove();
        $("#loading").fadeOut(function(){
			$('#dingwei').remove();
            $(this).remove();
            $('body').removeAttr('style');
        });
    });
    var i=60;
    var t;
    function getCode() {
        var name=$("#username").val();
        if(name=='') {$('#info').html("请输入用户名！");return false;}
        $.get("/eva/index.php/Home/Login/getCode",{name:name},function (data) {
            arr=data.split('{"success":');
           // if(arr.length==1){ $('#info').html("超过最大发送次数，请稍后再试！");return}
           // alert(arr[1].substr(1,1));
            switch(arr[1].substr(1,1)) {
                case "0":
                    $('#info').html("用户名不存在，请核对！");
                    $("#username").val('').focus();
                    return false;
                    break;
                case "1":
                    $('#info').html("验证码已发送至尾号为："+data.substr(0,4)+"的手机！");
                    $('#getCodeButton').attr("disabled", true);
                    updateTime();
                    break;
                case "2":
                    $('#info').html("超过最大发送次数，请稍后再试！");
                    break;
            }
        });
    }
    function updateTime() {
        var getCodeButton=$('#getCodeButton');
        if(i<0){
            clearTimeout(t);
            getCodeButton.attr("disabled", false);
            getCodeButton.val("发送验证码");
            i=60;
            return false;
        }else{
            getCodeButton.val(i+"秒后重发");
            i--;
            t=setTimeout("updateTime()",1000)
        }
    }
    function login() {
        var name=encodeURI($("#username").val());
        var pwd=$('#password').val();
        var code=$('#code').val();
        if(name=='') {$('#info').html("请输入用户名！");return false;}
        if(pwd==''){$('#info').html("请输入密码！");return false;}
        if(code==''){$('#info').html("请输入验证码！");return false;}
        var audata=mBase64(name+":"+pwd+":"+code);
        $('#formID').find('input').val(audata);
        $('#formID').submit();

    }
    function mBase64(input) {
        // public key notice the '\'
        var pem = '-----BEGIN PUBLIC KEY-----\
                MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCyDR+V/DU0CyYWakhPSjLwWVmC\
                mMqo3uugmVXPitly7BqltGDW0c1PbcQ4Y+O/lukAa3qBvxEGqwhZSQokUCQ/mCHt\
                FfmwlGmhHpSLZKKtPVOCKyZGCW6JdQs2ijKVgmks3jxSQ0ceeTrKU6f4KWL89DYq\
                CTqExJISjSAq5MAarQIDAQAB\
                -----END PUBLIC KEY-----';

        var key = RSA.getPublicKey(pem);
        return RSA.encrypt(input, key);
    }
    </script>
    <!-- LOADING SCRIPT -->

</body>

</html>