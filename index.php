<?php

define('FACEBOOK_SDK_V4_DIR', 'c:/yahrzeitcandle/facebook-php-sdk-v4/');
require FACEBOOK_SDK_V4_DIR . 'autoload.php';
session_start();

use Facebook\FacebookSession;
use Facebook\FacebookCanvasLoginHelper;
use Facebook\FacebookRedirectLoginHelper;
$appid="130902026920290";
$secret="8615d2d91ed9a24b7970062b2bc4814e";
FacebookSession::setDefaultApplication($appid, $secret);
$session=NULL;
$helper = new FacebookCanvasLoginHelper();

if (isset($_SESSION['access_token'])) {
 $token=$_SESSION['access_token'];
 try {
  $session = new FacebookSession($token);
  error_log("Validate");
  $session->Validate( $appid,$secret);
  error_log("using existing session");  
 } catch (\Exception $ex) {
  error_log(print_r($ex,1) . " creating new session");
  createnewsess();
 }
} else {
  createnewsess();
}
function createnewsess() {
 $helper = new FacebookCanvasLoginHelper();
 $session=$helper->getSession();
 if ($session) {
  $token=$session->getToken();
  $_SESSION['access_token'] = $token;
 } else {
  $helper=new FacebookRedirectLoginHelper("https://apps.facebook.com/ycdevapp/");
  $loginUrl=$helper->getLoginUrl();
  unset ($_SESSION);
  exit("<script type='text/javascript'>top.location.href = '".$loginUrl."';</script>");
 }
}

