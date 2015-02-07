<?php
date_default_timezone_set('Asia/Tokyo');
/*
*loadPicture.php
*
*idが一致した写真を表示
*
*/
require_once('config.php');
$pictureId = $_GET['id'];

showPicture($pictureId);

function showPicture($id){
	$sql = "SELECT picture,ext FROM t_picture WHERE id=$id";
	$dsn = 'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_NAME;
	try {
		$dbh = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
	} catch (PDOExeption $e){
		exit($e->getMessage());
	}

	$stmt = $dbh->query($sql);

	while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
		$picture = $result['picture'];
		$type = $result['ext'];
	}
	$dbh = null;

	header("Content-Type: " . $type);
	echo $picture;
}

?>