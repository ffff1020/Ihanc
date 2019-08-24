app.controller('LoadingController',function($scope,$resource,$state,$localStorage,$http){
    $state.go('auth.login');
    /*// alert();
    if (typeof($localStorage.auth) == "undefined") $state.go('auth.login');
    if($localStorage.auth==null) $state.go('auth.login');
    $http.defaults.headers.common['Authorization'] =$localStorage.auth;
    var $com = $resource($scope.app.host + "/index/index/index");
    $com.get(function(data){
	//	$scope.session_user = $localStorage.user = data;
        //  if(data.error==0) {
            $state.go('auth.login');
       //   }else {
       //     $state.go('app.dashboard');
      //  }
    },function(){
        $state.go('auth.login');
     }); */
});
app.controller('LoginController',function($scope,$state,$http,$resource,Base64,$localStorage,myhttp,$interval){
    $scope.codeInfo=null;
   // $scope.user={username:''};
    var i=0;
    $scope.getCode=function () {
        if(angular.isDefined($scope.user)&&i<=0)
        myhttp.getData('/index/index/getCode','POST',{data:$scope.user.username})
            .then(function (res) {
                if(res.data==='error'){
                    $scope.codeInfo="用户名输入错误!";
                    return false;
                }
                if(res.data.result.Message ==='OK'){
                    $scope.codeInfo="已将验证码发送到尾号为"+res.data.tel+"手机！";
                    i=60;
                    $scope.disabled='disabled';
                    $scope.updateTime();
                }else{
                    $scope.codeInfo='';
                    $scope.authError=res.data.result.Message;
                }
            });
    }
    $scope.updateTime=function() {
            $scope.timer = $interval(function () {
                $scope.mytimer = i + '秒后';
                i--;
                if(i<0){
                    $interval.cancel($scope.timer);
                    $scope.mytimer ='';
                    $scope.disabled='';
                }
            }, 1000);
    }
    $scope.login = function(){
        $scope.authError = "";
        var authdata = Base64.encrypt($scope.user.username + ':' + $scope.user.password+':'+$scope.user.code);
        $http.defaults.headers.common['Authorization'] =authdata;
        var $com = $resource($scope.app.host + "/index/index/login");
        $com.get(function(data){
            if(data.error==0) {
                $scope.authError = "用户名密码或验证码错误，请重新输入";
                //return;
            }else {
                $localStorage.user = data.name;
                $localStorage.auth = data.token;
                $localStorage.role_auth=data.role_auth;
                $localStorage.role=data.role_id===0;
                $state.go('app.dashboard');
            }
        },function(){
            $scope.authError = $scope.app.host + "/index/index/index";
        })
    }

});

