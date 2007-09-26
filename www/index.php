<?php
# uncomment this to check for uninitialized variables etc.:
# error_reporting (E_ALL);

#language support
require_once ("lib/lib.l10n.php");
require_once("inc/config.inc.php");
require_once("inc/www.class.inc.php");
require_once("inc/funkce.inc.php");
//session_name("navstevnik");
//session_register("page");

#set the language translation
l10n::set("$root/l10n/".$sclang."/main.lang");
l10n::set("$root/l10n/".$sclang."/date.lang");




# always get sorted directory entries
$adr = new SortDir("$gallery_dir");

# get variables passed in from the URL:
$galerie='';
if (isset($_GET['galerie'])) $galerie=$_GET["galerie"];
if (isset($_GET['gallery'])) $galerie=$_GET["gallery"];
$galerie = preg_replace('/\//', '', $galerie);
if (isset($_GET["thumbsize"])) $thumbsize=$_GET["thumbsize"];
$snimek = 0;
if (isset($_GET["snimek"])) $snimek=$_GET["snimek"];
if (isset($_GET["photo"])) $snimek=$_GET["photo"];
$snimek = intval($snimek);
$y='';
if (isset($_GET['y'])) $y=$_GET["y"];
$cmnt='';
if (isset($_GET["cmnt"])) $cmnt=$_GET["cmnt"];
$show_thumbs='';
if (isset($_GET["show_thumbs"])) $show_thumbs=$_GET["show_thumbs"];

$page = new C_www;
//default colors

if (!is_dir("$gallery_dir/$galerie/thumbs")) {
  $galerie = "";
}

//read interesting stuff from info.txt
if ($galerie) { 
	readInfo("$root/$gallery_dir/$galerie/info.txt", $galerie);
//check for restricted access
	if ($galerielogin[$galerie]) {
			 access_check($galerielogin[$galerie],$galeriepw[$galerie],$galerie);
	}
}

$page->process_comment_form();

//START RENDERING


$page->header("Photos");
require("inc/header.inc.php");

// folder > tree
//print "<div class=\"navigation\"><a href=\"$ThisScript\">" . $scnamegallery . "</a>";
print "<div class=\"navigation\"><a href=\"./\">" . $scnamegallery . "</a>";

