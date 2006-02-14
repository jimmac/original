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

// to hide and show the comment block
// inspired by www.wikipedia.org
function toggle_comment() {
    var comment_form = document.getElementById('comment_form');
    var showlink=document.getElementById('showlink');
    var hidelink=document.getElementById('hidelink');
    if(comment_form.style.display == 'none') {
	comment_was = comment_form.style.display; 
	comment_form.style.display = '';
	hidelink.style.display='';
	showlink.style.display='none';
    } else {
	comment_form.style.display = comment_was;
	hidelink.style.display='none';
	showlink.style.display='';
    }
}

function toggle_div(classname) {
	var div = document.getElementById(classname);
    if(div.style.display == 'none') {
			div.style.display = 'block';
		} else {
			div.style.display = 'none';
		}
}

