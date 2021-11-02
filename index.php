<?php

include "common.php";
include "config.php";

function set_popup($text) {
  return(" onmouseover=\"return overlib(".$text.");\" onmouseout=\"return nd();\"");
}

function main() {
  global $SUPPORT_DIR;
  include $SUPPORT_DIR."main_header.html";
  $PODCASTS = file("podcasts.txt");
  for ($i = 0; $i < count($PODCASTS); $i++) {
    if(!(strstr($PODCASTS{$i},"#"))) {
      $pinfo		= preg_replace("/\n/","",$PODCASTS{$i});
      $pinfo		= preg_replace("/\r/","",$pinfo);
      #(feed name, short title, web site, directory name, keep days, full title, passwd Required)
      $name		= PC_GetDetail($pinfo,"FeedName");
      $title		= PC_GetDetail($pinfo,"ShortTitle");
      $esc_title	= preg_replace("/'/","\'",$title);
      $url		= PC_GetDetail($pinfo,"URL");
      $keep		= PC_GetDetail($pinfo,"KeepDays");
      $longname		= preg_replace("/'/","\'",PC_GetDetail($pinfo,"FullName"));
      $text = "<a href=\'$url\'>$esc_title</a>";
      if(W_GetCurrentUserDetail("UsePopups")) {
        $full_popup = "'Visit the show\'s site: $text', CAPTION, '";
        $full_popup .= $longname;
        $full_popup .= "', STICKY, MOUSEOFF, WRAP, CELLPAD, 5";
        $full_popup = set_popup($full_popup);
        $text = "Files are kept for $keep days";
        $feed_popup = "'$text', CAPTION, 'Podcast feed for $longname', WRAP, CELLPAD, 5";
        $feed_popup = set_popup($feed_popup);
        $list_popup = "'$text', CAPTION, 'File listing for $longname', WRAP, CELLPAD, 5";
        $list_popup = set_popup($list_popup);
      }
      include $SUPPORT_DIR."main_line.html";
    }
  }
  include $SUPPORT_DIR."main_footer.html";
}

function list_files($feed,$format) {
  global $SUPPORT_DIR,$ROOTDIR;
  $nError = 0;
  $PODCASTS = file("podcasts.txt");
  $fnum = -1;
  for ($i = 0; $i < count($PODCASTS); $i++) {
    $name = PC_GetDetail($PODCASTS{$i},"FeedName");
    if($name == $feed) {
      $fnum = $i;
    }
  }
  if($nError==0) {
    if($fnum==-1) {
      $nError=2;
    }
  }
  if($nError==0) {
    if((PC_GetDetail($PODCASTS{$fnum},"ReqPW")=="Y")&&(!(W_ValidateUser()))) {
      header('WWW-Authenticate: Basic realm="podcasts"');
      header('HTTP/1.0 401 Unauthorized');
      echo 'Access to this podcast is restricted; a password is required.';
      exit;
    }
  }
  if($nError==0) {
    if($fnum >= 0) {
      switch($format) {
        case(""): $format = "xml";
        case("xml"):
        case("html"):
          list_files_in_feed($PODCASTS{$fnum},".$format");
          break;
        default:
          include $SUPPORT_DIR.'error_bad_feed.html';
          print "Error=".$nError;
          break;
      }
    } else {
      include $SUPPORT_DIR.'error_bad_feed.html';
      print "Error=".$nError;
    }
  } else {
    include $SUPPORT_DIR.'error_bad_feed.html';
    print "Error=".$nError;
  }
}

function newest($a, $b) 
{ 
    return filemtime($b) - filemtime($a); 
} 

