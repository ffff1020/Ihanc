/**
 * Created by Administrator on 2017/6/17 0017.
 */
app.controller('printSaleCreditController',function ($scope,$stateParams,myhttp,$filter,$localStorage,$timeout) {
    var check=!!window.ActiveXObject || "ActiveXObject" in window;
    $scope.nostyle={'margin-top':"0px"};
    if(check===false){
        $scope.mystyle={'margin-top':"-60px"};
    }
    $scope.doPrint=function () {
        if(!!window.ActiveXObject || "ActiveXObject" in window){
            try {
                var myDoc = {
                    settings:{topMargin:0,   //100,代表10毫米
                        leftMargin:20,
                        bottomMargin:500,
                        rightMargin:20},
                    documents: document,
                    /*
                     要打印的div 对象在本文档中，控件将从本文档中的 id 为 'page1' 的div对象，
                     作为首页打印id 为'page2'的作为第二页打印            */
                    copyrights: '杰创软件拥有版权  www.jatools.com' // 版权声明,必须
                };
                document.getElementById("jatoolsPrinter").print(myDoc, false); // 直接打印，不弹出打印机设置对话框
                window.close();
            }catch (e) {
                //  alert(e.name + ": " + e.message + "。可能是没有安装相应的控件或插件,请下载驱动程序!");
                window.open('vendor/jatoolsPrinter_free.zip','_self');
            }
        }else {

            window.print();
            window.close();
        }
    };
    $scope.preview=function () {
        if(!!window.ActiveXObject || "ActiveXObject" in window){
            try {
                var myDoc = {
                    settings:{topMargin:0,   //100,代表10毫米
                        leftMargin:20,
                        bottomMargin:500,
                        rightMargin:20},
                    documents: document,
                    /*
                     要打印的div 对象在本文档中，控件将从本文档中的 id 为 'page1' 的div对象，
                     作为首页打印id 为'page2'的作为第二页打印            */
                    copyrights: '杰创软件拥有版权  www.jatools.com' // 版权声明,必须
                };
                 document.getElementById("jatoolsPrinter").printPreview(myDoc, false); // 直接打印，不弹出打印机设置对话框
                 window.close();
            }catch (e) {
                //  alert(e.name + ": " + e.message + "。可能是没有安装相应的控件或插件,请下载驱动程序!");
                window.open('vendor/jatoolsPrinter_free.zip','_self');
            }
        }
    }
    $scope.member_name=$stateParams.member_name;
    $scope.now=$filter('date')(new Date(),'yyyy-MM-dd HH:mm:ss');
    if($stateParams.sale_id.indexOf('payCheck')!==-1){       //销售统计中打印
        var data={};
        if($stateParams.sale_id.length>9){
             data.sdate=$stateParams.sale_id.substr(8,19);
             data.edate=$stateParams.sale_id.substr(28);
         }
         data.member_id=$stateParams.member_id;
         console.log(data);
        myhttp.getData('/index/sale/printPayCheck','GET',data).then(function (res) {
            $scope.info=res.data.info;
            $scope.address=$scope.info.cadd.split("+");
            $scope.ttl=res.data.ttl;
            $scope.credits=res.data.credit;
            $scope.more=$scope.credits.length%$scope.info.print_lopo>Math.floor($scope.info.print_lopo/2)||$scope.credits.length%$scope.info.print_lopo===0;
            if($scope.info.print_lopo>0){
                $scope.pNum=$scope.more?Math.ceil($scope.credits.length/$scope.info.print_lopo)+1:Math.ceil($scope.credits.length/$scope.info.print_lopo);
                $scope.pages=new Array(Math.ceil($scope.credits.length/$scope.info.print_lopo));
                for(var i=0;i<Math.ceil($scope.credits.length/$scope.info.print_lopo);i++){
                    $scope.pages[i]=new Array(0);
                    for(var j=0;j<$scope.info.print_lopo&&(i*$scope.info.print_lopo+j)<$scope.credits.length;j++){
                        $scope.pages[i].push($scope.credits[i*$scope.info.print_lopo+j]);
                    }
                }
            }else{
                $scope.pages=new Array(0);
                $scope.pages.push(res.data.credit);
                $scope.pNum=1;
            }
            $scope.$on('ngRepeatFinished', function() {
                $scope.doPrint();
                $localStorage.summary="";
            });

        });
         return;
    }
    myhttp.getData('/index/sale/printCredit','GET',{member_id:$stateParams.member_id,sale_id:$stateParams.sale_id})
        .then(function (res) {
            $scope.info=res.data.info;
            $scope.address=$scope.info.cadd.split("+");
            $scope.ttl=res.data.ttl;
            $scope.credits=res.data.credit;
            $scope.more=$scope.credits.length%$scope.info.print_lopo>Math.floor($scope.info.print_lopo/2)||$scope.credits.length%$scope.info.print_lopo===0;
            if($scope.info.print_lopo>0){
                $scope.pNum=$scope.more?Math.ceil($scope.credits.length/$scope.info.print_lopo)+1:Math.ceil($scope.credits.length/$scope.info.print_lopo);
                $scope.pages=new Array(Math.ceil($scope.credits.length/$scope.info.print_lopo));
                for(var i=0;i<Math.ceil($scope.credits.length/$scope.info.print_lopo);i++){
                    $scope.pages[i]=new Array(0);
                    for(var j=0;j<$scope.info.print_lopo&&(i*$scope.info.print_lopo+j)<$scope.credits.length;j++){
                        $scope.pages[i].push($scope.credits[i*$scope.info.print_lopo+j]);
                    }
                }
            }else{
                $scope.pages=new Array(0);
                $scope.pages.push(res.data.credit);
                $scope.pNum=1;

            }
            $scope.$on('ngRepeatFinished', function() {
                $timeout(function(){
                    $scope.doPrint();
                    $localStorage.summary="";
                },500);

            });
        });
});
app.controller('printSaleController',function ($scope,$stateParams,myhttp,$localStorage,$filter) {
    var check=!!window.ActiveXObject || "ActiveXObject" in window;
    $scope.nostyle={'margin-top':"0px"};
    if(check===false){
        $scope.mystyle={'margin-top':"-60px"};
    }
    $scope.doPrint=function () {
        if(!!window.ActiveXObject || "ActiveXObject" in window){
            try {
                var myDoc = {
                    settings:{topMargin:100,   //100,代表10毫米
                        leftMargin:20,
                        bottomMargin:500,
                        rightMargin:10},
                    documents: document,
                    /*
                     要打印的div 对象在本文档中，控件将从本文档中的 id 为 'page1' 的div对象，
                     作为首页打印id 为'page2'的作为第二页打印            */
                    copyrights: '杰创软件拥有版权  www.jatools.com' // 版权声明,必须
                };
                document.getElementById("jatoolsPrinter").print(myDoc, false); // 直接打印，不弹出打印机设置对话框
                window.close();
            }catch (e) {
                //  alert(e.name + ": " + e.message + "。可能是没有安装相应的控件或插件,请下载驱动程序!");
                window.open('vendor/jatoolsPrinter_free.zip','_blank');
            }
        }else {
            window.print();
            window.close();
        }
    };
    $scope.preview=function () {
        if(!!window.ActiveXObject || "ActiveXObject" in window){
            try {
                var myDoc = {
                    settings:{topMargin:0,   //100,代表10毫米
                        leftMargin:20,
                        bottomMargin:500,
                        rightMargin:20},
                    documents: document,
                    /*
                     要打印的div 对象在本文档中，控件将从本文档中的 id 为 'page1' 的div对象，
                     作为首页打印id 为'page2'的作为第二页打印            */
                    copyrights: '杰创软件拥有版权  www.jatools.com' // 版权声明,必须
                };
                document.getElementById("jatoolsPrinter").printPreview(myDoc, false); // 直接打印，不弹出打印机设置对话框
                window.close();
            }catch (e) {
                //  alert(e.name + ": " + e.message + "。可能是没有安装相应的控件或插件,请下载驱动程序!");
                window.open('vendor/jatoolsPrinter_free.zip','_self');
            }
        }
    };
    $scope.member_name=$stateParams.member_name;
    //$scope.summary=$localStorage.summary;
    //$scope.now=$filter('date')(new Date(),'yyyy-MM-dd HH:mm:ss');
    //console.log($stateParams.sale_id);return;

    if($stateParams.sale_id==='0'){
        $scope.now1=$scope.now=$filter('date')(new Date(),'yyyy-MM-dd HH:mm:ss');
        myhttp.getData('/index/sale/printInfo','GET')
            .then(function(res){
                $scope.info = res.data.info;
                $scope.address=$scope.info.cadd.split("+");
                $scope.credits = $localStorage.credits;
                $scope.ttl = $localStorage.ttl;
                $scope.credit = $localStorage.credit;
                $scope.summary=$scope.info.print_remark>0?$localStorage.summary:"";
                $scope.more=$scope.credits.length%$scope.info.print_lopo>Math.floor($scope.info.print_lopo/2)||$scope.credits.length%$scope.info.print_lopo===0;
                if($scope.info.print_lopo>0){
                    $scope.pNum=$scope.more?Math.ceil($scope.credits.length/$scope.info.print_lopo)+1:Math.ceil($scope.credits.length/$scope.info.print_lopo);
                    $scope.pages=new Array(Math.ceil($scope.credits.length/$scope.info.print_lopo));
                    for(var i=0;i<Math.ceil($scope.credits.length/$scope.info.print_lopo);i++){
                        $scope.pages[i]=new Array(0);
                        for(var j=0;j<$scope.info.print_lopo&&(i*$scope.info.print_lopo+j)<$scope.credits.length;j++){
                            $scope.pages[i].push($scope.credits[i*$scope.info.print_lopo+j]);
                        }
                    }
                }else{
                    $scope.pages=new Array(0);
                    $scope.pages.push($localStorage.credits);
                    //console.log($scope.pages);
                    $scope.pNum=1;
                }
                $scope.printTel=$localStorage.tel !== false;
                $scope.tel=$localStorage.tel;
               // alert($scope.tel);
                $scope.$on('ngRepeatFinished', function () {
                    $scope.doPrint();
                    $localStorage.summary="";
            });
        });
    }else {
        myhttp.getData('/index/sale/printSale', 'GET', {sale_id: $stateParams.sale_id,member_id:$stateParams.member_id})
            .then(function (res) {
                $scope.now1=$filter('date')(new Date(),'yyyy-MM-dd HH:mm:ss');
                $scope.now = $stateParams.time;
                $scope.info = res.data.info;
                $scope.address=$scope.info.cadd.split("+");
                $scope.ttl = res.data.ttl;
                $scope.credits = res.data.credits;
                $scope.credit = res.data.credit;
                $scope.summary=$scope.info.print_remark>0?res.data.summary:"";
                $scope.more=$scope.credits.length%$scope.info.print_lopo>Math.floor($scope.info.print_lopo/2)||$scope.credits.length%$scope.info.print_lopo===0;
                if($scope.info.print_lopo>0){
                    $scope.pNum=$scope.more?Math.ceil($scope.credits.length/$scope.info.print_lopo)+1:Math.ceil($scope.credits.length/$scope.info.print_lopo);
                    $scope.pages=new Array(Math.ceil($scope.credits.length/$scope.info.print_lopo));
                    for(var i=0;i<Math.ceil($scope.credits.length/$scope.info.print_lopo);i++){
                        $scope.pages[i]=new Array(0);
                        for(var j=0;j<$scope.info.print_lopo&&(i*$scope.info.print_lopo+j)<$scope.credits.length;j++){
                            $scope.pages[i].push($scope.credits[i*$scope.info.print_lopo+j]);
                        }
                    }
                }else{
                    $scope.pages=new Array(0);
                    $scope.pages.push(res.data.credits);
                    $scope.pNum=1;
                }/*
                if(angular.isDefined($localStorage.tel)&&$localStorage.tel!==false){
                    $scope.printTel=true;
                    $scope.tel=$localStorage.tel;
                }else
                $scope.printTel=false;
                $scope.tel=$localStorage.tel;*/
                $scope.$on('ngRepeatFinished', function () {
                    $scope.doPrint();
                    $localStorage.summary="";
                });
            });
    }
});
app.controller('printPurchaseCreditController',function ($scope,$stateParams,myhttp,$filter) {
    var check=!!window.ActiveXObject || "ActiveXObject" in window;
    $scope.nostyle={'margin-top':"0px"};
    if(check===false){
        $scope.mystyle={'margin-top':"-60px"};
    }
    $scope.doPrint=function () {
        if(!!window.ActiveXObject || "ActiveXObject" in window){
            try {
                var myDoc = {
                    settings:{topMargin:0,   //100,代表10毫米
                        leftMargin:20,
                        bottomMargin:500,
                        rightMargin:20},
                    documents: document,
                    /*
                     要打印的div 对象在本文档中，控件将从本文档中的 id 为 'page1' 的div对象，
                     作为首页打印id 为'page2'的作为第二页打印            */
                    copyrights: '杰创软件拥有版权  www.jatools.com' // 版权声明,必须
                };
                document.getElementById("jatoolsPrinter").print(myDoc, false); // 直接打印，不弹出打印机设置对话框
                window.close();
            }catch (e) {
                //  alert(e.name + ": " + e.message + "。可能是没有安装相应的控件或插件,请下载驱动程序!");
                window.open('vendor/jatoolsPrinter_free.zip','_self');
            }
        }else {
            window.print();
            window.close();
        }
    };
    $scope.preview=function () {
        if(!!window.ActiveXObject || "ActiveXObject" in window){
            try {
                var myDoc = {
                    settings:{topMargin:0,   //100,代表10毫米
                        leftMargin:20,
                        bottomMargin:500,
                        rightMargin:20},
                    documents: document,
                    /*
                     要打印的div 对象在本文档中，控件将从本文档中的 id 为 'page1' 的div对象，
                     作为首页打印id 为'page2'的作为第二页打印            */
                    copyrights: '杰创软件拥有版权  www.jatools.com' // 版权声明,必须
                };
                document.getElementById("jatoolsPrinter").printPreview(myDoc, false); // 直接打印，不弹出打印机设置对话框
                window.close();
            }catch (e) {
                //  alert(e.name + ": " + e.message + "。可能是没有安装相应的控件或插件,请下载驱动程序!");
                window.open('vendor/jatoolsPrinter_free.zip','_self');
            }
        }
    }
    $scope.supply_name=$stateParams.supply_name;
    $scope.now=$filter('date')(new Date(),'yyyy-MM-dd HH:mm:ss');
    myhttp.getData('/index/purchase/printCredit','GET',{supply_id:$stateParams.supply_id,purchase_id:$stateParams.purchase_id})
        .then(function (res) {
            $scope.info=res.data.info;
            $scope.address=$scope.info.cadd.split("+");
            $scope.ttl=res.data.ttl;
            $scope.credits=res.data.credit;
            $scope.more=$scope.credits.length%$scope.info.print_lopo>Math.floor($scope.info.print_lopo/2)||$scope.credits.length%$scope.info.print_lopo===0;
            if($scope.info.print_lopo>0){
                $scope.pNum=$scope.more?Math.ceil($scope.credits.length/$scope.info.print_lopo)+1:Math.ceil($scope.credits.length/$scope.info.print_lopo);
                $scope.pages=new Array(Math.ceil($scope.credits.length/$scope.info.print_lopo));
                for(var i=0;i<Math.ceil($scope.credits.length/$scope.info.print_lopo);i++){
                    $scope.pages[i]=new Array(0);
                    for(var j=0;j<$scope.info.print_lopo&&(i*$scope.info.print_lopo+j)<$scope.credits.length;j++){
                        $scope.pages[i].push($scope.credits[i*$scope.info.print_lopo+j]);
                    }
                }
            }else{
                $scope.pages=new Array(0);
                $scope.pages.push(res.data.credit);
                $scope.pNum=1;
            }
            $scope.$on('ngRepeatFinished', function() {
                $scope.doPrint();
            });
        });
});
app.controller('printAllController',function ($scope,myhttp) {
    $scope.printTime="";
    myhttp.getData('/index/sale/printAll','GET',null).then(function (res) {
        $scope.printTime=res.data.printTime;
        $scope.data=res.data.data;
        $scope.$on('ngRepeatFinished', function() {
            $scope.doPrint();
        });
    });
    $scope.doPrint=function () {
        if(!!window.ActiveXObject || "ActiveXObject" in window){
            try {
                var myDoc = {
                    settings:{topMargin:0,   //100,代表10毫米
                        leftMargin:20,
                        bottomMargin:500,
                        rightMargin:20},
                    documents: document,
                    /*
                     要打印的div 对象在本文档中，控件将从本文档中的 id 为 'page1' 的div对象，
                     作为首页打印id 为'page2'的作为第二页打印            */
                    copyrights: '杰创软件拥有版权  www.jatools.com' // 版权声明,必须
                };
                document.getElementById("jatoolsPrinter").print(myDoc, false); // 直接打印，不弹出打印机设置对话框
                window.close();
            }catch (e) {
                //  alert(e.name + ": " + e.message + "。可能是没有安装相应的控件或插件,请下载驱动程序!");
                window.open('vendor/jatoolsPrinter_free.zip','_self');
            }
        }else {
            window.print();
            window.close();
        }
    };

});
app.filter('myfilter', function() {
    return function(value) {
         value =value.split(':');
         return value[value.length-1];
    }
});
