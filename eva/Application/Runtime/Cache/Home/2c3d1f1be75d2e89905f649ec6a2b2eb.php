<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<!--[if IE 7 ]>   <html lang="en" class="ie7 lte8"> <![endif]--> 
<!--[if IE 8 ]>   <html lang="en" class="ie8 lte8"> <![endif]--> 
<!--[if IE 9 ]>   <html lang="en" class="ie9"> <![endif]--> 
<!--[if gt IE 9]> <html lang="en"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<head><meta http-equiv="X-UA-Compatible" content="IE=IE9">

<!-- iPad Settings -->
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" /> 
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<!-- Adding "maximum-scale=1" fixes the Mobile Safari auto-zoom bug: http://filamentgroup.com/examples/iosScaleBug/ -->
<!-- iPad Settings End -->

<title>瀚盛销售管理系统</title>

<link rel="shortcut icon" href="favicon.ico">

<!-- iOS ICONS -->
<link rel="apple-touch-icon" href="touch-icon-iphone.png" />
<link rel="apple-touch-icon" sizes="72x72" href="touch-icon-ipad.png" />
<link rel="apple-touch-icon" sizes="114x114" href="touch-icon-iphone4.png" />
<link rel="apple-touch-startup-image" href="touch-startup-image.png">
<!-- iOS ICONS END -->

<!-- STYLESHEETS -->

<link rel="stylesheet" href="/eva/Application/Home/View/public/css/reset.css" media="screen" />
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/grids.css" media="screen" />
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/ui.css" media="screen" />
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/forms.css" media="screen" />
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/device/general.css" media="screen" />
<!--[if !IE]><!-->
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/device/tablet.css" media="only screen and (min-width: 768px) and (max-width: 991px)" />
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/device/mobile.css" media="only screen and (max-width: 767px)" />
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/device/wide-mobile.css" media="only screen and (min-width: 480px) and (max-width: 767px)" />
<!--<![endif]-->
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/jquery.uniform.css" media="screen" />
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/jquery.popover.css" media="screen">
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/jquery.itextsuggest.css" media="screen">
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/themes/lightblue/style.css" media="screen" />
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/jquery-ui.css"/>
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/jquery-ui-dialog.css"/>
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/mytable.css"/>
<link rel="stylesheet" href="/eva/Application/Home/View/public/css/page.css"/>


<style type = "text/css">
    #loading-container {position: absolute; top:50%; left:50%;}
    #loading-content {width:800px; text-align:center; margin-left: -400px; height:50px; margin-top:-25px; line-height: 50px;}
    #loading-content {font-family: "Helvetica", "Arial", sans-serif; font-size: 18px; color: black; text-shadow: 0px 1px 0px white; }
    #loading-graphic {margin-right: 0.2em; margin-bottom:-2px;}
    #loading {background-color:#abc4ff; background-image: -moz-radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); background-image: -webkit-radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); background-image: -o-radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); background-image: -ms-radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); background-image: radial-gradient(50% 50%, ellipse closest-side, #abc4ff, #87a7ff 100%); height:100%; width:100%; overflow:hidden; position: absolute; left: 0; top: 0; z-index: 99999;}
#dingwei{
 padding:10px;background-color:#dce9f9;
 width:300px;
 display:block; 
 position: fixed;
 top:50%;
 left:50%;
font-size:14px;
}

</style>

<!-- STYLESHEETS END -->

<!--[if lt IE 9]>
<script src="/eva/Application/Home/View/public/js/html5.js"></script>
<script type="text/javascript" src="/eva/Application/Home/View/public/js/selectivizr.js"></script>
<![endif]-->

