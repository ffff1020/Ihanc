'use strict';

/* Controllers */

  // bootstrap controller
  app.controller('AccordionDemoCtrl', ['$scope', function($scope) {
    $scope.oneAtATime = true;

    $scope.groups = [
      {
        title: 'Accordion group header - #1',
        content: 'Dynamic group body - #1'
      },
      {
        title: 'Accordion group header - #2',
        content: 'Dynamic group body - #2'
      }
    ];

    $scope.items = ['Item 1', 'Item 2', 'Item 3'];

    $scope.addItem = function() {
      var newItemNo = $scope.items.length + 1;
      $scope.items.push('Item ' + newItemNo);
    };

    $scope.status = {
      isFirstOpen: true,
      isFirstDisabled: false
    };
  }])
  ; 
  app.controller('AlertDemoCtrl', ['$scope', function($scope) {
    $scope.alerts = [
      { type: 'success', msg: 'Well done! You successfully read this important alert message.' },
      { type: 'info', msg: 'Heads up! This alert needs your attention, but it is not super important.' },
      { type: 'warning', msg: 'Warning! Best check yo self, you are not looking too good...' }
    ];

    $scope.addAlert = function() {
      $scope.alerts.push({type: 'danger', msg: 'Oh snap! Change a few things up and try submitting again.'});
    };

    $scope.closeAlert = function(index) {
      $scope.alerts.splice(index, 1);
    };
  }])
  ; 
  app.controller('ButtonsDemoCtrl', ['$scope', function($scope) {
    $scope.singleModel = 1;

    $scope.radioModel = 'Middle';

    $scope.checkModel = {
      left: false,
      middle: true,
      right: false
    };
  }])
  ; 
  app.controller('CarouselDemoCtrl', ['$scope', function($scope) {
    $scope.myInterval = 5000;
    var slides = $scope.slides = [];
    $scope.addSlide = function() {
      slides.push({
        image: 'img/c' + slides.length + '.jpg',
        text: ['Carousel text #0','Carousel text #1','Carousel text #2','Carousel text #3'][slides.length % 4]
      });
    };
    for (var i=0; i<4; i++) {
      $scope.addSlide();
    }
  }])
  ; 
  app.controller('DropdownDemoCtrl', ['$scope', function($scope) {
    $scope.items = [
      'The first choice!',
      'And another choice for you.',
      'but wait! A third!'
    ];

    $scope.status = {
      isopen: false
    };

    $scope.toggled = function(open) {
      //console.log('Dropdown is now: ', open);
    };

    $scope.toggleDropdown = function($event) {
      $event.preventDefault();
      $event.stopPropagation();
      $scope.status.isopen = !$scope.status.isopen;
    };
  }])
  ; 
  app.controller('ModalInstanceCtrl', ['$scope', '$modalInstance', 'items', function($scope, $modalInstance, items) {
    $scope.items = items;
    $scope.selected = {
      item: $scope.items[0]
    };

    $scope.ok = function () {
      $modalInstance.close($scope.selected.item);
    };

    $scope.cancel = function () {
      $modalInstance.dismiss('cancel');
    };
  }])
  ; 
  app.controller('ModalDemoCtrl', ['$scope', '$modal', '$log', function($scope, $modal, $log) {
    $scope.items = ['item1', 'item2', 'item3'];
    $scope.open = function (size) {
      var modalInstance = $modal.open({
        templateUrl: 'myModalContent.html',
        controller: 'ModalInstanceCtrl',
        size: size,
        resolve: {
          items: function () {
            return $scope.items;
          }
        }
      });

      modalInstance.result.then(function (selectedItem) {
        $scope.selected = selectedItem;
      }, function () {
        $log.info('Modal dismissed at: ' + new Date());
      });
    };
  }])
  ; 
  app.controller('PaginationDemoCtrl', ['$scope', '$log', function($scope, $log) {
    $scope.totalItems = 64;
    $scope.currentPage = 4;

    $scope.setPage = function (pageNo) {
      $scope.currentPage = pageNo;
    };

    $scope.pageChanged = function() {
      $log.info('Page changed to: ' + $scope.currentPage);
    };

    $scope.maxSize = 5;
    $scope.bigTotalItems = 175;
    $scope.bigCurrentPage = 1;
  }])
  ; 
  app.controller('PopoverDemoCtrl', ['$scope', function($scope) {
    $scope.dynamicPopover = 'Hello, World!';
    $scope.dynamicPopoverTitle = 'Title';
  }])
  ; 
  app.controller('ProgressDemoCtrl', ['$scope', function($scope) {
    $scope.max = 200;

    $scope.random = function() {
      var value = Math.floor((Math.random() * 100) + 1);
      var type;

      if (value < 25) {
        type = 'success';
      } else if (value < 50) {
        type = 'info';
      } else if (value < 75) {
        type = 'warning';
      } else {
        type = 'danger';
      }

      $scope.showWarning = (type === 'danger' || type === 'warning');

      $scope.dynamic = value;
      $scope.type = type;
    };
    $scope.random();

    $scope.randomStacked = function() {
      $scope.stacked = [];
      var types = ['success', 'info', 'warning', 'danger'];

      for (var i = 0, n = Math.floor((Math.random() * 4) + 1); i < n; i++) {
          var index = Math.floor((Math.random() * 4));
          $scope.stacked.push({
            value: Math.floor((Math.random() * 30) + 1),
            type: types[index]
          });
      }
    };
    $scope.randomStacked();
  }])
  ; 
  app.controller('TabsDemoCtrl', ['$scope', function($scope) {
    $scope.tabs = [
      { title:'Dynamic Title 1', content:'Dynamic content 1' },
      { title:'Dynamic Title 2', content:'Dynamic content 2', disabled: true }
    ];
  }])
  ; 
  app.controller('RatingDemoCtrl', ['$scope', function($scope) {
    $scope.rate = 7;
    $scope.max = 10;
    $scope.isReadonly = false;

    $scope.hoveringOver = function(value) {
      $scope.overStar = value;
      $scope.percent = 100 * (value / $scope.max);
    };
  }])
  ; 
  app.controller('TooltipDemoCtrl', ['$scope', function($scope) {
    $scope.dynamicTooltip = 'Hello, World!';
    $scope.dynamicTooltipText = 'dynamic';
    $scope.htmlTooltip = 'I\'ve been made <b>bold</b>!';
  }])
  ;
  app.controller('GoEasyCtrl',['$scope','$localStorage','toaster',function ($scope,$localStorage,toaster) {
    $scope.newOrder=false;
    $scope.notices=$localStorage.notice;
    $scope.chat={
      audio:"/ihanc/hx/order.mp3",
      id:"order"
    };
    $scope.push={
      audio:"/ihanc/hx/push.mp3",
      id:"push"
    };
    if($localStorage.channel!==null){
      //$scope.goEasy.unsubscribe({channel:$localStorage.channel});
      $scope.goEasy.subscribe({
        channel: $localStorage.channel,
        onMessage: function (message) {
          if(angular.isUndefined($localStorage.notice)) $localStorage.notice=[];
          $scope.notices=$localStorage.notice;
          if(message.content.indexOf("新订单")>-1){
              $scope.newOrder=true;
              $localStorage.notice.push(message.content);
             // document.addEventListener("mousemove",function (evt) {
                //document.getElementById("order").muted=false;
                console.log("play");
                document.getElementById("order").play();
              //});

          }else if(message.content.indexOf("goEasyChannel:")>-1){
             console.log("clear");
             var i=message.content.split(":");
             $scope.notices=$localStorage.notice;
             if($scope.notices.length===0 && $scope.newOrder) $scope.newOrder=false;
          }
          else{
            $localStorage.notice.push(message.content);
            toaster.pop('success', message.content);
            console.log("play");
            document.getElementById("push").play();
          }
        }
      });
    }
    $scope.clear=function () {
      $scope.newOrder=false;
      if (angular.isDefined($localStorage.notice)){$localStorage.notice=[];$scope.notices=[];$scope.newOrder=false;}
    };
    $scope.removeNotice=function (index) {
      $localStorage.notice.splice(index,1);
      $scope.goEasy.publish({
        channel: $localStorage.channel,
        message: "goEasyChannel:"+index
      });
    };

  }]);
  app.controller('TypeaheadDemoCtrl', ['$scope', '$http','$interval', function($scope, $http, $interval) {
    $scope.weight=888.8;
    $scope.on=false;
    $scope.link=function () {
        if($scope.on){
          $scope.on=false;$scope.weight=888.8;
          $interval.cancel($scope.timer);
        }else{
             $scope.on=true;
             $scope.timer = $interval(function(){
                $.getJSON("http://127.0.0.1:8080", function(data){
                    $scope.weight=(parseInt(data.weight)/100).toFixed(2);
                });
            },1000);
        }

    }
    app.controller('roleAuthController',function ($localStorage,$scope,$timeout) {
        $timeout(function () {
            $scope.$apply(function () {
                $scope.role=$localStorage.role;
                $scope.auth=$localStorage.role_auth;
            });
        });
    })

   /* $scope.getLocation = function(val) {
      return $http.get('http://maps.googleapis.com/maps/api/geocode/json', {
        params: {
          address: val,
          sensor: false
        }
      }).then(function(res){
        var addresses = [];
        angular.forEach(res.data.results, function(item){
          addresses.push(item.formatted_address);
        });
        return addresses;
      });
    };*/
  }])
  ; 
  app.controller('DatepickerDemoCtrl', ['$scope', function($scope) {
    $scope.today = function() {
      $scope.dt = new Date();
    };
   // $scope.today();

    $scope.clear = function () {
      $scope.dt = null;
    };

    // Disable weekend selection
    $scope.disabled = function(date, mode) {
      return ( mode === 'day' && ( date.getDay() === 0 || date.getDay() === 6 ) );
    };

    $scope.toggleMin = function() {
      $scope.minDate = $scope.minDate ? null : new Date();
    };
    $scope.toggleMin();

    $scope.open = function($event) {
      $event.preventDefault();
      $event.stopPropagation();
      $scope.opened = true;

    };

    $scope.dateOptions = {
      formatYear: 'yy',
      startingDay: 1,
      class: 'datepicker',
        showWeeks:true
    };

    $scope.initDate = new Date('2016-15-20');
    $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
    $scope.format = $scope.formats[0];
  }])
  ; 
  app.controller('TimepickerDemoCtrl', ['$scope', function($scope) {
    $scope.mytime = new Date();
    $scope.hstep = 1;
    $scope.mstep = 15;

    $scope.options = {
      hstep: [1, 2, 3],
      mstep: [1, 5, 10, 15, 25, 30]
    };

    $scope.ismeridian = true;
    $scope.toggleMode = function() {
      $scope.ismeridian = ! $scope.ismeridian;
    };

    $scope.update = function() {
      var d = new Date();
      d.setHours( 14 );
      d.setMinutes( 0 );
      $scope.mytime = d;
    };

    $scope.changed = function () {
      //console.log('Time changed to: ' + $scope.mytime);
    };

    $scope.clear = function() {
      $scope.mytime = null;
    };
  }]);
  app.controller('OrderController',function ($scope,myhttp,$location,$state,$rootScope,$modal) {
      $scope.$on('openFolder',function () {
          console.log("openFolder");
          $scope.show=false;
          myhttp.getData('/index/sale/orderList','GET').then(function (res) {
              $scope.lists=res.data;
              $scope.show=true;
          });
      });
      $scope.loadOrder=function (list) {
          angular.element('#setting').removeClass("active");
          if($location.url()!=='/app/sale/sale') $state.go('app.sale.sale');
          $scope.$emit('loadOrderList',list);
      };
      $scope.delOrder=function (list) {
          var myscope = $rootScope.$new();
          myscope.info=list.member_name+'的订单';
          var modalInstance = $modal.open({
              templateUrl: 'admin/confirm.html',
              controller: 'ConfirmController',
               size:'sm',
              scope:myscope
          });
          modalInstance.result.then(function () {
             myhttp.getData('/index/sale/deleteOrder','POST',{sale_id:list.sale_id,info:'客户订单：'+list.member_name})
                  .then(function (res) {
                      $scope.lists=res.data;
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