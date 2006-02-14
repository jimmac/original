<?php


function formatEXIF ($k,$v) {
	// format some special cases
	// dunno if EXIF support formats it so weirdly everywhere.
	// Sure does in my php4

	switch ($k) {
		case "FocalLength":
			$out = eval("return $v;"); 
			$out .= "mm";
			return $out;
			break;
		case "FNumber":
			$out = "f/";
			$out .= eval("return $v;");
			return $out;
			break;
		case "ExposureTime":
			// numerator = cistatel; jmenovatel = denominator :)
			$x = explode("/",$v,2);
			if ($x[0]>=100) {
				$out = eval("return ${x[0]}/${x[1]};");
				$out .= "s";
				return $out;
			} else {
				return "1/".(1.0/($x[0]/$x[1]))."s";
			}
			break;
		case "Flash";
			if ($v=="0") {
				return "No Flash";
			} else {
				return $v;
			}
			break;
		case "ISOSpeedRatings":
			return "ISO" . $v;
			break;
		case "GainControl";
			return "EV" . $v;
			break;
		case "FocalLengthIn35mmFilm":
			return $v . "mm";
			break;
		case "DateTime":
			//return date("M d Y H:i:s", $v);
			ereg("^([0-9]{4}):([0-9]{1,2}):([0-9]{1,2}) (.*)",$v,$x);
			return $x[1] . "/" . $x[2] . "/" . $x[3] . " " . $x[4];
			break;
		default:
			return $v;
	}
}


if ($exif_prog=="php4") {
   // php internal handling
	 // $file is LQ image
   $exif_array = exif_read_data("$file");
	 reset($exif_array);
	 if ($exif_show) reset($exif_show);

	 if ($exif_array["Make"]) { // only render all this 
		 													// if there is EXIF header
			// fancy table look
			echo "<div id=\"exif_table\" style=\"display: none;\">\n";
			echo "<table class=\"exif\" border=\"0\">\n";
			//co ukazat (podle exif_show)
			if ($exif_show) {
				 while (list($kx,$x) = each($exif_show)) {
					 while(list($k,$v)=each($exif_array)) {
					 if ($kx==$k) {
						 echo "<tr>";
						 echo "<td align=\"right\">";
						 echo $x;
						 echo ": </td>";
						 echo "<td><b>";
						 echo formatEXIF($k,$v);
						 echo "</b></td>";
						 echo "</tr>\n";
					 }
					 }
					 reset($exif_array);
				 }
			} else {
				 //ukaze vsechno v tabulce
				 while(list($k,$v)=each($exif_array)) {
						echo "<tr>";
						echo "<td align=\"right\">";
						echo $k;
						echo ": </td>";
						echo "<td><b>";
						echo formatEXIF($k,$v);
						echo "</b></td>";
						echo "</tr>\n";
				 }
			}
			echo "<tr>\n";
			echo "<td></td>";
			echo "<td><a href=\"javascript:toggle_div('exif_table');toggle_div('exif_line');\">" . __("Less info");
			echo "</a></td>";
			echo "</tr>\n";
			echo "</table>\n";
			echo "</div>\n";





			// selected EXIF header on one line
			echo "<div id=\"exif_line\">\n";
			echo "<p class=\"exif\">";
			reset($exif_array);
			if ($exif_show) reset($exif_show);
			if ($exif_show) {
				 while (list($kx,$x) = each($exif_show)) {
					 while(list($k,$v)=each($exif_array)) {
							 if ($kx==$k) {
									echo "<span title=\"$x\">";
									echo formatEXIF($k,$v);
									echo "</span> | ";
							 }
						}
				 reset($exif_array);
				 }
			} else {
				/* vsechny exif headers inline */
				 while(list($k,$v)=each($exif_array)) {
						echo "<span title=\"";
						echo $k;
						echo "\">";
						echo formatEXIF($k,$v);
						echo "</span> | ";
				 }
			}

			echo "<a href=\"javascript:toggle_div('exif_table');toggle_div('exif_line');\">" . __("More info");
			echo "</a></p>\n";
			echo "</div>\n";
 		}








	 
} else {
   // the old code, handles e.g. metacam and jhead as EXIF extractors
   // loading lq means it won't work if the convertor doesn't copy EXIF data
   // (newer ImageMagick can)
	 // PATCHES WELCOME
   exec("$exif_prog \"$gallery_dir/$galerie/lq/img-$snimek.jpg\"", $exif_data, $exif_status);
   if ($exif_status!="2") {
      if ($exif_style=="descriptive") {
         // fancy table look
         echo "<table border=\"0\" align=\"center\" class=\"exif\">\n";
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
            echo "<td><a href=\"$ThisScript?galerie=$galerie&photo=$snimek";
            echo "&exif_style=simple&show_thumbs=$show_thumbs\">";
            echo "<b>display in-line</b></a></td>\n";
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
            echo "<a href=\"$ThisScript?galerie=$galerie&photo=$snimek";
            echo "&exif_style=descriptive&show_thumbs=$show_thumbs\">";
            echo "<b>display table</b></a>\n";
            echo "</p>\n";
         }
      }
   }
}
?>
