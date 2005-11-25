<?php

# uncomment this to check for uninitialized variables etc.:
# error_reporting (E_ALL);

# get variables from the _SERVER array in order to not 
# rely on register_globals = On
# (this will not work with standalone PHP)
$ThisScript=$_SERVER['PHP_SELF'];
$ScriptFileName=$_SERVER['SCRIPT_FILENAME'];
$HostName=$_SERVER['SERVER_NAME']; 

require("inc/config.inc.php");
require("inc/www.class.inc.php");
require("inc/funkce.inc.php");
//session_name("navstevnik");
//session_register("page");

# always get sorted directory entries
$adr = new SortDir("$gallery_dir");

# get variables passed in from the URL:
$galerie=$_GET["galerie"];
$thumbsize=$_GET["thumbsize"];
$snimek=$_GET["snimek"];
$y=$_GET["y"];
$cmnt=$_GET["cmnt"];
$show_thumbs=$_GET["show_thumbs"];
$exif_style=$_GET["exif_style"];

$page = new C_www;
//default colors

if (!is_dir("$gallery_dir/$galerie/thumbs")) {
  $galerie = "";
}

$page->header("Photos");
require("inc/header.inc.php");

// folder > tree
echo "<div class=\"navigation\"><a href=\"$ThisScript\">Photo Gallery Index</a>";

