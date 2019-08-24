/**
 * Created by Administrator on 2017/5/13.
 */
'use strict';

app.controller('SettingController', function($scope,$http, $resource,$localStorage,toaster) {
    //alert(mycache.get('goods'));
    $scope.print_remark=false;
    $scope.query=function () {
        $http.defaults.headers.common['Authorization'] = $localStorage.auth;
        var $com = $resource($scope.app.host + "/index/setting/info");
        $com.get(function (data) {
            $scope.info=data;
            if($scope.info.print_remark>0) $scope.print_remark=true;
        });
    };
    $scope.query();
    //修改公司基本信息
    $scope.editInfo=function () {
        console.log($scope.print_remark);
        toaster.pop('wait','公司信息','修改中...',50000);
        var mydata={'cname':$scope.info.cname,'ctel':$scope.info.ctel,'cadd':$scope.info.cadd,'print_lopo':$scope.info.print_lopo,'print_remark':$scope.print_remark};
        $http.defaults.headers.common['Authorization'] = $localStorage.auth;
        $http({
            method: 'POST',
            url: $scope.app.host + "/index/setting/editInfo",
            data:mydata
        }).then(function(response){
            toaster.clear();
            if(response.data==="1"){
                toaster.pop('success','修改成功！');
            }else{
                toaster.pop("error",'修改错误','请联系管理员。');
            }
        });
    }
});
//种类产品设置
app.controller('GoodsCtrl', function($scope, $resource,$http, $filter,$localStorage,toaster,mycache,myhttp) {
    $scope.py=function (item) {
        item.goods_sn=makePy(item.goods_name).toString();
    };
    $scope.queryCat=function () {
        $http.defaults.headers.common['Authorization'] = $localStorage.auth;
        var $com = $resource($scope.app.host + "/cat");
        $com.query(function (data) {
            $scope.cats=data;
        });
    };
    $scope.queryUnit=function () {
        $http.defaults.headers.common['Authorization'] = $localStorage.auth;
        var $com = $resource($scope.app.host + "/index/setting/unit");
        $com.query(function (data) {
            $scope.units=data;
        });
    };
    $scope.queryGoods=function () {
        toaster.pop('wait','载入中，请稍后...','',50000);
        $http.defaults.headers.common['Authorization'] = $localStorage.auth;
        var $com = $resource($scope.app.host + "/index/setting/goods");
        $com.query(function (data) {
            toaster.clear();
            $scope.items=data;
            mycache.put('goods',data);
        });
    };

    $scope.queryCat();
    $scope.queryUnit();
    if(mycache.get('goods')==undefined){
         $scope.queryGoods();
    }else {
         $scope.items=mycache.get('goods');
    }

    $scope.filter = '';
    $scope.item='';


    $scope.createCat = function(){
        var cat = {cat_name: '新种类'};
        cat.cat_name = $scope.checkItem(cat, $scope.cats, 'cat_name');
        $scope.cats.push(cat);
    };

    $scope.checkItem = function(obj, arr, key){
        var i=0;
        angular.forEach(arr, function(item) {
            if(item[key].indexOf( obj[key] ) == 0){
                var j = item[key].replace(obj[key], '').trim();
                if(j){
                    i = Math.max(i, parseInt(j)+1);
                }else{
                    i = 1;
                }
            }
        });
        return obj[key] + (i ? ' '+i : '');
    };

    $scope.deleteCat = function(item){
        if(item.cat_id==undefined) {
            $scope.cats.splice($scope.cats.indexOf(item), 1);
        }else{
            toaster.pop('wait','种类删除中...','',50000);
            $http.defaults.headers.common['Authorization'] = $localStorage.auth;
            var $com = $resource($scope.app.host + "/index/setting/catDelete");
            $com.delete({'cat_id':item.cat_id},function (response) {
                toaster.clear();
                if(response.result==1) {
                    toaster.pop('success','种类删除成功！');
                    $scope.queryCat();
                }else if(response.result==2){
                    toaster.pop('warn','该种类下有产品，不能删除！');
                }else
                    toaster.pop('error','种类删除失败！');
            });
        }
    };

    $scope.selectCat = function(item){
        angular.forEach($scope.cats, function(item) {
            item.selected = false;
        });
        $scope.cat = item;
        $scope.cat.selected = true;
        $scope.filter = item.cat_id;
    };

    $scope.selectItem = function(item){
        angular.forEach($scope.items, function(item) {
            item.selected = false;
            item.editing = false;
        });
        $scope.item = item;
        $scope.item.selected = true;
        $scope.item.editing = false;
        $scope.item.is_order=(item.is_order==1);
        $scope.item.promote=(item.promote==1);
        for(var i=0;i<$scope.cats.length;i++){
            if(($scope.cats[i].cat_id==item.cat_id)){
                item.cat_id=$scope.cats[i];
                //return false;
            }
        }
        myhttp.getData('/index/setting/getUnitPrice', 'GET', {'goods_id': item.goods_id}).then(function (result) {
           if(result.data.length>0){
               item.unit_price1=result.data[0];
               item.unit_price1.fx=parseFloat(item.unit_price1.fx);
           }
            if(result.data.length>1){
                item.unit_price2=result.data[1];
                item.unit_price2.fx=parseFloat(item.unit_price2.fx);
            }
            for(var j=0;j<$scope.units.length;j++){
                //alert(i);
                if(($scope.units[j].unit_id==item.unit_id)){
                    item.unit_id=$scope.units[j];
                   // return false;
                }
                if(angular.isDefined(item.unit_price1)&&item.unit_price1.unit_id==$scope.units[j].unit_id){
                    item.unit_price1.unit_id=$scope.units[j];
                }
                if(angular.isDefined(item.unit_price2)&&item.unit_price2.unit_id==$scope.units[j].unit_id){
                    item.unit_price2.unit_id=$scope.units[j];
                }
            }
        });

    };

    $scope.deleteItem = function(item){
        item.editing=false;
        //console.log(item);
        if(item.goods_id===0){
            $scope.items.splice($scope.items.indexOf(item), 1);
            $scope.item = $filter('orderBy')($scope.items, 'first')[0];
            if($scope.item) $scope.item.selected = true;
        }
    };

    $scope.createItem = function(){
        var item = {
            cat_id: $scope.cat?$scope.cat.cat_id:'',
            cat_name:$scope.cat?$scope.cat.cat_name:'',
            goods_id:0,
            is_order:0,
            warn_stock:0,
            out_price:0,
            promote:false
        };
        $scope.items.push(item);
        $scope.selectItem(item);
        $scope.item.editing = true;
    };

    $scope.saveGoods=function (item) {
        myhttp.getData('/index/setting/goodsCheck', 'GET', {search: item.goods_name})
            .then(function (res) {
                if(res.data!=='null'){
                    if(item.goods_id===0) {
                        toaster.pop('info', '该产品名称已存在，请核对后再录入！');
                        return false;
                    }else {
                        if(res.data.goods_id!==item.goods_id) {
                            toaster.pop('info', '该产品名称已存在，请核对后再录入！');
                            return false;
                        }
                    }
                }
              // if((item.goods_id===0&&res.data!==null)||(item.goods_id!==res.data[0].goods_id)){toaster.pop('info','该产品名称已存在，请核对后再录入！'); return false;}
        var check;
        if(angular.isDefined(item.unit_price1)&&angular.isDefined(item.unit_price1.fx)&&item.unit_price1.fx>0)
        {
            if(angular.isUndefined(item.unit_price1.unit_id)||angular.isUndefined(item.unit_price1.unit_id||angular.isUndefined(item.unit_price1.fx))){
                toaster.pop('info','请完善预置单位1和预置售价1的信息。');
                return false;
            }else if(item.unit_price1.unit_id==item.unit_id){
                toaster.pop('info','请预置单位1不能和库存单位相同。');
                return false;
            }
            check=true;

        }
        if(angular.isDefined(item.unit_price2))
        {
            if(angular.isUndefined(item.unit_price2.unit_id)||angular.isUndefined(item.unit_price2.unit_id||angular.isUndefined(item.unit_price2.fx))){
                toaster.pop('info','请完善预置单位2和预置售价2的信息。');
                return false;
            }else if(item.unit_price2.unit_id==item.unit_id){
                toaster.pop('info','请预置单位2不能和库存单位相同。');
                return false;
            } else if(check&&(item.unit_price2.unit_id==item.unit_price1.unit_id)){
                toaster.pop('info','请预置单位2不能和预置单位1相同。');
                return false;
            }

        }
        if(item.goods_name&&item.goods_sn&&item.cat_id&&item.unit_id) {
            toaster.pop('wait', '产品保存中...', '', 50000);
            $http.defaults.headers.common['Authorization'] = $localStorage.auth;
            var $com = $resource($scope.app.host + "/index/setting/goodsUpdate");
            $com.save(item, function (response) {
                toaster.clear();
                if (response.result === 1) {
                  //  mycache.remove('goods');
                    if(angular.isUndefined(item.goods_id))
                       $scope.queryGoods();
                    item.editing = false;
                    toaster.pop('success', '产品修改成功！');
                } else toaster.pop('error', '产品修改失败！');
            });
        }else{
            toaster.pop('info','请完善产品信息。');
        }
            });
    };
    $scope.editItem = function(item){
        if(item && item.selected){
            item.editing = true;
        }
    };

    
//种类更新与添加
    $scope.doneEditing = function(item){
        for(var i=0;i<$scope.cats.length;i++){
            if(($scope.cats[i].cat_name==item.cat_name)&&($scope.cats.indexOf(item)!=i)){
                toaster.pop('error','添加更新种类名称重复！');
                return false;
            }
        }
        item.editing = false;
        toaster.pop('wait','种类更新中...','',50000);
        $http.defaults.headers.common['Authorization'] = $localStorage.auth;
        if(item.cat_id==undefined){
             var $com=$resource($scope.app.host+'/catCreate');
             $com.save(item,function (response) {
                 toaster.clear();
                 if(response.result==1) {
                     toaster.pop('success','种类添加成功！');
                     $scope.queryCat();
                 }else toaster.pop('error','添加种类失败！');
             });
        }else {
            var $com = $resource($scope.app.host + "/catUpdate");
            $com.save(item,function (response) {
                toaster.clear();
                if(response.result==1) {
                    toaster.pop('success','种类更新成功！');
                    $scope.queryCat();
                }else toaster.pop('error','种类更新失败！');
            });

        }
    };

});
app.controller('UserController', function($scope,myhttp,Base64,$localStorage,toaster) {
    $scope.check=false;
    $scope.updateUser=function () {
        $scope.check=true;
        if($scope.old_pwd==$scope.new_pwd){
            toaster.pop('info','新密码和旧密码不能相同，请重新输入！');
            $scope.new_pwd=$scope.confirm_password='';
            return false;
        }
       var mydata=Base64.encrypt($localStorage.user + ':' + $scope.old_pwd+':'+$scope.new_pwd);
        toaster.pop('info','密码修改中，请稍等...',5000);
       myhttp.getData('/index/setting/pwdUpdate','POST',{data:mydata}).then(function (res) {
           toaster.clear();
           $scope.check=false;
           $scope.old_pwd=$scope.new_pwd=$scope.confirm_password='';
           if(res.data.result==1){
               toaster.pop('success','密码修改成功！');
           }else toaster.pop('error','旧密码输入有误，请重新输入');
       });
        
    }
});
app.controller('StoreController',function ($scope,myhttp,ngDialog,toaster) {
    myhttp.getData('/index/setting/store','GET').then(function (result) {
        $scope.data=result.data;
        //alert("hello");
    });
    $scope.storeDialog=function (data) {
        ngDialog.open({
            template: 'admin/setting/storeUpdate.html',
            className: 'ngdialog-theme-default' ,
            showClose: false,
            controller:'StoreUpdateController',
            data:data,
            preCloseCallback: function(value){
                switch(value)
                {
                    case 3:
                        toaster.pop('info','该仓库已经存在，请核对...');
                        break;
                    case 0:
                        toaster.pop('error','仓库更新失败，请刷新后再试!');
                        break;
                    case 1:
                        myhttp.getData('/index/setting/store','GET').then(function (result) {
                            $scope.data=result.data;
                        });
                        break;
                }
            }
        });
    }
});
app.controller('StoreUpdateController',function ($scope,myhttp) {
    var check=true;
    $scope.data=$scope.ngDialogData;
    $scope.updateStore=function () {
        check=false;
        if(typeof( $scope.data.store_id)=="undefined") $scope.data.store_id='' ;
        myhttp.getData('/index/setting/storeUpdate','POST',$scope.data)
            .then(function (res) {
                check=true;
                $scope.closeThisDialog(res.data.result);
            });
    }
    
});
app.controller('StaffController', function($scope,$http, $resource,myhttp,$stateParams,ngDialog,toaster) {
    $scope.query = function(page,filter){
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        if(!filter) filter='';
        myhttp.getData('/index/setting/staff','GET',{page:page,search:filter})
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
    $scope.staffDialog=function(data) {
        ngDialog.open({
            template: 'admin/setting/staffUpdate.html',
            className: 'ngdialog-theme-default' ,
            showClose: false,
            controller:'StaffUpdateController',
            data:data,
            preCloseCallback: function(value){
                switch(value)
                {
                    case 3:
                        toaster.pop('info','该员工姓名或者电话已经存在，请核对...');
                        break;
                    case 0:
                        toaster.pop('error','员工更新失败，请刷新后再试!');
                        break;
                    case 1:
                        $scope.query($stateParams.page,$stateParams.search);
                        break;
                }
            }
        });
    };
});
app.controller('StaffUpdateController',function ($scope,myhttp,$timeout,toaster) {
    myhttp.getData('/index/setting/role','GET').then(function (res) {
        $scope.roles=res.data.role;
        $scope.roles.unshift({role_id:-1,role_name:'非系统用户'});
         $timeout(function () {
                $scope.$apply(function () {
                    $scope.data=$scope.ngDialogData;
                    if(angular.isUndefined($scope.data)) $scope.data={role_id:-1} ;
                });
         });
    });
    var check=true;
    $scope.py=function (data) {
        $scope.data.staff_sn=makePy(data).toString();
    };
    $scope.updateStaff=function () {
        //console.log($scope.data.staff_phone);return false;
        check=false;
        if(typeof( $scope.data.staff_id)==="undefined") $scope.data.staff_id='' ;
        if($scope.data.role_id>0&&angular.isUndefined($scope.data.staff_phone)) {
            toaster.pop('info','必须输入电话号码');
            return false;
        }
        myhttp.getData('/index/setting/staffUpdate','POST',$scope.data)
            .then(function (res) {
                check=true;
                $scope.closeThisDialog(res.data.result);
            });
    }
});

app.controller('UnitController', function($scope,$http, $resource,myhttp,$stateParams,ngDialog,toaster) {
    $scope.query = function(page,filter){
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        if(!filter) filter='';
        myhttp.getData('/index/setting/unit','GET',{page:page,search:filter})
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
    $scope.unitDialog=function(data) {
        ngDialog.open({
            template: 'admin/setting/unitUpdate.html',
            className: 'ngdialog-theme-default' ,
            showClose: false,
            controller:'UnitUpdateController',
            data:data,
            preCloseCallback: function(value){
                switch(value)
                {
                    case 3:
                        toaster.pop('info','该单位已经存在，请核对...');
                        break;
                    case 0:
                        toaster.pop('error','单位更新失败，请刷新后再试!');
                        break;
                    case 1:
                        $scope.query($stateParams.page,$stateParams.search);
                        break;
                }
            }
        });
    };
});
app.controller('UnitUpdateController',function ($scope,myhttp) {
    var check=true;
    $scope.data=$scope.ngDialogData;
    $scope.updateUnit=function () {
        check=false;
        if(typeof( $scope.data.unit_id)==="undefined") $scope.data.unit_id='' ;
        myhttp.getData('/index/setting/unitUpdate','POST',$scope.data)
            .then(function (res) {
                check=true;
                $scope.closeThisDialog(res.data.result);
            });
    }
});
app.controller('DataController',function ($scope,myhttp,$timeout) {
    $scope.url='';
    $scope.templateUrl=function () {
        return $scope.url;
    }
    $scope.mCredit=function () {
        $scope.url='admin/setting/mCredit.html';
        $timeout(function(){$scope.templateUrl();},0,true);
    }
    $scope.sCredit=function () {
        $scope.url='admin/setting/sCredit.html';
        $timeout(function(){$scope.templateUrl();},0,true);
    }
    $scope.stock=function () {
        $scope.url='admin/setting/stock.html';
        $timeout(function(){$scope.templateUrl();},0,true);
    }
});
app.controller('SettingMCreditController',function ($scope,myhttp,$timeout,toaster,ngDialog) {
    $scope.rows=new Array(5);
    $scope.memberDialog=function () {
        ngDialog.open({
            template: 'admin/sale/memberUpdate.html',
            className: 'ngdialog-theme-default' ,
            showClose: false,
            controller:'MemberUpdateController',
            preCloseCallback: function(value){
                switch(value)
                {
                    case 3:
                        ngDialog.open({
                            template:'<div><i class="icon-info text-info-lter"></i>  该客户姓名已经存在，请核对...</div>',
                            plain: true
                        });
                        break;
                    case 0:
                        ngDialog.open({
                            template:'<div><i class="icon-info text-error" ></i>  客户更新失败，请刷新后再试!</div>',
                            plain: true
                        });

                }
            }
        });
    }
    $scope.query_member=function (search) {
        if(search!='')
            myhttp.getData('/index/sale/memberSearch', 'GET', {page: 1, search: search})
                .then(function (res) {
                    $scope.members = res.data.member;
                });
    }
    $scope.getMember=function (index) {
        if(index===$scope.rows.length-1) $scope.rows.push({});
        $timeout(function () {
            angular.element('#mCredit tbody tr:eq('+index+') td:eq(2) ').find('input').focus();
        },10,false);
    }
    $scope.next=function (e,index) {
        var keycode = window.event?e.keyCode:e.which;
        if(keycode===13){
            $timeout(function () {
                angular.element('#mCredit tbody tr:eq('+index+') td:eq(3) ').find('input').focus();
            },10,false);
        }
    }
    $scope.nextRow=function (e,index) {
        var keycode = window.event?e.keyCode:e.which;
        if(keycode===13){
            $timeout(function () {
                index++;
                angular.element('#mCredit tbody tr:eq('+index+') td:eq(1) ').find('input').focus();
            },10,false);
        }
    }
    $scope.cal=function () {
        var ttl=0;
        angular.forEach($scope.rows,function (item) {
            if(angular.isDefined(item.member)) ttl+=Number(item.sum);
        });
        $scope.ttl_sum=ttl;
    }
    $scope.save=function () {
        $scope.check=true;
        var data=[];
        angular.forEach($scope.rows,function (item) {
            if(angular.isDefined(item.member)&&item.sum!==0){
                data.push({'member_id':item.member.member_id,'sum':item.sum,'summary':item.summary});
            }
        });
        myhttp.getData('/index/setting/mCreditAdd','post',data).then(function (res) {
             $scope.check=false;
             if(res.data.result===1){
                 toaster.pop('success','保存成功!');
                 $scope.rows=new Array(5);
             }else {
                 toaster.pop('error','保存失败!');
             }
        });
    }
});
app.controller('SettingSCreditController',function ($scope,myhttp,$timeout,toaster,ngDialog) {
    $scope.rows=new Array(5);
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
    $scope.query_supply=function (search) {
        if(search!='')
            myhttp.getData('/index/purchase/supplySearch', 'GET', {page: 1, search: search})
                .then(function (res) {
                    $scope.supplys = res.data.supply;
                });
    }
    $scope.getSupply=function (index) {
        if(index===$scope.rows.length-1) $scope.rows.push({});
        $timeout(function () {
            angular.element('#sCredit tbody tr:eq('+index+') td:eq(2) ').find('input').focus();
        },10,false);
    }
    $scope.next=function (e,index) {
        var keycode = window.event?e.keyCode:e.which;
        if(keycode===13){
            $timeout(function () {
                angular.element('#sCredit tbody tr:eq('+index+') td:eq(3) ').find('input').focus();
            },10,false);
        }
    }
    $scope.nextRow=function (e,index) {
        var keycode = window.event?e.keyCode:e.which;
        if(keycode===13){
            $timeout(function () {
                index++;
                angular.element('#sCredit tbody tr:eq('+index+') td:eq(1) ').find('input').focus();
            },10,false);
        }
    }
    $scope.cal=function () {
        var ttl=0;
        angular.forEach($scope.rows,function (item) {
            if(angular.isDefined(item.supply)) ttl+=Number(item.sum);
        });
        $scope.ttl_sum=ttl;
    }
    $scope.save=function () {
        $scope.check=true;
        var data=[];
        angular.forEach($scope.rows,function (item) {
            if(angular.isDefined(item.supply)&&item.sum!==0){
                data.push({'supply_id':item.supply.supply_id,'sum':item.sum,'summary':item.summary});
            }
        });
        myhttp.getData('/index/setting/sCreditAdd','post',data).then(function (res) {
            $scope.check=false;
            if(res.data.result===1){
                toaster.pop('success','保存成功!');
                $scope.rows=new Array(5);
            }else {
                toaster.pop('error','保存失败!');
            }
        });
    }
});
app.controller('SettingStockController',function (myhttp,toaster,$scope,$timeout) {
    $scope.rows=new Array(5);
    $scope.query_goods=function (search) {
        if(search!=='') {
            myhttp.getData('/index/setting/goodsQuery', 'GET', {search: search})
                .then(function (res) {
                    $scope.goods = res.data;
                });
        }
    }
    $scope.$on('ngRepeatFinished', function() {
        $timeout(function () {
            angular.element('#purchaseTable tbody tr:eq(0) td:eq(1) ').find('input').focus();
        },20,false);

    });
    myhttp.getData('/index/setting/store','GET').then(function (res) {
        $scope.stores=res.data.store;
        $scope.storeId=$scope.stores[0].store_id;
        //console.log(res);
    });
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
    $scope.getUnit=function (item,index) {
        if(index===$scope.rows.length-1){ $scope.addRow();}
        $timeout(function () {
            angular.element('#purchaseTable tbody tr:eq('+index+') td:eq(2) ').find('input').focus();
        },5,false);
       // $scope.rows[index].store_id = $scope.stores[0].store_id;
       //  $scope.rows[index].unit = $scope.units[j];
        myhttp.getData('/index/purchase/getUnit', 'GET', {
            'goods_id': item.goods_id,
            'unit_id':item.unit_id,
            'supply_id':0
        }).then(function (res) {
                $scope.goods=[];
                $scope.rows[index].units = res.data.unit;
                for (var j = 0; j < $scope.rows[index].units.length; j++) {
                    if (($scope.rows[index].units[j].unit_id === item.unit_id)) {
                       // item.unit_id = $scope.rows[index].units[j];
                        $scope.rows[index].unit = $scope.rows[index].units[j];
                    }
                }
                $scope.rows[index].store_id = $scope.storeId;
            });
    };
    $scope.cal=function (index) {
        if($scope.rows[index]===null){toaster.pop('info','请完善进货信息！');return false;}
        $scope.rows[index].sum=Math.round($scope.rows[index].number*$scope.rows[index].price);
        var sum=0,num=0;
        angular.forEach($scope.rows,function (row) {
            if(angular.isDefined(row.good)) {
                num += parseFloat(row.number);
                sum += parseInt(row.sum);
            }
        });
        $scope.ttl_number=num;
        $scope.ttl_sum=sum;
    }
    $scope.next_number=function (e,index) {
        var keycode = window.event?e.keyCode:e.which;
        if(keycode===13){
            $timeout(function () {
                angular.element('#purchaseTable tbody tr:eq('+index+') td:eq(4) ').find('input').focus();
            },10,false);
            return false;
        }
    }
    $scope.next=function (e,index) {
        index++;
        var keycode = window.event?e.keyCode:e.which;
        if(keycode===13){
            $timeout(function () {
                angular.element('#purchaseTable tbody tr:eq('+index+') td:eq(1) ').find('input').focus();
            },10,false);
            return false;
        }
    }
    $scope.save=function () {
        var data=[];
        $scope.check=false;
        var temp={};
        angular.forEach($scope.rows,function (row) {
            if(angular.isDefined(row.good)) {
               if(isNaN(row.sum)) $scope.check=true;
               temp={
                   goods_id:row.good.goods_id,
                   unit_id_0:row.good.unit_id,
                   is_order:row.good.is_order,
                   number:parseFloat(row.number),
                   unit_id:row.unit.unit_id,
                   sum:row.sum,
                   store_id:row.store_id
               };
               data.push(temp);
            }
        });
        if($scope.check) {toaster.pop('info','请完善进货信息！');$scope.check=false;return false;}
        myhttp.getData('/index/setting/stockDetail','POST',data).then(function (res) {
            $scope.check=false;
            if(res.data.result===1){
                toaster.pop('success','保存成功!');
                $scope.rows=new Array(5);
            }else {
                toaster.pop('error','保存失败!');
            }
        });
    }
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
app.controller('LogController',function ($scope,myhttp,$stateParams,$filter) {
    var sdate='';
    var edate='';
    $scope.query = function(page,filter){
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        if(!filter) filter='';
        if($scope.sdate&&$scope.sdate){
            if($scope.sdate>$scope.edate){
                toaster.pop('info','结束日期不能早于开始日期');
                $scope.sdate='';
                $scope.edate='';
                return false;
            }
            sdate=$filter('date')($scope.sdate, "yyyy-MM-dd 00:00:00");
            edate=$filter('date')($scope.edate, "yyyy-MM-dd 23:59:59");
        }
        myhttp.getData('/index/setting/log','GET',
            {
                page:page,
                search:filter,
                edate:edate,
                sdate:sdate
            })
            .then(function (res) {
                // alert(res.data.total_count);
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
});