/**
 * Created by Administrator on 2017/5/22 0022.
 */
'use strict';
app.controller('MemberController', function($scope,$http, $resource,myhttp,$stateParams,ngDialog,toaster) {
    $scope.query = function(page,filter){
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        if(!filter) filter='';
        myhttp.getData('/index/sale/member','GET',{page:page,search:filter})
            .then(function (res) {
                var data=res.data;
                data.page_index = page;
                data.pages = [];    //页签表
                var N = 5;          //每次显示5个页签
                data.page_count=Math.ceil(data.total_count/10);
                var s = Math.floor(page/N)*N;
                if(s===page)s-=N;
                s += 1;
                var e = Math.min(data.page_count,s+N-1);
                for(var i=s;i<=e;i++)
                    data.pages.push(i)
                $scope.search_context = filter;
                $scope.data=data;
            });
    };
    $scope.query($stateParams.page,$stateParams.search);
    $scope.search=function () {
        $scope.query(1,$scope.search_context);
    };
    $scope.memberDialog=function(data) {
        ngDialog.open({
            template: 'admin/sale/memberUpdate.html',
            className: 'ngdialog-theme-default' ,
            showClose: false,
           // controller:'MemberUpdateController',
            data:data,
            preCloseCallback: function(value){
                switch(value)
                {
                    case 3:
                        toaster.pop('info','该客户姓名已经存在，请核对...');
                        $scope.query($scope.data.page_index,$scope.search_context);
                        break;
                    case 0:
                        toaster.pop('info','客户更新失败，请刷新后再试!');
                        break;
                    default:
                       // alert();
                        $scope.query($scope.data.page_index,$scope.search_context);
                }
            }
        });
    };
});
app.controller('MemberUpdateController',function ($scope,myhttp) {
    var check=true;
    $scope.data=$scope.ngDialogData;
    $scope.py=function (data) {
        $scope.data.member_sn=makePy(data).toString();
    };
    $scope.updateMember=function () {
        check=false;
        if(typeof( $scope.data.member_id)==="undefined") $scope.data.member_id='' ;
        myhttp.getData('/index/sale/memberUpdate','POST',$scope.data)
            .then(function (res) {
                check=true;
                $scope.closeThisDialog(res.data.result);
            });
    }
});
app.controller('SaleController',function ($scope,myhttp,toaster,ngDialog,$state,$timeout,$localStorage,$location,$modal,$rootScope,$filter,mycache) {
    $scope.data={member:$localStorage['member']};
    $scope.members=[];
    $scope.pay={bank:{}};
    $scope.show_numbers=false;
    if(angular.isUndefined($localStorage.purchaseInfo)){
        myhttp.getData('/index/purchase/purchaseInfo','GET')
            .then(function (res) {
                $localStorage.purchaseInfo=res.data;
                $scope.banks=$localStorage.purchaseInfo.bank;
                $scope.stores=$localStorage.purchaseInfo.store;
                $scope.units=$localStorage.purchaseInfo.units;
                $scope.pay.bank=$scope.banks[0].bank_id;
            });
    }else{
        $scope.banks=$localStorage.purchaseInfo.bank;
        $scope.stores=$localStorage.purchaseInfo.store;
        $scope.units=$localStorage.purchaseInfo.units;
        $scope.pay.bank=$localStorage.purchaseInfo.bank[0].bank_id;
    }

    if(angular.isDefined($localStorage['member'].sale_id)){
        $scope.rows=[];
        myhttp.getData('/index/sale/orderDetail','GET',{sale_id:$localStorage['member'].sale_id}).then(function (res) {
           var index=0;
            angular.forEach(res.data.detail,function (item) {
                var temp={};
                temp.good={goods_id:item.goods_id,goods_name:item.goods_name,unit_id:{unit_id:item.unit_id_0}};
                temp.price=item.price;
                temp.number=parseFloat(item.number);
                temp.sum=item.sum;
                temp.store_id=0;
                temp.units = item.unit;
                temp.remark=item.remark;
               // console.log(item.unit);
                for (var j = 0; j < item.unit.length; j++) {
                    if (item.unit[j].unit_id === item.unit_id) {
                        temp.unit = item.unit[j];
                    }
                }
                $scope.rows.push(temp);
                $scope.cal(index);
                $timeout(function () {
                    $scope.$apply(function () {
                        $scope.rows[index].store_id=item.store_id;
                        index++;
                    });
                },0,false);
            });
            $scope.data.summary=res.data.summary;
            $scope.addRow();
        });
    }else{
        $scope.rows=new Array(5);
    }
    $scope.tIndex=$localStorage['tIndex'];
    $scope.check=false;
    $timeout(function () {
        angular.element('#'+$scope.tIndex+' tbody tr:eq(0) td:eq(1) ').find('input').focus();
    });
    $scope.getStock=function (index) {
        var data={goods_id:$scope.rows[index].good.goods_id,store_id:$scope.rows[index].store_id,unit_id:$scope.rows[index].good.unit_id};
        myhttp.getData('/index/stock/getStock','GET',data).then(function (res) {
            $scope.rows[index].info='库存：'+res.data.number+res.data.unit.unit_name;
            if(res.data.number<=0)
                 $scope.rows[index].check=-1;
            else if($scope.rows[index].good.warn_stock>res.data.number)
                $scope.rows[index].check=0;
            else
                $scope.rows[index].check=1;
        });
    }
    $scope.query_member=function (search) {
        if(search!='')
            myhttp.getData('/index/sale/memberSearch', 'GET', {page: 1, search: search})
                .then(function (res) {
                    $scope.members = res.data.member;
                });
    };
    $scope.memberDialog=function() {
        ngDialog.open({
            template: 'admin/sale/memberUpdate.html',
            className: 'ngdialog-theme-default' ,
            showClose: false,
            controller:'MemberUpdateController',
            preCloseCallback: function(value){
                switch(value)
                {
                    case 3:
                        toaster.pop('info','该客户姓名已经存在，请核对...');
                        break;
                    case 0:
                        toaster.pop('error','客户更新失败，请刷新后再试!');
                }
            }
        });
    };
    $scope.goods=[];
    $scope.query_goods=function (search) {
        if(search!=='') {
            myhttp.getData('/index/setting/goodsQuery', 'GET', {search: search})
                .then(function (res) {
                    $scope.goods = res.data;
                });
        }
    }
    $scope.cal=function (index) {
        if(index>=0){
            if($scope.rows[index]===null){toaster.pop('info','请完善销售信息！');return false;}
            $scope.rows[index].sum=Math.round($scope.rows[index].number*$scope.rows[index].price);
        }
        var sum=0,num=0;
        angular.forEach($scope.rows,function (row) {
            if(angular.isDefined(row.good)) {
                num += parseFloat(row.number);
                sum += parseInt(row.sum);
            }
        });
        $scope.ttl_number=num;
        $scope.data.ttl_sum=sum;
    }
    $scope.cal_sum=function ($index) {
        var sum=0;
        if($scope.rows[$index].number!==0)
            $scope.rows[$index].price=($scope.rows[$index].sum/$scope.rows[$index].number).toFixed(1);
        angular.forEach($scope.rows,function (row) {
            if(angular.isDefined(row.good)&&angular.isDefined(row.good.goods_id)) {
                sum += parseInt(row.sum);
            }
        });
        $scope.data.ttl_sum=sum;
    }
    $scope.addRow=function () {
        $scope.rows.push({number:null,price:null});
    }
    $scope.delRow=function (index) {
        $scope.rows.splice(index,1);
       // $scope.rows.push({});
        $scope.cal(-1);
    }
    $scope.save=function (back) {
       // angular.element('#setting').removeClass("active");
        if($scope.data.member===''){toaster.pop('info','请输入客户');return false;}
        var detail=[];
        var rowcheck=false;
        angular.forEach($scope.rows,function (row) {
            var data={};
            if(angular.isDefined(row.good)&&angular.isDefined(row.good.goods_id)) {
               // console.log(row.good);
                if(isNaN(row.sum)) {rowcheck=true;console.log(row);}
                if(row.store_id===0) {rowcheck=true;console.log(row);}
                //console.log(row.good);
                data.good = {
                    goods_id: row.good.goods_id,
                    goods_name: row.good.goods_name,
                    unit_id_0: row.good.unit_id
                };
                data.number = row.number;
                if($scope.show_numbers) {
                    data.number1=row.number1;
                    data.number2=row.number2;
                }
                data.price = row.price;
                data.sum = row.sum;
                data.unit_id = row.unit.unit_id;
                data.store_id = row.store_id;
                data.remark=row.remark;
                detail.push(data);
            }
        });
        //console.log(detail);return false;
        if (angular.isDefined($scope.data.time))$scope.data.time=$filter('date')($scope.data.time,'yyyy-MM-dd 00:00:00');
        if(detail.length===0&&angular.isUndefined($scope.pay.paid_sum)) return false;
        if(rowcheck){toaster.pop('info','请完善销售信息');return false;}
        $scope.check=true;
        myhttp.getData('/index/sale/sale','POST',
            {
                'data':$scope.data,
                'pay':$scope.pay,
                'detail':detail,
                'back':back
            })
            .then(function (res) {
                $scope.check=false;
                switch(res.data.result) {
                    case 1:
                        $timeout(function () {
                            $scope.$emit('sale_ok', $scope.tIndex);
                        },0,false);

                        break;
                    case 0:
                        toaster.pop('info','请刷新，重新输入！');
                        break;
                    default :
                        toaster.pop('error','系统错误，请联系管理员');

                }
            });
    };
    $scope.getUnit=function (item,index) {
        $scope.goods=[];
        if(index===$scope.rows.length-1){ $scope.addRow();}

        $timeout(function () {
            angular.element('#'+$scope.tIndex+' tbody tr:eq('+index+') td:eq(2) ').find('input').focus();
            myhttp.getData('/index/sale/getUnit', 'GET', {
                'goods_id': item.goods_id,
                'unit_id':item.unit_id,
                'member_id':angular.isDefined($scope.data.member.member_id)?$scope.data.member.member_id:0
            })
                .then(function (res) {
                    $timeout(function () {
                        //angular.element('#'+$scope.tIndex+' tbody tr:eq('+index+') td:eq(2) ').find('input').focus();
                    },0,false);
                    var myCheck=true;
                    $scope.rows[index].units = res.data.unit;
                    for (var j = 0; j < $scope.rows[index].units.length&&myCheck; j++) {
                        if(angular.isDefined(res.data.unit_price)&&$scope.rows[index].units[j].unit_id === res.data.unit_price){
                            $scope.rows[index].unit = $scope.rows[index].units[j];
                            myCheck=false;
                            // console.log($scope.rows[index].unit);
                        }else if (($scope.rows[index].units[j].unit_id === item.unit_id)) {
                            $scope.rows[index].unit = $scope.rows[index].units[j];
                        }
                        // console.log($scope.rows[index].unit);
                    }
                    $scope.rows[index].price= parseFloat(res.data.result);

                    $scope.cal(index);
                    $scope.rows[index].store_id=$scope.storeId;
                    $scope.getStock(index);
                });
        },5,false);
    };
    $scope.getPrice=function (item,index) {
        if($scope.data.member!=='') {
            angular.element('#'+$scope.tIndex+' tbody tr:eq('+index+1+') td:eq(1) ').find('input').focus();
            myhttp.getData('/index/sale/getPriceTab','GET',
                {
                    'goods_id':$scope.rows[index].good.goods_id,
                    'member_id':$scope.data.member.member_id,
                    'unit_id':item.unit_id
                })
                .then(function (res) {
                    //alert(res.data.result);
                    $scope.rows[index].price= res.data.result;
                    $scope.cal(index);
                   // $scope.rows[index].stock= parseInt(res.data.stock);

                });
        }
    }
    $scope.getCredit=function (item) {
        myhttp.getData('/index/sale/getMemberCredit','GET',{'member_id':item.member_id})
            .then(function (res) {
                $scope.credit=res.data.credit;
            });
    }
    $scope.printCredit=function (list) {
        var url = $state.href('print.saleCredit',{member_id:list.member_id,member_name:list.member_name});
        // window.open('admin/sale/printCredit.html','_blank');
        window.open(url,'_blank');
    }
    $scope.next=function (e,index) {
        var keycode = window.event?e.keyCode:e.which;
        if(keycode===13){
            $timeout(function () {
                index++;
               // $scope.rows[index].goods=null;
                angular.element('#'+$scope.tIndex+' tbody tr:eq(' + index + ') td:eq(1) ').find('input').focus();
                if(index==0) $scope.save(1);
            },200,false);

        }
        if(keycode==42) $timeout(function () {angular.element('#'+$scope.tIndex+'paid_sum').focus();},200,false);
        if(keycode==32) $timeout(function () {
            var weight=angular.element('#weight').val();
            //angular.element('#'+$scope.tIndex+'number'+index).val(weight);
            $scope.rows[index].number=weight;$scope.cal(index);
            });
    }
    $scope.print=function(credit){
        $localStorage.credits={};
        if($scope.data.member.length===0) return false;
       /* if($scope.memberInfo){
            myhttp.getData('/index/sale/getMemberPhone', 'GET', {'member_id': $scope.data.member.member_id})
                .then(function (res) {
                    $localStorage.tel = res.data.tel;
                });
        }else
        {
            $localStorage.tel=false;
        }*/
        $localStorage.summary=$scope.data.summary;
        if(credit===true) {
            myhttp.getData('/index/sale/getMemberCredit', 'GET', {'member_id': $scope.data.member.member_id})
                .then(function (res) {
                   // $localStorage.credit = res.data.credit;
                    var credits = [];
                    var num = 0;
                    var sum = 0;
                    angular.forEach($scope.rows, function (item) {
                        // alert(angular.isDefined(item.good));
                        if (angular.isDefined(item.good)) {
                            var credit = {
                                goods_name: item.good.goods_name,
                                remark:item.remark,
                                price: item.price,
                                number: item.number,
                                sum: item.sum,
                                unit: item.unit.unit_name
                            };
                            credits.push(credit);
                            num += parseFloat(item.number);
                            sum += parseFloat(item.sum);
                        }
                    });
                    if(angular.isDefined($scope.pay.paid_sum)&&$scope.pay.paid_sum!==0){
                        $localStorage.credit=res.data.credit+sum-$scope.pay.paid_sum;
                        credits.push({goods_name:'客户付款',sum:$scope.pay.paid_sum*(-1)});
                    }else
                        $localStorage.credit=res.data.credit+sum;
                    $localStorage.ttl = {number: num, sum: sum};
                    $localStorage.credits = credits;
                    if($scope.memberInfo){
                        myhttp.getData('/index/sale/getMemberPhone', 'GET', {'member_id': $scope.data.member.member_id})
                            .then(function (res) {
                                $localStorage.tel = res.data.tel;
                                var url = $state.href('print.sale', {sale_id: 0, member_name: $scope.data.member.member_name});
                                $timeout(function () {
                                    window.open(url, '_blank');
                                }, 100, false);
                            });
                    }else
                    {
                        $localStorage.tel=false;
                        var url = $state.href('print.sale', {sale_id: 0, member_name: $scope.data.member.member_name});
                        $timeout(function () {
                            window.open(url, '_blank');
                        }, 100, false);
                    }
                });
        }else {
            $localStorage.credit = 0;
            var credits = [];
            var num = 0;
            var sum = 0;
            angular.forEach($scope.rows, function (item) {
                // alert(angular.isDefined(item.good));
                if (angular.isDefined(item.good)) {
                    var credit = {
                        goods_name: item.good.goods_name,
                        price: item.price,
                        number: item.number,
                        sum: item.sum,
                        remark:item.remark,
                        unit: item.unit.unit_name
                    };
                    credits.push(credit);
                    num += parseFloat(item.number);
                    sum += parseFloat(item.sum);
                }
            });
            $localStorage.ttl = {number: num, sum: sum};
            if(angular.isDefined($scope.pay.paid_sum)&&$scope.pay.paid_sum!==0) {
                credits.push({goods_name: '客户付款', sum: $scope.pay.paid_sum * (-1)});
            }
            $localStorage.credits = credits;
            if($scope.memberInfo){
                myhttp.getData('/index/sale/getMemberPhone', 'GET', {'member_id': $scope.data.member.member_id})
                    .then(function (res) {
                        $localStorage.tel = res.data.tel;
                        var url = $state.href('print.sale', {sale_id: 0, member_name: $scope.data.member.member_name});
                        $timeout(function () {
                            window.open(url, '_blank');
                        }, 100, false);
                    });
            }else
            {
                $localStorage.tel=false;
                var url = $state.href('print.sale', {sale_id: 0, member_name: $scope.data.member.member_name});
                $timeout(function () {
                    window.open(url, '_blank');
                }, 100, false);
            }
        }
    }
    $scope.myPrint=function(type){
        $localStorage.credits={};
        if($scope.data.member.length===0) return false;
        $localStorage.credit = 0;
       /* if($scope.memberInfo){
            myhttp.getData('/index/sale/getMemberPhone', 'GET', {'member_id': $scope.data.member.member_id})
                .then(function (res) {
                    $localStorage.tel = res.data.tel;
                });
        }else  $localStorage.tel=false;*/
        var credits=[];
        var num=0;var sum=0;
        $localStorage.summary=$scope.data.summary;
        angular.forEach($scope.rows, function(item) {
            // alert(angular.isDefined(item.good));
            if(angular.isDefined(item.good)) {
                var credit;
                if(type==='number'){
                     credit = {
                        goods_name: item.good.goods_name,
                        number:item.number,
                         remark:item.remark,
                        unit: item.unit.unit_name
                    };
                }else{
                     credit = {
                        goods_name: item.good.goods_name,
                        price: item.price,
                         remark:item.remark,
                        unit: item.unit.unit_name
                    };
                }
                credits.push(credit);
                if(type==='number')num += parseFloat(item.number);
                //sum += item.sum;
            }
        });
        $localStorage.ttl={number:num,sum:sum};
        $localStorage.credits=credits;
        if($scope.memberInfo){
            myhttp.getData('/index/sale/getMemberPhone', 'GET', {'member_id': $scope.data.member.member_id})
                .then(function (res) {
                    $localStorage.tel = res.data.tel;
                    var url = $state.href('print.sale', {sale_id: 0, member_name: $scope.data.member.member_name});
                    $timeout(function () {
                        window.open(url, '_blank');
                    }, 100, false);
                });
        }else
        {
            $localStorage.tel=false;
            var url = $state.href('print.sale', {sale_id: 0, member_name: $scope.data.member.member_name});
            $timeout(function () {
                window.open(url, '_blank');
            }, 100, false);
        }
    }
    $scope.$on('setStoreID', function(event, data) {
        $scope.storeId = data;
    });
    $scope.smallChange=function () {
         if($scope.data.smallChange){
             $scope.data.ttl_sum=parseInt($scope.data.ttl_sum/10)*10;
         }
    }
    $scope.order=function () {

        if($scope.data.member===''){toaster.pop('info','请输入客户');return false;}
        var detail=[];
        var rowcheck=false;
        angular.forEach($scope.rows,function (row) {
            var data={};
            if(angular.isDefined(row.good)&&angular.isDefined(row.good.goods_id)) {
                if(isNaN(row.sum)) {rowcheck=true;console.log(row)}
                if(row.store_id===0) rowcheck=true;
                data.good = {
                    goods_id: row.good.goods_id,
                    goods_name: row.good.goods_name,
                    unit_id_0: row.good.unit_id
                };
                data.number = row.number;
                data.price = row.price;
                data.sum = row.sum;
                data.unit_id = row.unit.unit_id;
                data.store_id = row.store_id;
                data.remark=row.remark;
                detail.push(data);
                //console.log(detail);
            }
        });
        if(detail.length===0) return false;
        if(rowcheck){toaster.pop('info','请完善销售信息');return false;}
        var myscope = $rootScope.$new();
       // console.log($scope.data);
        myscope.time=angular.isDefined($localStorage['member'].time)?$localStorage['member'].time:$filter('date')(new Date(),'yyyy-MM-dd');
        var modalInstance = $modal.open({
            templateUrl: 'admin/sale/Dtime.html',
            controller: 'DtimeController',
            scope:myscope,
            size:'sm'
        });
        modalInstance.result.then(function (Dtime) {
           // alert(Dtime);
            $scope.check=true;
            myhttp.getData('/index/sale/order','POST',
                {
                    'data':$scope.data,
                    'pay':$scope.pay,
                    'detail':detail,
                    'time':Dtime
                })
                .then(function (res) {
                    $scope.check=false;
                    switch(res.data.result) {
                        case 1:
                            $scope.$emit('sale_ok', $scope.tIndex);
                            $timeout(function () {
                                toaster.pop('success', '订单保存成功！');
                            },100,false);

                            break;
                        case 0:
                            toaster.pop('info','请刷新，重新输入！');
                            break;
                        default :
                            toaster.pop('error','系统错误，请联系管理员');

                    }
                });
        });
    }
    $scope.$on('modifyMember',function (event,member) {
        $scope.data.member=member;
    });
    $scope.cal_numbers=function (index) {
        if(index>=0){
            if($scope.rows[index]===null){toaster.pop('info','请完善销售信息！');return false;}
            $scope.rows[index].number=Math.abs($scope.rows[index].number1-$scope.rows[index].number2).toFixed(2);
            $scope.cal(index);
        }
    }

});
app.controller("DtimeController",function ($scope,$modalInstance,$filter) {
    $scope.Dtime=$scope.time;
    $scope.ok = function () {
        $modalInstance.close($filter('date')($scope.Dtime,'yyyy-MM-dd'))
    };
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };

});
app.controller('SaleListController',function ($scope,myhttp,$stateParams,$filter,$modal,$rootScope,toaster,$state,$localStorage) {
    var sdate='';
    var edate='';
    $scope.role=$localStorage.role;
    $scope.query = function(page,filter,urlSdate,urlEdate){
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
            //$stateParams.page=page;
        }
        if(urlEdate&&urlSdate){
            sdate=urlSdate;
            edate=urlEdate;
        }
        if(!filter) filter='';
        myhttp.getData('/index/sale/saleList','GET',
            {
                page:page,
                search:filter,
                sdate:sdate,
                edate:edate
            })
            .then(function (res) {
                // console.log(res.data);return false;
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
                $scope.search_context = filter;
                $scope.edate=$filter('limitTo')(edate, 10);
                $scope.sdate=$filter('limitTo')(sdate, 10);
                $scope.data=data;
            });
    };
    $scope.query($stateParams.page,$stateParams.search,$stateParams.sdate,$stateParams.edate);
    $scope.search=function () {
        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
             sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
             edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            //console.log(sdate);
            $state.go('app.sale.list', {page:1,search: $scope.search_context, sdate: sdate, edate: edate});
        }else{
            $state.go('app.sale.list',{page:1,search: $scope.search_context,sdate:null, edate:null});
        }
    };
    $scope.pageQuery=function (page,search,sdate,edate) {
        sdate = $filter('date')(sdate, "yyyy-MM-dd 00:00:00");
        edate = $filter('date')(edate, "yyyy-MM-dd 23:59:59");
        $state.go('app.sale.list', {page:page,search: $scope.search_context, sdate: sdate, edate: edate});
    }
    //删除销售单
    $scope.delete=function (list) {
        var myscope = $rootScope.$new();
        myscope.info=list.member_name+'的销售单';
        var modalInstance = $modal.open({
            templateUrl: 'admin/confirm.html',
            controller: 'ConfirmController',
           // size:'sm',
            scope:myscope
        });
        modalInstance.result.then(function () {
            myhttp.getData('/index/sale/deleteSale','POST',{sale_id:list.sale_id,info:list.member_name+'金额:'+list.sum})
                .then(function (res) {
                    if(res.data.result===1){
                        toaster.pop('success','删除成功！');
                        $scope.query($stateParams.page,$stateParams.search,$stateParams.sdate,$stateParams.edate);
                    }else
                        toaster.pop('error','删除失败！');
                });
        });
    }
    $scope.printSale=function (list) {
       // console.log(list);
        $localStorage.summary=list.summary;
        var url = $state.href('print.sale',{sale_id:list.sale_id,member_name:list.member_name,time:list.time,member_id:list.member_id});
        window.open(url,'_blank');
    }
    $scope.backFlush=function (list) {
        var myscope = $rootScope.$new();
        myscope.info=list.member_name+'的销售单';
        var modalInstance = $modal.open({
            templateUrl: 'admin/confirmOnly.html',
            controller: 'ConfirmController',
            //size:'sm',
            scope:myscope
        });
        modalInstance.result.then(function () {
            myhttp.getData('/index/sale/backFlush','POST',{sale_id:list.sale_id,info:list.member_name+'金额:'+list.sum})
                .then(function (res) {
                    if(res.data.result===1){
                        toaster.pop('success','反冲成功！');
                        $scope.query($stateParams.page,$stateParams.search,$stateParams.sdate,$stateParams.edate);
                    }else
                        toaster.pop('error','反冲失败！');
                });
        });
    }
    $scope.summary='';
    $scope.summaryEdit=function (index) {
        var i=0;
        angular.forEach($scope.data.lists,function (list) {
            if(i===index) {
                $scope.summary=angular.isDefined(list.summary)?list.summary:'';
                list.edit = true;
            }
            else{
                list.edit=false;
            }
            i++;
        });
    }
    $scope.editBlur=function (index) {
        $scope.data.lists[index].edit=false;
        if($scope.summary===$scope.data.lists[index].summary) return false;
        myhttp.getData('/index/sale/editSummary','GET',{
            sale_id:$scope.data.lists[index].sale_id,
            summary:$scope.data.lists[index].summary,
            finish:$scope.data.lists[index].finish
        })
            .then(function (res) {
                if(res.data.result===1){
                    //toaster.pop('success','修改成功！');
                   // $scope.query($stateParams.page,$stateParams.search,$stateParams.sdate,$stateParams.edate);
                }else {
                    toaster.pop('error', '修改失败！');
                    $scope.data.lists[index].edit=false;
                    $scope.data.lists[index].summary = $scope.summary;
                }
            });
    }
});
app.controller('ConfirmController', ['$scope', '$modalInstance', function($scope, $modalInstance){
    $scope.ok = function () {
        $modalInstance.close();
    };
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}]);
app.controller('SaleDetailController',function ($scope,myhttp,$stateParams,toaster,$rootScope,$modal,$state,$timeout) {
    $scope.member_name=$stateParams.member_name;
    $scope.finish=0;
    myhttp.getData('/index/sale/saleDetail','GET',{sale_id:$stateParams.sale_id})
        .then(function (res) {
            if(res.data.finish===0) $scope.finish=1;
            $scope.details=res.data.data;
            var sum=0,num=0;
            angular.forEach($scope.details,function (row) {
                num+=Number(row.number);
                sum+=row.sum;
            });
            $scope.ttl_number=num;
            $scope.ttl_sum=sum;
        });
    $scope.edit=function (index) {
        if($scope.details[index].editing){
            if(isNaN($scope.details[index].sum)||($scope.details[index].sum==0)){toaster.pop('info','请完善修改信息！');return false;}
            var data={};
            data.sale_detail_id=$scope.details[index].sale_detail_id;
            data.price=$scope.details[index].price;
            data.number=$scope.details[index].number;
            data.sum=$scope.details[index].sum;
            data.sale_id=$scope.details[index].sale_id;
            data.unit_check=$scope.details[index].unit_id===$scope.details[index].unit_id_0;
            //data.unit_check=false;
            //data.unit_id=4;
            myhttp.getData('/index/sale/saleDetailUpdate','POST',data)
                .then(function (res) {
                    if(res.data.result===1){
                        toaster.pop('success','修改成功！');
                        $scope.details[index].editing=false;
                    }else if(res.data.result===2){
                        toaster.pop('error','已经付款，不能进行修改！');
                        $scope.details[index].editing=false;
                        $scope.finish=1;
                    }else{
                        toaster.pop('error','修改失败！');
                    }
                });
        }
        $scope.details[index].editing=true;
    }
    $scope.myBack=function(){
        // console.log($rootScope.previousStateParams);
         $state.go($rootScope.previousState,$rootScope.previousStateParams);
    }
    $scope.cal=function (index) {
        //$scope.data.smallChange=false;
        $scope.details[index].sum=Math.round($scope.details[index].number*$scope.details[index].price);
        var sum=0,num=0;
        angular.forEach($scope.details,function (row) {
            num+=Number(row.number);
            sum+=Number(row.sum);
        });
        $scope.ttl_number=num;
        $scope.ttl_sum=sum;
    }
    $scope.cal_sum=function ($index) {
        //alert();
        var sum=0;
        if($scope.details[$index].number!==0)
            $scope.details[$index].price=($scope.details[$index].sum/$scope.details[$index].number).toFixed(1);
        angular.forEach($scope.details,function (row) {
            sum+=row.sum;
        });
        $scope.ttl_sum=sum;
    }
    $scope.delete=function (detail) {
       // console.log(detail);
        var myscope = $rootScope.$new();
        myscope.info=$scope.member_name+'的销售单';
        var modalInstance = $modal.open({
            templateUrl: 'admin/confirm.html',
            controller: 'ConfirmController',
            // size:'sm',
            scope:myscope
        });
        modalInstance.result.then(function () {
            myhttp.getData('/index/sale/saleDetailDelete','POST',
                {sale_detail_id:detail.sale_detail_id,sale_id:detail.sale_id,info:$scope.member_name+':'+detail.goods_name+':'+detail.number+'*￥'+detail.price+'='+detail.sum})
                .then(function (res) {
                    if(res.data.result===1){
                        toaster.pop('success','删除成功！');
                        $state.reload();
                       /* myhttp.getData('/index/sale/saleDetail','GET',{sale_id:detail.sale_id})
                            .then(function (res) {
                                $scope.details=res.data;
                                var sum=0,num=0;
                                angular.forEach($scope.details,function (row) {
                                    num+=Number(row.number);
                                    sum+=row.sum;
                                });
                                $scope.ttl_number=num;
                                $scope.ttl_sum=sum;
                                $timeout(function () { $scope.$apply(); });
                            });*/
                    }
                });
        });

    }
});
app.controller('McreditController',function ($scope,myhttp,toaster,$stateParams,$modal,$rootScope,$state
) {
    //alert($stateParams.search);
    $scope.search_context=$stateParams.search;
    $scope.order=$stateParams.order;
    $scope.query_ttl=function () {
        myhttp.getData('/index/sale/ttlCredit','GET')
            .then(function (res) {
                $scope.ttlCredit=res.data;
            });
    };
    $scope.query_ttl();
    $scope.query_credit=function (page,search,order) {
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        if(!search){
            search='';
        }if(!order){
            order='';
        }
        myhttp.getData('/index/sale/credit','GET',{page:page,search:search,order:order})
            .then(function (res) {
                var data=res.data;
                data.page_index = page;
                data.pages = [];    //页签表
                var N = 5;          //每次显示5个页签
                data.page_count=Math.ceil(data.total_count/10);
                var s = Math.floor(page/N)*N;
                if(s===page)s-=N;
                s += 1;
                var e = Math.min(data.page_count,s+N-1);
                for(var i=s;i<=e;i++)
                    data.pages.push(i);
                $scope.data=data;
            });
    };

    $scope.query_credit($stateParams.page,$stateParams.search,$stateParams.order);
    $scope.more=function (list) {
        var myscope = $rootScope.$new();
        myscope.member=list;
        var modalInstance = $modal.open({
            templateUrl: 'admin/sale/creditDetail.html',
            controller: 'saleCreditDetailController',
            size:'lg',
            scope:myscope
        });
        modalInstance.result.then(function (res) {
           // console.log($stateParams.page);
            $scope.query_credit($stateParams.page,$stateParams.search,$stateParams.order);
            $scope.query_ttl();
            if(res===1){
                toaster.pop('success','保存成功!');
            } else{
                toaster.pop('error','保存失败，请联系管理员！');
            }
        });
    };
    //打印对账单
    $scope.printCredit=function (list) {
        var url = $state.href('print.saleCredit',{member_id:list.member_id,member_name:list.member_name});
       // window.open('admin/sale/printCredit.html','_blank');
        window.open(url,'_blank');
    };
    $scope.pageQuery=function (page) {
        $state.go('app.sale.mcredit', {page:page,search: $scope.search_context,order:$scope.order});
    };
    
    $scope.printAll=function () {
        var url = $state.href('print.printAll');
        // window.open('admin/sale/printCredit.html','_blank');
        window.open(url,'_blank');
    }

});

