<?php
date_default_timezone_set('Asia/Tokyo');
require_once('config.php');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title>桜マッピング</title>
	<script src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
	<script src="./js/gmap.js"></script>
</head>
<body onload="initialize()">
<h2>桜マッピング</h2>
<?php
//日付を選ばせる
echo '<p>';
showDateSelect(loadDateTimes());
echo '</p>'
?>
<div id="map_canvas" style="width:50%;height:480px;float:left"></div>
<div id="selected_picture" style="width:50%;float:left">ここに選択した写真が表示されます</div>
<input type="hidden" name="selected_picture_id">
<br>
<b2>コメント</b2>
<div id="comments"></div>
<p><textarea id="comment_write" name="comment" rows="4" cols="40"></textarea></p>
<p><input type="button" onclick="addComment();" value="投稿"></p>

<div id="pictures"></div>
<p><a href="up_photo.php">画像のアップロードはこちらから</a></p>

</body>
</html>
<?php
function showDateSelect($dateArr){
	echo '<select name="date" onchange="getPictureJson(this);">' . "\n";
	echo '<option value="">日付を選択してください</option>' . "\n";
	foreach ($dateArr as $d)
	{
		$dt = new DateTime($d);
		$yyyymmdd = $dt->format('Y-m-d');
		if(isset($before_d) && $before_d === $yyyymmdd){
			continue;
		}else{
			echo '<option value="' . $dt->format('Y-m-d') . '">' . $dt->format('Y年m月d日') . '</option>' . "\n";
		}
		$before_d = $yyyymmdd;	//一つ前の日にちを保持するため（同じ日は1つだけ表示）
	}
	echo '</select>' . "\n";
}
function loadDateTimes(){
	$sql = "SELECT DISTINCT datetime FROM t_picture";
	$dsn = 'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_NAME;
	try {
		$dbh = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
	} catch (PDOExeption $e){
		exit($e->getMessage());
	}

	$stmt = $dbh->query($sql);

	while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
		$retArr[] = $result['datetime'];
	}
	$dbh = null;

	return $retArr;
}

function loadPictureId($dateTime){
	$sql = "SELECT id FROM t_picture WHERE datetime= '". $dateTime . "'";
	$dsn = 'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_NAME;
	try {
		$dbh = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
	} catch (PDOExeption $e){
		exit($e->getMessage());
	}

	$stmt = $dbh->query($sql);

	while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
		$retArr[] = $result['id'];
	}
	$dbh = null;

	return $retArr;
}
