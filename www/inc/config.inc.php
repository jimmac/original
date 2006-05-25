<?php
#base dirname
   //dirname("__FILE__/..")
	$ThisScript=preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
	$ScriptFileName=$_SERVER['SCRIPT_FILENAME'];
	$HostName=$_SERVER['SERVER_NAME']; 
	$ThisUrl = $_SERVER['REQUEST_URI'];
  eregi("^(.*)/[^/]*$", $ScriptFileName, $x);
  $root = $x[1];
# ===========================================================================
# dir index
	 $sortinmonth = 0;// 1 - alphabetically
	 								  // 0 - by date (reverse)

# ===========================================================================
# default languages
# use UA's accept language
require_once("$root/inc/l10nget.inc.php"); //get from UA
if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
	$sclang = get_lang_from_agent($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
} else {
	$sclang = "en";
}
l10n::set("$root/l10n/".$GLOBALS['sclang']."/exif.lang");

# ===========================================================================
#Name to dsplay on the gallery
$scnamegallery = "Photo Gallery Index";


# ===========================================================================
# albums to show
   $yearsince = 1999;
#images FIXME - this is stylesheet's job
   $img["left"] = "left.png";
   $img["right"] = "right.png";
   $img["top"] = "top.png";
# ===========================================================================
#thumbnail behaviour
   //keep this unset for dynamic thumbnail size
   //$thumbsize = "fixed";
   //How the previous and Next thumbnails should be scaled
   // 1 is 1:1, >1 is smaller, <1 is bigger
   $PNthumbScale = 1.5;
# Photos Copyright
#	CHANGE THIS! I am not trying to take over the world ;)
	 $copy = "Copyright &copy; 1999-2005 Jakub Steiner";
#app info
   $app["name"] = "Original"; // opensource remote image gallery,
                              // initialy not as lovely 
   $app["url"] = "http://jimmac.musichall.cz/original.php3";
   $app["version"] = "0.12pre";
# ===========================================================================
# EXIF metadata app path (helper app for php3 and older php4)
# uncomment the method you want to use if you want EXIF data reported
# ---------------------------------------------------------------------------
## use internal function of PHP 4:
   $exif_prog = "php4";
   $exif_show = array("DateTime"=>__("Time Taken"), 
	 						"Make"=>__("Camera Manufacturer"), 
	 						"Model"=>__("Camera Model"), 
							"FocalLength"=>__("Real Focal Length"), 
	 						"FocalLengthIn35mmFilm"=>__("Focal Length Relative to 35mm Film"),
	 						"FNumber"=>__("F Stop"), 
							"ExposureTime"=>__("Time of Exposure"), 
							"ISOSpeedRatings"=>__("Film/Chip Sensitivity"),
							"Flash"=>__("Flash"));
# ---------------------------------------------------------------------------
## use metacam (give absolute path to the binary on the server):
#   $exif_prog = "/usr/local/bin/metacam";
## what EXIF data to show (if unset, all will be shown)
## some example fields for metacam:
#   $exif_show = array("Image Capture Date", "Make", "Model",
#                "Exposure Program", "Exposure Mode",
#                "Focal Length", "Exposure Time",
#                "Aperture Value", "ISO Speed Rating", "White Balance",
#                "Flash", "Scene Capture Type",
#                "Metering Mode", "Max Aperture Value", "Shutter Speed Value"
#                );
# ---------------------------------------------------------------------------
## use jhead (give absolute path to the binary on the server):
#  $exif_prog = "/usr/local/bin/jhead";
## what EXIF data to show (if unset, all will be shown)
## some example fields for jhead:
# $exif_show = array(
#                    "Date/Time",
#                    "Camera make",
#                    "Camera model",
#                    "Focal length",
#                    "Exposure time",
#                    "Aperture Value",
#                    "ISO equiv.",
#                    "Exposure",
#                  );
# ===========================================================================
## Gallery Directory
# This is a path relative to the directory where original is installed
# eg. it can be "../galleries" to use a galleries dir above the original dir.
  $gallery_dir="../galleries";

#Enable this to access extended tracking functionality
#depends on sqlite
$have_sqlite = 0;

# This controls wheather web visitors will be able to post
# comments to images
$comments = 1;

# Access Log/Counter
# $log_access = 0; // no access logging
$log_access = 1; 

#css styles
   $themes = array(
               "dark" => "inc/styles/dark/dark.css",
               "classic" => "inc/styles/classic/classic.css",
               "gorilla" => "inc/styles/gorilla/gorilla.css"
   );




?>
