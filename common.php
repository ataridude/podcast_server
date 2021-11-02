<?php

global $BLACKOUT_FILE;

date_default_timezone_set("America/New_York");

$DEBUG = 1;
$gtUsername = "";
$gyUsePopups = true;

if(is_file($BLACKOUT_FILE)) {
  $yUseBlackout = true;
} else {
  # set to true to always have a blackout
  # set to false to use the blackout file
  $yUseBlackout = false;
}

function W_ValidateUser() {
  global $PHP_AUTH_USER,$PHP_AUTH_PW,$USERS,$PASSWORDS,$yUseBlackout;
  $u = $_SERVER["PHP_AUTH_USER"];
  $p = $_SERVER["PHP_AUTH_PW"];

  $nReturn = 0;
  if($u != "") {
    for($nLoop=0;$nLoop<sizeof($USERS);$nLoop++) {
      if((!strcmp($u,$USERS[$nLoop])) && (!strcmp($p,$PASSWORDS[$nLoop]))) {
        $nReturn = 1;
      }
    }
  } else {
    if((is_bypass_network()) && (!$yUseBlackout)) {
      $nReturn=1; # no password needed for bypass networks, when no blackout is in effect
    }
    if(is_local_network()) $nReturn=1; # no password needed for local networks, ever
  }
  return($nReturn);
}

function is_blackout_period() {
  global $nBlackout_Start,$nBlackout_Stop,$yUseBlackout,$ADMIN_USERS;
  $remote = $_SERVER["REMOTE_ADDR"];
  $nRetval = 0;
  $time_arr = localtime(time(),1);
  $hour = $time_arr["tm_hour"];
  if(($hour>=$nBlackout_Start)&&($hour<$nBlackout_Stop)) {
    $nRetval = 1;
  }
  # return 0 if we're not in a blackout
  if(!($yUseBlackout)) {
    $nRetval = 0;
  }
  # return 0 if we're on the local subnet
  if(is_local_network()) {
    $nRetval = 0; # never black out the "local" subnet
  }
  # return 0 for user admin users (admin users have no blackout)
  $tCurrentUser = W_GetCurrentUserDetail("Username");
  if((sizeof($ADMIN_USERS)>0)&&(strcmp($tCurrentUser,""))) {
    for($nLoop=0;$nLoop<sizeof($ADMIN_USERS);$nLoop++) {
      if(!strcmp($tCurrentUser,$ADMIN_USERS[$nLoop])) {
        $nRetval = 0;
      }
    }
  }
  return($nRetval);
}

function is_local_network() {
  global $LOCAL_NETS,$LOCAL_MASKS;
  $remote = $_SERVER["REMOTE_ADDR"];
  $retval = false;
  for($nLoop=0;$nLoop<sizeof($LOCAL_NETS);$nLoop++) {
    if(ipcompare($remote,$LOCAL_NETS[$nLoop],$LOCAL_MASKS[$nLoop])) {
      $retval = true;
    }
  }
  return($retval);
}

function is_bypass_network() {
  global $BYPASS_NETS,$BYPASS_MASKS,$yUseBlackout;
  $remote = $_SERVER["REMOTE_ADDR"];
  $retval = false;
  if(!($yUseBlackout)) { # if we're not in a blackout, see if this is a bypass network (no password required)
    for($nLoop=0;$nLoop<sizeof($BYPASS_NETS);$nLoop++) {
      if(ipcompare($remote,$BYPASS_NETS[$nLoop],$BYPASS_MASKS[$nLoop])) {
        $retval = true;
      }
    }
  }
  return($retval);
}

function ipcompare ($ip1, $ip2, $mask) {
  $masked1 = ip2long($ip1) & ip2long($mask); // bitwise AND of $ip1 with the mask
  $masked2 = ip2long($ip2) & ip2long($mask); // bitwise AND of $ip2 with the mask
  return($masked1 == $masked2);
}

function datestring($dtime) {
  # "Sun, 5 Feb 2005 18:05:05 EST";
  $dformat = "D, j M Y H:i:s T";
  return(date($dformat,$dtime));
}

function W_SetCurrentUserDetail() {
  global $gtUsername,$gyUsePopups,$PHP_AUTH_USER,$USERS,$USE_POPUPS;
  if(isset($_SERVER["PHP_AUTH_USER"])) {
    $gtUsername = $_SERVER["PHP_AUTH_USER"];
    $nWhere = array_search($gtUsername,$USERS);
    $gyUsePopups = $USE_POPUPS[$nWhere];
  } else {
    $gtUsername = "anonymous";
    $gyUsePopups = true;
  }
}

function W_GetCurrentUserDetail($tDetail) {
  global $gtUsername,$gyUsePopups;
  $tReturn = "";
  switch($tDetail) {
    case("Username"):
      $tReturn = $gtUsername;
      break;
    case("UsePopups"):
      $tReturn = $gyUsePopups;
      break;
  }
  return($tReturn);
}

function PC_GetDetail($pinfo,$tDetail) {
  #(feed name, short title, web site, directory name, keep days, ReqPW, local, file URL, full title)
  # ch:Clark Howard:clarkhoward.com:Audio/ClarkHoward:10:Y:Y::The Clark Howard Show
  # 0 = feedname, 1= short title, 2= website, 3=dir, 4=keep days, 5= requires PW, 6=Is Local, 7= File URL, 8= long title

  global $yUseBlackout;

  $podcast_arr	= preg_split("/:/",$pinfo,9);
  switch($tDetail) {
    case("FeedName"):
      $tReturn = $podcast_arr[0];
      break;
    case("ShortTitle"):
    case("ShortName"):
      $tReturn = $podcast_arr[1];
      break;
    case("Website"):
    case("URL"):
      $tReturn = "http://".$podcast_arr[2];
      break;
    case("Directory"):
    case("Dir"):
      $tReturn = $podcast_arr[3];
      break;
    case("KeepDays"):
    case("Keep"):
      $tReturn = $podcast_arr[4];
      break;
    case("ReqPW"):
      $tReturn = $podcast_arr[5];		# default to podcast setting
      if(is_local_network()) $tReturn="N";	# local networks don't require passwords
      if((is_bypass_network()) && (!$yUseBlackout)) {
        $tReturn="N";				# bypass networks don't require passwords if we're not in a blackout
      }
      $tReturn="N";
      break;
    case("Local"):
    case("IsLocal"):
      $tReturn = $podcast_arr[6];
      break;
    case("FileURL"):
      if(PC_GetDetail($pinfo,"IsLocal")=="N") {
        $tReturn = "http://".$podcast_arr[7];
      } else {
        $tReturn = "";
      }
      break;
    case("FullTitle"):
    case("FullName"):
    case("Description"):
      $tReturn = $podcast_arr[8];
      break;
  }
  return($tReturn);
}

function DEBUG($text) {
  global $DEBUG;
  if(($DEBUG) && (is_local_network())) {
    echo $text;
  }
}

?>
