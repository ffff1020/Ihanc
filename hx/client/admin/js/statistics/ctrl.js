app.controller('StatisticsSaleController',function ($scope,myhttp,$timeout,toaster,$filter,$modal,$state) {
    $scope.item=new Array(1);
    $scope.myTime='本月';
    $scope.data={member:{}};
    $scope.d0_1=[];
    $scope.d0_2=[];
    $scope.d1_1=[];
    $scope.d1_2=[];
    $scope.time=[];
    $scope.query=function () {
        var mydata={};
        if(angular.isDefined($scope.data.member.member_id)) {
            mydata.member_id=$scope.data.member.member_id;
        }
        if(angular.isDefined($scope.time)){
            $scope.time=[];
            $scope.d0_1=[];
            $scope.d0_2=[];
            $scope.d1_1=[];
            $scope.d1_2=[];
        }
        myhttp.getData('/index/statistics/salePlot','GET',mydata).then(function (res) {
            var i=0;
            angular.forEach(res.data,function (item) {
                i++;
                $scope.time.push(new Array(i,item.myTime));
                $scope.d0_1.push(new Array(i,parseInt(item.num)));
                $scope.d0_2.push(new Array(i,parseInt(item.ttl)));
                $scope.d1_1.push(new Array(i,0));
                $scope.d1_2.push(new Array(i,0));
            });
            $scope.showPlot=true;
          //
        });
        $timeout(function () { $scope.showPlot=false; },2000);
    };

    $scope.queryGrid=function () {
        var modalInstance = $modal.open({
            templateUrl: 'admin/wait.html',
            size:'sm'
        });
        $scope.query();
        var data={};
        if(angular.isDefined($scope.data.member.member_id)) {
            data.member_id=$scope.data.member.member_id;
        }
        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            data.sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            data.edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $scope.myTime=$filter('date')($scope.sdate, "yyyy-MM-dd")+'到'+$filter('date')($scope.edate, "yyyy-MM-dd");
        }
        myhttp.getData('/index/statistics/Sale','GET',data).then(function (res) {
            $scope.myData=res.data.data;
            $scope.sum=res.data.sum;
            modalInstance.dismiss();
        });

    };
    $scope.queryGrid();
    var check=0;
    $scope.gridOptions = {
        data: 'myData',
        rowHeight:35,
        multiSelect:false,
        selectedItems:$scope.item,
        showFilter:true,
        afterSelectionChange:function (rowIndex, state) {
            if(check!==rowIndex.entity.goods_id) {
                $scope.more(rowIndex.entity.goods_id);
                check=rowIndex.entity.goods_id;
            }
        },
        columnDefs: [
            {field: 'goods_name', displayName: '产品名称'},
            {field:'num', displayName:'销售数量',cellFilter:'number:2'},
            {displayName:'平均售价',cellTemplate:'<div class="ngCellText" ng-class="col.colIndex()">￥{{(row.entity.ttl/row.entity.num).toFixed(1)}}</div>'},
            {field:'ttl', displayName:'营业额',cellFilter:'currency:"￥"'},
          //  {displayName: '对比',width:'10%',cellTemplate:'<div class="ngCellText" ng-class="col.colIndex()"><i class="icon-magnifier" ng-click="more()"></i></div>' }
            ]
    };
    $scope.mySelect=function () {
       // alert();
        $scope.sum=0;
    };
    $scope.more=function (goods_id) {
        var data={};
        $scope.wait='载入中...';
        data.goods_id=goods_id;
        if(angular.isDefined($scope.data.member.member_id)) data.member_id=$scope.data.member.member_id;
        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            data.sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            data.edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $scope.myTime=$filter('date')($scope.sdate, "yyyy-MM-dd")+'到'+$filter('date')($scope.edate, "yyyy-MM-dd");
        }

        myhttp.getData('/index/statistics/salePlot','GET',data).then(function (res) {
            for(var i=0;i<$scope.time.length;i++){
                var check=true;
                angular.forEach(res.data,function (item) {
                    if(check) {
                        if ($scope.time[i][1] === item.myTime) {
                            $scope.d1_1[i][1] = parseInt(item.num);
                            $scope.d1_2[i][1] = parseInt(item.ttl);
                            check = false;
                        } else {
                            $scope.d1_1[i][1] = 0;
                            $scope.d1_2[i][1] = 0;
                        }
                    }
                });

            }
            $scope.showSpline=true;
            $scope.wait='';
        });
        $scope.showSpline=false;
    };
    $scope.query_member=function (search) {
        if(search!=='')
            myhttp.getData('/index/sale/memberSearch', 'GET', {page: 1, search: search})
                .then(function (res) {
                    $scope.members = res.data.member;
                });

    }
    $scope.clear=function () {
        $scope.data.member={};
        $scope.members=[];
    }
    $scope.printCheck=function () {
        var data={};
        if(angular.isDefined($scope.data.member.member_id)) {
            data.member_id=$scope.data.member.member_id;
            data.member_name=$scope.data.member.member_name;
        }else{
            toaster.pop('info', '请输入客户！');
            return;
        }
        data.sdate='';
        data.edate='';
        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            data.sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            data.edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $scope.myTime=$filter('date')($scope.sdate, "yyyy-MM-dd")+'到'+$filter('date')($scope.edate, "yyyy-MM-dd");
        }
        var url = $state.href('print.saleCredit',{member_id:data.member_id,member_name:data.member_name,sale_id:"payCheck"+data.sdate+"*"+data.edate});
        window.open(url,'_blank');
    }
});
app.controller('StatisticspurchaseController',function ($scope,myhttp,$timeout,toaster,$filter,$modal) {
    $scope.item=new Array(1);
    $scope.myTime='本月';
    $scope.data={supply:{}};
    $scope.d0_1=[];
    $scope.d0_2=[];
    $scope.d1_1=[];
    $scope.d1_2=[];
    $scope.time=[];
    $scope.query=function () {
        var mydata={};
        if(angular.isDefined($scope.data.supply.supply_id)) {
            mydata.supply_id=$scope.data.supply.supply_id;
        }
        if(angular.isDefined($scope.time)){
            $scope.time=[];
            $scope.d0_1=[];
            $scope.d0_2=[];
            $scope.d1_1=[];
            $scope.d1_2=[];
        }
        myhttp.getData('/index/statistics/purchasePlot','GET',mydata).then(function (res) {
            var i=0;
            angular.forEach(res.data,function (item) {
                i++;
                $scope.time.push(new Array(i,item.myTime));
                $scope.d0_1.push(new Array(i,parseInt(item.num)));
                $scope.d0_2.push(new Array(i,parseInt(item.ttl)));
                $scope.d1_1.push(new Array(i,0));
                $scope.d1_2.push(new Array(i,0));
            });
            $scope.showPlot=true;
            $timeout(function () { $scope.showPlot=false; },2000);
        });

    };

    $scope.mySelect=function () {
        // alert();
        $scope.sum=0;
    }
    $scope.queryGrid=function () {
        var modalInstance = $modal.open({
            templateUrl: 'admin/wait.html',
            size:'sm'
        });
        $scope.query();
        var data={};
        if(angular.isDefined($scope.data.supply.supply_id)) {
            data.supply_id=$scope.data.supply.supply_id;
        }
        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            data.sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            data.edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $scope.myTime=$filter('date')($scope.sdate, "yyyy-MM-dd")+'到'+$filter('date')($scope.edate, "yyyy-MM-dd");
        }
        myhttp.getData('/index/statistics/purchase','GET',data).then(function (res) {
            $scope.myData=res.data.data;
            $scope.sum=res.data.sum;
            modalInstance.dismiss();
        });
    }
    $scope.queryGrid();
    var check=0;
    $scope.gridOptions = {
        data: 'myData',
        rowHeight:35,
        multiSelect:false,
        selectedItems:$scope.item,
        showFilter:true,
        afterSelectionChange:function (rowIndex, state) {
            if(check!==rowIndex.entity.goods_id) {
                $scope.more(rowIndex.entity.goods_id);
                check=rowIndex.entity.goods_id;
            }
        },
        columnDefs: [
            {field: 'goods_name', displayName: '产品名称'},
            {field:'num', displayName:'进货数量',cellFilter:'number:2'},
            {displayName:'平均进价',cellTemplate:'<div class="ngCellText" ng-class="col.colIndex()">￥{{(row.entity.ttl/row.entity.num).toFixed(1)}}</div>'},
            {field:'ttl', displayName:'营业额',cellFilter:'currency:"￥"'},
            //  {displayName: '对比',width:'10%',cellTemplate:'<div class="ngCellText" ng-class="col.colIndex()"><i class="icon-magnifier" ng-click="more()"></i></div>' }
        ]
    };
    $scope.more=function (goods_id) {
        var data={};
        $scope.wait='载入中...';
        data.goods_id=goods_id;
        if(angular.isDefined($scope.data.supply.supply_id)) data.supply_id=$scope.data.supply.supply_id;
        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            data.sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            data.edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $scope.myTime=$filter('date')($scope.sdate, "yyyy-MM-dd")+'到'+$filter('date')($scope.edate, "yyyy-MM-dd");
        }
        myhttp.getData('/index/statistics/purchasePlot','GET',data).then(function (res) {
            for(var i=0;i<$scope.d1_1.length;i++){
                var check=true;
                angular.forEach(res.data,function (item) {
                    if(check) {
                        if ($scope.time[i][1] === item.myTime) {
                            $scope.d1_1[i][1] = parseInt(item.num);
                            $scope.d1_2[i][1] = parseInt(item.ttl);
                            check = false;
                        } else {
                            $scope.d1_1[i][1] = 0;
                            $scope.d1_2[i][1] = 0;
                        }
                    }
                });

            }
            $scope.showSpline=true;
            $scope.wait='';
        });
        $scope.showSpline=false;
    };
    $scope.query_supply=function (search) {
        if(search!=='')
            myhttp.getData('/index/purchase/supplySearch', 'GET', {page: 1, search: search})
                .then(function (res) {
                    $scope.supplys = res.data.supply;
                });

    }
    $scope.clear=function () {
        $scope.data.supply={};
        $scope.supplys=[];
    }
});
app.controller('StatisticsProfitController',function ($scope,myhttp,$timeout,toaster,$filter,$modal) {
    $scope.item=new Array(1);
    $scope.myTime='本月';
    $scope.data={supply:{}};
    $scope.d0_1=[];
    $scope.d0_2=[];
    $scope.d1_1=[];
    $scope.d1_2=[];
    $scope.time=[];
    $scope.query=function () {
        myhttp.getData('/index/statistics/profitPlot','GET').then(function (res) {
            for(var i=0;i<13;i++){
                if(i<res.data.length) {
                    $scope.time.push(new Array(i, res.data[i].myTime));
                    $scope.d0_1.push(new Array(i, parseInt(res.data[i].profit)));
                }
                else {
                    $scope.time.push(new Array(i, 0));
                    $scope.d0_1.push(new Array(i,0));
                }

                $scope.d0_2.push(new Array(i,0));
                $scope.d1_1.push(new Array(i,0));
                $scope.d1_2.push(new Array(i,0));
            }
            $timeout(function () { $scope.$apply(); });
        });
    }
    $scope.query();
    $scope.queryGrid=function () {
        var modalInstance = $modal.open({
            templateUrl: 'admin/wait.html',
            size:'sm'
        });
        var data={};
        if(angular.isDefined($scope.data.supply.supply_id)) {
            data.supply_id=$scope.data.supply.supply_id;
        }
        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            data.sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            data.edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $scope.myTime=$filter('date')($scope.sdate, "yyyy-MM-dd")+'到'+$filter('date')($scope.edate, "yyyy-MM-dd");
        }
        myhttp.getData('/index/statistics/profit','GET',data).then(function (res) {
            modalInstance.dismiss();
            $scope.sum=res.data.sum;
            $scope.myData=res.data.data;
            var D=res.data.D;
            for (i=0;i<$scope.myData.length;i++){
                if(D.length===0) return true;
                //console.log(D.length);
                ddd:
                for(j=0;j<D.length;j++){
                    if(D[j].goods_id===$scope.myData[i].goods_id){
                        $scope.myData[i].Dstock=D[j].Dstock;
                        $scope.myData[i].Dsum=D[j].Dsum;
                        D.splice(j,1);
                        break ddd;
                    }
                }
            }
        });
    }
    $scope.queryGrid();
    var check=0;
    $scope.gridOptions = {
        data: 'myData',
        rowHeight:35,
        multiSelect:false,
        selectedItems:$scope.item,
        showFilter:true,
        afterSelectionChange:function (rowIndex, state) {
            //console.log(rowIndex.entity);
            if(check!==rowIndex.entity.goods_id) {
                $scope.more(rowIndex.entity.goods_id);
                check=rowIndex.entity.goods_id;
            }
        },
        columnDefs: [
            {field: 'goods_name', displayName: '产品名称'},
            {field:'profit', displayName:'毛利润',cellFilter:'currency:"￥"'},
            {field:'Dstock', displayName:'溢损数量',cellFilter:'number:2'},
            {field:'Dsum', displayName:'溢损金额',cellFilter:'currency:"￥"'}
            //  {displayName: '对比',width:'10%',cellTemplate:'<div class="ngCellText" ng-class="col.colIndex()"><i class="icon-magnifier" ng-click="more()"></i></div>' }
        ]
    };
    $scope.more=function (goods_id) {
        var data={};
        $scope.wait='载入中...';
        data.goods_id=goods_id;
        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            data.sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            data.edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $scope.myTime=$filter('date')($scope.sdate, "yyyy-MM-dd")+'到'+$filter('date')($scope.edate, "yyyy-MM-dd");
        }
        myhttp.getData('/index/statistics/profitPlotGoods','GET',data).then(function (res) {
            //console.log(res.data.plot);
            for(var i=0;i<13;i++){
                var check=true;
                var index=0;
                angular.forEach(res.data.plot,function (item) {
                    if(check) {
                        if ($scope.time[i][1] === item.myTime) {
                            $scope.d1_1[i][1] = parseInt(item.Dstock);
                            if(angular.isDefined(res.data.number[index].num)&&res.data.number[index].num!==0)
                            $scope.d1_2[i][1] = parseInt(item.Dstock/res.data.number[index].num*100);
                            else
                                $scope.d1_2[i][1]=0;
                            $scope.d0_2[i][1] = parseInt(item.profit);
                            check = false;
                        } else {
                            $scope.d1_1[i][1] = 0;
                            $scope.d1_2[i][1] = 0;
                            $scope.d0_2[i][1] = 0;
                        }
                    }
                    index++;
                });
            }
            $scope.showSpline=true;
            $scope.wait='';
        });
        $scope.showSpline=false;
    };
});
app.controller('StatisticsOrderController',function ($scope,myhttp,$stateParams,$filter,$modal,$rootScope) {
    $scope.query = function(page){
        var modalInstance = $modal.open({
            templateUrl: 'admin/wait.html',
            size:'sm'
        });
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }

        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
        }
        myhttp.getData('/index/statistics/orderList','GET',
            {
                page:page,
                search:$scope.search_context,
                sdate:$filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00"),
                edate:$filter('date')($scope.edate, "yyyy-MM-dd 23:59:59")
            })
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
                    data.pages.push(i);
                $scope.data=data;
                modalInstance.dismiss();
            });
    };
    $scope.query();
    $scope.more=function (inorder) {
        var myscope = $rootScope.$new();
        myscope.inorder=inorder;
        var modalInstance = $modal.open({
            templateUrl: 'admin/statistics/orderDetail.html',
            controller: 'orderDetailController',
            scope:myscope,
            size:'lg'
        });
    }
});
app.controller('orderDetailController',function ($scope,myhttp,$modalInstance) {
    myhttp.getData('/index/statistics/orderDetail','GET',{inorder:$scope.inorder}).then(function (res) {
              $scope.lists=res.data;

    });
    $scope.cancel=function () {
        $modalInstance.dismiss('cancel');
    }
});
app.controller('StatisticsIncomeController',function ($scope,myhttp,$timeout,toaster,$filter,$modal) {
    $scope.item = new Array(1);
    $scope.myTime = '本月';
    $scope.data = {member: {}};
    $scope.d0_1 = [];
    $scope.d0_2 = [];
    $scope.d1_1 = [];
    $scope.d1_2 = [];
    $scope.time = [];
    $scope.query = function () {
        myhttp.getData('/index/statistics/incomePlot', 'GET').then(function (res) {
            var i = 0;
            angular.forEach(res.data, function (item) {
                $scope.time.push(new Array(i, item.myTime));
                $scope.d0_1.push(new Array(i, parseInt(item.cost)));
                $scope.d0_2.push(new Array(i, parseInt(item.income)));
                $scope.d1_1.push(new Array(i, 0));
                $scope.d1_2.push(new Array(i, 0));
                i++;
            });
            $timeout(function () {
                $scope.$apply();
            });

        });
    }
    $scope.query();
    $scope.queryGrid = function () {
        var modalInstance = $modal.open({
            templateUrl: 'admin/wait.html',
            size: 'sm'
        });
        var data = {};
        if ($scope.sdate && $scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            data.sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            data.edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $scope.myTime = $filter('date')($scope.sdate, "yyyy-MM-dd") + '到' + $filter('date')($scope.edate, "yyyy-MM-dd");
        }
        myhttp.getData('/index/statistics/Income', 'GET', data).then(function (res) {
            $scope.myData = res.data;
            modalInstance.dismiss();
        });
    }
    $scope.queryGrid();
    var check = 0;
    $scope.gridOptions = {
        data: 'myData',
        rowHeight: 35,
        multiSelect: false,
        selectedItems: $scope.item,
        showFilter: true,
        afterSelectionChange: function (rowIndex, state) {
            if (check !== rowIndex.entity.subject) {
                $scope.more(rowIndex.entity.subject);
                check = rowIndex.entity.subject;
            }
        },
        columnDefs: [
            {field: 'subject_name', displayName: '会计科目'},
            {field: 'cost', displayName: '费用金额', cellFilter: 'currency:"￥"'},
            {field: 'income', displayName: '收入金额', cellFilter: 'currency:"￥"'}
            //  {displayName: '对比',width:'10%',cellTemplate:'<div class="ngCellText" ng-class="col.colIndex()"><i class="icon-magnifier" ng-click="more()"></i></div>' }
        ]
    };
    $scope.more = function (subject) {
        var data = {};
        $scope.wait = '载入中...';
        data.subject = subject;
        if ($scope.sdate && $scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            data.sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            data.edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $scope.myTime = $filter('date')($scope.sdate, "yyyy-MM-dd") + '到' + $filter('date')($scope.edate, "yyyy-MM-dd");
        }
        myhttp.getData('/index/statistics/incomePlot', 'GET', data).then(function (res) {
            for (var i = 0; i < $scope.d1_1.length; i++) {
                var check = true;
                angular.forEach(res.data, function (item) {
                    if (check) {
                        if ($scope.time[i][1] === item.myTime) {
                            $scope.d1_1[i][1] = parseInt(item.cost);
                            $scope.d1_2[i][1] = parseInt(item.income);
                            check = false;
                        } else {
                            $scope.d1_1[i][1] = 0;
                            $scope.d1_2[i][1] = 0;
                        }
                    }
                });

            }
            $scope.showSpline = true;
            $scope.wait = '';
        });
        $scope.showSpline = false;
    };
});
app.controller('StatisticsAssetController',function ($scope,myhttp,$timeout,toaster,$filter,$modal) {
    $scope.item = new Array(1);
    $scope.data = {member: {}};
    $scope.d1_1 = [];
    $scope.d1_2 = [];
    $scope.d1_3 = [];
    $scope.d1_4 = [];
    $scope.d1_5 = [];
    $scope.d1_6 = [];
    $scope.time = [];
    $scope.query = function () {
        myhttp.getData('/index/statistics/AssetPlot', 'GET').then(function (res) {
            var i = 0;
            angular.forEach(res.data, function (item) {
                $scope.time.push(new Array(i, item.time));
                $scope.d1_1.push(new Array(i, parseInt(item.bank)));
                $scope.d1_2.push(new Array(i, parseInt(item.mcredit)));
                $scope.d1_3.push(new Array(i, parseInt(item.scredit)));
                $scope.d1_4.push(new Array(i, parseInt(item.stock)));
                $scope.d1_5.push(new Array(i, parseInt(item.staffCredit)));
                $scope.d1_6.push(new Array(i, parseInt(item.bank)+ parseInt(item.mcredit)+parseInt(item.stock)-parseInt(item.scredit)-parseInt(item.staffCredit)));
                i++;
            });
            $timeout(function () {
                $scope.$apply();
            });

        });
    }
    $scope.query();
    $scope.queryGrid = function () {
        var modalInstance = $modal.open({
            templateUrl: 'admin/wait.html',
            size: 'sm'
        });
        var data = {};
        myhttp.getData('/index/statistics/Asset', 'GET', data).then(function (res) {
            $scope.myData = res.data;
            //$scope.myData[0].asset=res.data.bank+res.data.mcredit-res.data.scredit+res.data.stock-res.data.staffCredit;
            modalInstance.dismiss();
        });
    };
    $scope.queryGrid();
    $scope.gridOptions = {
        data: 'myData',
        rowHeight: 40,
        columnDefs: [
            {field: 'bank', displayName: '银行总金额',cellFilter: 'currency:"￥"'},
            {field: 'mcredit', displayName: '应收总金额', cellFilter: 'currency:"￥"'},
            {field: 'scredit', displayName: '应付总金额', cellFilter: 'currency:"￥"'},
            {field: 'stock', displayName: '库存总金额', cellFilter: 'currency:"￥"'},
            {field: 'staffCredit', displayName: '员工应付总金额', cellFilter: 'currency:"￥"'},
            {field: 'asset', displayName: '总资产合计', cellFilter: 'currency:"￥"'}
        ]
    };

});