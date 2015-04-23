<!DOCTYPE html>
<html><head>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<style>
  table {
	border-collapse: collapse;
  }
  tr:nth-child(odd) input.editing {
   background-color: lightgreen;
  }
  tr:nth-child(even) input.editing {
   background-color: lightgreen;
  }
  tr:nth-child(odd) input {
   background: white;
   border: none;
   width: 100%;
  }
  tr:nth-child(even) input {
   background-color: lightgray;
   border: none;
   width: 100%;
  }
  td {
   margin: 0px;
   padding: 0px;
   width:50px;
   border: solid thin black;
  }
  td.editcol {
   width:200px;
   border: none;
  }
  input {
	width:100%;
  }
  div[ng-app] {
   margin: 5em;
  }
</style>
</head>
<body>

<div ng-app="yahrzeitcandle" ng-controller="InplaceEditController as myCtrl">
 <script type="text/ng-template" id="myModalContent.html">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Delete</h3>
        </div>
        <div class="modal-body">
            remove {{record.name}} ?
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" ng-click="ok()">OK</button>
            <button class="btn btn-warning" ng-click="cancel()">Cancel</button>
        </div>
    </script>
{{showdeletefor}}
<table ng-mouseleave="showdeletefor=0">
<tr ng-repeat="record in records" ng-mouseenter="$parent.showdeletefor=record.id">
<td ng-repeat="field in fields">
 <input type="hidden" ng-model="record.id">
 <input ng-model="record[field]" ng-focus="focusme($event,record)" ng-blur="focusme($event,record)" ng-keyup="focusme($event,record)">
</td>
<!-- td>
<button type="button"><span class="glyphicon glyphicon-camera"></span></button>
</td -->
<td class="editcol">
    <span ng-show="record.id==0">
     <button type="button" ng-click="submitnew(record)">save</button>
     <button type="button" ng-click="reset(record)">cancel</button>
    </span>
	<span ng-show="showdeletefor==record.id && addinguser==false">
	<button type="button" ng-click="confirmdelete(record)"><span class="glyphicon glyphicon-remove"></span></button>
	</span>
</td>
</tr>
 </table>
 <div  ng-hide="addinguser">
 <button type="button" ng-click="addnew()">
   <span class="glyphicon glyphicon-plus"></span>Add user
  </button>
 </div>
</div>

<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js"></script>
<script src="//code.angularjs.org/1.3.15/angular-resource.min.js"></script>
<script src="ui-bootstrap-custom-0.12.1.min.js"></script>
<script src="ui-bootstrap-custom-tpls-0.12.1.min.js"></script>
<script>
 (function (){
  angular.module('yahrzeitcandle',['ngResource','ui.bootstrap'])
  .controller('InplaceEditController',function($scope,$resource,$log,$modal) {
	  
   /*****INIT VARIABLES*****/
   $scope.addinguser=false;
   $scope.showdeletefor=0;
   $scope.fields=["name","age"];
   $scope.Record=$resource('ajax.php',{},{get:{isArray:true}});
   $scope.records=$scope.Record.get();
   $scope.mousenter=function(){
	   //console.log('mousenter');
   }
   $scope.delete=function(r) {
	 r.$remove({"id":r.id}, function() {
	  $log.info("deleting " + r.id);
 	  for (i in $scope.records) {
       if ($scope.records[i].id==r.id) {
        $scope.records.splice(i,1);
        break;
       }
      }
	 });
   }
   $scope.addnew=function(){
	record=new $scope.Record;
	record.id=0;
	$scope.records.push(record);
	$scope.addinguser=true;
   }
   $scope.submitnew=function(record) {
	record.$save(function(e){
	 $log.info(e);
	 if (e.id==0) {
	  $scope.reset(record);
	 }
	});
	$scope.addinguser=false;
   }
   $scope.reset=function(record){
   if(record.id==0) {
    $scope.records.pop();
	$scope.addinguser=false;
   }
  };
   $scope.focusme=function(e,r) {
	$log.info(e.type);
    if (e.type==='focus') {
	 e.target.className="editing";
	} else if (e.type==='blur') {
	 e.target.className="";
	 r.id && r.$save();
	} else if (!$scope.addinguser && e.type==='keyup' && e.keyCode==13) {
	 e.target.blur();
	}
   }
   //dialog *********
   $scope.confirmdelete=function(record) {
	  $log.info('r u sure u want to delete ' + record.name);
	  var modalInstance = $modal.open({
       templateUrl: 'myModalContent.html',
       controller: 'ModalInstanceCtrl',
       size: 'sm',
       resolve: {
        record:  function(){return record;}
       }
    });
	modalInstance.result.then(function (record) {
      $log.info('ok, deleting ' + record.name);
	  $scope.delete(record);
    }, function () {
      $log.info('Modal dismissed at: ' + new Date());
    });

   };
  });
  
  angular.module('yahrzeitcandle').controller('ModalInstanceCtrl', function ($scope, $modalInstance, $log, record) {
   $scope.record=record;
   $scope.ok = function () {
    $modalInstance.close(record);
   };

   $scope.cancel = function () {
    $modalInstance.dismiss();
   };
  });
  
  
 })();
</script>
</body>
</html>