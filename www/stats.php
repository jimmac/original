<?php
require_once ("lib/lib.l10n.php");
require_once("inc/config.inc.php");
require_once("inc/www.class.inc.php");
require_once("inc/funkce.inc.php");

l10n::set("$root/l10n/".$sclang."/main.lang");

$page = new C_www;
if ($GLOBALS['have_sqlite']) {
	$page->header("Photo Statistics");
	require("inc/header.inc.php");
	//recent views
	print "<h2>Recently Viewed</h2>";
	//recently commented
	print "<h2>Recently Commented</h2>";
	//most viewed
	print "<h2>Most Viewed</h2>";
	//most discussed
	print "<h2>Most Discussed</h2>";
} else {
	$page->error("No SQLite", "You need SQLite to use view statistics.");
}
$page->footer();
?>
