<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Frontend TEST</title>
    <script src="https://unpkg.com/vue"></script>

</head>
<body>
<div id="app">
    {{ message }}
</div>

<?php
//Load all the Vue JS templates into file.
$path = $UI . '/' . $ADMIN_THEME_URL . 'parts/';

$dir = new \DirectoryIterator($path);
$retVal = array();
//$i = new auth();

//get the names
$name = array();
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $names[] = $fileinfo->getFilename();
    }
}

//Set up the basic components based on the names
//these will get extended in the js app file
foreach ($names as $name) {
        $sname = str_replace('.template','',$name);
        echo "  <script type='application/javascript' id='$sname'>
                    Vue.component('$sname', {
                    template: '#$sname'
                })</script>";
}

//set up the templates based on the names
foreach ($names as $name) {
        echo '<script type="text/x-template" id="'. str_replace('.template','',$name) .'">';
        include($path . $name);
        echo '</script>';
}
?>
<script src="/RedQueen/assets/js/app.js"></script>


</body>
</html>