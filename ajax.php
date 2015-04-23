<?php
error_log($_SERVER['REQUEST_METHOD']);
$record=json_decode(file_get_contents("php://input"));
error_log(print_r($record,1));
$mysql=new mysqli("localhost","root","","crud");
if ($_SERVER['REQUEST_METHOD']=="DELETE") {
	$id= $_REQUEST['id'];
	error_log("delete $id");
	$sql="delete from crud where id=$id";
	error_log($sql);
	$mysql->query($sql);
	exit("{\"id\":$id}");
}
if ($_SERVER['REQUEST_METHOD']=="POST") {
 if ($record->id==0) {
  $sql="insert into crud (age,name) values (" . $record->age . ",'" . $record->name . "')";
  error_log($sql);
  $mysql->query($sql);
  $record->id=$mysql->insert_id;
  exit(json_encode($record)); 
 } else {
  $sql="update crud set age=" . $record->age . ", name='" . $record->name . "' where id = " . $record->id;
  $mysql->query($sql);
 }
 error_log($sql);
 exit(json_encode($record));
}
$result=$mysql->query("select * from crud order by name asc,age asc");
$results=array();
foreach ($result as $key => $value) {
	//$value=array_merge($value,array("new"=>false));
	$results[]=$value;
}
exit(json_encode($results,JSON_NUMERIC_CHECK));
?>