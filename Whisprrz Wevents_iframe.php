<!DOCTYPE html>
<?php
	$url = "http://108.174.197.216/Whisprrz_Wevents/public/";
	if(isset($_GET['url']))
		$url = $_GET['url'];
	if(!isset($url)) $url = "http://108.174.197.216/Whisprrz_Wevents/public/";
?>
<html>
<head>
<title>Whisprrz Wevents</title>
<link rel="shortcut icon" href="./_files/favicon_16.ico" type="image/x-icon">
<script type="application/javascript">

	function resizeIFrameToFitContent( iFrame ) {

		const vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
		const vh = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
		iFrame.width  = vw-25;
		iFrame.height = vh-25;
	}

	window.addEventListener('DOMContentLoaded', function(e) {

		var iFrame = document.getElementById( 'party_house' );
		resizeIFrameToFitContent( iFrame );

		// or, to resize all iframes:
		var iframes = document.querySelectorAll("iframe");
		for( var i = 0; i < iframes.length; i++) {
			resizeIFrameToFitContent( iframes[i] );
		}
	} );

</script>
</head>
<body>

<iframe id="party_house" src="<?php echo $url; ?>"></iframe>

</body>
</html>