function list_files_in_feed($pinfo,$ext) {
  global $ROOTDIR, $HELPDIR, $SUPPORT_DIR, $host;

  $pinfo	= preg_replace("/\n/","",$pinfo);
  $pinfo	= preg_replace("/\r/","",$pinfo);
  $name		= PC_GetDetail($pinfo,"FeedName");
  $title	= PC_GetDetail($pinfo,"ShortTitle");
  $url		= PC_GetDetail($pinfo,"URL");
  $keep		= PC_GetDetail($pinfo,"KeepDays");
  $longname	= preg_replace("/'/","\'",PC_GetDetail($pinfo,"FullName"));
  $directory	= PC_GetDetail($pinfo,"Directory");
  $keep		= PC_GetDetail($pinfo,"KeepDays");
  $description	= PC_GetDetail($pinfo,"FullName");
  if(strpos($directory,"/")==0) {
    $usedir = $directory;
  } else {
    $usedir = $ROOTDIR.$directory;
  }
  $cd_retval = chdir($usedir);
  if(!$cd_retval) {
    # could not cd to dir
    exit;
  }
  $dir = glob("*"); // put all files in an array 
  uasort($dir, "newest"); // sort the array by calling newest() 
  $nCount=0;
  $i=0;                 
  #while ($entry = $d->read()) { 
  foreach ($dir as $entry) { 
    #if(is_file($usedir."/".$entry)) {
    if(is_file($entry)) {
      switch($entry) {
        case(".DS_Store"):
        case("index.html"):
        case("index.php"):
	  break;
        default:
          $files[$i] = $entry;
          $i++;               
	  $nCount++;
      }
    }
  }             
  #$nCount = count($files);
  if($nCount>0) {
    #$stat_arr = stat($usedir."/".$files[$i-1]);
    $stat_arr = stat($files[$i-1]);
    $mtime = $stat_arr[9];
    $ctime = $stat_arr[10];
    $datestring = datestring($mtime);
  } else {
    $datestring = datestring(time());
  }
  include $SUPPORT_DIR."list_files_header".$ext;
  if((is_blackout_period())&&(!(is_local_network()))&&(!(is_bypass_network()))&&(PC_GetDetail($pinfo,"IsLocal")=="Y")) {
    // if we're in the blackout period and it's not a local request and this is a local podcast
    $nCount = -1;
  }
  for($i=$nCount-1;$i>=0;$i--) {
    #$fname = $usedir."/".$files[$i];
    $fname = $files[$i];
    $stat_arr = stat($fname);
    $size = $stat_arr[8];
    $fname_for_url = urlencode($files[$i]);
    $fname_for_url = preg_replace("/ /","%20",$files[$i]);
    $stat_arr = stat($fname);
    $size = $stat_arr[7];
    $mtime = $stat_arr[9];
    $ctime = $stat_arr[10];
    $ismp3 = strpos($fname,"mp3");
    if($ismp3) {
        $filetype = "mp3";
    } else {
        $filetype = "m4a";
    }
    if(PC_GetDetail($pinfo,"IsLocal")=="Y") {
      $url  = "https://$host/podcasts/index-new.php&#63;file=".PC_GetDetail($pinfo,"Directory")."/".$fname_for_url;
      $url  = "https://$host/$directory/$fname_for_url";
      $url  = "https://$host/$directory/$fname_for_url";
      $guid = $url;
      $guid = $fname_for_url;
    } else {
      $url  = PC_GetDetail($pinfo,"FileURL")."/".$fname_for_url;
      $guid = $url;
      $guid = $fname_for_url;
    }
    $datestring = datestring($mtime);
    $longname = preg_replace("/'/","\'",PC_GetDetail($pinfo,"FullName"));
    $pdate = preg_replace("/'/","\'",$datestring);
    $popup = "'$pdate', CAPTION, '$longname', WRAP, CELLPAD, 5";
    if(W_GetCurrentUserDetail("UsePopups")) {
      $popup = set_popup($popup);
    } else {
      $popup = "";
    }
    include $SUPPORT_DIR."list_files_line".$ext;
  }
  include $SUPPORT_DIR."list_files_footer".$ext;
}

$podcast = (isset($_GET["podcast"]) ? $_GET["podcast"] : null);
$format  = (isset($_GET["format"])  ? $_GET["format"]  : null);
W_SetCurrentUserDetail();
if($podcast == "") {
  main();
} else {
  list_files($podcast,$format);
}

?>
