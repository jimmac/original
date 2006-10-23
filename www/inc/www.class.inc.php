<?php
// www.class.inc.php
class C_www {
   var $background, $bgcolor, $link, $vlink, $alink, $hover, $language;
   var $textcol, $font, $fontsize;

   ////
   // !vykpise HTML hlavicku dokumentu
   // Ten CSS style jeste neni moc dodelanej
   function header($title) {
      global $gallery_dir,$root, $snimek, $galerie, $ThisScript, $themes;

			header("Content-Type: text/html; charset=utf-8");// make sure we send in utf8
																											 // and override Apache

																											 // For some reason text/xml is
																											 // causing trouble with stylesheets
			echo "<?xml version=\"1.0\"?>\n";
			/*
			echo "<?xml-stylesheet type=\"text/css\" media=\"screen\""; // doesn't work yet :/
			echo " href=\"inc/style/dark/dark.css\" ?>\n";
			*/
      echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n";
      echo "   \"http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-strict.dtd\">\n";
      echo "<html>\n";
      echo "<head>\n";

			#IE hacks
			echo "<!-- This makes IE6 suck less (a bit) -->\n";
			echo "<!--[if lt IE 7]>\n";
			echo "<link rel=\"stylesheet\" type=\"text/css\" ";
			echo "href=\"inc/styles/ie6workarounds.css\" />\n";
			echo "<script src=\"inc/styles/ie7/ie7-standard.js\" type=\"text/javascript\">\n";
			echo "</script>\n";
			echo "<![endif]-->\n";

      echo "   <title>$title</title>\n";
			echo "<link rel=\"icon\" href=\"stock_camera-16.png\" ";
			echo "type=\"image/png\" />\n";
			echo "<link rel=\"shortcut icon\" href=\"favicon.ico\" ";
			echo "type=\"image/x-icon\" />\n";
      # mozilla style links
		if ($snimek && $galerie) {
			#Top
			echo "   <link rel=\"Top\"      href=\"$ThisScript\" />\n";
			#First
			#Prev
			$predchozi = $snimek - 1;
			$dalsi = $snimek + 1;
   		if ($snimek > 1) {
				echo "   <link rel=\"First\" ";
				echo "   href=\"$ThisScript?galerie=$galerie&amp;photo=1\" />\n";
				echo "   <link rel=\"Previous\" ";
				echo "href=\"$ThisScript?galerie=$galerie&amp;photo=$predchozi\" />\n";
			}
			#Next
			if (is_file("$gallery_dir/$galerie/lq/img-$dalsi.jpg")) {
				echo "   <link rel=\"Next\" ";
				echo "    href=\"$ThisScript?galerie=$galerie&amp;photo=$dalsi\" />\n";
			}
			#Last
			$adr = opendir("$gallery_dir/$galerie/thumbs/");
			$i = -2;
			while ($file = readdir($adr)) {
				$i++;
			}
			if ($i!=$snimek) {
				echo "   <link rel=\"Last\" ";
				echo "    href=\"$ThisScript?galerie=$galerie&amp;photo=$i\" />\n";
			}
		}
      
      /* check the theme in a cookie */
      $theme = @$_COOKIE["theme"];
      if (!$theme) { //we didn't set the cookie yet
	 // select first key of the themes array in config.inc.php as default
	 $theme_keys = array_keys($themes);
	 $theme = $theme_keys[0]; 
      }
      foreach ($themes as $skin => $url) {
         echo "<link type=\"text/css\" rel=\"";
         if ($skin==$theme) {
            echo "stylesheet";
         } else {
            echo "prefertch alternate stylesheet";
         }
         echo "\" href=\"$url\" title=\"$skin\"";
         echo " media=\"screen\" />\n";
      }
     
      //require("javascript.inc.php");
			echo "<script src=\"inc/global.js\" ";
			echo "type=\"text/javascript\"></script>\n";
      echo "</head>\n\n";
      echo "<body onload=\"checkForTheme()";
      echo "\">\n";
   }

