<?php

include "common.php";
include "config.php";

function send_file($file) {
  global $ROOTDIR;
  $file = urldecode($file);
  $nError=0;
  if($nError==0) {
    $tFilename = $ROOTDIR.$file;
    if(!$fdl=@fopen($tFilename,'r')){
      die("Invalid file specified: $file");
    } else {
      $slash = strrpos($file,"/");
      $attach_name = substr($file,$slash+1);
      header("HTTP/1.1 200 OK");
      header("Cache-Control: ");// leave blank to avoid IE errors
      header("Pragma: ");// leave blank to avoid IE errors
      # send correct headers based on filename
      $ext = substr($file,strrpos($file,'.')+1);
      switch($ext) {
        case("gif"):
          header("Content-type: image/gif");
          break;
        case("mp3"):
        case("m4a"):
          header("Content-type: application/octet-stream");
          header("Content-Disposition: attachment; filename=\"".$attach_name."\"");
          header("Content-length:".(string)(filesize($tFilename)));
          break;
      }
      sleep(1);
      fpassthru($fdl);
    }
  } else {
    print "Error: $nError; fnum: $fnum; podcast: $podcast; file: $file";
  }
}

if(W_ValidateUser()) {
  W_SetCurrentUserDetail();
  if(!is_blackout_period()) {
    $file = getenv("REQUEST_URI");
    send_file($file);
  } else {
    header("HTTP/1.1 403 Forbidden");
  ?>
  <html>
  <head>
  <title>Blackout period in effect</title>
  </head>
  <body>
  The episode you requested is not available right now because a blackout period is in effect.  Please try again later.
  </body>
  </html>
  <?php
  }
} else {
  header('WWW-Authenticate: Basic realm="podcasts"');
  header('HTTP/1.0 401 Unauthorized');
  echo 'Access to this podcast is restricted; a password is required.';
  exit;
}

?>
