<?php

# BEGIN Configuration #########################

$ROOTDIR = "/usr/local/www/apache24/vhosts/podcasts.unixdude.net/";  # Root directory of podcast application
$HELPDIR = $ROOTDIR."Helpers/";			# Directory of supporting files (probably should be left alone)
$SUPPORT_DIR = $HELPDIR."html/";		# Location of HTML and XML files

$host = "podcasts.unixdude.net";			# Hostname
$ROOTURL = "https://".$host."/";	# Full URL to podcast application

# Specify the blackout filename; existence of this file signifies that
# the time-dependent blackout feature should be used
$BLACKOUT_FILE	= "/tmp/use_blackout";
$nBlackout_Start = 6;				# Blackout start time (e.g.  6=6am)
$nBlackout_Stop  = 23;				# Blackout  end  time (e.g. 23=11pm)

# Usernames and related passwords
$USERS		= array();
$PASSWORDS	= array();
$USE_POPUPS	= array(1);

# Bypass networks get blacked out, but don't require passwords
$BYPASS_NETS	= array("");		# 172.18.0.0, 65.161.183.0
$BYPASS_MASKS	= array("");		# 255.255.0.0, 255.255.255.128

# Local networks never get blacked out
#$LOCAL_NETS	= array("10.0.1.0");
#$LOCAL_MASKS	= array("255.255.255.0");
$LOCAL_NETS	= array("0.0.0.0");
$LOCAL_MASKS	= array("0.0.0.0");

# Users whose access is never blocked by a blackout, regardless of wherefrom they connect
$ADMIN_USERS	= array();

#  END  Configuration #########################

?>
