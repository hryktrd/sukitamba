<?php
date_default_timezone_set('Asia/Tokyo');
require_once('config.php');

$pictureId = htmlspecialchars($_GET['pictureId']);

$commentArr = getCommentFromDb($pictureId);

header("Content-Type: application/json; charset=utf-8");
echo json_encode($commentArr);

function getCommentFromDb($pictureId){
	$sql = "SELECT comment FROM t_comment WHERE picture_id = '". $pictureId . "'";
	$dsn = 'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_NAME;
	try {
		$dbh = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
	} catch (PDOExeption $e){
		exit($e->getMessage());
	}

	$stmt = $dbh->query($sql);

	while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
		$retArr[] = $result['comment'];
	}
	return $retArr;
}