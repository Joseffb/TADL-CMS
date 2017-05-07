<?php
	//Cheating here by doing the routing via php instead of Vue JS.
	//Todo move this theme routing from PHP to Vue JS.
	$r = explode( "/", $URI );
	array_shift( $r );
	$page = $r[0];
	$page = str_replace(array(".html", ".htm",".asp",".jsp",".go",".php"),"",$page);
	if ( empty( $page ) || $page == "index" ) {
		$page = "frontpage";
	} else if ( ! file_exists( $UI . $SITE_THEME_URL . 'pages/' . $page. '.template' ) ) {
		$page = "default";
	} ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport"
	      content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>TADL Alice Theme</title>

	<!-- Bootstrap core CSS -->
	<link
		href="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/vendor/bootstrap/css/bootstrap.min.css"
		rel="stylesheet">

	<!-- Custom fonts for this template -->
	<link
		href="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/vendor/font-awesome/css/font-awesome.min.css"
		rel="stylesheet" type="text/css">
	<link
		href='https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic'
		rel='stylesheet' type='text/css'>
	<link
		href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800'
		rel='stylesheet' type='text/css'>

	<!-- Custom styles for this template -->
	<link
		href="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/css/alice.css"
		rel="stylesheet">

	<!-- Temporary navbar container fix -->
	<style>
		.navbar-toggler {
			z-index: 1;
		}

		@media (max-width: 576px) {
			nav > .container {
				width: 100%;
			}
		}
	</style>	<?php echo View::instance()->raw($THEME_JS); ?>
	<script src="https://unpkg.com/vue"></script>
    <script src="https://cdn.jsdelivr.net/vue.resource/1.3.1/vue-resource.min.js"></script>

</head>

<body>
<div id="vjs">
	<navigation></navigation>
	<masthead></masthead>
	<!-- Main Content -->
	<?php echo "<$page></$page>"; ?>
	<bottom></bottom>
</div>
<!-- TADL-Bootstrap JavaScript -->
<?php
    echo \controllers\theme::load_vue_js( 'pages', $UI, $SITE_THEME_URL, $HOST );
    echo \controllers\theme::load_vue_js( 'components', $UI, $SITE_THEME_URL, $HOST );
?>

<script
	src="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/vendor/jquery/jquery.min.js"></script>
<script
	src="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/vendor/tether/tether.min.js"></script>
<script
	src="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/vendor/bootstrap/js/bootstrap.min.js"></script>

<!-- Custom scripts for this template -->
<script
	src="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/js/alice.js"></script>
<script src="http://cms.dev/cp/load.js"></script>
</body>

</html>