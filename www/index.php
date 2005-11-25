<?php
# uncomment this to check for uninitialized variables etc.:
# error_reporting (E_ALL);

# get variables from the _SERVER array in order to not 
# rely on register_globals = On
# (this will not work with standalone PHP)
$ThisScript=preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
$ScriptFileName=$_SERVER['SCRIPT_FILENAME'];
$HostName=$_SERVER['SERVER_NAME']; 
$ThisUrl = $_SERVER['REQUEST_URI'];

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
$exif_style='';
if (isset($_GET["exif_style"])) $exif_style=$_GET["exif_style"];

/*
if(!$exif_style) {
	$exif_style="descriptive";
} */

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

// processing of the user comment data
if($comments && @$_POST["commentdata"]) {
    $username = @$_COOKIE["username"];
    $comment_name = @$_POST["commentname"];
    $save_comment_name = @$_POST["savecommentname"];
    $comment_data = @$_POST["commentdata"];
		$comment_kolacek = @$_POST["commentkolacek"];
		$comment_spamcheck = @$_POST["commentspamcheck"];

		#check for HTML tags
		
		$comment_name = stripslashes(strip_tags($comment_name));
		$allowedTags = '<a><b><i><ul><li><blockquote><br>';
		$comment_data = stripslashes(strip_tags($comment_data,$allowedTags));
		// thanks google: 
		// http://www.google.com/googleblog/2005/01/preventing-comment-spam.html
		$comment_data = eregi_replace("<a ","<a rel=\"nofollow\" ",$comment_data);

		#further comment spam
		$comment_blacklist = array("pharmacy", "poker", "Viagra");

		foreach($comment_blacklist as $blackword) {
			$check = addslashes($blackword);
			if (eregi($check,$comment_data)) {
				#write error message
				$page->error( __('No comment spam'), __('Your comment includes blacklisted word') . __('No comment spam') );
				$page->footer();
				exit; //stop everything
			}
		}

		if ($comment_kolacek!=md5($comment_spamcheck)) {
				$page->error( __('No comment spam'), __('You ve written the check number wrong' ) );
				$page->footer();
				exit; //stop everything
		}

    if (!$comment_name) {
			$comment_name = $_COOKIE["username"];
    }
    
		// ok so we got a comment
		if ($comment_name && $save_comment_name) {
		// save out name in a cookie
			if (!setcookie("username","$comment_name", 
									mktime(0, 0, 0, 12, 30, 2030))) {
				print __('Could not set name cookie!');
				exit;
			}
		}

		// create a user_comment file if not existant or append to it
		if (is_writable("$root/$gallery_dir/$galerie/comments")) { // needs perms
			$comment = "$root/$gallery_dir/$galerie/comments/user_$snimek.txt";
			$fh = fopen("$comment", "a");

			if (!$comment_name) {
					$comment_name = __('Anonymous');
			}
			if (!fwrite($fh, "<div class=\"commententry\">\n")) {
					$page->error( __('Could not write to')  . $comment . "!" );
					$page->footer();
					exit; //stop everything
			}
			fwrite($fh, "   <div class=\"name\">" . __('Comment from') . "<em>$comment_name</em></div>\n",90);
			fwrite($fh, "   <div class=\"commentdata\">$comment_data</div>\n",280);
			fwrite($fh, "</div>\n");
			
			fclose($fh);
		}
}


//START RENDERING


$page->header("Photos");
require("inc/header.inc.php");

// folder > tree
//echo "<div class=\"navigation\"><a href=\"$ThisScript\">" . $scnamegallery . "</a>";
echo "<div class=\"navigation\"><a href=\"./\">" . $scnamegallery . "</a>";

#############################
# 	Overall Gallery Index		#
#############################
if (!$galerie) {
   # finish off navigation bar
   echo "</div>\n\n<!-- listing galleries-->\n\n";  
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
				 if (@$thisyear) { echo "   </div>\n</div>\n";}// end last year if this is 
																				 // not the first one
				 #This is a new year
				 unset($thismonth);
				 echo "<div class=\"year\"><h3>$year</h3>\n";
				 echo "";
			}
			$month=$galeriemonth["$foldername"];
			# now months
			if (@$thismonth!=$month) {
				 #first one
				 if (@$thismonth) { echo "   </div>\n"; } // end of last month if
																							// this is not the first one
				 #new month
				 $monthindex = $month - 1;
				 $monthname = $months[$monthindex];
				 echo "   <div class=\"month\"><h4>$monthname</h4>\n";
			}
			#galleries within month	
			if ($galerielogin[$foldername]) {
				echo "      <p class=\"restricted\"><a ";
			} else {
				echo "      <p><a ";
			}
			if (@$galeriename[$foldername]) {
				echo " href=\"$ThisScript?galerie=$foldername\">";
				echo $galeriename[$foldername];
				echo "</a>";
			} else {
				echo " href=\"$ThisScript?galerie=$foldername\">$foldername</a>";
			}
			if (@$galeriedesc[$foldername]) {
				echo "<span class=\"desc\">" . $galeriedesc[$foldername];
				echo "</span>\n";
			}
			if (@$galerieauthor[$foldername]) {
				echo "<span class=\"author\">by&nbsp;" . $galerieauthor[$foldername];
				echo "</span>\n";
			}
			if (@$galerieday[$foldername]) {
				echo "<span class=\"date\">";
				echo "$monthname&nbsp;" . $galerieday[$foldername];
				echo "</span>\n";
			}
			echo "</p>\n";
			$thisyear=$year;
			$thismonth=$month;
	 }
	 if ($one_out) echo ("   </div>\n</div>\n\n");
   
