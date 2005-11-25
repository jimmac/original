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
