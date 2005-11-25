<script language="JavaScript">
// javascript snipplets mostly nicked from
// http://liorean.web-graphics.com/scripts

//cookie handling

var cookie={  // The Cookie Handler main object
  Get:function(n){  // Function for getting cookies
    var re=new RegExp(n+'=([^;]*);?','gi');  // Create regex for cookies fetching
    var r=re.exec(document.cookie)||[];  // Fetch cookie using regex
    return unescape(r.length>1?r[1]:null)  // Return unescaped cookie
  },
  Set:function(n,v,e,p,d,s){  // Function for setting cookies
    var t=new Date;  // Get current time and date
    if(e)  // If days to expiry is set
      t.setTime(t.getTime()+(e*8.64e7));  // calculate expiry date
    document.cookie=n+'='+escape(v)+'; '+(!e?'':'; expires='+t.toUTCString())+(!p?'':'; path='+p)+(!d?'':'; domain='+d)+(!s?'':'; secure')  // Set cookie
  },
  Del:function(n,p,d){  // Function for deleting cookies
    var t=cookie.Get(n);  // Get cookie
    document.cookie=n+'='+(!p?'':'; path='+p)+(!d?'':'; domain='+d)+'; expires=Thu, 01-Jan-70 00:00:01 GMT';  // Delete cookie
    return t  // Return the deleted cookie
  },
  Sup:function(){  // Function for detecting cookies support
    cookie.Set('c',true);  // Set dummy cookie
    return cookie.Del('c');  // Return whether dummy was written
  }
};


// theme switching

var style={  // Theme Switcher main object
  Set:function(t){  // Function for setting active theme
    for(var i in this.col)  // For each existing title
      for(var j=0,f;(f=(j<this.col[i].length)?this.col[i][j]:null);j++)  // And all stylesheets of that title
        f.disabled=i!=t?true:false;  // Set to enabled or disabled depending on whether title matches user input
  },
  Get:function(){ // Function for determining active theme
    for(var i in this.col)  // For each existing title
      if(!this.col[i][0].disabled)  // Unless disabled
        return i;  // Return title
    return this.Pref()  // Otherwise try to determine preferred title
  },
  Pref:function(){  // Function to determine preferred title
    for(var i in this.col)  // For each existing title
      if(!this.col[i][0].disabled)  // Unless disabled
        return i;  // Return title
    return null  // Otherwise return null
  },
  sum:function(){  // Function to collect existing titles into a collection
    var s=document.styleSheets,i=0;  // Set needed variables
    for(var f;(f=(i<s.length)?s[i]:null);i++)  // For each existing stylesheet
      switch(f.title){  //  Read title
        case '':  // If none or blank
          break;  // Exit
        default:  // Otherwise
          switch(typeof this.col[f.title]){  // Read title
          case 'object':  // If exists in collection
            this.col[f.title][this.col[f.title].length]=f;  // Add stylesheet to that title in the collection
            break;  // Exit
          default:  // Otherwise
            this.col[f.title]=[f]  // Add new titla to collection and add stylesheet to that title
        }
      }
  },
  onload:function(){  // Function to send to onload handler
    style.sum();  // Collect titles
    if(cookie.Sup()){  // If cookies support exists
      var c=cookie.Get('style');  // Get preferred theme from cookie
      style.Set(c||style.Pref())  // Otherwise use the author specified
    }
  },
  onunload:function(){  // Function to send to onunload handler
    if(cookie.Sup()){  // If cookies support exists
      var s=style.Get();  // Get active theme
      cookie.Set('style',s,356,'/')  // Write active theme to cookie
    }
  },
  col:{}  // Collection for titles
};

event.Add(style.onload);  // Add onload handler
window.onunload=style.onunload;  // Add onunload handler


// IE/Mac voodoo

var event={  // The Event Handler main object
  Add:function(f){  // Function for adding onload handlers
    event.col[event.col.length]=f;  // Add event handler to collection
    if(typeof window.addEventListener!='undefined')  // If W3C compliant
      window.addEventListener('load',f,false);  // Apply event handler
    else if(!event.ieSet)  // Otherwise, unless already set
      if(typeof document.onreadystatechange!='undefined')  // If supported
        document.onreadystatechange=event.onload;  // Add In event handler handler
    event.ieSet=true;  // Specify that event handler already is set
    return(typeof window.addEventListener!='undefined')  // Return whether W3C compliant
  },
  onload:function(){  // Function for handling multiple onload handlers in IE
    var m=/mac/i.test(navigator.platform);  // Detect whether mac
    if(typeof document.readyState!='undefined')  // If supported
      if(m?document.readyState!='interactive':document.readyState!='complete')  // And not already finished
        return;  // Exit
    for(var i=0,f;(f=(i<event.col.length)?event.col[i]:null);i++)  // For all event handlers
      f();  // Run event handler
    return  // Exit
  },
  ieSet:false,  // Variable to say whether event handler is set or not
  col:[]  // Collection for event handlers
};
</script>
