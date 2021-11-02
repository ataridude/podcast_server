<html>
<head>
<title>Use blackout period?</title>
</head>
<body>
<?php

include "config.php";
include "common.php";

switch($use_blackout) {
  case("yes"):
    touch($BLACKOUT_FILE);
    #print ("Using blackout period.<P>");
    break;
  case("no"):
    if(is_file($BLACKOUT_FILE)) {
      unlink($BLACKOUT_FILE);
    }
    #print ("No longer using blackout period.<P>");
    break;
  case(""):
}

if(is_blackout_period()) {
  print ("Currently using blackout period.<P>");
} else {
  print ("Not currently using blackout period.<P>");
}
?>
<a href="?use_blackout=yes">Use blackout period</a>
<P>
<a href="?use_blackout=no">Do not use blackout period</a>
<P>
<a href="index.php">Podcasts</a>
</body>
</html>
