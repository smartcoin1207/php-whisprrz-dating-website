<!DOCTYPE html>
<?php
	header('X-Frame-Options: GOFORIT'); 
	$url = "https://meet.partyhouz.com7/";
	if(isset($_GET['url']))
		$url = $_GET['url'];
	if(!isset($url)) $url = "https://meet.partyhouz.com/";
?>
<html>
<head>
<title>Whisprrz Party Meeting</title>
<link rel="shortcut icon" href="./_files/favicon.ico" type="image/x-icon">
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
<blockquote class="quote">
<iframe id="party_house" src="<?php echo $url; ?>"></iframe>
</blockquote>
</body>
</html>