</head>
<body style="overflow: hidden;">

    <div id="loading"> 
        <script type = "text/javascript"> 
            document.write("<div id='loading-container'><p id='loading-content'>" +
                           "<img id='loading-graphic' width='16' height='16' src='/eva/Application/Home/View/public/images/ajax-loader-abc4ff.gif' /> " +
                           "Loading...</p></div>");
        </script> 
    </div> 

    <div id="wrapper">
        <header>
            <h1><a href="#">vPad</a></h1>
            <nav>
                <div class="container_12">
                    <div class="grid_12">
                        <a href="logOut" class="button icon-with-text fr"><img src="/eva/Application/Home/View/public/images/navicons-small/129.png" alt=""/>退出</a>
                        <a href="#sale/tsale.html" title="配送批量录入" class="button icon-with-text fl"><img src="/eva/Application/Home/View/public/images/navicons-small/38.png" alt=""/>配送批量录入</a>
                        
                        <div class="user-info fr">
                                            欢迎您 <a href="#"><?php echo (cookie('nick')); ?></a>
                                            
                        </div>
                    </div>
                </div>
            </nav>
        </header>
        
        <section>
            <!-- Sidebar -->
            <aside>
                <nav class="drilldownMenu">
                    <h1>
                        <span class="title">主菜单</span>
                        <button title="Go Back" class="back">返回</button>
                    </h1>
                    <div class="clearfix" id="searchform">
                        <div class="searchcontainer">
                                <div><h4>瀚盛销售系统水产专用版</h4></div>
                        </div> 
                    </div>                        
                    <ul class="tlm">
                        <li class="hasul"><a href="#"><img src="/eva/Application/Home/View/public/images/navicons/111.png" alt=""/><span>系统设置</span></a>
                            <ul>
                            	<li ><a href="#setting/index" ><img src="/eva/Application/Home/View/public/images/navicons/171.png" alt=""/><span>公司信息</span></a></li>
                                <li><a href="#setting/staff" ><img src="/eva/Application/Home/View/public/images/navicons/112.png" alt=""/><span>公司员工</span></a></li>  
                                <li><a href="#setting/cat" ><img src="/eva/Application/Home/View/public/images/navicons/20.png" alt=""/><span>种类设置</span></a></li>
                                <li><a href="#setting/goods" ><img src="/eva/Application/Home/View/public/images/navicons/109.png" alt=""/><span>产品设置</span></a></li>
                           		<li><a href="#setting/pwd" ><img src="/eva/Application/Home/View/public/images/navicons/25.png" alt=""/><span>密码管理</span></a></li>
                           		<li><a href="#setting/start" ><img src="/eva/Application/Home/View/public/images/navicons/166.png" alt=""/><span>初期数据录入</span></a></li>
                        
                            </ul>
                        </li>
                        <li class="current"><a href="#index/dashboard" title="Dashboard" id="dashboard_menu"><img src="/eva/Application/Home/View/public/images/navicons/81.png" alt=""/><span>财务概况</span></a></li>
                      
                        <li class="hasul"><a href="#" title="进货管理"><img src="/eva/Application/Home/View/public/images/navicons/164.png" alt=""/><span>进货管理</span></a>
                            <ul>
                                <li><a href="#purchase/supply.html" title="供应商管理"><img src="/eva/Application/Home/View/public/images/navicons/111.png" alt=""/><span>供应商管理</span></a></li>
                                <li><a href="#purchase/purchase.html" title="进货录入"><img src="/eva/Application/Home/View/public/images/navicons/164.png" alt=""/><span>新进货单</span></a></li>
                                <li><a href="#purchase/psale.html" title="即买即卖"><img src="/eva/Application/Home/View/public/images/navicons/138.png" alt=""/><span>即买即卖</span></a></li>
                                <li><a href="#purchase/index.html" title="进货记录" id='purchase_index_menu'><img src="/eva/Application/Home/View/public/images/navicons/160.png" alt=""/><span>进货记录</span></a></li>
                                <li><a href="#purchase/scredit.html" title="应付管理" id="scredit_index_menu"><img src="/eva/Application/Home/View/public/images/navicons/133.png" alt=""/><span>应付管理</span></a></li>
                                <li><a href="#purchase/check.html" title="供应商对账"><img src="/eva/Application/Home/View/public/images/navicons/06.png" alt=""/><span>供应商对账</span></a></li>
                                <li><a href="#purchase/freight.html" title="员工垫付费用"><img src="/eva/Application/Home/View/public/images/navicons/145.png" alt=""/><span>员工垫付费用</span></a></li>
                            </ul>
                        </li>
                          
                        <li class="hasul"><a href="#" title="销售管理"><img src="/eva/Application/Home/View/public/images/navicons/138.png" alt=""/><span>销售管理</span></a>
                        <ul>
                                <li><a href="#sale/member.html" title="客户管理"><img src="/eva/Application/Home/View/public/images/navicons/112.png" alt=""/><span>客户管理</span></a></li>
                                <li><a href="#sale/sale.html" title="销售录入"><img src="/eva/Application/Home/View/public/images/navicons/138.png" alt=""/><span>新销售单</span></a></li>
                                <li><a href="#sale/index.html" title="销售记录" id='purchase_index_menu'><img src="/eva/Application/Home/View/public/images/navicons/160.png" alt=""/><span>销售记录</span></a></li>
                                <li><a href="#sale/mcredit.html" title="应收管理"><img src="/eva/Application/Home/View/public/images/navicons/132.png" alt=""/><span>应收管理</span></a></li>
                                <li><a href="#sale/check.html" title="客户对账"><img src="/eva/Application/Home/View/public/images/navicons/06.png" alt=""/><span>客户对账</span></a></li>
                                <li><a onclick="intime()" href=''><img src="/eva/Application/Home/View/public/images/navicons/138.png" alt=""/><span>实时销售</span></a></li>
                                <li><a href="#sale/tsale.html" title="配送批量录入"><img src="/eva/Application/Home/View/public/images/navicons/38.png" alt=""/><span>配送批量录入</span></a></li>
                            </ul>
                        </li>
                        <li><a href="#stock/index" title="库存管理"><img src="/eva/Application/Home/View/public/images/navicons/123.png" alt=""/><span>库存管理</span></a></li>

                        <li class="hasul"><a href="#"><img src="/eva/Application/Home/View/public/images/navicons/128.png" alt=""/><span>财务管理</span></a>
                            <ul>
                                <li><a href="#finance/bank" title="银行管理" ><img src="/eva/Application/Home/View/public/images/navicons/167.png" alt=""/><span>银行管理</span></a></li>
                                <li><a href="#finance/transfer" title="内部转帐"><img src="/eva/Application/Home/View/public/images/navicons/104.png" alt=""/><span>内部转账</span></a></li>
                                <li><a href="#finance/fee" title="费用管理"><img src="/eva/Application/Home/View/public/images/navicons/64.png" alt=""/><span>费用管理</span></a></li>
                                
                                <li><a href="#finance/income" title="收入管理"><img src="/eva/Application/Home/View/public/images/navicons/65.png" alt=""/><span>收入管理</span></a></li>
                                <li><a href="#finance/staff" title="预支管理"><img src="/eva/Application/Home/View/public/images/navicons/111.png" alt=""/><span>员工预支</span></a></li>
                                <li><a href="#finance/subject" title="会计科目"><img src="/eva/Application/Home/View/public/images/navicons/97.png" alt=""/><span>会计科目</span></a></li>
                           		<li><a href="#purchase/freight.html" title="员工垫付费用"><img src="/eva/Application/Home/View/public/images/navicons/145.png" alt=""/><span>员工垫付费用</span></a></li>
                            </ul>
                        </li>
                        <li class="hasul"><a href="#"><img src="/eva/Application/Home/View/public/images/navicons/137.png" alt=""/><span>统计报表</span></a>
                            <ul>
                                <li><a href="#statistics/salereport" title="销售统计"><img src="/eva/Application/Home/View/public/images/navicons/122.png" alt=""/><span>销售统计报表</span></a></li>
                                <li><a href="#statistics/profit" title="营业统计报表"><img src="/eva/Application/Home/View/public/images/navicons/77.png" alt=""/><span>营业统计报表</span></a></li>
                                <li><a href="#statistics/supply" title="供应商分析统计"><img src="/eva/Application/Home/View/public/images/navicons/16.png" alt=""/><span>供应商分析统计</span></a></li>
                                <li><a href="#statistics/member"><img src="/eva/Application/Home/View/public/images/navicons/95.png" alt=""/><span>客户分析统计</span></a></li>
                                <li><a href="#statistics/asset"><img src="/eva/Application/Home/View/public/images/navicons/97.png" alt=""/><span>总资产统计报表</span></a></li>
                                <li><a href="#statistics/fee"><img src="/eva/Application/Home/View/public/images/navicons/55.png" alt=""/><span>费用分析统计报表</span></a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- Sidebar End -->

            <section>
                <header>
                    <div class="container_12 clearfix">
                        <a href="#menu" class="showmenu button">Menu</a>
                        <h1 class="grid_12"></h1>
                    </div>
                </header>
                <section id="main-content" class="clearfix">
                </section>
                <footer class="clearfix">
                    <div class="container_12">
                        <div class="grid_12">
                            Copyright &copy; 2016. 瀚盛销售管理系统水产专用版  技术支持:15183022666 Theme by <a target="_blank" href="http://themeforest.net/user/vivantdesigns?ref=vivantdesigns">VivantDesigns</a>
                        <input type="hidden" id="path" value="/eva/index.php/Home/"/>
                        <input type="hidden" id="img" value="/eva/Application/Home/View/public/images"/>
                        <input type="hidden" id="exp" value=<?php echo (cookie('exp')); ?>>
                        </div>
                    </div>
                </footer>
            </section>

            <!-- Main Section End -->
        </section>
    </div>
    
    <!-- MAIN JAVASCRIPTS -->

    <script>window.jQuery || document.write("<script src='/eva/Application/Home/View/public/js/jquery.min.js'>\x3C/script>")</script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.tools.min.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.uniform.min.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.easing.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.ui.totop.js"></script>
  
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.itextclear.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.hashchange.min.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.drilldownmenu.js"></script>
	<script src="/eva/Application/Home/View/public/js/jquery-ui.js"></script>
	<script src="/eva/Application/Home/View/public/js/py.js"></script>
	<script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.form.js"></script>
	<script type="text/javascript" src="/eva/Application/Home/View/public/js/notice.js"></script>
    <script src="/eva/Application/Home/View/public/js/highcharts.js"></script>

    <!--[if lt IE 9]>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/PIE.js"></script>
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/ie.js"></script>
    <![endif]-->

    <script type="text/javascript" src="/eva/Application/Home/View/public/js/global.js"></script>
    <!-- MAIN JAVASCRIPTS END -->

    <!-- LOADING SCRIPT -->
    <script>
    $(window).load(function(){
        $("#loading").fadeOut(function(){
            $(this).remove();
            $('body').removeAttr('style');
        });
    });
    
    function intime(){
    	window.open("/eva/index.php/home/sale/intime");
    }
    </script>
   </body>
</html>