?>
<!DOCTYPE html>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="hebcal.noloc.js"></script>

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<style>
  div[ng-app] {
   margin-left: 5em;
   margin-top: 5em;
  }
  .year {
	  max-width:90px;
  }
  .day {
	  max-width:70px;
  }
  .editcol {
	  width: 100px;
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
            Delete selected Yahrzeit for {{record.honoree}}?
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" ng-click="ok()">OK</button>
            <button class="btn btn-warning" ng-click="cancel()">Cancel</button>
        </div>
  </script>
 <span ng-bind="records[0].error"></span>
<table ng-if="!records[0].error" ng-mouseleave="showdeletefor=0" class="form-inline">
<tr>
<th>Name</th><th>Date</th>
</tr>
<tr  ng-repeat="record in records" ng-mouseenter="$parent.showdeletefor=record.id">
<td>
 <input type="hidden" ng-model="record.id">
 <input class="form-control"  ng-model="record.honoree" ng-blur="(record.id>0) && gregChange(record)">
</td>
<td><!-----greg date --------------------------------------->
  <button type="button" class="btn btn-default" ng-click="opencal($event,record)">  
  <i class="glyphicon glyphicon-calendar"></i>
  </button>
  <select ng-model="record.greg_month" 
  ng-options="month.value as month.label for month in gregmonths"
  ng-change="gregChange(record)" 
  class="form-control">
  </select> 
  <input type="number" min="1" max="31" ng-model="record.greg_day" 
  ng-change="gregChange(record)" class="form-control day" />
  <input type="number" ng-model="record.greg_year" 
  ng-change="gregChange(record)" class="form-control year" />
  <span   
  ng-model="record.pickerdate" type="text" datepicker-popup is-open="showcal[record.id]" 
  close-on-date-selection="false"
  ng-change="
  record.greg_month=record.pickerdate.getMonth()+1;
  record.greg_day=record.pickerdate.getDate();
  record.greg_year=record.pickerdate.getFullYear();
  gregChange(record)"></span>
</td><!-- /greg date ------------------------------------->
<td> <!--- heb date --------------------------------------->
 <select  ng-model="record.heb_month"
 ng-options="month.value as month.label for month in hebmonths" 
 ng-change="hebChange(record)" 
 class="form-control">
 </select>
 <input type="number" min="1" max="31" ng-model="record.heb_day"  
 ng-change="hebChange(record)" class="form-control day" />
 <input type="number"  ng-model="record.heb_year"  
 ng-change="hebChange(record)" 
 class="form-control year" />
 
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
 <div  ng-hide="addinguser||records[0].error">
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
  {"label":'Jan',"value":1},
  {"label":'Feb',"value":2},
  {"label":'Mar',"value":3},
  {"label":'Apr',"value":4},
  {"label":'May',"value":5},
  {"label":'Jun',"value":6},
  {"label":'Jul',"value":7},
  {"label":'Aug',"value":8},
  {"label":'Sep',"value":9},
  {"label":'Oct',"value":10},
  {"label":'Nov',"value":11},
  {"label":'Dec',"value":12}];
  $scope.hebmonths=[
  {"label":"Nissan","value":1},
  {"label":"Iyar",   "value":2},
  {"label":"Sivan",  "value":3},
  {"label":"Tamuz",  "value":4},
  {"label":"Av",     "value":5},
  {"label":"Elul",    "value":6},
  {"label":"Tishrei", "value":7},
  {"label":"Cheshvan","value":8},
  {"label":"Kislev",  "value":9},
  {"label":"Tevet",   "value":10},
  {"label":"Shvat",   "value":11},
  {"label":"Adar",    "value":12},
  {"label":"Adar II", "value":13}];
  $scope.days=[];
  for (i=1;i<32;i++){
   $scope.days.push(i);
  }
   $scope.gregChange=function(record){
	var d=$scope.calcHeb(record.greg_day,record.greg_month,record.greg_year);
	record.heb_day=d[0];
	record.heb_month=d[1];
	record.heb_year=d[2];
	if (record.id>0) {
     record.$save(function(r){ //in order to repopulate what came back from ajax
	   $log.info("repop: " + r.heb_year);
		var d=$scope.calcGreg(r.heb_day,r.heb_month,r.heb_year);
	    r.greg_day=d[0];
	    r.greg_month=d[1];
	    r.greg_year=d[2];
		r.pickerdate=new Date(r.greg_year, r.greg_month-1, r.greg_day);
	 });
	} else { //adding new
		record.pickerdate=new Date(record.greg_year, record.greg_month-1, record.greg_day);
	}
   }
   $scope.hebChange=function(record){
	if (record.id>0) {
	 record.$save(function(r){ //in order to repopulate what came back from ajax
	   var d=$scope.calcGreg(r.heb_day,r.heb_month,r.heb_year);
	   r.greg_day=d[0];
	   r.greg_month=d[1];
	   r.greg_year=d[2];
	   r.pickerdate=new Date(r.greg_year, r.greg_month-1, r.greg_day);
	 });
	} else { //adding new
	   var d=$scope.calcGreg(record.heb_day,record.heb_month,record.heb_year);
	   record.greg_day=d[0];
	   record.greg_month=d[1];
	   record.greg_year=d[2];
	   record.pickerdate=new Date(record.greg_year, record.greg_month-1, record.greg_day);
	}
   }
   $scope.calcGreg=function(hebday,hebmonth,hebyear){
    if (!hebday || !hebmonth ||!hebyear) {
	 return;
	}
    day=new Hebcal.HDate(hebday,
	hebmonth, hebyear);
	gregday=day.greg().getDate();
    gregmonth=day.greg().getMonth()+1; //Date index is 0 based
	gregyear=day.greg().getFullYear();
	return [gregday,gregmonth,gregyear];
   }
   $scope.calcHeb=function(gregday,gregmonth,gregyear){
   if (!gregday||!gregmonth ||!gregyear) {
	 $log.info("d,m,y " + gregday + " " + gregmonth + " " + gregyear);
	 return;
	}
	day=new Hebcal.HDate(new Date(gregyear,gregmonth-1,gregday)); //gregmonth is 0 indexed
	return [day.day,day.month,day.year];
   }  
	  
   /*****INIT VARIABLES*****/
     
   $scope.opencal=function($event,record) {
	 $scope.showcal=$scope.showcal.map(function(x){return false});
	 $event.preventDefault();
	 $event.stopPropagation();
	 $scope.showcal[record.id]=true;
   };
   $scope.addinguser=false;
   $scope.showdeletefor=0;
   $scope.showcal=[];
   $scope.Record=$resource('ajax.php');
   $scope.records=$scope.Record.query(function(){
    for (i=0;i<$scope.records.length;i++){
 	   record=$scope.records[i];
	   $scope.showcal[record.id]=false;
 	   var d=$scope.calcGreg(record.heb_day,record.heb_month,record.heb_year);
 	   record.greg_day=d[0];
 	   record.greg_month=d[1];
 	   record.greg_year=d[2];
 	   $log.info('greg_month ' + d[1]);
	   record.pickerdate=new Date(record.greg_year, record.greg_month-1, record.greg_day);
	}
   },function(){$log.info("oops");});
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
	d=new Date();
	record.greg_day=d.getDate();
	record.greg_month=d.getMonth()+1; //0 based
	record.greg_year=d.getFullYear();
	$scope.gregChange(record);
	$scope.records.push(record);
	$scope.addinguser=true;
   }
   $scope.submitnew=function(record) {
	record.$save(function(e){
	 $log.info(e);
	 if (e.id==0) {
	  $scope.reset(record);
	 }
    var d=$scope.calcGreg(record.heb_day,record.heb_month,record.heb_year);
    record.greg_day=d[0];
    record.greg_month=d[1];
    record.greg_year=d[2];
    //$log.info('greg_month ' + d[1]);
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