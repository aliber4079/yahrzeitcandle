<?php
error_log($_SERVER['REQUEST_METHOD']);
$record=json_decode(file_get_contents("php://input"));
error_log(print_r($record,1));
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
  $sql="insert into yahrzeit (honoree,heb_day,heb_month,heb_year) values ('" . $record->honoree . "'," . $record->heb_day . "," . $record->heb_month . "," . $record->heb_year . ")";
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
 exit(json_encode(array($result->fetch_array(MYSQLI_ASSOC)),JSON_NUMERIC_CHECK));
 //exit(json_encode($record));
}
$result=$mysql->query("select * from yahrzeit");
$results=array();
foreach ($result as $key => $value) {
	$results[]=$value;
}
exit(json_encode($results,JSON_NUMERIC_CHECK));
?>