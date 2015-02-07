<?php
date_default_timezone_set('Asia/Tokyo');
/* getPictureUrls.php
*
*
*
*
*/
require_once('config.php');

// テスト用
// $_POST['date'] = "2014-02-22";
$date = $_GET['date'];

$picIdArr = loadPictureId($date);

foreach($picIdArr as $arr)
{
	$pictureArr[] = array(
						'pictureUrl' 	=> 'loadPicture.php?id=' . $arr['id'],
						'lat'			=> $arr['lat'],
						'lon'			=> $arr['lon'],
						'id'			=> $arr['id']
						);
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($pictureArr);


function loadPictureId($dateTime){
	$sql = "SELECT id, Y(location), X(location) FROM t_picture WHERE DATE_FORMAT(datetime, '%Y-%m-%d') = '". $dateTime . "'";
	$dsn = 'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_NAME;
	try {
		$dbh = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
	} catch (PDOExeption $e){
		exit($e->getMessage());
	}

	$stmt = $dbh->query($sql);

	while($result = $stmt->fetch(PDO::FETCH_NUM)){
		$retArr[] = array(
						'id' => $result[0],
						'lat' => $result[1],
						'lon' => $result[2]
						);
	}
	$dbh = null;

	return $retArr;
}