   ////
   // !zavre html stranku
   function footer() {
      echo "</body>\n";
      echo "</html>\n";
   }

   ////
   // !vypise chybovou hlasku
   // $title - nadpis a title HTML stranky
   // $message - vlastni chybova hlaska
   function error($title, $message) {
         $this->header($title);
         echo "<h1>$title</h1>\n";
         echo $message;
         $this->footer();
         exit; //vysere se na vsechno
   }

  
     
   ////
   // !zacatek fomrulare
   function form_start($action, $method, $upload) {
      echo "<form ";
      if ($upload) echo "enctype=\"multipart/form-data\" ";
      echo "action=\"";
			echo htmlentities($action,ENT_COMPAT,"UTF-8");
			echo "\" method=\"$method\">\n";
   }

   ////
   // !konec formulare
   function form_end() {
      echo "</form>\n";
   }
 
   ////
   // !vykresli polozku formulare
   // umi text, password, submit, file, hidden, textarea, select
   // u textarea je default pocet radku...
   function input($type, $name, $value, $popis, $default, $title) {
			echo "<div class=\"row\">\n";
			if (!$title) {
				echo "	<div class=\"label\">$popis</div>\n";
			} else {
				echo "	<div class=\"label\"><a title=\"$title\" ";
				echo "href=\"#\">$popis</a></div>\n";
			}
			echo "	<div class=\"control\">";
      switch ($type) {
         case "checkbox":
            echo "<input type=\"$type\" name=\"$name\" value=\"$value\"";
            if ($default) echo " checked=\"checked\"";
            echo " />";
            break;
         case "password": 
         case "text": 
            echo "<input type=\"$type\" size=\"30\" name=\"$name\" value=\"$value\" />";
            break;
         case "file": 
            echo "<input type=\"$type\" size=\"30\" name=\"$name\" />";
            break;
         case "hidden": 
            echo "<input type=\"$type\" name=\"$name\" value=\"$value\" />";
            break;
         case "textarea":
            echo "<textarea name=\"$name\" cols=\"40\"";
            if ($default) {
                echo " rows=\"$default\"";
            } else {
                echo " rows=\"10\"";
            }
            echo ">$value</textarea>";
            break;
         case "select":
            echo "<select name=\"$name\" size=\"1\">\n";
            while (list($optval, $option) = each($value)) {
                echo "<option value=\"$optval\"";
                if ($optval == $default) echo " selected";
                echo ">";
                echo $option;
                echo "</option>\n";
            }
            echo "</select>";
            break;
         case "submit":
            echo "<input type=\"$type\" name=\"$name\" value=\"$value\" />";
            break;
      }
			echo "	</div>\n";
			echo "</div>\n";
   }

	
	function navigation ($gallery, $snapshot, $image) {
   global $gallery_dir, $root, $ThisScript, $textnav, $img, 
          $show_thumbs, $exif_style, $PNthumbScale;

   $next = $snapshot + 1;
   $prev = $snapshot - 1;

   if (!$image) { // this will render a navigation bar - max 3 buttons
      echo "\n<div class=\"navbuttons\">\n";
      echo "<div class=\"navbuttonsshell\">\n";
      if ($snapshot > 1) { //previous 
         echo "<a id=\"previcon\" href=\"$ThisScript?galerie=$gallery&amp;photo=$prev";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\"";
				 echo " accesskey=\"p\">";
         echo "&lt; <span class=\"accesskey\">P</span>revious</a>\n";
      }
      echo "&nbsp;";
      if (is_file("$gallery_dir/$gallery/lq/img-$next.jpg")) { //next
         echo "<a id=\"nexticon\" href=\"$ThisScript?galerie=$gallery&amp;photo=$next";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\"";
				 echo " accesskey=\"n\">";
         echo "<span class=\"accesskey\">N</span>ext &gt;</a>\n";
      }
      echo "</div>\n</div>\n";
   } elseif ($image=="prev") { //previous thumbnail
      if ($snapshot > 1) { //previous 
         echo "<div class=\"prevthumb\">";
         echo "<a href=\"$ThisScript?galerie=$gallery&amp;photo=$prev";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\">";
         if (file_exists("$gallery_dir/$gallery/thumbs/img-$prev.png")) {
            $Pthumb = "$gallery_dir/$gallery/thumbs/img-$prev.png";
         } else {
            $Pthumb = "$gallery_dir/$gallery/thumbs/img-$prev.jpg";
         }
         $v = getimagesize("$root/$Pthumb");
         echo "<img alt=\"Previous\" src=\"";
         echo $Pthumb . "\" width=\"" . round($v[0]/$PNthumbScale);
         echo "\" height=\"" . round($v[1]/$PNthumbScale) . "\" />";
         echo "<br />" . __('Previous');
         echo "</a></div>\n";
      }
   } else { //next thumbnail
      if (is_file("$gallery_dir/$gallery/lq/img-$next.jpg")) {
         echo "<div class=\"nextthumb\">";
         echo "<a href=\"$ThisScript?galerie=$gallery&amp;photo=$next";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\">";
         if (file_exists("$gallery_dir/$gallery/thumbs/img-$next.png")) {
            $Nthumb = "$gallery_dir/$gallery/thumbs/img-$next.png";
         } else {
            $Nthumb = "$gallery_dir/$gallery/thumbs/img-$next.jpg";
         }
         $v = getimagesize("$root/$Nthumb");
         echo "<img alt=\"Next\" src=\"";
         echo $Nthumb . "\" width=\"" . round($v[0]/$PNthumbScale);
         echo "\" height=\"" . round($v[1]/$PNthumbScale) . "\" />";
         //echo "<br /><span class=\"accesskey\">N</span>ext";
         echo "<br />" . __('Next') ;	 
         echo "</a></div>\n";
      }
   }
	}

