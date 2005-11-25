<script language="JavaScript">
function setActiveStyleSheet(title) {
   var i, a, main;
   for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
     if(a.getAttribute("rel").indexOf("style") != -1
        && a.getAttribute("title")) {
       a.disabled = true;
       if(a.getAttribute("title") == title) a.disabled = false;
     }
   }
   if (title!="") {
      writeCookie(title);
   }
}

function getInactiveStyleSheet() {
  var i, a;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title") && a.disabled) return a.getAttribute("title");
  }
  return null;
}

function readCookie() {
    var theme = document.cookie;
    var theme = unescape(theme);

    return theme;
}

function writeCookie(theme) {
   //FIXME - set expires
   var original_cookie = "theme=" + escape(theme);
   document.cookie = original_cookie;
}

function checkForTheme() {
   var theme = readCookie();
   //alert(theme);
   if (theme=="undefined") {
      var theme = "none";
   }
}

// what a kludge. Luckily I found a clean way
function alignForGorilla() {
var image_preview = document.getElementById('preview');
image_preview.style.marginLeft = "-" + (image_preview.width/2 + 16) + "px";
}

</script>
