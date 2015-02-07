<?php
/*
*addDb.php
*
*DBに写真を追加するプログラム
*/
date_default_timezone_set('Asia/Tokyo');
require_once('config.php');

//アップロードファイル解析
$tmpFileName = $_FILES['upPhoto']['tmp_name'];
$inputFileName = $_FILES['upPhoto']['tmp_name'] . '_fixed';
$ext = $_FILES['upPhoto']['type'];

//アップロードファイルの向き修正
orientationFixedImage($inputFileName, $tmpFileName);

$exif = @exif_read_data($tmpFileName, 0, true);
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

		$photoDateTime = DateTime::createFromFormat('Y:m:d H:i:s', $exif['EXIF']['DateTimeOriginal']);
		$datetimeStr = $photoDateTime->format('Y/m/d h:i:s');
	}
	else{
		echo "<p>GPS情報がありません</p>";
		echo '<a href="up_photo.php">元の画面に戻る</a>';
		exit();
	}
}else{
		echo "<p>Exif情報がありません</p>";
		echo '<a href="up_photo.php">元の画面に戻る</a>';
		exit();
}

addDb($inputFileName, $ext, $lat, $lon, $datetimeStr);
header('Location: up_photo.php');

function addDb($fileName, $ext, $lat, $lon, $datetime){
	$sql = "INSERT INTO t_picture (picture, ext, location, datetime)
			VALUES (:file, :ext, GeomFromText(:loc), :datetime)";
			// values (:file, :ext, GeomFromText( 'POINT( :loc )' ), :datetime)";
	$dsn = 'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_NAME;
	try {
		$dbh = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
	} catch (PDOExeption $e){
		exit($e->getMessage());
	}

	$stmt = $dbh->prepare($sql);
	$fp = fopen($fileName, 'rb');
	$stmt->bindValue(':file', $fp, PDO::PARAM_LOB);
	$stmt->bindValue(':ext', $ext);
	// $stmt->bindValue(':lat', $lat);
	// $stmt->bindValue(':lon', $lon);
	$stmt->bindValue(':loc', "POINT($lon  $lat)");
	$stmt->bindValue(':datetime', $datetime);

	$flag = $stmt->execute();
	$dbh=null;
}


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

// 画像の左右反転
function image_flop($image){
    // 画像の幅を取得
	$w = imagesx($image);
    // 画像の高さを取得
	$h = imagesy($image);
    // 変換後の画像の生成（元の画像と同じサイズ）
	$destImage = @imagecreatetruecolor($w,$h);
    // 逆側から色を取得
	for($i=($w-1);$i>=0;$i--){
		for($j=0;$j<$h;$j++){
			$color_index = imagecolorat($image,$i,$j);
			$colors = imagecolorsforindex($image,$color_index);
			imagesetpixel($destImage,abs($i-$w+1),$j,imagecolorallocate($destImage,$colors["red"],$colors["green"],$colors["blue"]));
		}
	}
	return $destImage;
}
// 上下反転
function image_flip($image){
    // 画像の幅を取得
	$w = imagesx($image);
    // 画像の高さを取得
	$h = imagesy($image);
    // 変換後の画像の生成（元の画像と同じサイズ）
	$destImage = @imagecreatetruecolor($w,$h);
    // 逆側から色を取得
	for($i=0;$i<$w;$i++){
		for($j=($h-1);$j>=0;$j--){
			$color_index = imagecolorat($image,$i,$j);
			$colors = imagecolorsforindex($image,$color_index);
			imagesetpixel($destImage,$i,abs($j-$h+1),imagecolorallocate($destImage,$colors["red"],$colors["green"],$colors["blue"]));
		}
	}
	return $destImage;
}
// 画像を回転
function image_rotate($image, $angle, $bgd_color){
	return imagerotate($image, $angle, $bgd_color, 0);
}

// 画像の方向を正す
function orientationFixedImage($output,$input){
	$image = ImageCreateFromJPEG($input);
	$exif_datas = @exif_read_data($input);
	if(isset($exif_datas['Orientation'])){
		$orientation = $exif_datas['Orientation'];
		if($image){
                  // 未定義
			if($orientation == 0){
                  // 通常
			}else if($orientation == 1){
                  // 左右反転
			}else if($orientation == 2){
				image_flop($image);
                  // 180°回転
			}else if($orientation == 3){
				image_rotate($image,180, 0);
                  // 上下反転
			}else if($orientation == 4){
				image_Flip($image);
                  // 反時計回りに90°回転 上下反転
			}else if($orientation == 5){
				image_rotate($image,270, 0);
				image_flip($image);
                  // 時計回りに90°回転
			}else if($orientation == 6){
				image_rotate($image,90, 0);
                  // 時計回りに90°回転 上下反転
			}else if($orientation == 7){
				image_rotate($image,90, 0);
				image_flip($image);
                  // 反時計回りに90°回転
			}else if($orientation == 8){
				image_rotate($image,270, 0);
			}
		}
	}
    // 画像の書き出し
	ImageJPEG($image ,$output);
	return false;
}