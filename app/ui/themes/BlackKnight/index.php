<?php
//Load all the Vue JS templates into file.

function load_vue_templates($folder_name, $ui_folder, $theme_url, $HOST) {
    $path = $ui_folder . '/' . $theme_url . $folder_name.'/';

    $dir = new \DirectoryIterator($path);
//todo move this directory into the view controller with an event for plugins to hook into.
    $retVal = array();
//$i = new auth();

//get the names
    $name = array();
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $names[] = $fileinfo->getFilename();
        }
    }

//set up the templates based on the names
    foreach ($names as $name) {
        echo '<script type="text/x-template" id="' . str_replace('.template', '', $name) . '">';
        include($path . $name);
        echo '</script>';
    }

//Set up the basic components based on the names
//these will get extended in the js app file
    foreach ($names as $name) {
        $sname = str_replace('.template', '', $name);
        echo "  <script type='application/javascript'>
                    Vue.component('$sname', {
                    template: '#$sname'
                })
                </script>";
    }
}

//Cheating here by doing the routing via php instead of Vue JS.
//Todo move this theme routing from PHP to Vue JS.
$r = explode("/",$URI);
array_shift($r);
$page = $r[1];
if(empty($r[1])) {
    $page = "frontpage";
} else if(!file_exists($UI . $ADMIN_THEME_URL .'pages/'.$r[1].'.template')) {
    $page = "unknown";
} ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>TADL - Black Knight Theme</title>
    <script src="https://unpkg.com/vue"></script>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Bootstrap Core CSS -->
    <link href="//<?php echo $HOST; ?>/BlackKnight/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="//<?php echo $HOST; ?>/BlackKnight/assets/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="//<?php echo $HOST; ?>/BlackKnight/assets/css/blackknight.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="//<?php echo $HOST; ?>/BlackKnight/assets/vendor/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="//<?php echo $HOST; ?>/BlackKnight/assets/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<div id="wrapper">
    <navigation></navigation>
    <?php echo "<$page></$page>"; ?>
    <foot></foot>

</div>

<?php
    load_vue_templates('pages', $UI, $ADMIN_THEME_URL, $HOST);
    load_vue_templates('components', $UI, $ADMIN_THEME_URL, $HOST);
?>

<!-- jQuery -->
<script src="//<?php echo $HOST; ?>/BlackKnight/assets/vendor/jquery/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="//<?php echo $HOST; ?>/BlackKnight/assets/vendor/bootstrap/js/bootstrap.min.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="//<?php echo $HOST; ?>/BlackKnight/assets/vendor/metisMenu/metisMenu.min.js"></script>

<!-- Morris Charts JavaScript-->
<script src="//<?php echo $HOST; ?>/BlackKnight/assets/vendor/raphael/raphael.min.js"></script>
<script src="//<?php echo $HOST; ?>/BlackKnight/assets/vendor/morrisjs/morris.min.js"></script>
<script src="//<?php echo $HOST; ?>/BlackKnight/assets/data/morris-data.js"></script>

<!-- Custom Theme JavaScript -->
<script src="//<?php echo $HOST; ?>/BlackKnight/assets/js/sb-admin-2.js"></script>

<script src="//<?php echo $HOST; ?>/BlackKnight/assets/js/app.js"></script>


</body>
</html>