##############################
#  Individual Gallery Index  #
##############################
} elseif (!$snimek) {
	 
   # finish off navigation header
	 
   echo "\n &gt; ";
	 if ($galeriename[$galerie]) {
		 echo $galeriename[$galerie];
	 } else {
		 echo $galerie;
	 }
	 echo "</div>\n\n";

	 //thumbnails
	 echo "<p class=\"bigthumbnails\">\n";
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

         echo "   <a href=\"$ThisScript?galerie=$galerie&amp;photo=${x[1]}\"";
				 echo " title=\"$title, $NumOfComments\"";
				 if ($class) echo " class=\"$class\"";
				 echo ">";
         echo "<img ";
         if ($thumbsize) {
            echo "width=\"120\" height=\"80\" ";
         } else {
            // scale portraits to 80 height
            if ($portrait) {
							//portrait
               echo "width=\"";
               $scaled = round($imgsize[0] / 1.5);
               echo $scaled;
               echo "\" height=\"${imgsize[0]}\"";
            } else {
							//landscape
               echo $imgsize[3]; 
            }
         }
         echo " src=\"$thumb\" ";
         echo "alt=\"photo No. ${x[1]}\" />";
         echo "</a>\n";
      }
   }
   echo "</p>\n";

	 //info
	 echo "<div id=\"info\">\n";
	 if ($galeriedesc[$galerie]) {
		 echo "<p>";
		 echo "<span class=\"value\">";
		 echo $galeriedesc[$galerie] . "</span></p>\n";
	 }
	 if ($galerieauthor[$galerie]) {
		 echo "<p><span class=\"key\">Author: </span>";
		 echo "<span class=\"value\">";
		 echo $galerieauthor[$galerie] . "</span></p>\n";
	 }
	 echo "</div>\n";

   //and links to archived images:
   echo "\n<p class=\"archives\">\n";
   if (file_exists("$gallery_dir/$galerie/zip/mq.zip")) {
      echo "[ <a href=\"$gallery_dir/$galerie/zip/mq.zip\">" . __('zipped MQ images') . "</a> ] ";
   }
   if (file_exists("$gallery_dir/$galerie/zip/mq.tar.bz2")) {
      echo "[ <a href=\"$gallery_dir/$galerie/zip/mq.tar.bz2\">" . __('MQ images tarball') . "</a> ] ";
   }
   if (file_exists("$gallery_dir/$galerie/zip/hq.zip")) {
      echo "[ <a href=\"$gallery_dir/$galerie/zip/hq.zip\">" . __('zipped HQ images') . "</a> ]";
   }
   if (file_exists("$gallery_dir/$galerie/zip/hq.tar.bz2")) {
      echo "[ <a href=\"$gallery_dir/$galerie/zip/hq.tar.bz2\">" . __('HQ images tarball') . "</a> ]";
   }
   echo "</p>";

