<?php 
/* 
 * The HTML contained in this file is valid XHTML 1.0 Strict 
 */

echo "<div id=\"comment_block\">\n";

?>
	<?php echo"<div id=\"showhideform\"><strong> " . __('Post a Comment') . "</strong>:"; ?>
	<span class="comment_toggle"> 
	[&nbsp;
	<?php echo"<a href=\"javascript:toggle_comment()\"><span id=\"showlink\">" . __('Show Form') . "</span><span id=\"hidelink\" style=\"display:none;\">" . __('Hide Form') . "</span></a>"; ?>
	&nbsp;]
	</span>
	</div>
	
	<div id="comment_form" style="display: none;">
<?php
$this->form_start($ThisUrl, "post", NULL);
$this->input("text", "commentname", $username, __('Name:'), NULL, __('Enter your name.') );
$this->input("checkbox", "savecommentname", "1", __('Remember Name:'), "yes", 
             __('Should the browser remember your name?'));
$magic_number = random_digits(4);
//temporary. should generate an image instead
echo "<div class=\"row\"><div class=\"control\">$magic_number</div></div>\n";
$this->input("hidden", "commentkolacek", md5($magic_number), NULL, NULL, NULL);
$this->input("text", "commentspamcheck", "", __('Retype PIN Above:'), NULL, __('Enter the number shown above.'));
$this->input("textarea", "commentdata", "", __('Comment') . " :" , NULL, __('Allowed HTML tags: a,b,i,ul,li,blockquote,br.') );
$this->input("submit", "", __('Send') , NULL, NULL, NULL);
$this->form_end();
?>
	</div>
</div>
