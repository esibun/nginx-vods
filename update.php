<html>
	<head>
		<title>Past Recordings</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href='http://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="/css/style.css">
	</head>
	<body>
		<span id="container">
			<table id="containertable">
				<tr>
					<td id="borderleft"></td>
					<td id="content">
						<h1><img id="avatar" src="images/ava.png"> Past Recordings</h1>
						<hr>
						<p><?php 
function get_title() {
	global $mysql;
	$result = $mysql->query("SELECT cachekey, value, UNIX_TIMESTAMP(expiry) AS expiry FROM api_cache WHERE cachekey = 'media_status'");
	$row = $result->fetch_assoc();
	if ( isset($row['value'])) {
		$status = $row['value'];
		$expiry = $row['expiry'];
		$now = time();
		if ( $now > $expiry ) {
			$mysql->query("DELETE FROM api_cache WHERE cachekey='media_status'");
		}
		else {
			return $status;
		}
	}
	$c = curl_init("https://api.twitch.tv/kraken/channels/<yourusername>");
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	$r = json_decode(curl_exec($c));
	curl_close($c);
	$status = $r->status;
	$expiry = time()+60;
	$mysql->query("INSERT INTO api_cache (cachekey, value, expiry) VALUES ('media_status', '$status', FROM_UNIXTIME($expiry))");
	return $status;
}
$mysql = new mysqli("localhost", "<username>", "<password>", "vods");

if ( isset($_POST['call']) && isset($_POST['name']) ) {
	$status = $_POST['call'] == "publish" ? 1 : 0;
	$name = mysql_escape_string($_POST['name']);
	$expiry = time()+60;
	$mysql->query("UPDATE api_cache SET value = $status, expiry = FROM_UNIXTIME($expiry) WHERE cachekey = 'media_is_live'");
	$mysql->query("UPDATE api_cache SET value = '$name', expiry = FROM_UNIXTIME($expiry) WHERE cachekey = 'media_live_on'");
}

$videos = scandir("./videos");
$processed = 0;
$i = 2;

$result = $mysql->query("SELECT filename, duration FROM videos ORDER BY time DESC LIMIT 1");
$row = $result->fetch_assoc();
$vidduration = floor(exec("ffprobe -loglevel error -show_streams ./videos/".$row['filename'].".mp4 | grep duration | cut -f2 -d="));
if ( $vidduration != $row['duration'] ) {
	$rescan = true;
}
else {
	$rescan = false;
}
if ( $rescan ) {
	$result = $mysql->query("DELETE FROM videos ORDER BY time DESC LIMIT 1");
	passthru("rm -f ./thumbnails/".$row['filename'].".thumb.png");
}
while ( $processed < 5 ) {
	if ( isset($videos[$i]) ) {
		$vid = $videos[$i];
		if ( explode(".", $vid)[1] != "mp4" ) {
			print "Non-videos in directory, exiting.";
			break;
		}
		else {
			$name = explode(".", $vid)[0];
			$result = $mysql->query("SELECT filename, name, UNIX_TIMESTAMP(time) AS time, duration, thumbnail FROM videos WHERE filename = '".$name."'");
			$row = $result->fetch_assoc();
			if ( isset($row) ) {
				$i++;
				continue;
			}
			else {
				$tag = explode("-", $name)[0];
				if ( strpos($tag, "local") !== false || strpos($tag, "pulse") !== false ) {
					$i++;
					continue;
				}
				$vidtime = DateTime::createFromFormat("*-Y-m-d_H-i-s", $name)->format("U");
				$vidtitle = get_title();
				$vidduration = floor(exec("ffprobe -loglevel error -show_streams ./videos/$vid | grep duration | cut -f2 -d="));
				passthru("ffmpeg -i ./videos/$vid -vf \"thumbnail,scale=320:180\" -frames:v 1 ./thumbnails/$name.thumb.png", $ret);
				if ( $ret == 0 ) {
					$vidthumbnail = "yes";
				}
				else {
					$vidthumbnail = "no";
				}
				$result = $mysql->query("INSERT INTO videos VALUES ('$name', FROM_UNIXTIME('$vidtime'), '$vidtitle', '$vidduration',  '$vidthumbnail')");
				$processed++;
			}
			print $name."<br />";
		}
		$i++;
	}
	else {
		break;
	}
}
if ( $rescan ) {
	echo "Processed ".($processed-1)." new videos, rescanned 1.";
}
else {
	echo "Processed ".$processed." new videos.";
}
?></p>
					</td>
					<td id="borderright"></td>
				</tr>
			</table>
		</span>
	</body>
</html>