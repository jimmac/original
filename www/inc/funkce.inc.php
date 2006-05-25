<?php

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
   
	 $HTTP_USER_AGENT=$_SERVER["HTTP_USER_AGENT"];
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

function infoParse ($infofile) {
	
	$info_array = file($infofile);
	foreach ($info_array as $line) {
		list($key,$value) = split("\|",$line);
		$result[$key]=$value;
	}
	return $result;
}

function readInfo ($infofile, $file) {
	global $galerieyear, $galeriemonth, $galerieday, $galeriedesc, $galerieauthor,
	       $galeriename, $galerielogin, $galeriepw, $gallery_dir;
	
	if (file_exists($infofile)) {
		//read from info.txt
		$info_array = infoParse($infofile);
		if ($info_array["date"]) {
			// try to be a little smarter about format
			if (ereg("([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})",
				$info_array["date"])) {
				// remain compatible - DD.MM.YYYY
				list($day,$month,$year) = split("\.", $info_array["date"]);
				$year = rtrim($year);
				$month = rtrim($month);
				$day = rtrim($day);
				$info_array["date"] = "$year-$month-$day"; //make it US date
			}
			// US date format at this point
			$tstamp = strtotime($info_array["date"]);
		} else {
			$tstamp = filemtime("$gallery_dir/$file");// Get from filesystem
		}
		$galerieyear["$file"] = date("Y", $tstamp);
		$galeriemonth["$file"] = date("m", $tstamp);
		$galerieday["$file"] = date("d", $tstamp);
		
		if (@$info_array["description"]) {
			$galeriedesc["$file"] = rtrim($info_array["description"]);
		}
		
		if (@$info_array["author"]) {
			$galerieauthor["$file"] = rtrim($info_array["author"]);
		}
		
		if (@$info_array["name"]) {
			$galeriename["$file"] = rtrim($info_array["name"]);
		}
		
		if (@$info_array["restricted_user"]) {
			$galerielogin["$file"] = rtrim($info_array["restricted_user"]);
			$galeriepw["$file"] = rtrim($info_array["restricted_password"]);
		}
 } else { // Get Dates from modification stamp
		$mtime = filemtime("$gallery_dir/$file");
		$galerieyear["$file"] = date("Y", $mtime);
		$galeriemonth["$file"] = date("m", $mtime); //F
		$galerieday["$file"] = date("d", $mtime);
 }
}

function access_check($login, $password,$realm) {
   if (!($_SERVER['PHP_AUTH_USER']=="$login" && $_SERVER['PHP_AUTH_PW']=="$password")) {
      header("WWW-authenticate: Basic Realm=$realm");
      Header("HTTP/1.0 401 Unauthorized");
			$err = new C_www;
      $err->header("Access Denied");
			echo "<div class=\"error\">\n";
			echo "<h1>Access Denied</h1>\n";
			echo "<p>Sorry, this gallery is restricted</p>\n";
			echo "<p><a href=\"index.php\">Return to index</a></p>\n";
			echo "</div>\n";
			$err->footer();
      exit;
   }

}

function random_digits($times) {
	$random="";
	for ($i=0;$i<$times;$i++) {
		$random .= rand(0,9);
	}
	return $random;
}

function get_photo_title($galerie, $id) {
  global $gallery_dir;
  if ($title = @file_get_contents("$gallery_dir/$galerie/comments/${id}.txt")) {
    $title = trim(preg_replace('/[\s\n\r]+/', ' ', strip_tags($title)));
    if (strlen($title) > 80)
      $title = trim(substr($title, 0, 77)) . "...";
  } else 
    $title = "Photo ${id}";
  return $title;
}

?>
