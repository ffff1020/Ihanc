'use strict';
app
  .run(
      function ($rootScope, $state, $stateParams,$localStorage,myhttp) {
          $rootScope.$state = $state;
          $rootScope.$stateParams = $stateParams;        
          $rootScope.$on('$stateChangeSuccess', function(event, to, toParams, from, fromParams) {
            $rootScope.previousState = from;
            $rootScope.previousStateParams = fromParams;
              if ($localStorage.auth === undefined) $state.go('auth.login');
              if($localStorage.auth===null) $state.go('auth.login');
              if(from.url!=='/login'){
              myhttp.getData('/index/index','GET').then(function (data) {
                  if(data.data.error===0) $state.go('auth.login');
              });
              }
          });
          $rootScope.$on('$stateChangeStart',
              function(event, toState, toParams, fromState, fromParams) {
                  //console.log(toState);
                  if(fromState.url==='/sale'&&toState.url!=='/login'){
                      event.preventDefault();
                      var url = $state.href(toState.name);
                      window.open(url,'_blank');
                  }
                  if (angular.isDefined(toState.eva)&&$localStorage.role===false&&angular.isUndefined($localStorage.role_auth[toState.eva])) {
                      event.preventDefault();
                  }
              });
      }
  )
.config(
      function ($stateProvider,   $urlRouterProvider) {
          $urlRouterProvider
              .otherwise('/auth/loading');
          $stateProvider
              .state('auth',{
                  abstract: true,
                  url:'/auth',
                  template: '<div ui-view class="fade-in"></div>',
                  resolve: {
                      deps: ['$ocLazyLoad',
                        function( $ocLazyLoad ){
                          return $ocLazyLoad.load('admin/auth/ctrl.js');
                      }]
                  }
              })
              .state('auth.loading',{
                  url:'/loading',
                  templateUrl:'admin/auth/loading.html'
              })
              .state('auth.login',{
                  url:'/login',
                  templateUrl:'admin/auth/login.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('admin/js/auth/jsbn.js').then(
                                  function(){
                                      return $ocLazyLoad.load('admin/js/auth/rsa.js');
                                  }
                              );
                          }]
                  }
              })
              .state('app', {
                  abstract: true,
                  url: '/app',
                  templateUrl: 'admin/app.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad){
                              return $ocLazyLoad.load('toaster').then(
                                  function () {
                                      return $ocLazyLoad.load('ui.select');
                                  }
                              );
                          }]
                  }
              })
              .state('app.dashboard', {
                  url: '/dashboard',
                  templateUrl: 'admin/dashboard.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('admin/js/dashboard.js').then(
                                  function () {
                                      return $ocLazyLoad.load('admin/js/sale/ctrl.js')
                                  }
                              );
                          }]
                  },
                  ncyBreadcrumb: {
                    label: '<i class="fa fa-home"></i> 首页'
                  }
              })

              //系统设置
              .state('app.setting', {
                  abstract: true,
                  url: '/setting',
                  template: '<div ui-view class="fade-in"></div>',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad){
                              return $ocLazyLoad.load('js/py.js').then(
                                  function () {
                                      return $ocLazyLoad.load('admin/js/setting/ctrl.js');
                              });
                          }]
                  }
              })
              .state('app.setting.info', {
                  url: '/info',
                  eva:'info',
                  templateUrl: 'admin/setting/info.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '系统设置'
                  }
              })
              .state('app.setting.data', {
                  url: '/data',
                  eva:'info',
                  templateUrl: 'admin/setting/data.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '初期数据录入'
                  },
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                            //  return $ocLazyLoad.load('admin/js/statistics/ctrl.js').then(function () {
                                  return $ocLazyLoad.load('ngGrid');
                            //  });
                          }]
                  }
              })
              .state('app.setting.pwd', {
                  url: '/pwd',
                  eva:'pwdUpdate',
                  templateUrl: 'admin/setting/pwd.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('admin/js/auth/jsbn.js').then(
                                  function(){
                                      return $ocLazyLoad.load('admin/js/auth/rsa.js');
                                  }
                              );
                          }]
                  },
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '修改密码'
                  }
              })
              .state('app.setting.store', {
                  url: '/store',
                  eva:'store',
                  templateUrl: 'admin/setting/store.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '仓库设置'
                  }
              })
              .state('app.setting.staff', {
                  url: '/staff',
                  eva:'staff',
                  templateUrl: 'admin/setting/staff.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '员工管理'
                  }
              })
              .state('app.setting.log', {
                  url: '/log',
                 // eva:'staff',
                  templateUrl: 'admin/setting/log.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '系统日志'
                  }
              })
              .state('app.setting.auth', {
                  url: '/auth',
                  eva:'auth',
                  templateUrl: 'admin/setting/auth.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '权限设置'
                  },
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('angularBootstrapNavTree').then(
                                  function(){
                                      return $ocLazyLoad.load('js/controllers/tree.js');
                                  }
                              );
                          }
                      ]
                  }
              })
              .state('apps', {
                  abstract: true,
                  url: '/apps',
                  templateUrl: 'tpl/layout.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad){
                              return $ocLazyLoad.load('toaster').then(function () {
                                  return $ocLazyLoad.load('ui.select').then(function () {
                                      return $ocLazyLoad.load('admin/js/setting/ctrl.js').then(function () {
                                          return $ocLazyLoad.load('js/py.js');
                                      });
                                  });
                              });
                          }]
                  }
              })
              .state('app.setting.unit', {
                  url: '/unit',
                  eva:'unit',
                  templateUrl: 'admin/setting/unit.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '单位设置'
                  }
              })
              .state('apps.goods',{
                  url:'/goods',
                  eva:'goods',
                  templateUrl:'admin/setting/goods.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '种类产品设置'
                  }
                  }

              )
              //进货管理
              .state('app.purchase', {
              abstract: true,
              url: '/purchase',
              template: '<div ui-view class="fade-in"></div>',
              resolve: {
                  deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                          return $ocLazyLoad.load('admin/js/purchase/ctrl.js');
                      }]
                  }
              })
              .state('app.purchase.supply',{
                  url:'/supply',
                  eva:'supply',
                  templateUrl:'admin/purchase/supply.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('js/py.js');
                          }]
                  },
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '供应商管理'
                  }
              })
              .state('app.purchase.purchase',{
                  url:'/purchase',
                  eva:'purchase',
                  templateUrl:'admin/purchase/purchase.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                             return $ocLazyLoad.load('js/py.js');
                      }]
              },
              ncyBreadcrumb: {
                  parent:'app.dashboard',
                  label: '新进货单'
                 }
              })
              .state('app.purchase.list',{
                  url:'/list?page&search&sdate&edate',
                  eva:'purchase_list',
                  templateUrl:'admin/purchase/list.html',
                  ncyBreadcrumb: {
                      parent:'app.purchase.purchase',
                      label: '进货列表'
                  }
              })
              .state('app.purchase.detail',{
                  url:'/list/detail/{purchase_id}/{supply_name}/{finish}',
                  templateUrl:'admin/purchase/detail.html',
                  ncyBreadcrumb: {
                      parent:'app.purchase.list',
                      label: '进货明细'
                  }
              })
              .state('app.purchase.scredit',{
                  url:'/scredit',
                  eva:'scredit',
                  templateUrl:'admin/purchase/scredit.html',
                  ncyBreadcrumb: {
                      parent:'app.purchase.purchase',
                      label: '应付管理'
                  }
              })
              //销售管理
              .state('app.sale', {
                  abstract: true,
                  url: '/sale',
                  template: '<div ui-view class="fade-in"></div>',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('admin/js/sale/ctrl.js');
                          }]
                  }
              })
              .state('app.sale.member',{
                  url:'/member',
                  eva:'member',
                  templateUrl:'admin/sale/member.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('js/py.js');
                          }]
                  },
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '客户管理'
                  }
              })
              .state('app.sale.sale',{
                  url:'/sale',
                  eva:'sale',
                  templateUrl:'admin/sale/sale.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('js/py.js');
                          }]
                  },
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '新销售单'
                  }
              })
              .state('app.sale.list',{
                  url:'/list?page&search&sdate&edate',
                  eva:'sale_list',
                  templateUrl:'admin/sale/list.html',
                  ncyBreadcrumb: {
                      parent:'app.sale.sale',
                      label: '销售列表'
                  }
              })
              .state('app.sale.detail',{
                  url:'/list/detail/{sale_id}/{member_name}/{finish}',
                  templateUrl:'admin/sale/detail.html',
                  ncyBreadcrumb: {
                      parent:'app.sale.list',
                      label: '销售明细'
                  }
              })
              .state('app.sale.mcredit',{
                  url:'/mcredit?page&search&order',
                  eva:'mcredit',
                  templateUrl:'admin/sale/mcredit.html',
                  ncyBreadcrumb: {
                      parent:'app.sale.sale',
                      label: '应收管理'
                  }
              })
              //打印
              .state('print',{
                  abstract: true,
                  url:'/print',
                  template: '<div ui-view class="fade-in"></div>',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('admin/js/print.js');
                          }]
                  }
              })
              .state('print.saleCredit',{
                  url:'/saleCredit/{member_id}/{member_name}/{sale_id}',
                  templateUrl:'admin/sale/printCredit.html',
                  controller:'printSaleCreditController'
              })
              .state('print.sale',{
                  url:'/sale/{sale_id}/{member_name}/{time}/{member_id}',
                  templateUrl:'admin/sale/printSale.html',
                  controller:'printSaleController'
              })
              .state('print.purchaseCredit',{
                  url:'/purchaseCredit/{supply_id}/{supply_name}/{purchase_id}',
                  templateUrl:'admin/purchase/printCredit.html',
                  controller:'printPurchaseCreditController'
              })
              .state('print.printAll',{
                  url:'/printAll',
                  templateUrl:'admin/sale/printAll.html',
                  controller:'printAllController'
              })
               //财务管理
              .state('app.stock', {
                  abstract: true,
                  url: '/stock',
                  template: '<div ui-view class="fade-in"></div>',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('admin/js/stock/ctrl.js');
                          }]
                  }
              })
              .state('app.stock.list',{
                  url:'/list',
                  eva:'stock',
                  templateUrl:'admin/stock/list.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '库存管理'
                  }
              })
              .state('app.finance', {
                  abstract: true,
                  url: '/finance',
                  template: '<div ui-view class="fade-in"></div>',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load('admin/js/finance/ctrl.js');
                          }]
                  }
              })
              .state('app.finance.bank',{
                  url:'/bank',
                  eva:'bank',
                  templateUrl:'admin/finance/bank.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '银行管理'
                  }
              })
              .state('app.finance.transfer',{
                  url:'/transfer',
                  eva:'bank_transfer',
                  templateUrl:'admin/finance/transfer.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '银行间转账'
                  }
              })
              .state('app.finance.subject',{
                  url:'/subject',
                  eva:'subject',
                  templateUrl:'admin/finance/subject.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '会计科目'
                  }
              })
              .state('app.finance.income',{
                  url:'/income',
                  eva:'income',
                  templateUrl:'admin/finance/income.html',
                  ncyBreadcrumb: {
                      parent:'app.dashboard',
                      label: '收入管理'
                  }
              })
              .state('app.finance.fee',{
                url:'/fee',
                  eva:'cost',
                templateUrl:'admin/finance/fee.html',
                ncyBreadcrumb: {
                     parent:'app.dashboard',
                     label: '支出管理'
                 }
              })
              .state('app.finance.bdetail',{
                  url:'/bdetail/{bank_id}/{bank_name}',
                  templateUrl:'admin/finance/bdetail.html',
                  ncyBreadcrumb: {
                      parent:'app.finance.bank',
                      label: '银行明细'
                  }
              })
              .state('app.finance.staffCredit',{
                  url:'/staffCredit',
                  eva:'cost',
                  templateUrl:'admin/finance/staffCredit.html',
                  ncyBreadcrumb:{
                      parent:'app.finance.bank',
                      label:'员工工资管理'
                  }
              })
              .state('app.statistics', {
              abstract: true,
              url: '/statistics',
              template: '<div ui-view class="fade-in"></div>',
              resolve: {
                  deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                          return $ocLazyLoad.load('admin/js/statistics/ctrl.js').then(function () {
                              return $ocLazyLoad.load('ngGrid');
                          });
                      }]
              }
          })
              .state('app.statistics.sale',{
              url:'/statistics/sale',
              eva:'statistics_sale',
              templateUrl:'admin/statistics/statistics_sale.html',
              ncyBreadcrumb:{
                  parent:'app.dashboard',
                  label:'销售统计'
              }
          })
              .state('app.statistics.purchase',{
                  url:'/statistics/purchase',
                  eva:'statistics_purchase',
                  templateUrl:'admin/statistics/statistics_purchase.html',
                  ncyBreadcrumb:{
                      parent:'app.dashboard',
                      label:'进货统计'
                  }
              })
              .state('app.statistics.profit',{
                  url:'/statistics/profit',
                  eva:'statistics_profit',
                  templateUrl:'admin/statistics/statistics_profit.html',
                  ncyBreadcrumb:{
                      parent:'app.dashboard',
                      label:'毛利润统计'
                  }
              })
              .state('app.statistics.order',{
                  url:'/statistics/order',
                  eva:'statistics_order',
                  templateUrl:'admin/statistics/statistics_order.html',
                  ncyBreadcrumb:{
                      parent:'app.dashboard',
                      label:'批次营业统计'
                  }
              })
              .state('app.statistics.income',{
                  url:'/statistics/income',
                  eva:'statistics_income',
                  templateUrl:'admin/statistics/statistics_income.html',
                  ncyBreadcrumb:{
                      parent:'app.dashboard',
                      label:'收入支出统计'
                  }
              })
              .state('app.statistics.asset',{
                  url:'/asset',
                  eva:'statistics_income',
                  templateUrl:'admin/statistics/statistics_asset.html',
                  ncyBreadcrumb:{
                      parent:'app.dashboard',
                      label:'总资产统计'
                  }
              })

		}
  );

app.config(function ($httpProvider) {
  $httpProvider.interceptors.push('AuthInterceptor');
});
app.factory('AuthInterceptor', function ($rootScope, $q,$location) {
  return {
    responseError: function (response) {
        if(response.status===401)
        {
            $location.url('/auth/login');
        }
      return $q.reject(response);
    }
  };
});