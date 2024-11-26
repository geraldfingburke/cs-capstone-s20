<?php
	//This site is a mixture of HTML and CSS, which is interpreted and rendered by the web browser,
	//  and server-side PHP code, which the web server executes as the page is retrieved by the browser.
	//PHP code exists between <?php and ?\> tags. You can add PHP code in the middle of any HTML on this page using these tags.
	//A common thing PHP code will do is "echo"/output HTML tags, which becomes part of the HTML of the page, allowing for dynamic content.

	//This PHP function starts a new session, or resumes the current session if one already exists.
	//It's able to identify who the user is because of a PHPSESSID browser cookie that the user's browser sent to the server.
	//After this call, you'll be able to access PHP session variables in the $_SESSION array.
	session_start();
	
	//Now that we've started/resumed the session, let's destroy it (which is the purpose of the logout.php page).
	session_unset();
	session_destroy();

	//This PHP function includes all of the HTML and PHP from the "header.php" file.
	include('header.php');
?>

<!-- THIS IS THE START OF THE HTML BODY THAT APPEARS ON THE LOGOUT PAGE OF THE SITE. CHANGE IT HOWEVER YOU LIKE. -->

<!-- There really isn't much to do except display a yellow Bootstrap 4 "alert" box that the user is now logged out. -->
<div class="container">
	<div class="alert alert-warning" role="alert">You have been logged out.</div>
</div>

<!-- THIS IS THE START OF THE HTML BODY THAT APPEARS ON THE LOGOUT PAGE OF THE SITE. CHANGE IT HOWEVER YOU LIKE. -->

<?php
	//This includes all the HTML from "footer.php", which displays the navigation links and other stuff at the bottom of every page.
	include('footer.php');
?>