app.controller('saleCreditDetailController',function ($scope,myhttp,$modalInstance,$modal,$rootScope,$state,$filter) {
    $scope.pay={bank:{},paid_sum:0};
    $scope.check=false;
    myhttp.getData('/index/sale/creditDetail','GET',{member_id:$scope.member.member_id})
        .then(function (res) {
            $scope.credits=res.data.credit;
            $scope.banks=res.data.bank;
            $scope.pay.bank=$scope.banks[0].bank_id;
        });
    $scope.cancel=function () {
        $modalInstance.dismiss('cancel');
    };
    var selected = false;
    $scope.selectAll = function(){
        selected = !selected;
        var sum=0;
        var index=0;
        angular.forEach($scope.credits,function(item) {
            if(selected===false||index===0||(item.type==='P')||(item.sale_id!==$scope.credits[index-1].sale_id)||((item.sale_id===$scope.credits[index-1].sale_id&&item.type!==$scope.credits[index-1].type)))
                item.selected = selected;
            if(!selected){$scope.pay.paid_sum=0;return false; }
            if ((item.type=== 'S') || (item.type ==='B')) {
                sum += item.sum;
            } else {
                sum += item.ttl;
            }
            $scope.pay.paid_sum = sum;
            index++;
        });
    };
    $scope.cal=function (sum) {
       // var sum=$scope.pay.paid_sum;
            $scope.pay.paid_sum+=sum;
      //  var index=-1;
      /*  angular.forEach($scope.credits,function(item){
            if(item.selected) sum+=item.ttl;
                if(item.type!='S') {
                    sum+=item.ttl;
                }else{
                if((index<0)||(item.time!=$scope.credits[index].time))
                        sum += item.ttl;
                }
            }
            index++;
        });*/
       // $scope.pay.paid_sum=sum;
    };
    $scope.clean=function () {
        angular.forEach($scope.credits,function(item){
            item.selected=false;
        });
    };
    $scope.payment=function () {
       // console.log($scope.pay.paid_sum);
        if(angular.isUndefined($scope.pay.paid_sum)){return false;}
        var saleID=[];
        angular.forEach($scope.credits,function(item){
            if(item.selected)saleID.push(item.sale_id);
        });
        $scope.check=true;
       // if($scope.pay.paid_sum){
            myhttp.getData("/index/sale/payment",'POST',{pay:$scope.pay,saleID:saleID,member:$scope.member})
                .then(function (res) {
                    //console.log(res.data);
                    $scope.check=false;
                    $modalInstance.close(res.data.result);
                });

        //}
    };
    $scope.print=function () {
        var saleID=[];
        var count=0;
        angular.forEach($scope.credits,function(item){
            if(item.selected){saleID.push(item.sale_id);count++;}
        });
        if(count===0){
            //alert();
           var url = $state.href('print.saleCredit',{member_id:$scope.member.member_id,member_name:$scope.member.member_name});
            // window.open('admin/sale/printCredit.html','_blank');
            window.open(url,'_blank');
        }else{
            var url = $state.href('print.saleCredit',{member_id:$scope.member.member_id,member_name:$scope.member.member_name,sale_id:saleID});
            // window.open('admin/sale/printCredit.html','_blank');
            window.open(url,'_blank');
        }
        $modalInstance.dismiss();
    }
    $scope.carryOver=function () {
        var saleID=[];
        var count=0;
        angular.forEach($scope.credits,function(item){
            if(item.selected){saleID.push(item.sale_id);count++;}
        });
        if(count>0){
            $scope.check=true;
            var remark=$modal.open({
                templateUrl: 'admin/carryOverRemark.html',
                controller: 'remarkController'
            });
            remark.result.then(function (remark) {
                myhttp.getData('/index/sale/carryOver','POST',{saleID:saleID,summary:remark,member_id:$scope.member.member_id})
                    .then(function (res) {
                        $scope.check=false;
                        $modalInstance.close(res.data.result);
                    });
            });
        }
    }
    $scope.cfPrint=function (sale_id) {
        var myscope = $rootScope.$new();
        myscope.sale_id=sale_id;
        myscope.member=$scope.member;
        var remark=$modal.open({
            templateUrl: 'admin/sale/cfPrint.html',
            controller: 'cfPrintController',
            scope:myscope
        });

    }
    $scope.selectCredit=function () {
        var sum=0;
        if($scope.sdate&&$scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            var sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            var edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            angular.forEach($scope.credits, function (item) {
                item.selected = false;
                if (item.time >=sdate && item.time <= edate) {
                    item.selected = true;
                    if ((item.type === 'S') || (item.type === 'B')) {
                        sum += item.sum;
                    } else {
                        sum += item.ttl;
                    }
                    $scope.pay.paid_sum = sum;
                }
            });
        }
    }
});
app.controller('cfPrintController',function ($modalInstance,$scope,myhttp,$rootScope,$modal,$state) {
    myhttp.getData('/index/sale/cfPrint','GET',{cf:$scope.sale_id})
        .then(function (res) {
            $scope.credits = res.data.credit;
        });
    $scope.cfPrint=function (sale_id) {
        var myscope = $rootScope.$new();
        myscope.sale_id = sale_id;
        myscope.member=$scope.member;
        var remark = $modal.open({
            templateUrl: 'admin/sale/cfPrint.html',
            controller: 'cfPrintController',
            scope: myscope
        });
    }
    $scope.cancel=function () {
        $modalInstance.dismiss('cancel');
    }
    $scope.print=function () {
        var saleID=[];
      //  var count=0;
        angular.forEach($scope.credits,function(item){
            saleID.push(item.sale_id);
        });
        var url = $state.href('print.saleCredit',{member_id:$scope.member.member_id,member_name:$scope.member.member_name,sale_id:saleID});
        window.open(url,'_blank');
    }
});
app.controller('remarkController',function ($modalInstance,$scope) {
    $scope.ok=function () {
        $modalInstance.close($scope.remark);
    }
});
app.controller('SaleTabController',function ($scope,myhttp,toaster,$localStorage,ngDialog,$state,$rootScope,$modal,$timeout,mycache) {
    $scope.mytabs=[];
    $scope.data={member:{}};
    var tab={};
    $scope.modifyMember=false;
    myhttp.getData('/index/purchase/purchaseInfo','GET')
        .then(function (res) {
            $localStorage.purchaseInfo = res.data;
            $scope.stores=res.data.store;
            $scope.storeId=$scope.stores[0].store_id;
        });

    $scope.query_member=function (search) {
        if(search!=='')
            myhttp.getData('/index/sale/memberSearch', 'GET', {page: 1, search: search})
                .then(function (res) {
                    $scope.members = res.data.member;
                });
    };
    $scope.memberDialog=function() {
        ngDialog.open({
            template: 'admin/sale/memberUpdate.html',
            className: 'ngdialog-theme-default' ,
            showClose: false,
            controller:'MemberUpdateController',
            preCloseCallback: function(value){
                switch(value)
                {
                    case 3:
                        toaster.pop('info','该客户姓名已经存在，请核对...');
                        break;
                    case 0:
                        toaster.pop('error','客户更新失败，请刷新后再试!');
                }
            }
        });
    };
    $scope.getCredit=function (item) {
        $timeout(function () {
            myhttp.getData('/index/sale/getMemberCredit','GET',{'member_id':item.member_id})
                .then(function (res) {
                    $scope.credit=res.data.credit;
                    var index=-1,i=0;
                    if($scope.modifyMember){
                        angular.forEach($scope.mytabs,function (tab) {
                            if(tab.active) index=i;
                            i++;
                        });
                        if(angular.isDefined($scope.mytabs[index].sale_id)){
                            toaster.pop('error','已保存订单不能更改客户！');
                            return false;
                        }
                        $localStorage['member']=item;
                        $scope.mytabs[index].title=item.member_name;
                        $scope.mytabs[index].member_id=item.member_id;
                        $scope.modifyMember=false;
                        $scope.$broadcast('modifyMember',item);
                        return false;                                                     //eva mark
                    }
                    angular.forEach($scope.mytabs,function (tab) {
                        if(angular.isUndefined(tab.sale_id)&&tab.member_id===item.member_id){
                            index=i;
                        }
                        i++;
                    });
                    if(index>-1){$scope.mytabs[index].active=true;return false;}
                    tab={
                        title:item.member_name,
                        active:true,
                        member_id:item.member_id,
                        tIndex:Date.parse(new Date())/1000
                    };
                    $localStorage['member']=item;
                    $scope.mytabs.push(tab);
                    $scope.$broadcast('modifyMember',item);
                    $localStorage['tIndex']=tab.tIndex;               //$scope.mytabs.indexOf(tab);
                    angular.element('#member').find('input').val('');
                    //angular.element('#'+$localStorage['tIndex']+' tbody tr:eq(0) td:eq(1) ').find('input').focus();
                });
        },100,false);
    };

    $scope.more=function () {
       // console.log($scope.data.member);
        var myscope = $rootScope.$new();
        myscope.member=$scope.data.member;
        var modalInstance = $modal.open({
            templateUrl: 'admin/sale/creditDetail.html',
            controller: 'saleCreditDetailController',
            size:'lg',
            scope:myscope
        });
        modalInstance.result.then(function (res) {
            if(res===1){
                myhttp.getData('/index/sale/getMemberCredit','GET',{'member_id':myscope.member.member_id})
                    .then(function (res) {
                        $scope.credit=res.data.credit;});
                toaster.pop('success','保存成功!');
            } else{
                toaster.pop('error','保存失败，请联系管理员！');
            }
        });
    };
    $scope.closeTab=function (index,check) {
        if(check) {
            if(confirm("是否关闭该销售单？")) $scope.mytabs.splice(index,1);
        }else
        $scope.mytabs.splice(index,1);
    };
    $scope.setStoreID=function () {
        $timeout(function () {
            if($scope.storeId!=0){
                $scope.$broadcast('setStoreID', $scope.storeId);
            }
        },200,false);

    };
    $scope.$on('sale_ok',function (event,index) {
        console.log('sale_ok');
        if(angular.element('#setting').hasClass("active"))angular.element('#setting').removeClass("active");
        var i=0;
        angular.forEach($scope.mytabs,function (tab) {
            if(tab.tIndex===index){
                if(angular.isDefined($scope.data.member)&&$scope.mytabs[i].member_id===$scope.data.member.member_id)
                    myhttp.getData('/index/sale/getMemberCredit','GET',{'member_id':$scope.data.member.member_id})
                        .then(function (res) {$scope.credit=res.data.credit;});
                $scope.closeTab(i);
            }
            i++;
        });
        angular.element('#member').find('input').focus();
    });
    $scope.$on('loadOrder',function (event,list) {
        var check=-1,index=0;
        tab={
            title:list.member_name+'订单',
            active:true,
            sale_id:list.sale_id,
            member_id:list.member_id,
            tIndex:Date.parse(new Date())/1000
        };
        angular.forEach($scope.mytabs,function (tab) {
           if(angular.isDefined(tab.sale_id)&&angular.isDefined(list.sale_id)&&tab.sale_id===list.sale_id) check=index;
           index++;
        });
        if(check>-1){ $scope.mytabs[check].active=true;return false;}
        $localStorage['member']=list;
        $scope.mytabs.push(tab);
        $localStorage['tIndex']=tab.tIndex;
        $scope.mytabs.indexOf(tab);
        angular.element('#member').find('input').val('');
    });

    $scope.myIndex=-1;
    $scope.length=0;
    $scope.$watch("mytabs", function(n, o){
        var index=-1;
        var i=0;
        angular.forEach(n,function (tab,j) {
            if(tab.active===true){
               index=n.indexOf(tab);
               i++;
               $scope.mytabs[j].mystyle={
                    "color" : "white",
                    "background-color" : "coral"};
            }else
                $scope.mytabs[j].mystyle={};
        });
        if(n.length > o.length ||n.length===0||n===o){
            return;
        }
        if(angular.isUndefined(n[index])||angular.isUndefined(n[index].member_id)) return;
        if(i>1) return;
       // console.log(n[index].title);
        if($scope.myIndex===index&&n.length===o.length&&$scope.length===n.length){$scope.myIndex=index;return;}
        //console.log(n[index].title);
        $scope.myIndex=index;
        $scope.length=n.length;
        if($scope.data.member.member_id===n[index].member_id) return;
        myhttp.getData('/index/sale/getMemberCredit','GET',{'member_id':n[index].member_id})
            .then(function (res) {
                $scope.credit=res.data.credit;
                $scope.data.member.member_id=n[index].member_id;
                if(n[index].title.indexOf("订单")>0)
                    $scope.data.member.member_name=n[index].title.substr(0,n[index].title.length-2);
                else
                    $scope.data.member.member_name=n[index].title;
                $timeout(function(){$scope.$apply()},0,true);
            });

    },true);
});