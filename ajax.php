<?php
//ini_set("error_reporting",E_ALL);
session_start();
define('FACEBOOK_SDK_V4_DIR', 'c:/yahrzeitcandle/facebook-php-sdk-v4/');
require FACEBOOK_SDK_V4_DIR . 'autoload.php';
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\GraphSessionInfo;
FacebookSession::setDefaultApplication('130902026920290', '8615d2d91ed9a24b7970062b2bc4814e');
$session=$user_id=NULL;
if (isset($_GET['accessToken'])){
 $token=$_GET['accessToken'];
 //error_log($token);
try {
 $session = new FacebookSession($token);
 $session->Validate();
 error_log("passed ajax validation");
 $user = ( new FacebookRequest($session, 'GET', '/me'))->execute()
	->getGraphObject()->cast(GraphUser::className());
 $user_id=$user->getId();
 error_log($user_id);
 $perms=( new FacebookRequest($session, 'GET', '/me/permissions'))->execute()
	->getResponse();//response object
 foreach ($perms->data as $perm) {
  error_log($perm->permission);
  error_log($perm->status);
 }
} catch(FacebookRequestException $ex) {
    // When Facebook returns an error
	error_log($ex);
} catch(\Exception $ex) {
    // When validation fails or other local issues
	error_log($ex);
	exit(json_encode(array(array("error"=>"something went wrong"))));
 }
}
$record=json_decode(file_get_contents("php://input"));
$mysql=new mysqli("localhost","root","","crud");
if ($_SERVER['REQUEST_METHOD']=="DELETE") {
	$id= $_REQUEST['id'];
	error_log("delete $id");
	$sql="delete from yahrzeit where id=$id";
	error_log($sql);
	$mysql->query($sql);
	exit("{\"id\":$id}");
}
if ($_SERVER['REQUEST_METHOD']=="POST") {
 if ($record->id==0) {
  $sql="insert into yahrzeit (honoree,uid,heb_day,heb_month,heb_year) values ('" . $record->honoree . "'," . $user_id . "," . $record->heb_day . "," . $record->heb_month . "," . $record->heb_year . ")";
  error_log($sql);
  $mysql->query($sql);
  $record->id=$mysql->insert_id;
  $result=$mysql->query("select * from yahrzeit where id=" . $record->id);
  exit(json_encode($result->fetch_array(MYSQLI_ASSOC),JSON_NUMERIC_CHECK));
 } else {
  $sql="update yahrzeit set honoree='" . $record->honoree . "', heb_day=" . $record->heb_day . 
  ", heb_month=". $record->heb_month . ", heb_year=" . $record->heb_year . " where id = " . $record->id;
  $mysql->query($sql);
 }
 $result=$mysql->query("select * from yahrzeit where id=" . $record->id);
 exit(json_encode($result->fetch_array(MYSQLI_ASSOC),JSON_NUMERIC_CHECK));
}
$result=$mysql->query("select * from yahrzeit");
$results=array();
foreach ($result as $key => $value) {
	$results[]=$value;
}
exit(json_encode($results,JSON_NUMERIC_CHECK));
?>