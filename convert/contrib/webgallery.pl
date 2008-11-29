#!/usr/bin/perl -w
#
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                       #
#  Web Gallery script for Nautilus - Depends on "original" web backend  #
#  for displaying the gallery.                                          #
#                                                                       #
#  Written in perl because I suck more with sh scripting.               #
#  Also needs gnome-utils for gdialog.                                  #
#                                                                       #
#  Hacked together by Tuomas Kuosmanen <tigert@ximian.com>              #
#  Tweaked to use NetPBM by Jakub Steiner <jimmac@ximian.com>           #
#  gtk2 port by Jan Girlich -- vollkorn freenet de                      #
#  Released under the GPL license.                                      #
#                                                                       #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # 

die "No files to convert" unless @ARGV;

use Gtk2;
init Gtk2;

@files = sort(@ARGV);

my $dir = "web-gallery";

sub make_dirs {

	 unless (-d "$dir") {
		  mkdir("./$dir") or die "Aargh.\n";
	 }
	 unless (-d "$dir/thumbs") {
		  mkdir("./$dir/thumbs") or die "Aargh.\n";
	 }
	 unless (-d "$dir/lq") {
		  mkdir("./$dir/lq") or die "Aargh.\n";
	 }
	 unless (-d "$dir/mq") {
		  mkdir("./$dir/mq") or die "Aargh.\n";
	 }
	 unless (-d "$dir/hq") {
		  mkdir("./$dir/hq") or die "Aargh.\n";
	 }
	 unless (-d "$dir/comments") {
		  mkdir("./$dir/comments") or die "Aargh.\n";
	 }
}

sub make_gallery_fake {
	 my $foo = shift;
	 print ("PARAM: $foo\n");
	 sleep 1;
}


$w = new Gtk2::Window;
$label = new Gtk2::Label(' Web Gallery generation in progress... ');
$pbar = new Gtk2::ProgressBar;
$vb = new Gtk2::VBox(0, 0);
$b = new Gtk2::Button('Cancel');
$w->add($vb);
$vb->add($label);
$vb->add($pbar);
$vb->add($b);

$b->signal_connect('clicked', sub {exit(1)});
$w->signal_connect('destroy', sub {exit(1)});

$w->show_all();
$i = 0;
$pbar->set_fraction($i);

@files = sort(@ARGV);
$num_of_files = scalar @files;
$increment = 1 / ( 5 * $num_of_files );
$i = 1;
$progress = 0;

make_dirs();
#read nautilus metafile if available
open(METAXML, "./.nautilus-metafile.xml") or print("no metafile");
@meta_xml = <METAXML>;
close(METAXML);


foreach $file (@files) {
	 
	 $pbar->set_fraction($progress);
	 $pbar->set_text("$i / $num_of_files");
	 $progress += $increment;
	 while (Gtk2->events_pending) {
		  Gtk2->main_iteration;
	 }

	 # do the stuff, collect error messages to a variable.
	 #$reply=`convert -geometry 120x120 -colors 64 -dither $file $dir/thumbs/img-$i\.png 2>&1`;
	 $reply=`convert -geometry 120x120 $file $dir/thumbs/img-$i\.jpg 2>&1`;
	 #$reply =`jpegtopnm $file | pnmscale -xysize 120 120 | ppmquant -floyd 16 | pnmtopng -interlace -compression 9 > $dir/thumbs/img-$i\.png 2>&1`;

	 $pbar->set_fraction($progress);
	 $progress += $increment;
	 while (Gtk2->events_pending) {
		  Gtk2->main_iteration;
	 }

	 $reply = $reply . `convert -geometry 640x640 $file $dir/lq/img-$i.jpg 2>&1`;
	 #$reply = $reply . `jpegtopnm $file | pnmscale -xysize 640 640 | ppmtojpeg --quality 80 --progressive > $dir/lq/img-$i.jpg 2>&1`;

	 $pbar->set_fraction($progress);
	 $progress += $increment;
	 while (Gtk2->events_pending) {
		  Gtk2->main_iteration;
	 }

	 $reply = $reply . `convert -geometry 800x800 $file $dir/mq/img-$i.jpg 2>&1`;
	 #$reply = $reply . `jpegtopnm $file | pnmscale -xysize 800 800 | ppmtojpeg --quality 80 --progressive > $dir/mq/img-$i.jpg 2>&1`;

	 $pbar->set_fraction($progress);
	 $progress += $increment;
	 while (Gtk2->events_pending) {
		  Gtk2->main_iteration;
	 }

	 $reply = $reply . `cp $file $dir/hq/img-$i.jpg 2>&1`;

	 $pbar->set_fraction($progress);
	 $progress += $increment;
	 while (Gtk2->events_pending) {
	 	  Gtk2->main_iteration;
	 }
	 
	 # comment
	 open (COMM, ">$dir/comments/$i\.txt");
	 print(COMM "<span>image $i: </span>\n");
    #check for comments in the metafile
    #maybe using a proper XML parser would make sense in future
    foreach $radek  (@meta_xml) {
       chomp($radek);
       if ($radek =~ m/.*name="([^"]*)".*annotation="([^"]*).*"/) {
          if ($1 eq $file) {
             print(COMM "<span>$2</span>\n");
          }
       }
    }
	 close(COMM);

	 $i++;

	 # print possible error messages
	 if ($reply ne "") {
		  print("There was an error message: $reply\n");
	 }
}

