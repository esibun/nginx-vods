<?php
$mysql = new mysqli("localhost", "<username>", "<password>", "vods");

function get_live_status() {
	global $mysql;
	$result = $mysql->query("SELECT value FROM api_cache WHERE cachekey = 'media_is_live'");
	$row = $result->fetch_assoc();
	if ( isset($row['value']) AND $row['value'] == "1" ) {
		$result = $mysql->query("SELECT value FROM api_cache WHERE cachekey = 'media_live_on'");
		$row = $result->fetch_assoc();
		if ( isset($row['value']) ) {
			switch ( $row['value'] ) {
				case "<streamkey>":
					return 'online on <a href="//twitch.tv/yourusername">&lt;yourusername&gt;</a> (twitch)';
				default:
					return "offline";
			}
		}
	}
	else {
		return "offline";
	}
}
function get_vod_list() {
	global $mysql;
	$result = $mysql->query("SELECT filename, name, UNIX_TIMESTAMP(time) AS time, duration, thumbnail FROM videos ORDER BY time DESC");
	$row = $result->fetch_all(MYSQLI_ASSOC);
	return $row;
}
function thumbnail($video) {
	global $mysql;
	if ( !is_array($video) ) {
		$result = $mysql->query("SELECT filename, thumbnail FROM videos WHERE filename = '".$video."'");
		$video = $result->fetch_assoc();
	}
	if ( $video['thumbnail'] == "yes" ) {
		return "thumbnails/".$video["filename"].".thumb.png";
	}
	else {
		return "images/nothumbnail.png";
	}
}
function lookup_full_name($filename) {
	global $mysql;
	$result = $mysql->query("SELECT filename, name FROM videos WHERE filename = '".$filename."'");
	$row = $result->fetch_assoc();
	return $row['name'];
}
function get_duration($file) {
	return floor(exec("ffprobe -loglevel error -show_streams $file | grep duration | cut -f2 -d="));
}
?><html>
	<head>
		<title>Past Recordings</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href='http://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="/css/style.css">
<?php if ( isset($_GET['vid']) ) { ?>		<link href="http://vjs.zencdn.net/4.7/video-js.css" rel="stylesheet">
		<script src="http://vjs.zencdn.net/4.7/video.js"></script>
<?php } ?>	</head>
	<body>
		<span id="container">
			<table id="containertable">
				<tr>
					<td id="borderleft"></td>
					<td id="content">
						<?php if ( !isset($_GET['vid']) ) { ?><h1><img id="avatar" src="images/ava.png"> Past Recordings</h1>
						<hr>
						<p>live status: <?php echo get_live_status(); ?></p>
	<?php foreach ( get_vod_list() as $video ) { ?>					<div class="video">
							<div class="thumbnailbg">
								<a href="?vid=<?php echo $video['filename']; ?>"><img src="<?php echo thumbnail($video); ?>"></a>
							</div>
							<div class="videoinfo">
								<div class="videotitle">
									<a href="?vid=<?php echo $video['filename']; ?>"><?php echo $video['name']; ?></a>
								</div>
								<div class="videoduration"><?php $temp = gmdate("H:i:s", $video['duration']); 
								echo $temp; ?></div>
								<div class="videodate"><?php echo date("n/j/Y g:i:s A", $video['time']); ?></div>
								<div class="videolink"><a href="/videos/<?php echo $video['filename']; ?>.mp4">Direct Link</a></div>
							</div>
						</div>
<?php } } else { ?><h1><img id="avatar" src="images/ava.png"> esi's Past Recordings</h1>
						<hr>
						<h2><?php echo lookup_full_name(mysql_escape_string($_GET['vid'])); ?></h2>

						<video id="vod" class="video-js vjs-default-skin" controls autoplay preload="auto" poster="<?php echo thumbnail(mysql_escape_string($_GET['vid'])); ?>" width="75vw" height="42.1875vw" data-setup='{"nativeControlsForTouch": false}'>
 							<source src="videos/<?php echo htmlspecialchars($_GET['vid']); ?>.mp4" type='video/mp4'>
							<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
						</video>
						<p class="backbutton"><a href="/">&lt;-- Back</a></p>
<?php } ?>					</td>
					<td id="borderright"></td>
				</tr>
			</table>
		</span>
	</body>
</html>