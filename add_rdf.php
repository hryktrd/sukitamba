<?php
date_default_timezone_set('Asia/Tokyo');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
</head>
<body>
<h2>写真の中の情報</h2>

<?php
$inputFileName = $_FILES['upPhoto']['tmp_name'];
$exif = @exif_read_data($inputFileName, 0, true);

if ($exif) {
	//写真の位置情報取得
	if (array_key_exists('GPS', $exif)) {
  // $exif['GPS']['GPSLongitude']を使用する測地系に変換して格納
		$lat0Arr = explode('/', $exif['GPS']['GPSLatitude'][0]);
		$lat1Arr = explode('/', $exif['GPS']['GPSLatitude'][1]);
		$lat2Arr = explode('/', $exif['GPS']['GPSLatitude'][2]);
		$lon0Arr = explode('/', $exif['GPS']['GPSLongitude'][0]);
		$lon1Arr = explode('/', $exif['GPS']['GPSLongitude'][1]);
		$lon2Arr = explode('/', $exif['GPS']['GPSLongitude'][2]);


		$lat = $lat0Arr[0] / $lat0Arr[1]
				+ $lat1Arr[0] / $lat1Arr[1] / 60
				+ $lat2Arr[0] / $lat2Arr[1] / 3600;


		$lon = $lon0Arr[0] / $lon0Arr[1]
				+ $lon1Arr[0] / $lon1Arr[1] / 60
				+ $lon2Arr[0] / $lon2Arr[1] / 3600;
  // $exif['GPS']['GPSLatitude']を使用する測地系に変換して格納
	}
	echo 'latlng: ' .  $lat . ',' . $lon . '<br>';

	//写真の撮影時刻取得
	echo '撮影時刻: ' . $exif['EXIF']['DateTimeOriginal'] . '<br>';
	$photoDateTime = DateTime::createFromFormat('Y:m:d H:i:s', $exif['EXIF']['DateTimeOriginal']);

	// echo $photoDateTime;

	// echo date('Y-m-d', $photoDateTime) . '<br>';
	// echo $exif['EXIF']['DateTimeOriginal'];

	$exif_data = "";
    // Exif情報の解析
	foreach ($exif as $key => $section) {
		foreach ($section as $name => $value) {
			if (is_array($value)) {
				foreach($value as $k => $v) {
					$exif_data .= htmlspecialchars("$key.$name.$k: $v",
						ENT_QUOTES) . "<br />\n";
				}
			} else {
				$exif_data .= htmlspecialchars("$key.$name: $value",
					ENT_QUOTES) . "<br />\n";
			}
		}
	}
} else {
	$exif_data = "Exif情報がありません";
}

echo $exif_data;
?>

<p>情報ここまで</p>
</body>
</html>

<?php
//度分秒表記を角度に変換
function dms2degree( $pos_n , $pos_e ){
	$posN_a = explode( "." , $pos_n );
	$posE_a = explode( "." , $pos_e );
	$posN_a[2] = $posN_a[2] . "." .$posN_a[3];
	$posE_a[2] = $posE_a[2] . "." .$posE_a[3];
    $posN = $posN_a[0] + $posN_a[1]/60 + $posN_a[2]/3600;//北緯
    $posE = $posE_a[0] + $posE_a[1]/60 + $posE_a[2]/3600;//東経

    $gps['lat'] = $posN;
    $gps['lon'] = $posE;
    return $gps;
}
?>