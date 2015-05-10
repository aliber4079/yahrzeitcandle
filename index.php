<?php
require "appconfig.php";
define('FACEBOOK_SDK_V4_DIR', 'c:/yahrzeitcandle/facebook-php-sdk-v4/');
require FACEBOOK_SDK_V4_DIR . 'autoload.php';
session_start();
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
FacebookSession::setDefaultApplication($appid, $appsecret);
$helper=new FacebookRedirectLoginHelper("https://apps.facebook.com/ycdevapp/");
$loginUrl=$helper->getLoginUrl();

?>
<!DOCTYPE html>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="hebcal.noloc.js"></script>

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<style>
  div[ng-controller] {
   margin-left: 5px;
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
	  padding: 0px;
  }
  #tablecontainer   {
	  width:100%;
  }
  
  
  
  #tablecontainer  td {
	  max-width:150px;
  }
  
  .honoree{
	  width:210px;
  }
  
  .hebdate{
	  width:auto;
  }
  
  #tablecontainer .editcol {
	  padding:0px;
	  vertical-align: middle;
	  max-width:75px;
  }
  
</style>
</head>
<body>

<div  ng-controller="yahrzeitcandleController">
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
 {{user.email}}
 <input type="checkbox" ng-model="user.email" ng-change="useremail($event)">user.email
 {{user.id}}
 <div id="tablecontainer" class="panel panel-default">
<table ng-if="!records[0].error" ng-mouseleave="showdeletefor=0" class="table table-condensed form-inline">
<tr><th>honoree</th><th>Gregorian Date
<span   
  ng-model="edited_record.pickerdate" type="text" datepicker-popup is-open="$parent.showcal" 
  close-on-date-selection="false" 
  ng-change="
  edited_record.greg_month=edited_record.pickerdate.getMonth()+1;
  edited_record.greg_day=edited_record.pickerdate.getDate();
  edited_record.greg_year=edited_record.pickerdate.getFullYear();
  gregChange(edited_record)"></span>	
</th><th>Hebrew Date</th></tr>

<tr  ng-repeat="record in records"  ng-include="record.template" ng-mouseenter="$parent.$parent.showdeletefor=record.id">

