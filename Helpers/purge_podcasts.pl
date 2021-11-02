#!/usr/bin/env perl

$tRootDir = "/usr/local/www/apache24/podcasts";

open(INFILE,$tRootDir."podcasts.txt");
while(<INFILE>) {
  chomp;
  # find /Volumes/FW_Backup_Drive/BACKUPS/iMAC/ -type f -ctime +1 -exec rm {} \;
  # ch:Clark Howard:clarkhoward.com:Audio/ClarkHoward:10:Y:Y::The Clark Howard Show
  ($name,$title,$website,$tDir,$nSaveFor,$tIsLocal,$junk,$junk,$junk) = split(/:/,$_,9);
  if($tIsLocal eq "Y") {
    $tDestination = $tRootDir.$tDir;
    print "Processing $title ($nSaveFor days)\n\t$tDestination\n";
    open(FIND,"/bin/find $tDestination -type f -ctime +$nSaveFor -print |");
    while($find = <FIND>) {
      chomp($find);
      $cmd = "rm \"$find\"";
      system($cmd) unless ($find =~ /index.html/);
      print $cmd."\n" unless ($find =~ /index.html/);
    }
  } else {
    print "Skipping $title.\n";
  } 
}
close(INFILE);