#############################
# 	Overall Gallery Index		#
#############################
if (!$galerie) {
   # finish off navigation bar
   print "</div>\n\n<!-- listing galleries-->\n\n";  
	 # I've nuked date.txt to replace it with a more generic info.txt
   # It optionally supplies i18n name, camera model, author and date
	 # TODO: imgconv script support
   while ($file = $adr->Read()) {
      // checking for inc is only really needed when gallery_dir == $root
      // hopefully not many galleries will be named inc ;)
      if (is_dir("$gallery_dir/$file") && !ereg("\.", $file) && $file!="inc") { 
         // Use date file for gallery date if avaliable
				 // info.txt format described in README
         readInfo("$root/$gallery_dir/$file/info.txt", $file);
         
      }
   }

	 if (!isset($galeriemonth)) $galeriemonth = array();
   if (!isset($galerieday)) $galerieday = array();
	 //sort within month depending on $sortinmonth
	 if ($sortinmonth) {
		 //alphabetically
		 ksort($galeriemonth);
		 reset($galeriemonth);
	 } else {//by date
		 arsort($galerieday);
		 reset($galerieday);
	 }


	 $thisyear = 0;
	 if (!$yearto) $yearto = date("Y");
   for ($i = $yearto; $i >= $yearsince; $i--) {
      for ($thismonth=12; $thismonth>0; $thismonth--) { // go year by year, month by month
																												// down
				 foreach ($galerieday as $foldername => $day) { //using $galerieday (for when sorted)
						if ($galeriemonth["$foldername"] == $thismonth && 
							      $galerieyear["$foldername"] == $i) { //such Y/M exists

								$galerieyearordered["$foldername"]=$galerieyear["$foldername"];
								$galeriemonthordered["$foldername"]=$galeriemonth["$foldername"];
						}
				 }
      }
   }


	 $months = array(__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'),
	 __('September'), __('October'), __('November'), __('December'));
	 $one_out = false;
	 foreach ($galerieyearordered as $foldername => $year) {
		  $one_out = true;
			if (@$thisyear!=$year) { #if the year is not equal to the current year
				 #This is the first year
				 if (@$thisyear) { print "   </div>\n</div>\n";}// end last year if this is 
																				 // not the first one
				 #This is a new year
				 unset($thismonth);
				 print "<div class=\"year\"><h3>$year</h3>\n";
				 print "";
			}
			$month=$galeriemonth["$foldername"];
			# now months
			if (@$thismonth!=$month) {
				 #first one
				 if (@$thismonth) { print "   </div>\n"; } // end of last month if
																							// this is not the first one
				 #new month
				 $monthindex = $month - 1;
				 $monthname = $months[$monthindex];
				 print "   <div class=\"month\"><h4>$monthname</h4>\n";
			}
			#galleries within month	
			if ($galerielogin[$foldername]) {
				print "      <p class=\"restricted\"><a ";
			} else {
				print "      <p><a ";
			}
			if (@$galeriename[$foldername]) {
				print " href=\"$ThisScript?galerie=$foldername\">";
				print $galeriename[$foldername];
				print "</a>";
			} else {
				print " href=\"$ThisScript?galerie=$foldername\">$foldername</a>";
			}
			if (@$galeriedesc[$foldername]) {
				print "<span class=\"desc\">" . $galeriedesc[$foldername];
				print "</span>\n";
			}
			if (@$galerieauthor[$foldername]) {
				print "<span class=\"author\">by&nbsp;" . $galerieauthor[$foldername];
				print "</span>\n";
			}
			if (@$galerieday[$foldername]) {
				print "<span class=\"date\">";
				print "$monthname&nbsp;" . $galerieday[$foldername];
				print "</span>\n";
			}
			print "</p>\n";
			$thisyear=$year;
			$thismonth=$month;
	 }
	 if ($one_out) print ("   </div>\n</div>\n\n");
   
##############################
#  Individual Gallery Index  #
##############################
} elseif (!$snimek) {
	 
   # finish off navigation header
	 
   print "\n &gt; ";
	 if ($galeriename[$galerie]) {
		 print $galeriename[$galerie];
	 } else {
		 print $galerie;
	 }
	 print "</div>\n\n";

	 //thumbnails
	 print "<p class=\"bigthumbnails\">\n";
   $path = "$gallery_dir/$galerie/thumbs";
   $imgfiles = new SortDir($path);
   check($galerie); // check for nasty input
   while ($file = $imgfiles->read()) {
      if (is_file("$path/$file") && eregi("^img-([0-9]+)\.(png|jpe?g)", $file, $x)) {
				 				 
         $thumb = "$gallery_dir/$galerie/thumbs/img-${x[1]}.${x[2]}";
         $imgsize = getimagesize("$root/$thumb");
				 //check for portraits
				 $portrait = "false";
				 $class = "";
				 if($imgsize[0]<100) {
					 //portraits need a special class for styling
					 $class = "portrait";
				 }
				 //check for number of comments per photo
				 if ($comments) { //there probably won't be user comments if it's off
				   $NumOfComments = 0;
					 if (file_exists("$gallery_dir/$galerie/comments/user_${x[1]}.txt")) {
							if ($class) $class .= " ";
							$class .= "hascomments";
							//now let's count'em
							$fh = fopen("$gallery_dir/$galerie/comments/user_${x[1]}.txt","r");
							while (!feof($fh)) {
								$line = fgets($fh);
								if (eregi("commententry",$line)) $NumOfComments++;
							}
							fclose($fh);
					 }
					 if ($NumOfComments==1) {
						 $NumOfComments = $NumOfComments . " " . __('Comment');
					 } else {
						 $NumOfComments = $NumOfComments . " " . __('Comments');
					 }
				 }
	 if (file_exists("$gallery_dir/$galerie/comments/${x[1]}.txt") &&
		   $title = file_get_contents("$gallery_dir/$galerie/comments/${x[1]}.txt")) {
	     $title = ereg_replace("(\"|\')","",trim(strip_tags($title)));
	     $title = ereg_replace("(.{77}).*","\\1",$title);
	 } else 
	   $title = "Photo ${x[1]}";

         print "   <a href=\"$ThisScript?galerie=$galerie&amp;photo=${x[1]}\"";
				 print " title=\"$title, $NumOfComments\"";
				 if ($class) print " class=\"$class\"";
				 print ">";
         print "<img ";
         if (isset($thumbsize)) {
            print "width=\"120\" height=\"80\" ";
         } else {
            // scale portraits to 80 height
            if ($portrait) {
							//portrait
               print "width=\"";
               $scaled = round($imgsize[0] / 1.5);
               print $scaled;
               print "\" height=\"${imgsize[0]}\"";
            } else {
							//landscape
               print $imgsize[3]; 
            }
         }
         print " src=\"$thumb\" ";
         print "alt=\"photo No. ${x[1]}\" />";
         print "</a>\n";
      }
   }
   print "</p>\n";

	 //info
	 print "<div id=\"info\">\n";
	 if ($galeriedesc[$galerie]) {
		 print "<p>";
		 print "<span class=\"value\">";
		 print $galeriedesc[$galerie] . "</span></p>\n";
	 }
	 if ($galerieauthor[$galerie]) {
		 print "<p><span class=\"key\">Author: </span>";
		 print "<span class=\"value\">";
		 print $galerieauthor[$galerie] . "</span></p>\n";
	 }
	 print "</div>\n";

   //and links to archived images:
   print "\n<p class=\"archives\">\n";
   if (file_exists("$gallery_dir/$galerie/zip/mq.zip")) {
      print "[ <a href=\"$gallery_dir/$galerie/zip/mq.zip\">" . __('zipped MQ images') . "</a> ] ";
   }
   if (file_exists("$gallery_dir/$galerie/zip/mq.tar.bz2")) {
      print "[ <a href=\"$gallery_dir/$galerie/zip/mq.tar.bz2\">" . __('MQ images tarball') . "</a> ] ";
   }
   if (file_exists("$gallery_dir/$galerie/zip/hq.zip")) {
      print "[ <a href=\"$gallery_dir/$galerie/zip/hq.zip\">" . __('zipped HQ images') . "</a> ]";
   }
   if (file_exists("$gallery_dir/$galerie/zip/hq.tar.bz2")) {
      print "[ <a href=\"$gallery_dir/$galerie/zip/hq.tar.bz2\">" . __('HQ images tarball') . "</a> ]";
   }
   print "</p>";

#######################
# 	Individual Image	#
#######################
} else { //low-res image
   # finish off header
   print "\n &gt; <a href=\"$ThisScript?galerie=$galerie\">";
	 if ($galeriename[$galerie]) {
		 print $galeriename[$galerie];
	 } else {
		 print $galerie;
	 }
	 print "</a>\n &gt; Photo";
   print " $snimek</div>";
   $path = "$gallery_dir/$galerie/thumbs";
   $imgfiles = new SortDir("$path");
   check($galerie);
   $path = "$gallery_dir/$galerie/lq";
   $file = "$path/img-$snimek.jpg";
   if (!file_exists($file)) {
      print __('No such image');
      $page->footer();
      exit;
   }
	 
	 if (!$picture) { //picture may have been created if commentform submitted
	    require_once("$root/inc/photo.class.inc.php");
	    $picture = new C_photo($file, $snimek);
	 }

   // mini thumbnail roll

   if ($show_thumbs) {
      print "\n<!--mini thumbnail roll-->\n<div class=\"thumbroll\">";
      print "<a id=\"minus\" href=\"$ThisScript?galerie=$galerie&amp;photo=$snimek";
      print "\">";
      print "</a>\n";
      print " : \n";
      while ($thumbfile = $imgfiles->read()) {
         if ( eregi("^img-([0-9]+)\.(png|jpe?g)",
             $thumbfile, $x)) {
            $thumb = "$gallery_dir/$galerie/thumbs/img-${x[1]}.${x[2]}";
            print "   <a href=\"$ThisScript?galerie=$galerie&amp;photo=${x[1]}";
            print "&amp;show_thumbs=$show_thumbs\"";
						print " title=" . get_photo_title($galerie, $x[1]) . ">";
            print "<img class=\"thumb\" ";
            // hadess' hack (TM) ;)
            if ($thumbsize) {
                 print " width=\"24\" height=\"16\"";
            } else {
                 $minithumb=getimagesize("$root/$thumb");
                 $w=$minithumb[0]/6;
                 $h=$minithumb[1]/6;
                 print " width=\"$w\" height=\"$h\"";
            }
            print " src=\"$thumb\" ";
            print "alt=\"photo No. ${x[1]}\" />";
            print "</a> \n";
         }
      }
      if (file_exists("$gallery_dir/$galerie/zip/hq.zip")) {
         print "<a id=\"zip\" href=\"$gallery_dir/$galerie/zip/hq.zip\">";
         print "zip<span /></a>";
      }
      if (file_exists("$gallery_dir/$galerie/zip/hq.tar.bz2")) {
         print "<a id=\"zip\" href=\"$gallery_dir/$galerie/zip/hq.tar.bz2\">";
         print "zip<span /></a>";
      }
      print "</div>\n";
   } else {
      // show the popup button
      print "\n<!--mini thumbnail popup-->\n<div class=\"thumbroll\">";
      print "<a id=\"plus\" href=\"$ThisScript?galerie=$galerie&amp;photo=$snimek";
      print "&amp;show_thumbs=yes\"";
			print " title=\"" . __('Show Thumbnail Navigation') . "\">";
      print "</a>\n";
      print "</div>\n";
   }

   /* main image + thumbnail navigation (prev/next) */

   $picture->renderPreview();
   $page->navigation($galerie, $snimek, "prev");
   $page->navigation($galerie, $snimek, "next");
   print "</div>\n"; //end image div



   if ($exif) require("$root/inc/exif.inc.php"); 
	 /* Image comment
	 		really poor naming here, it is caption.
	 */
	 $picture->renderCaption();

 	
	 //show page counter
	 if ($log_access) {
			$picture->renderCounter();
	 }

	 $picture->renderBigSize();

   $page->user_comments($picture->number);
   $page->navigation($galerie, $snimek, null);
}

require("inc/footer.inc.php");
$page->footer();
?>
