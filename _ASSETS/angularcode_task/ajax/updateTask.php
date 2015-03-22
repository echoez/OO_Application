<?php 
require_once '../includes/db.php'; // The mysql database connection script
if(isset($_GET['taskID'])){
$action = $_GET['action'];
$taskID = $_GET['taskID'];
$query="update tasks set action='$action' where id='$taskID'";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

$result = $mysqli->affected_rows;

$json_response = json_encode($result);
}
?>