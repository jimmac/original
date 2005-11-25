<div class="stylenavbar">
[ style: 
<?php
foreach ($themes as $skin => $url) {
      echo "<a href=\"#\" title=\"$skin\"";
      echo " onclick=\"setActiveStyleSheet('$skin')\">";
      echo "$skin</a> \n";
}
?>
]
</div>
<?php
echo "<h1 class=\"title\"><a href=\"http://$HostName$ThisScript\">Photo Gallery<span /></a></h1>\n\n";
?>
