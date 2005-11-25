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
#  Released under the GPL license.                                      #
#                                                                       #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # 

die "No files to convert" unless @ARGV;

use Gtk;
init Gtk;

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


$w = new Gtk::Window;
$label = new Gtk::Label(' Web Gallery generation in progress... ');
$pbar = new Gtk::ProgressBar;
$vb = new Gtk::VBox(0, 0);
$b = new Gtk::Button('Cancel');
$w->add($vb);
$vb->add($label);
$vb->add($pbar);
$vb->add($b);

$b->signal_connect('clicked', sub {Gtk->exit(0)});
$w->signal_connect('destroy', sub {Gtk->exit(0)});

$w->show_all();
$i = 0;
$pbar->update($i);

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
	 
	 $pbar->update($progress);
	 $pbar->set_show_text(1);
	 $pbar->set_format_string("$i of $num_of_files");
	 $progress += $increment;
	 while (Gtk->events_pending) {
		  Gtk->main_iteration;
	 }

	 # do the stuff, collect error messages to a variable.
	 #$reply=`convert -geometry 120x120 -colors 64 -dither $file $dir/thumbs/img-$i\.png 2>&1`;
	 $reply=`convert -geometry 120x120 $file $dir/thumbs/img-$i\.jpg 2>&1`;
    #$reply =`jpegtopnm $file | pnmscale -xysize 120 120 | ppmquant -floyd 16 | pnmtopng -interlace -compression 9 > $dir/thumbs/img-$i\.png 2>&1`;

	 $pbar->update($progress);
	 $progress += $increment;
	 while (Gtk->events_pending) {
		  Gtk->main_iteration;
	 }

	 $reply = $reply . `convert -geometry 640x640 $file $dir/lq/img-$i.jpg 2>&1`;
	 #$reply = $reply . `jpegtopnm $file | pnmscale -xysize 640 640 | ppmtojpeg --quality 80 --progressive > $dir/lq/img-$i.jpg 2>&1`;

	 $pbar->update($progress);
	 $progress += $increment;
	 while (Gtk->events_pending) {
		  Gtk->main_iteration;
	 }

	 $reply = $reply . `convert -geometry 800x800 $file $dir/mq/img-$i.jpg 2>&1`;
	 #$reply = $reply . `jpegtopnm $file | pnmscale -xysize 800 800 | ppmtojpeg --quality 80 --progressive > $dir/mq/img-$i.jpg 2>&1`;

	 $pbar->update($progress);
	 $progress += $increment;
	 while (Gtk->events_pending) {
		  Gtk->main_iteration;
	 }

	 $reply = $reply . `cp $file $dir/hq/img-$i.jpg 2>&1`;

	 $pbar->update($progress);
	 $progress += $increment;
	 while (Gtk->events_pending) {
	 	  Gtk->main_iteration;
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

