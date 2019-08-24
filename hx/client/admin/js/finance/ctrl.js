/**
 * Created by Administrator on 2017/5/25.
 */
app.controller('BankController',function ($scope,myhttp,$modal,toaster,$rootScope) {
    myhttp.getData('/index/finance/bank','GET')
            .then(function (res) { $scope.banks=res.data;});
    $scope.bankAdd=function (bank) {
        var myscope = $rootScope.$new();
        if(angular.isDefined(bank)) {
            myscope.bank={bank_name: bank.bank_name,bank_id:bank.bank_id};
        }else{
            myscope.bank={bank_name: '',bank_id:0};
        }
        var modalInstance = $modal.open({
            templateUrl: 'admin/finance/bankAdd.html',
            controller: 'bankAddController',
            scope:myscope,
            size:'sm'
        });
        modalInstance.result.then(function (bank) {
           myhttp.getData('/index/finance/bankAdd','POST',bank)
                  .then(function (res) {
                      switch(res.data.result){
                          case 1:
                              myhttp.getData('/index/finance/bank','GET')
                                  .then(function (res) { $scope.banks=res.data;});
                              break;
                          case 3:
                              toaster.pop('info','该银行名称已经存在，请核对！');
                              break;
                          default:
                              toaster.pop('error','添加银行失败，请刷新页面后重新添加！');
                      }
           })
        });
    };
    $scope.setWeight=function (bank) {
        myhttp.getData('/index/finance/bankUpdate','GET',{bank_id:bank.bank_id})
            .then(function (res) {
               if(res.data.result==1){
                   myhttp.getData('/index/finance/bank','GET')
                       .then(function (res) { $scope.banks=res.data;});
               }else {
                   toaster.pop('error','修改失败，请重新设置！');
               }
            });
    }
    $scope.search=function () {
      //  console.log($scope.search_context);
        if(angular.isUndefined($scope.search_context)) return false;
        var myscope = $rootScope.$new();
        myscope.search=$scope.search_context;
         $modal.open({
            templateUrl: 'admin/finance/bankDetailSearch.html',
            controller: 'bankDetailSearchController',
            scope:myscope,
             size:'lg'
        });
    }
});
app.controller('bankDetailSearchController',function ($scope,$modalInstance,myhttp) {
    $scope.query=function (page) {
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        myhttp.getData('/index/finance/bankDetailSearch','GET',{page:page,search:$scope.search})
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
    $scope.ok=function () {
        $modalInstance.close();
    };
});
app.controller('bankAddController',function ($scope,$modalInstance) {
    $scope.add=function () {
        $modalInstance.close($scope.bank);
    }
});
app.controller('BankDetailController',function ($scope,myhttp,$stateParams,$filter,toaster,$rootScope,$modal) {
    var sdate='';
    var edate='';
    $scope.bank_name=$stateParams.bank_name;
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
        myhttp.getData('/index/finance/bankDetail','GET',
            {
                page:page,
                search:filter,
                bank_id:$stateParams.bank_id,
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
    $scope.query($stateParams.page,$stateParams.search,$stateParams.bank_id);
    $scope.search=function () {
        $scope.query(1,$scope.search_context);
    };
    $scope.more=function (detail) {
        var data={'time':detail.time,'user':detail.user,'sum':0-detail.sum};
        myhttp.getData("/index/finance/getPaymentCFid",'GET',data).then(function (res) {
            //sconsole.log(res.data);
            if(res.data!=='null') {
                var myscope = $rootScope.$new();
                myscope.sale_id = res.data.sale_id;
                myscope.member = {member_id:res.data.member_id,member_name:detail.summary.substring(5)};
                var remark = $modal.open({
                    templateUrl: 'admin/sale/cfPrint.html',
                    controller: 'cfPrintController',
                    scope: myscope
                });
            }
        });

    }

});
app.controller('TransferController',function ($scope,myhttp,toaster,$state) {
    $scope.check=false;
    $scope.trans={outBank:{},inBank:{}};
    myhttp.getData('/index/finance/bank','GET')
        .then(function (res) {
            $scope.banks=res.data;
            $scope.trans.outBank=$scope.banks[0].bank_id;
            $scope.trans.inBank=$scope.banks[0].bank_id;
        });
    $scope.transfer=function () {
        $scope.check=true;
        myhttp.getData('/index/finance/transfer','POST',{trans:$scope.trans})
            .then(function(res){
                $scope.check=false;
                if(res.data.result==1){
                    toaster.pop('success','转账成功！');
                    $state.go('app.finance.bank');
                }else{
                    toaster.pop('error','转账失败，请联系管理员！');
                }
            });
    }
});
app.controller('SubjectController',function($scope,toaster,$rootScope,$modal,myhttp,$stateParams){
    $scope.query=function(page){
        if(!page) page=1;
        myhttp.getData('/index/finance/subject','GET',{page:page})
            .then(function(res){
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
    $scope.query($stateParams.page,$stateParams.search);
    $scope.subjectModel=function(subject){
        if(!subject) subject={subject_id:0,subject_type:0};
        var myscope = $rootScope.$new();
        myscope.subject=subject;
       // console.log(myscope.subject);
        var modalInstance = $modal.open({
            templateUrl: 'admin/finance/subjectUpdate.html',
            controller: 'SubjectUpdateController',
            size:'sm',
            scope:myscope
        });
        modalInstance.result.then(function(res){
            if(res==1){
                toaster.pop('success','添加修改成功！');
            }else {
                toaster.pop('error','添加修改失败，请联系管理员！');
            }
            $scope.query();
        });
    }
});
app.controller('SubjectUpdateController',function($scope,myhttp,$modalInstance){
    $scope.check=false;
    $scope.save=function() {
        $scope.check=true;
        myhttp.getData('/index/finance/subjectUpdate', 'POST', $scope.subject)
            .then(function (res) {
                $check=false;
                $modalInstance.close(res.data.result);
            });
    }
    $scope.cancel=function(){
        $modalInstance.dismiss('cancel');
    }
});
app.controller('IncomeController',function($scope,myhttp,$modal,$rootScope,toaster,$stateParams){
    $scope.query=function(page,filter){
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        if(!filter) filter='';
        myhttp.getData('/index/finance/income','GET',{page:page,search:filter})
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
    }
    $scope.query($stateParams.page,$stateParams.search);
    $scope.search=function () {
       // alert();
        $scope.query(1,$scope.search_context);
    };
    $scope.incomeModel=function(account){
        if(!account) account={account_id:0}; //console.log(account);
        var myscope = $rootScope.$new();
        myscope.account=account;
        var modalInstance = $modal.open({
            templateUrl: 'admin/finance/incomeUpdate.html',
            controller: 'IncomeUpdateController',
            //size:'sm',
            scope:myscope
        });
        modalInstance.result.then(function(res){
            if(res==1){
                toaster.pop('success','添加修改成功！');
            }else {
                toaster.pop('error','添加修改失败，请联系管理员！');
            }
            $scope.query();
        });
    }
    $scope.delete=function(data){
        var summary=data.summary.split('-');
        var myscope = $rootScope.$new();
        myscope.info=summary[1]+'的收入条目';
        var modalInstance = $modal.open({
            templateUrl: 'admin/confirm.html',
            controller: 'ConfirmController',
            scope:myscope
        });
        modalInstance.result.then(function () {
            myhttp.getData('/index/finance/deleteIncome','POST',{account_id:data.account_id,income:data.income,creditTable:data.creditTable,summary:data.summary,sale_detail_id:data.sale_detail_id})
                .then(function (res) {
                    if(res.data.result==1){
                        toaster.pop('success','删除成功！');
                        $scope.query(1,$scope.search_context);
                    }else
                        toaster.pop('error','删除失败！');
                });
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
app.controller('IncomeUpdateController',function($scope,myhttp,$modalInstance,toaster){
    $scope.check=false;
    $scope.account=$.extend($scope.account,{people:[]});
    if($scope.account.account_id!=0){
        var summary=$scope.account.summary.split('-');
        $scope.name=summary[1];
    }
    myhttp.getData('/index/finance/bankAndSubject','GET',{subject_type:0})
        .then(function (res) {
            $scope.banks=res.data.banks;
            $scope.subjects=res.data.subjects;
            $scope.account=$.extend($scope.account,{subject:$scope.subjects[0].subject_id,bank_id:0});
        });
    $scope.save=function() {
        if ($scope.account.account_id==0) {     //新增income
            if ($scope.account.bank_id == 0 && $scope.account.people.length == 0) {
                toaster.pop('info', '请输入联系人，或收款银行！');
                return false;
            }
            $scope.check=true;
            myhttp.getData('/index/finance/incomeAdd', 'POST', $scope.account)
                .then(function (res) {
                    $scope.check=false;
                    $modalInstance.close(res.data.result);
                });
        }else{                          //update income
            myhttp.getData('/index/finance/incomeUpdate', 'POST',{account_id:$scope.account.account_id,income:$scope.account.income,summary:$scope.account.summary,subjecct:$scope.account.subjecct})
                .then(function (res) {
                    $scope.check=false;
                    $modalInstance.close(res.data.result);
                });
       }

    }
    $scope.cancel=function(){
        $modalInstance.dismiss('cancel');
    }
    $scope.query_people=function(people){
        if(people!='')
            myhttp.getData('/index/finance/peopleSearch', 'GET', {search: people})
                .then(function (res) {
                    $scope.peoples = res.data;
                });
    }
});
//cost
app.controller('CostController',function($scope,myhttp,$modal,$rootScope,toaster,$stateParams){
    $scope.query=function(page,filter){
        if(!page){
            page=1;
        }else{
            page=parseInt(page);
        }
        if(!filter) filter='';
        myhttp.getData('/index/finance/cost','GET',{page:page,search:filter})
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
    }
    $scope.query($stateParams.page,$stateParams.search);
    $scope.search=function () {
        // alert();
        $scope.query(1,$scope.search_context);
    };
    $scope.costModel=function(account){
        if(!account) account={account_id:0};
        var myscope = $rootScope.$new();
        myscope.account=account;
        var modalInstance = $modal.open({
            templateUrl: 'admin/finance/costUpdate.html',
            controller: 'CostUpdateController',
            //size:'sm',
            scope:myscope
        });
        modalInstance.result.then(function(res){
            if(res===1){
                toaster.pop('success','添加修改成功！');
            }else {
                toaster.pop('error','添加修改失败，请联系管理员！');
            }
            $scope.query();
        });
    }
    $scope.delete=function(data){
        var summary=data.summary.split(':');
        var myscope = $rootScope.$new();
        myscope.info=summary[1]+'的支出条目';
        var modalInstance = $modal.open({
            templateUrl: 'admin/confirm.html',
            controller: 'ConfirmController',
            scope:myscope
        });
        modalInstance.result.then(function () {
            myhttp.getData('/index/finance/deletecost','POST',{account_id:data.account_id,cost:data.cost,creditTable:data.creditTable,summary:data.summary,sale_detail_id:data.sale_detail_id})
                .then(function (res) {
                    if(res.data.result==1){
                        toaster.pop('success','删除成功！');
                        $scope.query(1,$scope.search_context);
                    }else
                        toaster.pop('error','删除失败！');
                });
        });
    }
});
app.controller('CostUpdateController',function($scope,myhttp,$modalInstance,toaster){
    $scope.check=false;
    $scope.account=$.extend($scope.account,{people:[]});
    if($scope.account.account_id!=0){
        var summary=$scope.account.summary.split(':');
        $scope.name=summary[1];
    }
    myhttp.getData('/index/finance/bankAndSubject','GET',{subject_type:1})
        .then(function (res) {
            $scope.banks=res.data.banks;
            $scope.subjects=res.data.subjects;
            $scope.account=$.extend($scope.account,{subject:$scope.subjects[0].subject_id,bank_id:0});
        });
    $scope.save=function() {

       // console.log($scope.account.people);
        if ($scope.account.account_id==0) {     //新增cost
            if ($scope.account.bank_id == 0 && $scope.account.people.length == 0) {
                toaster.pop('info', '请输入联系人，或收款银行！');
                return false;
            }
            $scope.check=true;
            myhttp.getData('/index/finance/costAdd', 'POST', $scope.account)
                .then(function (res) {
                    $scope.check=false;
                    $modalInstance.close(res.data.result);
                });
        }else{                          //update cost
            myhttp.getData('/index/finance/costUpdate', 'POST',{account_id:$scope.account.account_id,cost:$scope.account.cost,summary:$scope.account.summary,subjecct:$scope.account.subjecct})
                .then(function (res) {
                    $scope.check=false;
                    $modalInstance.close(res.data.result);
                });
        }

    }
    $scope.cancel=function(){
        $modalInstance.dismiss('cancel');
    }
    $scope.query_people=function(people){
        if(people!='')
            myhttp.getData('/index/finance/peopleSearch', 'GET', {search: people})
                .then(function (res) {
                    $scope.peoples = res.data;
                });
    }
});
app.controller('staffCreditController',function ($scope,myhttp,toaster,$modal,$stateParams,$rootScope) {
    $scope.query_ttl=function () {
        myhttp.getData('/index/finance/ttlStaffCredit','GET')
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
        myhttp.getData('/index/finance/staffCredit','GET',{page:page})
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
        myscope.staff=list;
        var modalInstance = $modal.open({
            templateUrl: 'admin/finance/staffCreditDetail.html',
            controller: 'staffCreditDetailController',
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
    $scope.addPrePaid=function () {
        var modalInstance = $modal.open({
            templateUrl: 'admin/finance/prePaid.html',
            controller: 'prePaidController',
            size:'sm'
        });
        modalInstance.result.then(function (res) {
            $scope.query_credit($stateParams.page);
            $scope.query_ttl();
            if(res===1){
                toaster.pop('success','保存成功!');
                $scope.query_credit($stateParams.page);
            } else{
                toaster.pop('error','保存失败，请联系管理员！');
            }
        });
    }
});
app.controller('staffCreditDetailController',function ($scope,myhttp,$modalInstance) {
    $scope.pay={bank:{}};
    $scope.check=false;
    myhttp.getData('/index/finance/staffCreditDetail','GET',{staff_id:$scope.staff.staff_id})
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
        angular.forEach($scope.credits,function(item) {
            item.selected = selected;
            if(!selected){$scope.pay.paid_sum=0;return false; }
                sum += item.sum;
        });$scope.pay.paid_sum = sum;
    };
    $scope.cal=function () {
        var sum=0;
        angular.forEach($scope.credits,function(item){
            if(item.selected) sum += item.sum;
        });
        $scope.pay.paid_sum=sum;
    };
    $scope.clean=function () {
        angular.forEach($scope.credits,function(item){
            item.selected=false;
        });
    };
    $scope.payment=function () {
        var creditID=[];
        angular.forEach($scope.credits,function(item){
            if(item.selected)creditID.push(item.staff_credit_id);
        });
        $scope.check=true;
        if($scope.pay.paid_sum){
            myhttp.getData("/index/finance/payment",'POST',{pay:$scope.pay,staff_credit_id:creditID,staff:$scope.staff})
                .then(function (res) {
                    $scope.check=false;
                    $modalInstance.close(res.data.result);
                })

        }
    };
});
app.controller('prePaidController',function ($scope,myhttp,$modalInstance) {
    $scope.data={staff:[],bank_id:0};
     $scope.query_staff=function (search) {
         if(search==='') return false;
         myhttp.getData('/index/setting/staffSearch','GET',{search:search,page:1})
             .then(function (res) {
                 $scope.staffs=res.data.staff;
             });
     }
    myhttp.getData('/index/finance/bank','GET')
        .then(function (res) {
            $scope.banks=res.data;
            $scope.data.bank_id=res.data[0].bank_id;
        });
    $scope.cancel=function () {
        $modalInstance.dismiss('cancel');
    };
    $scope.prePaid=function () {
        myhttp.getData('/index/finance/prePaid','POST',$scope.data)
            .then(function (res) {
                $modalInstance.close(res.data.result);
            });
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