	function user_comments($photo) {
		global $root, $gallery_dir, $galerie, $comments, $picture;

   if ($comments) {
		 if (is_writable("$root/$gallery_dir/$galerie/comments")) { // needs perms
			 require("inc/comment_form.inc.php");

			 if ($picture->comments) {
					print "<div class=\"user_comment\">";
					print $picture->comments;
					print "</div>";
			 }
		 } else {
			 print "<!-- WARNING: comment dir not writable -->\n";
		 }
   }
	}

	function process_comment_form() { // processing of the user comment data
		global $comments, $root, $gallery_dir, $galerie, $snimek;
		
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
						$this->error( __('No comment spam'), __('Your comment includes blacklisted word') . __('No comment spam') );
						$this->footer();
						exit; //stop everything
					}
				}

				if ($comment_kolacek!=md5($comment_spamcheck)) {
						$this->error( __('No comment spam'), __('You ve written the check number wrong' ) );
						$this->footer();
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
				if (!$picture) {
				  require_once("$root/inc/photo.class.inc.php");
					$path = "$gallery_dir/$galerie/lq";
					$file = "$path/img-$snimek.jpg";
					$picture = new C_photo($file, $snimek);
				}
				$picture->addcomment($comment_name, $comment_data);
		}
	}
}

# return dirs sorted
class SortDir {
   var $items;

   function SortDir($directory) {
      $handle=@opendir($directory);
			if (!$handle) return;
      while ($file = readdir($handle)) {
         if ($file != "." && $file != "..") {
            $this->items[]=$file;
         }
      }
      closedir($handle);
	    if ($this->items) {
         natsort($this->items);
	    }
   }

   function Read() {
			if ($this->items) {
				$getback= (pos($this->items));
				next($this->items);
				return $getback;
			}
   }
}

?>
