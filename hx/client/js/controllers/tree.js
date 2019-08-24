app.controller('AbnTestController', function($scope, $timeout,myhttp,toaster) {
    $scope.roles=[];
    $scope.my_data = [];
    var init=[];
    var  tree, treedata_avm, treedata_geography;
    $scope.my_tree = tree = {};
    myhttp.getData('/index/setting/role','GET').then(function (res) {
        $scope.roles=res.data.role;
        $scope.my_data=res.data.auth;
        init=angular.copy(res.data.auth);
        $timeout(function () {
            $scope.my_tree.expand_all();
        },200,false);
    });

    $scope.my_tree_handler = function(branch) {
      $scope.output = "You selected: " + branch.label;
    };

    $scope.try_changing_the_tree_data = function() {
      if ($scope.my_data === treedata_avm) {
        return $scope.my_data = treedata_geography;
      } else {
        return $scope.my_data = treedata_avm;
      }
    };

    $scope.try_async_load = function() {
      $scope.my_data = [];
      $scope.doing_async = true;
      return $timeout(function() {
        if (Math.random() < 0.5) {
          $scope.my_data = treedata_avm;
        } else {
          $scope.my_data = treedata_geography;
        }
        $scope.doing_async = false;
        return tree.expand_all();
      }, 1000);
    };
    $scope.mySelected=function () {
        var s=[];

    };
    $scope.editItem = function(item){
        if(item && item.selected){
            item.editing = true;
        }
    };
    $scope.selectRole=function (item) {
        angular.forEach($scope.roles, function(item) {
            item.selected = false;
        });
        $scope.role = item;
        $scope.role.selected = true;
        myhttp.getData('/index/setting/roleAuth','GET',{role_id:item.role_id})
            .then(function (res) {
                $scope.i=0;
                angular.forEach($scope.my_data,function (data){
                        $scope.set_each_auth(data,res.data);
                });
            });
    }
    $scope.set_each_auth=function (data,auth_ids) {
     //   if(!$scope.check) return false;
        if(angular.isDefined(data.children)&&data.children.length>0){
            angular.forEach(data.children,function (ldata) {
               $scope.set_each_auth(ldata,auth_ids);
            });
        }else{
            $scope.check=false;
            angular.forEach(auth_ids,function (auth_id) {
                if($scope.check) return false;
                $scope.check=data.selected=data.auth_id===auth_id;
                //console.log(data);
            });
        }
    }
    $scope.doneEditing=function (item) {
        myhttp.getData('/index/setting/roleUpdate','POST',item)
            .then(function (res) {
                if(res.data.result===1){
                    toaster.pop('success','更新成功！');
                }else{
                    toaster.pop('error','更新失败！');
                }
            });
    }
    $scope.deleteRole=function (item) {
        if(angular.isUndefined(item.role_id)) return false;
        myhttp.getData('/index/setting/roleDelete','POST',item)
            .then(function (res) {
                if(res.data.result===1){
                    toaster.pop('success','更新成功！');
                    myhttp.getData('/index/setting/role','GET').then(function (res) {
                        $scope.roles=res.data.role;
                    });
                }else{
                    toaster.pop('error','更新失败！');
                }
            });
    }
    $scope.add_role = function(){
        var role = {role_name: '新角色'};
        role.role_name = $scope.checkItem(role, $scope.roles, 'role_name');
        $scope.roles.push(role);
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
    $scope.auth_ids=[];
    $scope.updateAuth=function () {
        $scope.auth_ids=[];
        angular.forEach($scope.my_data,function (data){
            get_each(data);
        });
        myhttp.getData('/index/setting/updateAuth','POST',{auth:$scope.auth_ids,role_id:$scope.role.role_id})
            .then(function (res) {
                if(res.data.result===1)
                    toaster.pop('success','保存成功!');
                else
                    toaster.pop('error','保存失败!');
            });
    };
    get_each=function (data) {
        if(angular.isDefined(data.children)&&data.children.length>0){
            angular.forEach(data.children,function (ldata) {
               get_each(ldata);
            });
        }else{
           if(angular.isDefined(data.selected)&&data.selected){
               $scope.auth_ids.push(data.auth_id);
           }
        }
    };
    return $scope.try_adding_a_branch = function() {
      var b;
      b = tree.get_selected_branch();
      return tree.add_branch(b, {
        label: 'New Branch',
        data: {
          something: 42,
          "else": 43
        }
      });
    };

});