#########################################
# 	Overall Gallery Index		#
#########################################
if (!$galerie) {
   # finish off navigation bar
   echo "</div>\n\n";  
   /*
     - nacti adresare
     - setrid podle casu
     - pro kazdy rok
       - setrid podle mesice
       - vypis
    */
# TODO:	Make date file support date of the month
#	And add info to README about date files
   while ($file = $adr->Read()) {
      // checking for inc is only really needed when gallery_dir == $root
      // hopefully not many galleries will be named inc ;)
      if (is_dir("$gallery_dir/$file") && !ereg("\.", $file) && $file!="inc") { 
         // Use date file for gallery date if avaliable
         $datefile = "$root/$gallery_dir/$file/date.txt";
         if (file_exists($datefile)) {
            $date_array = file($datefile);
            $year=trim($date_array[2]);
            $month=trim($date_array[1]);
            $galerieyear["$file"]=$year;
            $galeriemonth["$file"]=$month;
         } else { // Get Dates from modification times
            $mtime = filemtime("$gallery_dir/$file");
            $galerieyear["$file"] = date("Y", $mtime);
            $galeriemonth["$file"] = date("F", $mtime);
         }
      }
   }

   // re-sort array in order of months - so that they will be printed in order
   $months = array("December", "November", "October", "September", "August", "July", "June", "May", "April", "March", "February", "January");
   for ($i = 2010; $i >= 1990; $i--) {
      foreach ($months as $thismonth) {
         foreach ($galeriemonth as $foldername => $month) {
            if (strcasecmp($month, $thismonth) == 0) {
	      if ($galerieyear["$foldername"] == $i) {
                $galerieyearordered["$foldername"]=$galerieyear["$foldername"];
                $galeriemonthordered["$foldername"]=$galeriemonth["$foldername"];
              }
            }
         }
      }
   }

   foreach ($galerieyearordered as $file => $mtime) {
      $year=$galerieyearordered["$file"];
      $month=$galeriemonthordered["$file"];
      if ($thisyear!=$year) { #if the year is not equal to the current year
         #This is the first year
         if ($thisyear) { echo "   </div>\n</div>\n";}// end last year if this is 
                                         // not the first one
         #This is a new year
         unset($thismonth);
         echo "<div class=\"year\"><h3>$year</h3>\n";
         echo "";
      }
      # now months
      if ($thismonth!=$month) {
         #first one
         if ($thismonth) { echo "   </div>\n"; } // end of last month if
                                              // this is not the first one
         #new month
         echo "   <div class=\"month\"><h4>$month</h4>\n";
      }
      
      echo "      <p><a href=\"$ThisScript?galerie=$file\">$file</a></p>\n";
      $thisyear=$year;
      $thismonth=$month;
   }
   echo "   </div>\n</div>\n\n";
   
##############################
#  Individual Gallery Index  #
##############################
} elseif (!$snimek) {
   # finish off navigation header
   echo "\n &gt; $galerie</div>\n\n<p class=\"bigthumbnails\">\n";
   //thumbnails
   $path = "$gallery_dir/$galerie/thumbs";
   $obrazky = new SortDir($path);
   check($galerie); // check for nasty input
   while ($file = $obrazky->read()) {
      if (is_file("$path/$file") && eregi("^img-([0-9]+)\.(png|jpg)", $file, $x)) {
         $thumb = "$gallery_dir/$galerie/thumbs/img-${x[1]}.${x[2]}";
         $velikost = getimagesize("$root/$thumb");
         echo "   <a href=\"$ThisScript?galerie=$galerie&amp;snimek=${x[1]}\">";
         echo "<img ";
         if ($thumbsize) {
            echo "width=\"120\" height=\"80\" ";
         } else {
            // scale portraits to 80 height
            if ($velikost[0]>90) {
               echo $velikost[3]; 
            } else {
               echo "width=\"";
               $scaled = round($velikost[0] / 1.5);
               echo $scaled;
               echo "\" height=\"${velikost[0]}\"";
            }
         }
         echo " src=\"$thumb\" ";
         echo "alt=\"photo No. ${x[1]}\" />";
         echo "</a>\n";
      }
   }
   echo "</p>\n";
   //and links to archived images:
   echo "\n<p class=\"archives\">\n";
   if (file_exists("$gallery_dir/$galerie/zip/mq.zip")) {
      echo "[ <a href=\"$gallery_dir/$galerie/zip/mq.zip\">zipped MQ images</a> ] ";
   }
   if (file_exists("$gallery_dir/$galerie/zip/mq.tar.bz2")) {
      echo "[ <a href=\"$gallery_dir/$galerie/zip/mq.tar.bz2\">MQ images tarball</a> ] ";
   }
   if (file_exists("$gallery_dir/$galerie/zip/hq.zip")) {
      echo "[ <a href=\"$gallery_dir/$galerie/zip/hq.zip\">zipped HQ images</a> ]";
   }
   if (file_exists("$gallery_dir/$galerie/zip/hq.tar.bz2")) {
      echo "[ <a href=\"$gallery_dir/$galerie/zip/hq.tar.bz2\">HQ images tarball</a> ]";
   }
   echo "</p>";

#################################
# 	Individual Image	#
#################################
} else { //low-res image
   # finish off header
   echo "\n &gt; <a href=\"$ThisScript?galerie=$galerie\">$galerie</a>\n &gt; Photo";
   echo " $snimek</div>";
   $path = "$gallery_dir/$galerie/thumbs";
   $obrazky = new SortDir("$path");
   check($galerie);
   $path = "$gallery_dir/$galerie/lq";
   $file = "$path/img-$snimek.jpg";
   if (!file_exists($file)) {
      echo "No such image";
      $page->footer();
      exit;
   }
   $velikost = getimagesize("$root/$file");
   /*
   navigation($galerie, $snimek, null);
   */

   // mini thumbnail roll

   if ($show_thumbs) {
      echo "\n<!--mini thumbnail roll-->\n<div class=\"thumbroll\">";
      echo "<a id=\"minus\" href=\"$ThisScript?galerie=$galerie&amp;snimek=$snimek";
      echo "&amp;exif_style=$exif_style\">";
      echo "</a>\n";
      echo " : \n";
      while ($thumbfile = $obrazky->read()) {
         if ( eregi("^img-([0-9]+)\.(png|jpg)",
             $thumbfile, $x)) {
            $thumb = "$gallery_dir/$galerie/thumbs/img-${x[1]}.${x[2]}";
            echo "   <a href=\"$ThisScript?galerie=$galerie&amp;snimek=${x[1]}";
            echo "&amp;show_thumbs=$show_thumbs&amp;exif_style=$exif_style\">";
            echo "<img class=\"thumb\" ";
            // hadess' hack (TM) ;)
            if ($thumbsize) {
                 echo " width=\"24\" height=\"16\"";
            } else {
                 $minithumb=getimagesize("$root/$thumb");
                 $w=$minithumb[0]/6;
                 $h=$minithumb[1]/6;
                 echo " width=\"$w\" height=\"$h\"";
            }
            echo " src=\"$thumb\" ";
            echo "alt=\"photo No. ${x[1]}\" />";
            echo "</a> \n";
         }
      }
      if (file_exists("$gallery_dir/$galerie/zip/hq.zip")) {
         echo "<a id=\"zip\" href=\"$gallery_dir/$galerie/zip/hq.zip\">";
         echo "zip<span /></a>";
      }
      if (file_exists("$gallery_dir/$galerie/zip/hq.tar.bz2")) {
         echo "<a id=\"zip\" href=\"$gallery_dir/$galerie/zip/hq.tar.bz2\">";
         echo "zip<span /></a>";
      }
      echo "</div>\n";
   } else {
      // show the popup button
      echo "\n<!--mini thumbnail popup-->\n<div class=\"thumbroll\">";
      echo "<a id=\"plus\" href=\"$ThisScript?galerie=$galerie&amp;snimek=$snimek";
      echo "&amp;exif_style=$exif_style&amp;show_thumbs=yes\">";
      echo "</a>\n";
      echo "</div>\n";
   }

   /* main image + thumbnail navigation (prev/next) */
   
   $divheight = $velikost[1] + 10;
   echo "<div id=\"image\" style=\"height: ${divheight}px\">\n"; // extra kludge 
                                                                 // because of tall 
                                                                 // images

   echo "<img id=\"preview\" ${velikost[3]} src=\"$file\" alt=\"$snimek\" />\n";
   navigation($galerie, $snimek, "prev");
   navigation($galerie, $snimek, "next");
   echo "</div>\n"; //image


   if ($exif_prog) require("$root/inc/exif.inc.php"); // FIXME: prettify
   $comment = "$root/$gallery_dir/$galerie/comments/$snimek.txt";
   if (file_exists($comment)) {
      $cmnt_array = file($comment);
      while ($x = current($cmnt_array)) {
          eregi("^ *(.*) *", $x, $y);
          $cmnt .= $y[1];
          next($cmnt_array);
      }
      /* php4 only
      foreach ($cmnt_array as $x) {
         $cmnt .= $x;
      }
      */
      echo "<div class=\"comment\">$cmnt</div>";
   }
   echo "<div id=\"mqhq\">";
   if (file_exists("$gallery_dir/$galerie/mq/img-$snimek.jpg")) {
      echo "<a href=\"$gallery_dir/$galerie/mq/img-$snimek.jpg\">MQ</a> ";
   }
   if (file_exists("$gallery_dir/$galerie/hq/img-$snimek.jpg")) {
      echo "<a href=\"$gallery_dir/$galerie/hq/img-$snimek.jpg\">HQ</a>";
   }
   echo "</div>\n"; //mqhq

   navigation($galerie, $snimek, null);
}

require("inc/footer.inc.php");
$page->footer();
?>
