#!/usr/bin/perl -w

# little script to generate image galleries for use with original PHP backend
# uses Gnome's zenity for user dialogs
# uses gdk-pixbuf-convert if available, otherwise convert (from ImageMagick)
# (c) 2003-2004 Jakub 'jimmac' Steiner, (c) 2003-2004 Colin Marquardt
# based on webgallery.pl by Tuomas Kuosmanen

use strict;
use warnings;
use FileHandle;

my $num_of_args = scalar @ARGV;

if (!@ARGV or ($num_of_args == 0)) {
   exec("zenity --error --title \"\" --text \"No args\n\nYou have to select images to work on.\"");
   exit;
}

my $GdkPixbufConvert = "gdk-pixbuf-convert";
my $Convert = "convert";
my $dir = "web-gallery";


# try to find a scaler program
my $scaler;
$scaler = `which $GdkPixbufConvert`;
if ($scaler eq "") {
    $scaler = `which $Convert`;
}
if ($scaler eq "") {
    exec("zenity --error --title \"Giving Up\" --text \"No scaling program\n\nYou need to have '$GdkPixbufConvert' or '$Convert' available.\"");
    exit;
}
chomp $scaler;

my @args = sort(@ARGV);
my $NumOfIncrements = 5; # 5 increments per file (as we are
			 # expecting to create 5 files for each
			 # image)
my $increment = 100 / ($num_of_args * $NumOfIncrements);
my $progress=0;
my $reply="";

sub make_dirs {
    my $ErrMsg;
    unless (-d "$dir") {
	mkdir("./$dir") or
	    $ErrMsg .= "Could not create './$dir'!\n";
    }
    unless (-d "$dir/thumbs") {
	mkdir("./$dir/thumbs") or
	    $ErrMsg .= "Could not create './$dir/thumbs'!\n";
    }
    unless (-d "$dir/lq") {
	mkdir("./$dir/lq") or
	    $ErrMsg .= "Could not create './$dir/lq'!\n";
    }
    unless (-d "$dir/mq") {
	mkdir("./$dir/mq") or
	    $ErrMsg .= "Could not create './$dir/mq'!\n";
    }
    unless (-d "$dir/hq") {
	mkdir("./$dir/hq") or
	    $ErrMsg .= "Could not create './$dir/hq'!\n";
    }
    unless (-d "$dir/comments") {
	mkdir("./$dir/comments") or
	    $ErrMsg .= "Could not create './$dir/comments'!\n";
    }
    unless (-d "$dir/zip") {
	mkdir("./$dir/zip") or
	    $ErrMsg .= "Could not create './$dir/zip'!\n";
    }
    if ($ErrMsg ne "") {
	exec("zenity --error --title \"Giving Up\" --text \"Fatal Error\n\n$ErrMsg\"");
	die "Errors occurred:\n$ErrMsg";
   }
}
make_dirs();

# ------------------------------------------------------------------------
open(PROGRESS,"| zenity --progress --auto-close --title=\"Scaling\" \\
   --text=\"Scaling images, please wait\"");
PROGRESS->autoflush(1);

my $i=1;
my $SetDirDate = 0;
foreach my $arg (@args) {
   if (-d $arg) { # argument is a directory, skip it
      $progress += ($increment * $NumOfIncrements);
      print PROGRESS "$progress\n";
      next;
   }
   my $FileType = `file "$arg"`;
   unless ($FileType =~ /image data/i) { # check for valid file type
      # maybe check for JPEG and PNG explicitly?
      #print $FileType;
      $progress += ($increment * $NumOfIncrements);
      print PROGRESS "$progress\n";
      next;
   }
   if ($SetDirDate == 0) { # we are looking at the first image
       $SetDirDate = (stat $arg)[9]; # get mtime
       if ($SetDirDate > 0) {
	   # (can also return -1 if strange mtime, don't use this
	   # time stamp then)
	   # set mtime of gallery directory to the one of the first
	   # image file:
	   $reply .= `touch -r "$arg" $dir`;
	   print "Setting mtime of $dir to $SetDirDate\n";
       } else {
	   # give it another try the next time around:
	   $SetDirDate = 0;
       }
   }
   # thumbnails
   $reply .= `$scaler -geometry 120x120 -quality 60 "$arg" $dir/thumbs/img-$i\.jpg 2>&1`;
   $progress += $increment;
   print PROGRESS "$progress\n";
   # LQ size
   $reply .= `$scaler -geometry 640x480 -quality 75 "$arg" $dir/lq/img-$i\.jpg 2>&1`;
   $progress += $increment;
   print PROGRESS "$progress\n";
   # MQ size
   $reply .= `$scaler -geometry 800x600 -quality 75 "$arg" $dir/mq/img-$i\.jpg 2>&1`;
   $progress += $increment;
   print PROGRESS "$progress\n";
   # HQ size (just copy the original)
   $reply .= `cp "$arg" $dir/hq/img-$i\.jpg 2>&1`;
   $progress += $increment;
   print PROGRESS "$progress\n";
   # comment
   open(COMM, ">$dir/comments/$i\.txt");
   print(COMM "<span>image $i</span>\n");
   close(COMM);
   $progress += $increment;
   print PROGRESS "$progress\n";
   $i++;

   # an error occurred:
   if ($reply ne "") {
       print PROGRESS "100\n";
       close(PROGRESS);
       exec("zenity --error --title \"Giving Up\" --text \"Fatal Error\n\n$reply\"");
       die("Error while scaling");
   }
}
print PROGRESS "100\n";
close(PROGRESS);

# ------------------------------------------------------------------------
open(PROGRESS, "| zenity --progress --pulsate --auto-close \\
--title \"Compressing\" --text \"Zipping images\"");
PROGRESS->autoflush(1);
print PROGRESS "1";
system("zip -R $dir/zip/mq.zip  $dir/mq/*.jpg");
system("zip -R $dir/zip/hq.zip  $dir/hq/*.jpg");
print PROGRESS "100\n";
close(PROGRESS);
