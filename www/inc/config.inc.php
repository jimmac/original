<?
#base dirname
   eregi("^(.*)/[^/]*$", $ScriptFileName, $x);
   $root = $x[1];
# ===========================================================================
#images
   $img["left"] = "left.png";
   $img["right"] = "right.png";
   $img["top"] = "top.png";
# ===========================================================================
#thumbnail behaviour
   //keep this unset for dynamic thumbnail size
   //$thumbsize = "fixed";
   //How the previous and Next thumbnails should be scaled
   // 1 is 1:1, >1 is smaller, <1 is bigger
   $PNthumbScale = 1;
# ===========================================================================
#colors and backgrounds
# this has been removed in favour of custom CSS stylesheets
# ===========================================================================
#app info
   $app["name"] = "Original"; // opensource remote image gallery,
                              // initialy not as lovely 
   $app["url"] = "http://jimmac.musichall.cz/original.php3";
   $app["version"] = "0.8-cvs";
# ===========================================================================
# EXIF metadata app path (helper app for php3 and older php4)
# uncomment the method you want to use if you want EXIF data reported
# ---------------------------------------------------------------------------
## use internal function of PHP 4 (does not seem to work yet):
#   $exif_prog = "php4";
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
   $gallery_dir="galleries";

#css styles
   $themes = array(
               "default" => "inc/styles/default/default.css",
               "ie" => "inc/styles/ie/ie.css",
               "gorilla" => "inc/styles/gorilla/gorilla.css"
   );
?>