#######################
# 	Individual Image	#
#######################
} else { //low-res image
   # finish off header
   echo "\n &gt; <a href=\"$ThisScript?galerie=$galerie\">";
	 if ($galeriename[$galerie]) {
		 echo $galeriename[$galerie];
	 } else {
		 echo $galerie;
	 }
	 echo "</a>\n &gt; Photo";
   echo " $snimek</div>";
   $path = "$gallery_dir/$galerie/thumbs";
   $imgfiles = new SortDir("$path");
   check($galerie);
   $path = "$gallery_dir/$galerie/lq";
   $file = "$path/img-$snimek.jpg";
   if (!file_exists($file)) {
      echo __('No such image');
      $page->footer();
      exit;
   }
   $imgsize = getimagesize("$root/$file");
   /*
   navigation($galerie, $snimek, null);
   */

   // mini thumbnail roll

   if ($show_thumbs) {
      echo "\n<!--mini thumbnail roll-->\n<div class=\"thumbroll\">";
      echo "<a id=\"minus\" href=\"$ThisScript?galerie=$galerie&amp;photo=$snimek";
      echo "&amp;exif_style=$exif_style\">";
      echo "</a>\n";
      echo " : \n";
      while ($thumbfile = $imgfiles->read()) {
         if ( eregi("^img-([0-9]+)\.(png|jpe?g)",
             $thumbfile, $x)) {
            $thumb = "$gallery_dir/$galerie/thumbs/img-${x[1]}.${x[2]}";
            echo "   <a href=\"$ThisScript?galerie=$galerie&amp;photo=${x[1]}";
            echo "&amp;show_thumbs=$show_thumbs\"";
						echo " title=" . get_photo_title($galerie, $x[1]) . ">";
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
      echo "<a id=\"plus\" href=\"$ThisScript?galerie=$galerie&amp;photo=$snimek";
      echo "&amp;exif_style=$exif_style&amp;show_thumbs=yes\"";
			echo " title=\"" . __('Show Thumbnail Navigation') . "\">";
      echo "</a>\n";
      echo "</div>\n";
   }

   /* main image + thumbnail navigation (prev/next) */
   
   $divheight = $imgsize[1] + 10;
   echo "<div id=\"image\" style=\"height: ${divheight}px\">\n"; // extra kludge 
                                                                 // because of tall 
                                                                 // images

   echo "<img id=\"preview\" ${imgsize[3]} src=\"$file\" alt=\"$snimek\" />\n";
   navigation($galerie, $snimek, "prev");
   navigation($galerie, $snimek, "next");
   echo "</div>\n"; //image


   if ($exif_prog) require("$root/inc/exif.inc.php"); 
	 /* Image comment (caption really) */
   $comment = "$root/$gallery_dir/$galerie/comments/$snimek.txt";
   if (file_exists($comment)) {
      echo "<div class=\"comment\">";
			include($comment);
			echo "</div>";
   }
	 /* Counter/Access Log - also requires comments dir world writable */
	 if ($log_access==1) {
		 //simple counter
		 if (is_writable("$root/$gallery_dir/$galerie/comments")) { // needs perms
			 $log = "$root/$gallery_dir/$galerie/comments/log_$snimek.txt";
			 if (file_exists($log)){
				 $fh = fopen($log, "r");
				 $counter = rtrim(fgets($fh));
				 fclose($fh);
			 } else {
				 $counter = 0;
			 }
			 $counter++;
			 $fh = fopen($log,"w");
			 if (!fwrite($fh, "$counter\n")) {
					$page->error( __('Could not write to') . $log . "!");
					$page->footer();
					exit; //stop everything
			 }
			 fclose($fh);
			 //Now display something
			 echo "\n<div id=\"log\">\n";
			 echo __('This image has been viewed') . " ";
			 echo "<strong>$counter</strong>". " " . __('times') . ".";
			 //echo date("F dS, Y",filectime($log));
			 echo "</div>\n\n";
		 } else {
			 echo "<!-- ". __('WARNING: comment dir not writable') . "-->\n";
		 }
	 } elseif ($logaccess==2) {
		 // log time, IP, UA
		 // TODO - is this really a good thing to do?
	 }
	 

   if (file_exists("$gallery_dir/$galerie/mq/img-$snimek.jpg") || file_exists("$gallery_dir/$galerie/hq/img-$snimek.jpg")) {
		 echo "<div id=\"mqhq\">";
		 if (file_exists("$gallery_dir/$galerie/mq/img-$snimek.jpg")) {
				echo "<a href=\"$gallery_dir/$galerie/mq/img-$snimek.jpg\">". __('MQ') . "</a> ";
		 }
		 if (file_exists("$gallery_dir/$galerie/hq/img-$snimek.jpg")) {
				echo "<a href=\"$gallery_dir/$galerie/hq/img-$snimek.jpg\">" . __('HQ') . "</a>";
		 }
		 echo "</div>\n"; //mqhq
	 }

   /* User comments */
   if ($comments) {
		 if (is_writable("$root/$gallery_dir/$galerie/comments")) { // needs perms
			 require("inc/comment.inc.php");
			 $user_comment = "$root/$gallery_dir/$galerie/comments/user_$snimek.txt";

			 if (file_exists($user_comment)) {
					echo "<div class=\"user_comment\">";
					include($user_comment);
					echo "</div>";
			 }
		 } else {
			 echo "<!-- WARNING: comment dir not writable -->\n";
		 }
   }
   navigation($galerie, $snimek, null);
}

require("inc/footer.inc.php");
$page->footer();
?>
