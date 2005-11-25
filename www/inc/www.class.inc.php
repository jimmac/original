<?php
// www.class.inc.php
class C_www {
   var $background, $bgcolor, $link, $vlink, $alink, $hover, $language;
   var $textcol, $font, $fontsize;

   function C_www ($bgcolor="#ffffff", $link="blue", $vlink="#000000",
                   $alink="red", $hover="green", 
                   $font="Bitstream Vera Sans, sans-serif",
                   $fontsize="11px", $textcol="black") {
      #init colors etc
      
      $this->bgcolor = $bgcolor;
      $this->link = $link;
      $this->vlink = $vlink;
      $this->alink = $alink;
      $this->hover = $hover;
      $this->font = $font;
      $this->fontsize = $fontsize;
      $this->textcol = $textcol;
   }

   ////
   // !vykpise HTML hlavicku dokumentu
   // Ten CSS style jeste neni moc dodelanej
   function header($title) {
      global $gallery_dir,$root, $snimek, $galerie, $ThisScript, $themes;

      echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n";
      echo "   \"http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-strict.dtd\">\n";

      echo "<html>\n";
      echo "<head>\n";
      echo "   <title>$title</title>\n";
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
				echo "   href=\"$ThisScript?galerie=$galerie&amp;snimek=1\" />\n";
				echo "   <link rel=\"Previous\" ";
				echo "href=\"$ThisScript?galerie=$galerie&amp;snimek=$predchozi\" />\n";
			}
			#Next
			if (is_file("$gallery_dir/$galerie/lq/img-$dalsi.jpg")) {
				echo "   <link rel=\"Next\" ";
				echo "    href=\"$ThisScript?galerie=$galerie&amp;snimek=$dalsi\" />\n";
			}
			#Last
			$adr = opendir("$gallery_dir/$galerie/thumbs/");
			$i = -2;
			while ($file = readdir($adr)) {
				$i++;
			}
			if ($i!=$snimek) {
				echo "   <link rel=\"Last\" ";
				echo "    href=\"$ThisScript?galerie=$galerie&amp;snimek=$i\" />\n";
			}
		}
      
      /* check the theme in a cookie */
      $theme = $_COOKIE["theme"];
      if (!$theme) { //we didn't set the cookie yet
         if (browserCheck()=="ie6+" || browserCheck()=="ie4+") {
            $theme = "ie"; //IE crashes on other themes
         } else {
            $theme = "default"; 
         }
      }
      foreach ($themes as $skin => $url) {
         echo "<link type=\"text/css\" rel=\"";
         if ($skin==$theme) {
            echo "stylesheet";
         } else {
            echo "alternate stylesheet";
         }
         echo "\" href=\"$url\" title=\"$skin\"";
         echo " media=\"screen\" />\n";
      }
     
      require("javascript.inc.php");
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
      echo "target=\"_top\" action=\"$action\" method=\"$method\">\n";
      echo "<table width=\"600\" border=\"0\">\n";
   }

   ////
   // !konec formulare
   function form_end() {
      echo "</table>\n";
      echo "</form>\n";
   }
 
   ////
   // !vykresli polozku formulare
   // umi text, password, submit, file, hidden, textarea, select
   // u textarea je default pocet radku...
   function input($type, $name, $value, $popis, $default) {
      if ($type!="hidden") {
         echo "<tr valign=\"top\">\n";
         echo "<td>$popis</td>\n";
         echo "<td>";
      }
      switch ($type) {
         case "checkbox":
            echo "<input type=\"$type\" name=\"$name\" value=\"$value\"";
            if ($default) echo " checked";
            echo ">";
            break;
         case "password": 
         case "text": 
            echo "<input type=\"$type\" size=\"30\" name=\"$name\" value=\"$value\">";
            break;
         case "file": 
            echo "<input type=\"$type\" size=\"30\" name=\"$name\">";
            break;
         case "hidden": 
            echo "<input type=\"$type\" name=\"$name\" value=\"$value\">\n";
            break;
         case "textarea":
            echo "<textarea name=\"$name\" cols=\"40\"";
            if ($default) {
                echo " rows=\"$default\"";
            } else {
                echo " rows=\"10\"";
            }
            echo " wrap=\"virtual\">$value</textarea>";
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
            echo "<input type=\"$type\" name=\"$name\" value=\"$value\">";
            break;
      }
      if ($type!="hidden") {
         echo "</td>\n";
         echo "</tr>\n";
      }
   }

	   
}

# return dirs sorted
class SortDir {
   var $items;

   function SortDir($directory) {
      $handle=opendir($directory);
      while ($file = readdir($handle)) {
         if ($file != "." && $file != "..") {
            $this->items[]=$file;
         }
      }
   closedir($handle);
   natsort($this->items);
   }

   function Read() {
      $getback= (pos($this->items));
      next($this->items);
      return $getback;
   }
}

?>
