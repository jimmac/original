<?php
if ($exif_prog=="php4") {
   // php internal handling
   // read_exif_data is supposed to read the whole jpeg into memory,
   // so we'll use the thumbnails to save it
   // THIS IS COMPLETELY UNTESTED!!!!
   $exif_array = read_exif_data("$thumb");
   if ($exif_style=="descriptive") {
      // fancy table look
      echo "<table border=\"0\" align=\"center\">\n";
      //co ukazat (podle exif_show
      if ($exif_show) {
         while(list($k,$v)=each($exif_array)) {
            while ($x = current($exif_show)) {
               if ($x==$k) {
                  echo "<tr>";
                  echo "<td align=\"right\">$k :</td>";
                  echo "<td><b>$v</b></td>";
                  echo "</tr>\n";
               }
               next($exif_show);
            }
         }
      } else {
         //ukaze vsechno
         while(list($k,$v)=each($exif_array)) {
            echo "<tr>";
            echo "<td align=\"right\">$k :</td>";
            echo "<td><b>$v</b></td>";
            echo "</tr>\n";
         }
      }
      echo "<tr>\n";
      echo "<td></td>";
      # only show if EXIF header exists
      if ($exif_array["Make"]) {
         echo "<td><a href=\"$ThisScript?galerie=$galerie&snimek=$snimek";
         echo "&exif_style=simple&show_thumbs=$show_thumbs\">";
         echo "<b>display line</b></a></td>\n";
      }
      echo "</tr>\n";
      echo "</table>\n";
      echo "</table>";
   } else {
      // in one line
      if ($exif_show) {
         while(list($k,$v)=each($exif_array)) {
            while ($x = current($exif_show)) {
               if ($x==$k) {
                  echo "$v | ";
               }
               next($exif_show);
            }
         }
      } else {
         while(list($k,$v)=each($exif_array)) {
            echo "$v | ";
         }
      }
      # only show if EXIF header exists
      if ($exif_array["Make"]) {
         echo "<a href=\"$ThisScript?galerie=$galerie&snimek=$snimek";
         echo "&exif_style=descriptive&show_thumbs=$show_thumbs\">";
         echo "<b>display table</b></a>\n";
         echo "</p>\n";
      }  
   }
   
} else {
   // the old code, handles e.g. metacam and jhead as EXIF extractors
   // loading lq means it won't work if the convertor doesn't copy EXIF data
   // (newer ImageMagick can)
   exec("$exif_prog \"$gallery_dir/$galerie/lq/img-$snimek.jpg\"", $exif_data, $exif_status);
   if ($exif_status!="2") {
      if ($exif_style=="descriptive") {
         // fancy table look
         echo "<table border=\"0\" align=\"center\">\n";
         while ($x = current($exif_data)) {
            eregi("^ *([^:]*): *(.*)", $x, $y);
            //filter according to $exif_show array
            if (!$exif_show) { //all fields shown
                     echo "<tr>";
                     echo "<td align=\"right\">$y[1] :</td>";
                     echo "<td><b>$y[2]</b></td>";
                     echo "</tr>\n";
            } else {
               reset($exif_show);
               while ($z = current($exif_show)) {
                  //echo ".$z. ::: .$y[1].<br>";
		  if (trim($z) == trim($y[1])) {
                     echo "<tr>";
                     echo "<td align=\"right\">$y[1] :</td>";
                     echo "<td><b>$y[2]</b></td>";
                     echo "</tr>\n";
                  }
               next($exif_show);
               }
            }
            next($exif_data);
         }
         echo "<tr>\n";
         echo "<td></td>";
      # only show if EXIF header exists
      if ($y[1]!="File") { // don't show when no EXIF
            echo "<td><a href=\"$ThisScript?galerie=$galerie&snimek=$snimek";
            echo "&exif_style=simple&show_thumbs=$show_thumbs\">";
            echo "<b>display line</b></a></td>\n";
      }
         echo "</tr>\n";
         echo "</table>\n";
      } else {
         //simple plaintext look
         echo "<p class=\"exif\" align=\"center\">";
         while ($x = current($exif_data)) {
            eregi("^ *([^:]*): *(.*)", $x, $y);
            if (!$exif_show) { //all fields shown
               echo "$y[2] |";
            } else {
               reset($exif_show);
               while ($z = current($exif_show)) {
		  if (trim($z) == trim($y[1])) {
                     echo "$y[2] | ";
                  }
                  next($exif_show);
               }
            }
            next($exif_data);
         }
         # only show if EXIF header exists
         if ($y[1]!="File") {
            echo "<a href=\"$ThisScript?galerie=$galerie&snimek=$snimek";
            echo "&exif_style=descriptive&show_thumbs=$show_thumbs\">";
            echo "<b>display table</b></a>\n";
            echo "</p>\n";
         }
      }
   }
}
?>
