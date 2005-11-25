<?php
function navigation ($gallery, $snapshot, $image) {
   global $gallery_dir, $root, $ThisScript, $textnav, $img, 
          $show_thumbs, $exif_style, $PNthumbScale;

   $next = $snapshot + 1;
   $prev = $snapshot - 1;

   if (!$image) { // this will render a navigation bar - max 3 buttons
      echo "\n<div class=\"navbuttons\">\n";
      echo "<div class=\"navbuttonsshell\">\n";
      if ($snapshot > 1) { //previous 
         echo "<a id=\"previcon\" href=\"$ThisScript?galerie=$gallery&amp;snimek=$prev";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\">";
         echo "&lt; previous</a>\n";
      }
      echo "&nbsp;";
      if (is_file("$gallery_dir/$gallery/lq/img-$next.jpg")) { //next
         echo "<a id=\"nexticon\" href=\"$ThisScript?galerie=$gallery&amp;snimek=$next";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\">";
         echo "next &gt;</a>\n";
      }
      echo "</div>\n</div>\n";
   } elseif ($image=="prev") { //previous thumbnail
      if ($snapshot > 1) { //previous 
         echo "<div class=\"prevthumb\">";
         echo "<a href=\"$ThisScript?galerie=$gallery&amp;snimek=$prev";
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
         echo "<br />Previous";
         echo "</a></div>\n";
      }
   } else { //next thumbnail
      if (is_file("$gallery_dir/$gallery/lq/img-$next.jpg")) {
         echo "<div class=\"nextthumb\">";
         echo "<a href=\"$ThisScript?galerie=$gallery&amp;snimek=$next";
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
         echo "<br />Next";
         echo "</a></div>\n";
      }
   }

}

function check($file) {
   global $gallery_dir, $page;
   
#   if (eregi("[^0-9a-z\_\-\ ]",$file) || !file_exists("$gallery_dir/$file")) {
#   if (eregi("CVS",$file) || !file_exists("$gallery_dir/$file")) {
   if (!file_exists("$gallery_dir/$file")) {
      echo "funkce.inc.php/check(): Bad input";
      $page->footer();
      exit;
   }
}

function browserCheck() {
   global $HTTP_USER_AGENT;
   
   if (eregi("(MSIE.[456789]).*Mac.*",$HTTP_USER_AGENT)) {
        return("macie4+");
   } elseif (eregi("(MSIE.[678])",$HTTP_USER_AGENT)) {
        return("ie6+");
   } elseif (eregi("(MSIE.[45])",$HTTP_USER_AGENT)) {
        return("ie4+");
   } elseif (eregi("Opera",$HTTP_USER_AGENT)) {
        return("opera");
   } elseif (eregi("(Mozilla.4)",$HTTP_USER_AGENT)) {
        return("netscape4");
   } elseif (eregi("(Mozilla.[5-9])",$HTTP_USER_AGENT)) {
        return("mozilla");
   } elseif (eregi("KMeleon",$HTTP_USER_AGENT)) {
        return("mozilla");
   } else {
        return("Netscape3");
   }
}

?>
