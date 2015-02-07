<?php
date_default_timezone_set('Asia/Tokyo');
require_once('config.php');

$commentStr = htmlspecialchars($_GET['comment']);
$pictureId 	= htmlspecialchars($_GET['picutureId']);

addCommentToDb($pictureId, $commentStr);

// header("HTTP/1.1 200 OK");
echo "OK";

function addCommentToDb($pictureId, $commentStr){
	$sql = "INSERT INTO t_comment (picture_id, comment)
			VALUES (:pictureId, :comment)";
			// values (:file, :ext, GeomFromText( 'POINT( :loc )' ), :datetime)";
	$dsn = 'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_NAME;
	try {
		$dbh = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
	} catch (PDOExeption $e){
		exit($e->getMessage());
	}

	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(':pictureId', $pictureId);
	$stmt->bindValue(':comment', $commentStr);

	$flag = $stmt->execute();
	$dbh=null;
}