</tr>
 </table>
 
 </div> <!-- /panel -->
 <div  ng-hide="editinguser||records[0].error">
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
 
  angular.module('yahrzeitcandle',['ngResource','ui.bootstrap'])
  .controller('yahrzeitcandleController',function($scope,$resource,$log,$modal,$window) {
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
	if (false && record.id>0) {
     record.$save(function(r){ //in order to repopulate what came back from ajax
	   $log.info("repop: " + r.heb_year);
		var d=$scope.calcGreg(r.heb_day,r.heb_month,r.heb_year);
	    r.greg_day=d[0];
	    r.greg_month=d[1];
	    r.greg_year=d[2];
		r.pickerdate=new Date(r.greg_year, r.greg_month-1, r.greg_day);
	 });
	} else { 
		record.pickerdate=new Date(record.greg_year, record.greg_month-1, record.greg_day);
	}
   }
   $scope.hebChange=function(record){
	if (false && record.id>0) {
	 record.$save(function(r){ //in order to repopulate what came back from ajax
	   var d=$scope.calcGreg(r.heb_day,r.heb_month,r.heb_year);
	   r.greg_day=d[0];
	   r.greg_month=d[1];
	   r.greg_year=d[2];
	   r.pickerdate=new Date(r.greg_year, r.greg_month-1, r.greg_day);
	 });
	} else { 
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
   $scope.useremail=function($event){
	 /*$log.info($event);
	 $event.preventDefault();
	 $event.stopPropagation();*/
	 if ($scope.user.email==true) {
		 if (!$scope.user.emailperms ||  $scope.user.emailperms=="declined") {
			 opts={};
		 if ($scope.user.emailperms=="declined"){
			 $log.info("re-request email perms");
			 opts={scope:"email",auth_type:"rerequest",return_scopes:true};
		 } else {
			  $log.info("request email perms for the 1st time");
			  opts={scope:"email",return_scopes:true};
		 }
	     $window.FB.login(function(response) {
                if (response.authResponse) {
				 $log.info(response.authResponse.grantedScopes);
				 if(/email/.exec(response.authResponse.grantedScopes)){
					 $log.info("email permission granted");
					 $scope.user.email=true;
					 $scope.user.$save();
				 } else {
		           $scope.user.email=false;
		           $scope.user.$save();
	             }
                } else {
                 $log.info('User cancelled login or did not fully authorize.');
                }
            },opts);
			 
		 } else {
			 $scope.user.email=true;
			 $scope.user.$save();
		 }
	 } else {
		 $scope.user.email=false;
		 $scope.user.$save();
	 }
   };
   $scope.opencal=function($event,record) {
	 $event.preventDefault();
	 $event.stopPropagation();
	 $scope.showcal=true;
   };
   $scope.editinguser=false;
   $scope.showdeletefor=0;
   $scope.showcal=false;
   $scope.useresource=$resource('ajax.php/user',{accessToken:$window.accessToken});
   $scope.user=$scope.useresource.get(function(user){
	     if (!user.emailperms ||  user.emailperms=="declined") {
			 user.email=false;
		  }
   });
   
   $scope.Record=$resource('ajax.php/yahrzeits',{accessToken:$window.accessToken});
   $scope.records=$scope.Record.query(function(){
    for (i=0;i<$scope.records.length;i++){
 	   record=$scope.records[i];
	   record.template="recordtemplate.html";
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
	 $log.info("addnew");
	record=new $scope.Record;
	record.id=0;
	d=new Date();
	record.greg_day=d.getDate();
	record.greg_month=d.getMonth()+1; //0 based
	record.greg_year=d.getFullYear();
	$scope.gregChange(record);
	record.template="edittemplate.html";
	$scope.records.push(record);
	$scope.editinguser=true;
   }
   $scope.save=function(record) {
	record.$save(function(e){
	 $log.info(e);
	 if (!e.id || e.id==0) {
	  $scope.reset();
	 } else {
      var d=$scope.calcGreg(record.heb_day,record.heb_month,record.heb_year);
      record.greg_day=d[0];
      record.greg_month=d[1];
      record.greg_year=d[2];
	  record.template="recordtemplate.html";
      $scope.editinguser=false;
	 }
 	});
   }
   $scope.edit=function(record){
	$scope.editinguser=true;
	$scope.origrecord={};
    for (i in record) {
	 $scope.origrecord[i]=record[i];
    }
	record.template='edittemplate.html';
	$scope.edited_record=record;
   };
   $scope.reset=function(record,origrecord){
	   if (record && record.id>0 && origrecord) {
	    for (i in origrecord) {
         record[i]=origrecord[i];
	    }
	   } else {
		 $scope.records.pop();
	   }
	   $scope.editinguser=false;
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
  
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?= $appid ?>',
		  cookie	 : true,
          xfbml      : false,
          version    : 'v2.3'
        });
		FB.getLoginStatus(function(response) {
          console.log (response.status);
		  if (response.status==='connected') {
			window.accessToken = response.authResponse.accessToken;
		    angular.bootstrap(document, ['yahrzeitcandle']);
		  } else 
          if (response.status==='not_authorized') {
			  top.location.href="<?= $loginUrl ?>";
		  }
		});
      };
      (function(d, s, id){
         var js, fjs = d.getElementsByTagName(s)[0];
         if (d.getElementById(id)) {return;}
         js = d.createElement(s); js.id = id;
         js.src = "//connect.facebook.net/en_US/sdk.js";
         fjs.parentNode.insertBefore(js, fjs);
       }(document, 'script', 'facebook-jssdk'));

</script>
<div id="fb-root"></div> 
</body>
</html>