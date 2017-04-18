<?php
	//Load all the Vue JS templates into file.

	function load_vue_templates( $folder_name, $ui_folder, $theme_url, $HOST ) {
		$path = $ui_folder . '/' . $theme_url . $folder_name . '/';

		$dir = new \DirectoryIterator( $path );
//todo move this directory into the view controller with an event for plugins to hook into.
		$retVal = array();
//$i = new auth();

//get the names
		$name = array();
		foreach ( $dir as $fileinfo ) {
			if ( ! $fileinfo->isDot() ) {
				$names[] = $fileinfo->getFilename();
			}
		}

//set up the templates based on the names
		foreach ( $names as $name ) {
			echo '<script type="text/x-template" id="' . str_replace( '.template', '', $name ) . '">';
			include( $path . $name );
			echo '</script>';
		}

//Set up the basic components based on the names
//these will get extended in the js app file
		foreach ( $names as $name ) {
			$sname = str_replace( '.template', '', $name );
			echo " <script type='application/javascript'>
                    Vue.component('$sname', {
                    props: ['TADL'],
                    template: '#$sname'
                })
                </script>";
		}
	}

	//Cheating here by doing the routing via php instead of Vue JS.
	//Todo move this theme routing from PHP to Vue JS.
	$r = explode( "/", $URI );
	array_shift( $r );
	$page = $r[1];
	if ( empty( $r[1] ) ) {
		$page = "frontpage";
	} else if ( ! file_exists( $UI . $ADMIN_THEME_URL . 'pages/' . $r[1] . '.template' ) ) {
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

	<title>Clean Blog - Start TADL Bootstrap Theme</title>

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
		href="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/css/clean-blog.min.css"
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
	load_vue_templates( 'pages', $UI, $SITE_THEME_URL, $HOST );
	load_vue_templates( 'components', $UI, $SITE_THEME_URL, $HOST );
?>

<script
	src="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/vendor/jquery/jquery.min.js"></script>
<script
	src="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/vendor/tether/tether.min.js"></script>
<script
	src="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/vendor/bootstrap/js/bootstrap.min.js"></script>

<!-- Custom scripts for this template -->
<script
	src="//<?php echo $HOST; ?>/<?php echo $SITE_THEME; ?>/js/clean-blog.js"></script>
</body>

</html>