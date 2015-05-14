<?php
//error_log(print_r($_GET,1));

//ini_set("error_reporting",E_ALL);
require "appconfig.php";
session_start();
define('FACEBOOK_SDK_V4_DIR', 'c:/yahrzeitcandle/facebook-php-sdk-v4/');
require FACEBOOK_SDK_V4_DIR . 'autoload.php';
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\GraphSessionInfo;
FacebookSession::setDefaultApplication($appid,$appsecret);
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
 $user_id= number_format($user->getId(),null,null,"");
 error_log($user_id);
 $permsResponse=( new FacebookRequest($session, 'GET', '/me/permissions'))->execute()
	->getResponse();//response object
 foreach ($permsResponse->data as $perm) {
  $perms[$perm->permission]=$perm->status;
  //error_log($perm->permission);
  //error_log($perm->status);
 }
 error_log("perms: " . print_r($perms,1));
} catch(FacebookRequestException $ex) {
    // When Facebook returns an error
	error_log($ex);
} catch(\Exception $ex) {
    // When validation fails or other local issues
	error_log($ex);
	exit(json_encode(array(array("error"=>"something went wrong"))));
 }
}
$mysql=new mysqli("localhost","root","","crud");
//error_log("user id: $user_id");
$record=json_decode(file_get_contents("php://input"));

if ($_SERVER['PATH_INFO']==="/user"){
	//error_log("user " . $_SERVER['REQUEST_METHOD']);
	$mysql->query("insert into user (id) values ('$user_id') on duplicate key update id=id");

	
	if ($_SERVER['REQUEST_METHOD']=="POST") {
        $mysql->query("update user set email=" . intval($record->email) . " where id='" . $record->id . "'");
	}
	$result=$mysql->query("select * from user where id='$user_id'");
	$result=$result->fetch_array(MYSQLI_ASSOC);
	$result['email']= isset ($result['email']) && $result['email'];
	//$result['id']="" $result['id'];
	if (isset($perms)){
		$result['perms']=$perms;
	}
    exit(json_encode($result));
}


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
  $sql="insert into yahrzeit (honoree,uid,heb_day,heb_month,heb_year) values ('" . $record->honoree . "','" . $user_id . "'," . $record->heb_day . "," . $record->heb_month . "," . $record->heb_year . ")";
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
 $result=$result->fetch_array(MYSQLI_ASSOC);
 $result['template']=$record->template;
 exit(json_encode($result,JSON_NUMERIC_CHECK));
}
$result=$mysql->query("select * from yahrzeit where uid='$user_id'" );
$results=array();
foreach ($result as $key => $value) {
	$results[]=$value;
}
exit(json_encode($results,JSON_NUMERIC_CHECK));
?>