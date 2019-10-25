/**
 * Created by Administrator on 2017/7/14 0014.
 */
app.controller('DashBoardController', function ($scope,myhttp,$rootScope,$modal,$filter,$localStorage) {
    var now = new Date();
    now.setDate(now.getDate()+1);
    $scope.now1=$filter('date')(now,'yyyy-MM-dd');
    now.setDate(now.getDate()+1);
    $scope.now2=$filter('date')(now,'yyyy-MM-dd');
    now.setDate(now.getDate()+1);
    $scope.now3=$filter('date')(now,'yyyy-MM-dd');

    $scope.query=function (first) {
        if(first===0){
            $scope.date=$filter('date')($scope.sdate,'yyyy-MM-dd');
            if($filter('date')($scope.sdate,'yyyy-MM-dd')===$filter('date')(new Date(),'yyyy-MM-dd'))
                $scope.today="今日";
            else $scope.today=$scope.date;
        }else {
            $scope.date=$filter('date')(new Date(),'yyyy-MM-dd');
            $scope.today="今日";
        }
        myhttp.getData('/index/statistics/dashGetSum','GET',{date:$scope.date})
            .then(function(res){
                $scope.sum=res.data.sum;
                $scope.credit=res.data.credit;
                $scope.cash=res.data.cash;
                $scope.income=res.data.income;
                $scope.cost=res.data.cost;
                $scope.onWay=res.data.onWay;
                if(res.data.etime){
                    if(confirm("您已经3天没有备份数据，请备份！"))
                    {
                        $scope.data();
                    }
                }

            });
    };
    $scope.query(1);
    $scope.show=function(type){
        var myscope = $rootScope.$new();
        myscope.type=type;
        myscope.date=$scope.date;
        $modal.open({
            templateUrl: 'admin/statistics/saleToday.html',
            controller: 'SaleTodayController',
            // size:'sm',
            scope:myscope
        });
    };
    $scope.more=function (list) {
        var myscope = $rootScope.$new();
        myscope.purchase=list;
        $modal.open({
            templateUrl: 'admin/statistics/onWay.html',
            controller: 'onWayController',
            scope:myscope
        });
    }
    $scope.receiveGoods=function (list) {
        var myscope = $rootScope.$new();
        myscope.purchase=list;
        var mymodel=$modal.open({
            templateUrl: 'admin/statistics/receiveGoods.html',
            controller: 'receiveGoodsController',
            scope:myscope
        });
        mymodel.result.then(function () {
            myhttp.getData('/index/statistics/receiveGoods','GET',{purchase_id:list.purchase_id,finish:list.finish})
                .then(function (res) {
                    $scope.onWay=res.data.onWay;
                })
        });
    }
    $scope.showCredit=function () {
        var myscope = $rootScope.$new();
        myscope.date=$scope.date;
        $modal.open({
            templateUrl: 'admin/statistics/creditToday.html',
            controller: 'CreditTodayController',
            scope:myscope
        });
    }
    $scope.showCash=function () {
        var myscope = $rootScope.$new();
        myscope.date=$scope.date;
        $modal.open({
            templateUrl: 'admin/statistics/cashToday.html',
            controller: 'CashTodayController',
            scope:myscope
        });
    }
    $scope.showCost=function (type) {
        var myscope = $rootScope.$new();
        myscope.type=type;
        myscope.date=$scope.date;
        var mymodel=$modal.open({
            templateUrl: 'admin/statistics/cashMore.html',
            controller: 'CostTodayController',
            scope:myscope
        });
    }
    $scope.getOrder=function () {
        myhttp.getData('/index/statistics/getOrderStatistics','GET')
            .then(function (res) {
                $scope.orders1=res.data.orders1;
                $scope.orders2=res.data.orders2;
                $scope.orders3=res.data.orders3;
            });
    }
    $scope.getOrder();
    $scope.data=function () {
        var modalInstance = $modal.open({
            templateUrl: 'admin/wait.html',
            size:'sm'
        });
        myhttp.getData('/index/sale/backUp','GET',null).then(function (res) {
            var doc = new jsPDF();
            doc.addFont("msyh-normal.ttf", "msyh", "normal");
            doc.setFont('msyh');
            var printTime=res.data.printTime;
            var i=30; var page=1;var front=10; var space=15;var height=280;
            doc.text(105,10,res.data.info+'应收款项汇总', null, null, 'center');
            //doc.text(105,20,printTime,null,null,'center');
            doc.setFontSize(10);
            doc.text(180,height,printTime+"  第"+page+"页",null,null,'center');
            angular.forEach(res.data.data,function (item) {
                doc.setTextColor(0,0,255);
                doc.text(front,i,item.member_name+"--"+$filter('currency')(item.sum));
                doc.setTextColor(0,0,0);
                doc.setFontSize(10);
                var index=0;var title='';
                angular.forEach(item.detail,function (detail) {
                    doc.setFont('msyh');
                    i+=5;if(i>height){i=10;doc.addPage();page++;
                        doc.setFontType("normal");
                        doc.setFont('msyh');
                        doc.text(180,height,printTime+"  第"+page+"页",null,null,'center');}
                    if(index===0||(index>0&&item.detail[index-1].sale_id!==detail.sale_id)) {
                        i+=2;
                        doc.text(space,i,detail.time.substring(0,10));
                    }
                    index++;
                    doc.setFontType("normal");
                    doc.setFont('msyh');
                    switch (detail.type){
                        case 'B':
                        case 'S':
                            title=detail.goods_name;
                            title+=detail.remark===null?"":detail.remark;
                            title+=":  "+detail.number+detail.unit_name+"*"+$filter('currency')(detail.price)+"="+$filter('currency')(detail.sum);
                            break;
                        case 'P':
                            title="客户付款:  "+$filter('currency')(detail.ttl);
                            if(detail.summary!==null) title+=",备注："+detail.summary;
                            break;
                        case 'CF':
                            title="结转余额:  "+$filter('currency')(detail.ttl);
                            if(detail.summary!==null) title+=",备注："+detail.summary;
                            break;
                        case 'Init':
                            title="初期录入:  "+$filter('currency')(detail.ttl);
                            if(detail.summary!==null) title+=",备注："+detail.summary;
                            break;
                        case 'backFlush':
                            title="反冲生成已付款:  "+$filter('currency')(detail.ttl);
                            if(detail.summary!==null) title+=",备注："+detail.summary;
                            break;
                        case 'income':
                        case 'cost':
                            title=detail.summary+": "+$filter('currency')(detail.ttl);
                            break;

                    }
                    doc.text(space+25,i,title);
                });
                i+=5;
                if(i>height){
                    i=10;doc.addPage();page++;
                    doc.setFontType("normal");
                    doc.setFont('msyh');
                    doc.text(180,height,printTime+"  第"+page+"页",null,null,'center');}
            });
            doc.addPage();
            page++;
            doc.setFont('msyh');
            doc.text(105,10,res.data.info+'应付款项汇总', null, null, 'center');
           // doc.text(105,20,printTime,null,null,'center');
            doc.setFontType("normal");
            doc.setFontSize(10);
            doc.text(180,height,printTime+"  第"+page+"页",null,null,'center');
            i=30;
            angular.forEach(res.data.credit,function (item) {
                doc.setTextColor(0,0,255);
                doc.text(front,i,item.supply_name+"--"+$filter('currency')(item.sum));
                doc.setTextColor(0,0,0);
                doc.setFontSize(10);
                var index=0;var title='';
                angular.forEach(item.detail,function (detail) {
                    doc.setFont('msyh');
                    i+=5;if(i>height){i=10;doc.addPage();page++;
                        doc.setFontType("normal");
                        doc.setFont('msyh');
                        doc.text(180,height,printTime+"  第"+page+"页",null,null,'center');}
                    if(index===0||(index>0&&item.detail[index-1].purchase_id!==detail.purchase_id)) {
                        i+=2;
                        doc.text(space,i,detail.time.substring(0,10));
                    }
                    index++;
                    doc.setFontType("normal");
                    doc.setFont('msyh');
                    switch (detail.type){
                        case 'B':
                        case 'S':
                            title=detail.goods_name;
                            title+=":  "+detail.number+detail.unit_name+"*"+$filter('currency')(detail.price)+"="+$filter('currency')(detail.sum);
                            break;
                        case 'P':
                            title="付款给供应商:  "+$filter('currency')(detail.ttl);
                            if(detail.summary!==null) title+=",备注："+detail.summary;
                            break;
                        case 'CF':
                            title="结转余额:  "+$filter('currency')(detail.ttl);
                            if(detail.summary!==null) title+=",备注："+detail.summary;
                            break;
                        case 'Init':
                            title="初期录入:  "+$filter('currency')(detail.ttl);
                            if(detail.summary!==null) title+=",备注："+detail.summary;
                            break;
                        case 'backFlush':
                            title="反冲生成已付款:  "+$filter('currency')(detail.ttl);
                            if(detail.summary!==null) title+=",备注："+detail.summary;
                            break;
                        case 'income':
                        case 'cost':
                            title=detail.summary+": "+$filter('currency')(detail.ttl);
                            break;

                    }
                    doc.text(space+25,i,title);
                });
                i+=5;
                if(i>height){
                    i=10;doc.addPage();page++;
                    doc.setFontType("normal");
                    doc.setFont('msyh');
                    doc.text(180,height,printTime+"  第"+page+"页",null,null,'center');}
            });
            modalInstance.dismiss();
            doc.save(printTime+res.data.info+'.pdf');
        });



    }
});
app.controller('CostTodayController',function ($scope,myhttp,$modalInstance) {
    $scope.query=function (page) {
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        myhttp.getData('/index/statistics/costToday','GET',{page:page,type:$scope.type,date:$scope.date})
            .then(function (res) {
                var data=res.data;
                data.page_index = page;
                data.pages = [];    //页签表
                var N = 5;          //每次显示5个页签
                data.page_count=Math.ceil(data.total_count/10);
                var s = Math.floor(page/N)*N;
                if(s==page)s-=N;
                s += 1;
                var e = Math.min(data.page_count,s+N-1);
                for(var i=s;i<=e;i++)
                    data.pages.push(i)
                $scope.data=data;
            });
    };
    $scope.query(1);
    $scope.ok = function () {
        $modalInstance.close();
    };
});
app.controller('CashTodayController',function ($scope,myhttp,$modalInstance,$modal,$rootScope) {
    $scope.query=function (page) {
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        myhttp.getData('/index/statistics/cashToday','GET',{page:page,date:$scope.date})
            .then(function (res) {
                var data=res.data;
                data.page_index = page;
                data.pages = [];    //页签表
                var N = 5;          //每次显示5个页签
                data.page_count=Math.ceil(data.total_count/10);
                var s = Math.floor(page/N)*N;
                if(s==page)s-=N;
                s += 1;
                var e = Math.min(data.page_count,s+N-1);
                for(var i=s;i<=e;i++)
                    data.pages.push(i)
                $scope.data=data;
            });
    };
    $scope.query(1);
    $scope.ok = function () {
        $modalInstance.close();
    };
    $scope.cashMore=function (list) {
        var myscope = $rootScope.$new();
        myscope.list=list;
        myscope.date=$scope.date;
        $modal.open({
            templateUrl: 'admin/statistics/cashMore.html',
            controller: 'CashMoreTodayController',
            scope:myscope
        });
    }
    
});
app.controller('CashMoreTodayController',function ($scope,myhttp,$modalInstance) {
    $scope.query=function (page) {
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        myhttp.getData('/index/statistics/cashMoreToday','GET',{page:page,user:$scope.list.user,bank_id:$scope.list.bank_id,date:$scope.date})
            .then(function (res) {
                var data=res.data;
                data.page_index = page;
                data.pages = [];    //页签表
                var N = 5;          //每次显示5个页签
                data.page_count=Math.ceil(data.total_count/10);
                var s = Math.floor(page/N)*N;
                if(s==page)s-=N;
                s += 1;
                var e = Math.min(data.page_count,s+N-1);
                for(var i=s;i<=e;i++)
                    data.pages.push(i)
                $scope.data=data;
            });
    };
    $scope.query(1);
    $scope.ok = function () {
        $modalInstance.close();
    };
});
app.controller('CreditTodayController',function ($scope,myhttp,$modalInstance,$modal,$rootScope) {
    $scope.query=function (page) {
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        myhttp.getData('/index/statistics/creditToday','GET',{page:page,date:$scope.date})
            .then(function (res) {
                var data=res.data;
                data.page_index = page;
                data.pages = [];    //页签表
                var N = 5;          //每次显示5个页签
                data.page_count=Math.ceil(data.total_count/10);
                var s = Math.floor(page/N)*N;
                if(s==page)s-=N;
                s += 1;
                var e = Math.min(data.page_count,s+N-1);
                for(var i=s;i<=e;i++)
                    data.pages.push(i)
                $scope.data=data;
            });
    };
    $scope.query(1);
    $scope.ok = function () {
        $modalInstance.close();
    };
    $scope.more=function (list) {
        var myscope = $rootScope.$new();
        myscope.member = list;
        var modalInstance = $modal.open({
            templateUrl: 'admin/sale/creditDetail.html',
            controller: 'saleCreditDetailController',
            size: 'lg',
            scope: myscope
        });
    }
});
app.controller('SaleTodayController',function ($scope,myhttp,$modalInstance) {
    $scope.loading=true;
    $scope.query=function (page) {
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        myhttp.getData('/index/statistics/saleToday','GET',{page:page,type:$scope.type,date:$scope.date})
            .then(function (res) {
                $scope.loading=false;
                var data=res.data;
                data.page_index = page;
                data.pages = [];    //页签表
                var N = 5;          //每次显示5个页签
                data.page_count=Math.ceil(data.total_count/10);
                var s = Math.floor(page/N)*N;
                if(s==page)s-=N;
                s += 1;
                var e = Math.min(data.page_count,s+N-1);
                for(var i=s;i<=e;i++)
                    data.pages.push(i)
                $scope.data=data;
            });
    };
    $scope.query(1);
    $scope.ok = function () {
        $modalInstance.close();
    };
});
app.controller('onWayController',function ($scope,myhttp,$modalInstance) {
    $scope.query=function (page) {
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        myhttp.getData('/index/statistics/onWay','GET',{page:page,purchase_id:$scope.purchase.purchase_id})
            .then(function (res) {
                var data=res.data;
                data.page_index = page;
                data.pages = [];    //页签表
                var N = 5;          //每次显示5个页签
                data.page_count=Math.ceil(data.total_count/10);
                var s = Math.floor(page/N)*N;
                if(s==page)s-=N;
                s += 1;
                var e = Math.min(data.page_count,s+N-1);
                for(var i=s;i<=e;i++)
                    data.pages.push(i)
                $scope.data=data;
            });
    };
    $scope.query(1);
    $scope.ok = function () {
        $modalInstance.close();
    };
});
app.controller('receiveGoodsController',function ($scope,myhttp,$modalInstance) {
    $scope.ok = function () {
        $modalInstance.close();
    };
    $scope.cancel=function () {
        $modalInstance.dismiss();
    }
});

