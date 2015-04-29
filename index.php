<!DOCTYPE html>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="hebcal.noloc.js"></script>

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
  td {
   margin: 0px;
   padding: 0px;
  }
  td.editcol {
   width:200px;
   border: none;
  }
  input {
	//width:100%;
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
            remove {{record.honoree}} ?
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" ng-click="ok()">OK</button>
            <button class="btn btn-warning" ng-click="cancel()">Cancel</button>
        </div>
    </script>
<table ng-mouseleave="showdeletefor=0">
<tr>
<th>Name</th><th>Date</th>
</tr>
<tr ng-repeat="record in records" ng-mouseenter="$parent.showdeletefor=record.id">
<td>
 <input type="hidden" ng-model="record.id">
 <input class="form-control"  ng-model="record.honoree" ng-blur="record.$save()">
</td>
<td><!-----greg date --------------------------------------->
 <div class="input-group">
  <select ng-model="record.blip" 
  ng-options="opt as opt.label for opt in gregmonths"
  ng-change="gregChange(record)" >
  </select> 
  <input type="number" min="1" max="31" ng-model="record.greg_day" 
  ng-change="gregChange(record)"/>
  <input type="number" ng-model="record.greg_year" 
  ng-change="gregChange(record)"/>
 </div>

</td><!-- /greg date ------------------------------------->
<td> <!--- heb date --------------------------------------->
 <select  ng-options="opt as opt.label for opt in hebmonths" 
 ng-change="hebChange(record)" 
 ng-model="record.blah">
 </select>
 <input type="number" min="1" max="31" ng-model="record.heb_day"  
 ng-change="hebChange(record)" />
 <input type="number"  ng-model="record.heb_year"  
 ng-change="hebChange(record)" />
</td><!-- /heb date-------------------------------------->
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

<script src="angular.min.js"></script>
<script src="angular-resource.min.js"></script>
<script src="ui-bootstrap-0.12.1.min.js"></script>
<script src="ui-bootstrap-tpls-0.12.1.min.js"></script>
<script>
 (function (){
  angular.module('yahrzeitcandle',['ngResource','ui.bootstrap'])
  .controller('InplaceEditController',function($scope,$resource,$log,$modal) {
	  
  $scope.gregmonths=[
   {label:'Jan',value:1},
   {label:'Feb',value:2},
   {label:'Mar',value:3},
   {label:'Apr',value:4},
   {label:'May',value:5},
   {label:'Jun',value:6},
   {label:'Jul',value:7},
   {label:'Aug',value:8},
   {label:'Sep',value:9},
   {label:'Oct',value:10},
   {label:'Nov',value:11},
   {label:'Dec',value:12}];
  $scope.hebmonths=[
   {label:"Nissan", value: 1},
   {label:"Iyar",  value: 2},
   {label:"Sivan", value: 3},
   {label:"Tamuz", value: 4},
   {label:"Av",    value: 5},
   {label:	"Elul",     value:6 }, 
   {label:	"Tishrei",  value:7 },
   {label:	"Cheshvan", value:8 },
   {label:	"Kislev",   value:9 },
   {label:	"Tevet",    value:10},
   {label:	"Shvat",    value:11},
   {label:	"Adar",     value:12},
   {label:	"Adar II", value:13}];
  $scope.days=[];
  for (i=1;i<32;i++){
   $scope.days.push(i);
  }
   $scope.gregChange=function(record){
    record.greg_month=record.blip.value;
	var d=$scope.calcHeb(record.greg_day,record.greg_month,record.greg_year);
	record.heb_day=d[0];
	record.heb_month=d[1];
	record.heb_year=d[2];
	$scope.hebmonths.map(function(x){
		if (x.value==record.heb_month) {
			record.blah=x;
		}
	});
   }
   $scope.hebChange=function(record){
	record.heb_month=record.blah.value;
	var d=$scope.calcGreg(record.heb_day,record.heb_month,record.heb_year);
	record.greg_day=d[0];
	record.greg_month=d[1];
	record.greg_year=d[2];
	//record.blip=$scope.gregmonths[record.greg_month];	
    $scope.gregmonths.map(function(x){
			   if (x.value==record.greg_month){
		        record.blip=x;
			   }
		   });
	   }
   $scope.calcGreg=function(hebday,hebmonth,hebyear){
    if (!hebday || !hebmonth ||!hebyear) {
	 return;
	}
    day=new Hebcal.HDate(hebday,
	hebmonth, hebyear);
	gregday=day.greg().getDate();
    gregmonth=day.greg().getMonth()+1; //Date index is 0 based
	gregyear=day.greg().getYear()+1900;
	return [gregday,gregmonth,gregyear];
   }
   $scope.calcHeb=function(gregday,gregmonth,gregyear){
	$log.info("d,m,y " + gregday + " " + gregmonth + " " + gregyear);
	day=new Hebcal.HDate(new Date(gregyear,gregmonth-1,gregday)); //0 indexed
	return [day.day,day.month,day.year];
   }  
	  
   /*****INIT VARIABLES*****/
   
   $scope.addinguser=false;
   $scope.showdeletefor=0;
   $scope.fields=["honoree","greg_date"];
   $scope.Record=$resource('ajax.php',{},{get:{isArray:true}});
   $scope.records=$scope.Record.get(function(){
	   for (i=0;i<$scope.records.length;i++){
		   record=$scope.records[i];
		   var d=$scope.calcGreg(record.heb_day,record.heb_month,record.heb_year);
		   record.greg_day=d[0];
		   record.greg_month=d[1];
		   record.greg_year=d[2];
		   $log.info('greg_month ' + d[1]);
		   $scope.gregmonths.map(function(x){
			   if (x.value==record.greg_month){
		        record.blip=x;
			   }
		   });
			$scope.hebmonths.map(function(x){
				if (x.value==record.heb_month) {
					record.blah=x;
				}
			});
	   }
   });
   $scope.mousenter=function(){
	   //console.log('mousenter');
   }
   $scope.opencal=function($event,r) {
	$event.stopPropagation();
	$event.preventDefault();
	$scope.records.map(function(r1){
		if (r1.id==r.id) {
			r1.opened=true;
		} else {
			r1.opened=false;
		}
	});
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
	 $scope.addinguser=false;
	});
   }
   $scope.reset=function(record){
   if(record.id==0) {
    $scope.records.pop();
	$scope.addinguser=false;
   }
  };
   //dialog *********
   $scope.confirmdelete=function(record) {
	  $log.info('r u sure u want to delete ' + record.honoree);
	  var modalInstance = $modal.open({
       templateUrl: 'myModalContent.html',
       controller: 'ModalInstanceCtrl',
       size: 'sm',
       resolve: {
        record:  function(){return record;}
       }
    });
	modalInstance.result.then(function (record) {
      $log.info('ok, deleting ' + record.honoree);
	  $scope.delete(record);
    }, function () {
      $log.info('Modal dismissed at: ' + new Date());
    });

   };
  }).controller('ModalInstanceCtrl', function ($scope, $modalInstance, $log, record) {
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