/**
 * Created by Administrator on 2017/5/20.
 */
'use strict';
app.controller('SupplyController', function($scope,$http, $resource,myhttp,$stateParams,ngDialog,toaster) {
    $scope.query = function(page,filter){
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        if(!filter) filter='';
        myhttp.getData('/index/purchase/supply','GET',{page:page,search:filter})
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
                  $scope.search_context = filter;
                  $scope.data=data;
              });
    };
    $scope.query($stateParams.page,$stateParams.search);
    $scope.search=function () {
        $scope.query(1,$scope.search_context);
    };
    $scope.supplyDialog=function(data) {
        ngDialog.open({
            template: 'admin/purchase/supplyUpdate.html',
            className: 'ngdialog-theme-default' ,
            showClose: false,
            controller:'SupplyUpdateController',
            data:data,
            preCloseCallback: function(value){
                 switch(value)
                 {
                 case 3:
                     toaster.pop('info','该供应商姓名已经存在，请核对...');
                 break;
                 case 0:
                     toaster.pop('error','供应商更新失败，请刷新后再试!');
                     break;
                     default:
                         $scope.query($scope.data.page_index,$scope.search_context);
                 }
            }
        });
    };
});
app.controller('SupplyUpdateController',function ($scope,myhttp) {
    var check=true;
    $scope.data=$scope.ngDialogData;
    $scope.py=function (data) {
        $scope.data.supply_sn=makePy(data).toString();
    };
    $scope.updateSupply=function () {
        check=false;
        if(typeof( $scope.data.supply_id)==="undefined") $scope.data.supply_id='' ;
        myhttp.getData('/index/purchase/supplyUpdate','POST',$scope.data)
              .then(function (res) {
               check=true;
               $scope.closeThisDialog(res.data.result);
            });
    }
});
app.controller('PurchaseController',function ($scope,myhttp,toaster,ngDialog,$state,$timeout,$filter) {

    $scope.supplies=[];
    $scope.pay={bank:{}};
    $scope.data={supply:[]};      //此处必须定义成数组，angularjs's bug
    $scope.rows=new Array(5);
    $scope.check=false;
    $scope.$on('ngRepeatFinished', function() {
        $timeout(function () {
            angular.element('#supply').find('input').focus();
        },200,false);

    });
    $scope.nextGood=function (item) {
        $timeout(function () {
        angular.element('#purchaseTable tbody tr td:eq(1) ').find('input').focus();
        },200,false);
    };
    myhttp.getData('/index/purchase/purchaseInfo','GET')
        .then(function (res) {
            $scope.banks=res.data.bank;
            $scope.pay.bank=$scope.banks[0].bank_id;
            $scope.stores=res.data.store;
            $scope.units=res.data.units;
        });
    myhttp.getData('/index/purchase/getInOrder','GET').then(function (result) {
        $scope.orders=result.data;
        $timeout(function () {
            $scope.$apply(function () {
                $scope.inorder=result.data[0].inorder;
            });
        },100,false);


    });
    $scope.query_supply=function (search) {
        if(search!='')
            myhttp.getData('/index/purchase/supplySearch', 'GET', {page: 1, search: search})
                .then(function (res) {
                    $scope.supplies = res.data.supply;
                });
    };
    $scope.supplyDialog=function() {
        ngDialog.open({
            template: 'admin/purchase/supplyUpdate.html',
            className: 'ngdialog-theme-default' ,
            showClose: false,
            controller:'SupplyUpdateController',
            preCloseCallback: function(value){
                switch(value)
                {
                    case 3:
                        toaster.pop('info','该供应商姓名已经存在，请核对...');
                        break;
                    case 0:
                        toaster.pop('error','供应商更新失败，请刷新后再试!');
                }
            }
        });
    };

    $scope.query_goods=function (search) {
        if(search!='') {
            myhttp.getData('/index/setting/goodsQuery', 'GET', {search: search})
                .then(function (res) {
                    $scope.goods = res.data;
                });
        }
    }
    $scope.cal=function (index) {
        if($scope.rows[index]===null){toaster.pop('info','请完善进货信息！');return false;}
        $scope.rows[index].sum=Math.round($scope.rows[index].number*$scope.rows[index].price);
        var sum=0,num=0;
        angular.forEach($scope.rows,function (row) {
            if(angular.isDefined(row.good)) {
               // console.log(row.number);
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
        $scope.rows.push({});
    }
    $scope.delRow=function (index) {
        $scope.rows.splice(index,1);
        var sum=0,num=0;
        angular.forEach($scope.rows,function (row) {
            num+=parseFloat(row.number);
            sum+=row.sum;
        });
        $scope.ttl_number=num;
        $scope.data.ttl_sum=sum;
    }
    $scope.save=function (back) {
       // console.log($scope.inorder);return false;
        if($scope.data.supply==''){toaster.pop('info','请输入供应商');return false;}
        var detail=[];
        var rowcheck=false;
        angular.forEach($scope.rows,function (row) {
           // console.log(row.good.unit_id);
            if(angular.isDefined(row.good)&&angular.isDefined(row.good.goods_id)) {
                if(isNaN(row.sum)) rowcheck=true;
                detail.push({
                    'goods_id': row.good.goods_id,
                    'unit_check': row.good.unit_id === row.unit.unit_id,
                    'is_order': row.good.is_order,
                    'number': row.number,
                    'unit_id': row.unit.unit_id,
                    'store_id': row.store_id,
                    'price': row.price,
                    'sum': row.sum
                });
            }
        });

        if(rowcheck) {{toaster.pop('info','请完善进货信息!');return false;}}
        if (angular.isDefined($scope.data.time))$scope.data.time=$filter('date')($scope.data.time,'yyyy-MM-dd 00:00:00');
        $scope.check=true;
        myhttp.getData('/index/purchase/purchase','POST',
            {
                'data':$scope.data,
                'pay':$scope.pay,
                'detail':detail,
                'back':back,
                'inorder':$scope.inorder
            })
            .then(function (res) {
                $scope.check=false;
                switch(res.data.result) {
                    case 1:
                    toaster.pop('success', '保存成功！');
                    $state.go('app.purchase.list');
                    break;
                    case 0:
                        toaster.pop('info','请刷新，重新输入！');
                        break;
                    default :
                        toaster.pop('error','系统错误，请联系管理员');

                }
            });
    }
    $scope.getUnit=function (item,index) {
          if(index===$scope.rows.length-1){ $scope.addRow();}
          myhttp.getData('/index/purchase/getUnit', 'GET', {
              'goods_id': item.goods_id,
              'unit_id':item.unit_id,
              'supply_id':angular.isDefined($scope.data.supply)?$scope.data.supply.supply_id:0
          })
          .then(function (res) {
              $scope.goods=[];
              $scope.rows[index].units = res.data.unit;
              for (var j = 0; j < $scope.rows[index].units.length; j++) {
                  if ($scope.rows[index].units[j].unit_id === item.unit_id) {
                      //item.unit_id = $scope.units[j];
                      $scope.rows[index].unit = $scope.rows[index].units[j];
                  }
              }
              $scope.rows[index].price= parseFloat(res.data.result);
              $scope.cal(index);
              $scope.rows[index].store_id = $scope.stores[0].store_id;
              $timeout(function () {
                  angular.element('#purchaseTable tbody tr:eq('+index+') td:eq(2) ').find('input').focus();
              },5,false);

          });

    };
    $scope.getPrice=function (item,index) {
        if($scope.data.supply!='') {
            myhttp.getData('/index/purchase/getPrice','GET',
                {
                    'goods_id':$scope.rows[index].good.goods_id,
                    'supply_id':$scope.data.supply.supply_id,
                    'unit_id':item.unit_id
                })
                .then(function (res) {
                    $scope.rows[index].price= parseFloat(res.data.result);
                    $scope.cal(index);
                });
        }
    }
    $scope.next=function (e,index) {
        var keycode = window.event?e.keyCode:e.which;
        index++;
        if(keycode==13){
            $timeout(function () {
                angular.element('#purchaseTable tbody tr:eq(' + index + ') td:eq(1) ').find('input').focus();
            },200,false);
            if(index==0) $scope.save(1);
        }
        if(keycode==42) $timeout(function () {angular.element('#paid_sum').focus();},200,false);
    }
});
app.controller('PurchaseListController',function ($scope,myhttp,$stateParams,$filter,$modal,$rootScope,toaster,$state) {
    var sdate='';
    var edate='';
    $scope.query = function(page,filter,urlSdate,urlEdate){
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        if(!filter) filter='';
        if(urlEdate&&urlSdate){
            sdate=urlSdate;
            edate=urlEdate;
        }
        myhttp.getData('/index/purchase/purchaseList','GET',
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
                    data.pages.push(i)
                $scope.search_context = filter;
                $scope.data=data;
                $scope.edate=$filter('limitTo')(edate, 10);
                $scope.sdate=$filter('limitTo')(sdate, 10);

            });
    };
    $scope.query($stateParams.page,$stateParams.search,$stateParams.sdate,$stateParams.edate);
    $scope.search=function () {
        if ($scope.sdate && $scope.sdate) {
            if ($scope.sdate > $scope.edate) {
                toaster.pop('info', '结束日期不能早于开始日期');
                $scope.sdate = '';
                $scope.edate = '';
                return false;
            }
            sdate = $filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            edate = $filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
            $state.go('app.purchase.list', {search: $scope.search_context, sdate: sdate, edate: edate});
        } else {
            $state.go('app.purchase.list', {search: $scope.search_context, sdate: null, edate: null});
        }
    };
    $scope.pageQuery=function (page,search,sdate,edate) {
        sdate = $filter('date')(sdate, "yyyy-MM-dd 00:00:00");
        edate = $filter('date')(edate, "yyyy-MM-dd 23:59:59");
        $state.go('app.purchase.list', {page:page,search: $scope.search_context, sdate: sdate, edate: edate});
    }
    //删除进货单
    $scope.delete=function (list) {
        var myscope = $rootScope.$new();
        myscope.info=list.supply_name+'的进货单';
        var modalInstance = $modal.open({
            templateUrl: 'admin/confirm.html',
            controller: 'ConfirmController',
            scope:myscope
        });
        modalInstance.result.then(function () {
           toaster.pop('info',"删除中请稍后...",'', 50000);
          myhttp.getData('/index/purchase/deletePurchase','POST',{purchase_id:list.purchase_id,info:list.supply_name+':'+list.sum})
              .then(function (res) {
                  toaster.clear();
                   if(res.data.result==1){
                        toaster.pop('success','删除成功！');
                        $scope.query(1,$scope.search_context);
                   }else if(res.data.result==2){
                       toaster.pop('error','已经销售不能删除！');
                   }else
                       toaster.pop('error','删除失败！');
              });
        });
    }
    $scope.fee=function (inorder) {
        var myscope = $rootScope.$new();
        myscope.inorder=inorder;
        var modalInstance = $modal.open({
            templateUrl: 'admin/purchase/fee.html',
            controller: 'PurchaseFeeController',
            size:'lg',
            scope:myscope
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
        myhttp.getData('/index/purchase/editSummary','GET',{
            purchase_id:$scope.data.lists[index].purchase_id,
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
app.controller('PurchaseFeeController',function ($scope,myhttp,$modalInstance,$timeout,$rootScope,$modal) {
    $scope.check=false;
    $scope.staffs=[];
    $scope.account={people:{},inorder:$scope.inorder};
   // var init={};
    myhttp.getData('/index/finance/bankAndSubject','GET',{subject_type:1})
        .then(function (res) {
            $scope.banks=res.data.banks;
            $scope.subjects=res.data.subjects;
            $scope.account=$.extend($scope.account,{subject:$scope.subjects[0].subject_id,bank_id:0});
           // init=$scope.account;
        });
    $scope.feeList=function () {
        myhttp.getData('/index/purchase/feeList','GET',{'inorder':$scope.inorder})
            .then(function (res) {
                 $scope.lists=res.data;
            });
    }
    $scope.feeList();
    $scope.query_staff=function (search) {
        if(search==='') return false;
        myhttp.getData('/index/setting/staffSearch','GET',{search:search,page:1})
            .then(function (res) {
                $scope.staffs=res.data.staff;
            });
    }
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
    $scope.saveFee=function () {
       // console.log($scope.account.people);
        if(angular.isUndefined($scope.account.people.staff_id)) {
            alert("请选择员工！");
            angular.element('#staff').find('input').focus();
            return false;
        }
       $scope.check=true;
        myhttp.getData('/index/purchase/feeAdd','POST',$scope.account)
            .then(function (res) {
                $scope.check=false;
                if(res.data.result===1){
                    $timeout(function () {
                        $scope.$apply(function () {
                            $scope.account.people={};
                            $scope.account.cost=null;
                        });
                        },100,false);
                    $scope.feeList();
                }
            });
    }
    $scope.delete=function (list) {
        var myscope = $rootScope.$new();
        myscope.info=list.staff_name+'的费用';
        var modalInstance = $modal.open({
            templateUrl: 'admin/confirm.html',
            controller: 'ConfirmController',
            scope:myscope
        });
        modalInstance.result.then(function () {
            myhttp.getData('/index/purchase/feeDelete','POST',
                {
                    staff_credit_id:list.staff_credit_id,
                    account_id:list.account_id
                }).then(function (res) {
                if(res.data.result===1){
                    $scope.feeList();
                }else{
                    toaster.pop('error','删除发生错误，请联系管理员');
                }
            });
        });

    }
});
app.controller('ConfirmController', ['$scope', '$modalInstance', function($scope, $modalInstance){
    $scope.ok = function () {
        $modalInstance.close();
       // toaster.pop("info","删除中,请稍等...",'',5000);
    };
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}]);
app.controller('PurchaseDetailController',function ($scope,myhttp,$stateParams,toaster,$rootScope,$modal,$state) {
    $scope.supply_name=$stateParams.supply_name;
    $scope.finish=$stateParams.finish;
    myhttp.getData('/index/purchase/purchaseDetail','GET',{purchase_id:$stateParams.purchase_id})
        .then(function (res) {
            $scope.details=res.data;
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
            toaster.pop('info',"修改中,请稍等...",'',5000);
            if(isNaN($scope.details[index].sum)||($scope.details[index].sum==0)){toaster.pop('info','请完善修改信息！');return false;}
            var data={};
            data.purchase_detail_id=$scope.details[index].purchase_detail_id;
            data.price=$scope.details[index].price;
            data.number=$scope.details[index].number;
            data.sum=$scope.details[index].sum;
            data.purchase_id=$scope.details[index].purchase_id;
            data.goods_unit_id=$scope.details[index].unit_id;
            myhttp.getData('/index/purchase/purchaseDetailUpdate','POST',data)
                .then(function (res) {
                   // console.log(res.data);
                    toaster.clear();
                    if(res.data.result==1){
                        toaster.pop('success','修改成功！');
                        $scope.details[index].editing=false;
                    }
                });
        }
        $scope.details[index].editing=true;
    };
    $scope.cal=function (index) {
        $scope.details[index].sum=Math.round($scope.details[index].number*$scope.details[index].price);
        var sum=0,num=0;
        angular.forEach($scope.details,function (row) {
            num+=Number(row.number);
            sum+=row.sum;
        });
        $scope.ttl_number=num;
        $scope.ttl_sum=sum;
    };
    $scope.cal_sum=function ($index) {
        var sum=0;
        if($scope.details[$index].number!==0)
            $scope.details[$index].price=($scope.details[$index].sum/$scope.details[$index].number).toFixed(1);

        angular.forEach($scope.details,function (row) {
            sum+=row.sum;
        });
        $scope.ttl_sum=sum;
    };
    $scope.delete=function (detail) {
        var myscope = $rootScope.$new();
        myscope.info=$scope.supply_name+'的进货单';
        var modalInstance = $modal.open({
            templateUrl: 'admin/confirm.html',
            controller: 'ConfirmController',
           // size:'sm',
            scope:myscope
        });
        modalInstance.result.then(function () {
            myhttp.getData('/index/purchase/purchaseDetailDelete','POST',
                {purchase_detail_id:detail.purchase_detail_id,purchase_id:detail.purchase_id,goods_unit_id:detail.unit_id})
                .then(function (res) {
                    toaster.clear();
                    if(res.data.result==1){
                        toaster.pop('success','删除成功！');
                        myhttp.getData('/index/purchase/purchaseDetail','GET',{purchase_id:$stateParams.purchase_id})
                            .then(function (res) {
                                $scope.details=res.data;
                                var sum=0,num=0;
                                angular.forEach($scope.details,function (row) {
                                    num+=Number(row.number);
                                    sum+=row.sum;
                                });
                                $scope.ttl_number=num;
                                $scope.ttl_sum=sum;
                            });
                    }else if(res.data.result==2){
                        toaster.pop('info','该产品已经销售，不能删除！');
                    }
                })
        });

    }
    $scope.myBack=function(){
        $state.go($rootScope.previousState,$rootScope.previousStateParams);
    }
});
app.controller('ScreditController',function ($scope,myhttp,toaster,$stateParams,$modal,$rootScope,$state) {
    $scope.query_ttl=function () {
        myhttp.getData('/index/purchase/ttlCredit','GET')
            .then(function (res) {
                $scope.ttlCredit=res.data;
            });
    };
    $scope.query_ttl();
    $scope.query_credit=function (page) {
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        myhttp.getData('/index/purchase/credit','GET',{page:page})
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
            });
    };
    $scope.query_credit($stateParams.page);
    $scope.more=function (list) {
        var myscope = $rootScope.$new();
        myscope.supply=list;
        var modalInstance = $modal.open({
            templateUrl: 'admin/purchase/creditDetail.html',
            controller: 'supplyCreditDetailController',
            size:'lg',
            scope:myscope
        });
        modalInstance.result.then(function (res) {
            $scope.query_credit($stateParams.page);
            $scope.query_ttl();
            if(res===1){
                toaster.pop('success','保存成功!');
            } else{
                toaster.pop('error','保存失败，请联系管理员！');
            }
        });
    }
    $scope.printCredit=function (list) {
        var url = $state.href('print.purchaseCredit',{supply_id:list.supply_id,supply_name:list.supply_name});
        // window.open('admin/sale/printCredit.html','_blank');
        window.open(url,'_blank');
    }
});

app.controller('supplyCreditDetailController',function ($scope,myhttp,$modalInstance,$modal,$rootScope) {
      $scope.pay={bank:{}};
      $scope.check=false;
      myhttp.getData('/index/purchase/creditDetail','GET',{supply_id:$scope.supply.supply_id})
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
            if(index===0||(item.purchase_id!==$scope.credits[index-1].purchase_id))
            item.selected = selected;
            if(!selected){$scope.pay.paid_sum=0;return false; }
        if ((item.type== 'S') || (item.type =='B')) {
                sum += item.sum;
            } else {
                sum += item.ttl;
            }
            $scope.pay.paid_sum = sum;
            index++;
        });
    };
    $scope.cal=function () {
        var sum=0;
       // var index=-1;
        angular.forEach($scope.credits,function(item){
            if(item.selected){
                sum+=item.ttl;
            }
                //console.log(item);
                /*if(item.type!=='S') {
                    sum+=item.ttl;
                }else if(item.type!=='B'){
                    sum+=item.ttl;
                }
                else {
                    if ((index < 0) || (item.time != $scope.credits[index].time)) sum += item.ttl;
                }
            }
            index++;*/
        });
        $scope.pay.paid_sum=sum;
    };
    $scope.clean=function () {
        angular.forEach($scope.credits,function(item){
            item.selected=false;
        });
    };
    $scope.payment=function () {
        if(angular.isUndefined($scope.pay.paid_sum)){return false;}
        var purchaseID=[];
        angular.forEach($scope.credits,function(item){
            if(item.selected)purchaseID.push(item.purchase_id);
        });
        $scope.check=true;
       // if($scope.pay.paid_sum){
            myhttp.getData("/index/purchase/payment",'POST',{pay:$scope.pay,purchaseID:purchaseID,supply:$scope.supply})
                .then(function (res) {
                    //console.log(res.data);
                    $scope.check=false;
                    $modalInstance.close(res.data.result);
                })

       // }
    };
    $scope.carryOver=function () {
        var purchaseID=[];
        var count=0;
        angular.forEach($scope.credits,function(item){
            if(item.selected){purchaseID.push(item.purchase_id);count++;}
        });
        if(count>0){
            $scope.check=true;
            var remark=$modal.open({
                templateUrl: 'admin/carryOverRemark.html',
                controller: 'remarkController'
            });
            remark.result.then(function (remark) {
                myhttp.getData('/index/purchase/carryOver','POST',{purchaseID:purchaseID,summary:remark,supply_id:$scope.supply.supply_id})
                    .then(function (res) {
                        $scope.check=false;
                        $modalInstance.close(res.data.result);
                    });
            });
        }
    }
    $scope.cfPrint=function (purchase_id) {
        var myscope = $rootScope.$new();
        myscope.purchase_id=purchase_id;
        var remark=$modal.open({
            templateUrl: 'admin/purchase/cfPrint.html',
            controller: 'cfPrintController',
            scope:myscope
        });
        
    }
});
app.controller('cfPrintController',function ($modalInstance,$scope,myhttp,$rootScope,$modal) {
    myhttp.getData('/index/purchase/cfPrint','GET',{cf:$scope.purchase_id})
        .then(function (res) {
            $scope.credits = res.data.credit;
        });
    $scope.cfPrint=function (purchase_id) {
        var myscope = $rootScope.$new();
        myscope.purchase_id = purchase_id;
        var remark = $modal.open({
            templateUrl: 'admin/purchase/cfPrint.html',
            controller: 'cfPrintController',
            scope: myscope
        });
    }
    $scope.cancel=function () {
        $modalInstance.dismiss('cancel');
    }

});
app.controller('remarkController',function ($modalInstance,$scope) {
    $scope.ok=function () {
        $modalInstance.close($scope.remark);
    }
});
