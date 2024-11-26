<?php
	//This site is a mixture of HTML and CSS, which is interpreted and rendered by the web browser,
	//  and server-side PHP code, which the web server executes as the page is retrieved by the browser.
	//PHP code exists between <?php and ?\> tags. You can add PHP code in the middle of any HTML on this page using these tags.
	//A common thing PHP code will do is "echo"/output HTML tags, which becomes part of the HTML of the page, allowing for dynamic content.

	//This PHP function starts a new session, or resumes the current session if one already exists.
	//It's able to identify who the user is because of a PHPSESSID browser cookie that the user's browser sent to the server.
	//After this call, you'll be able to access PHP session variables in the $_SESSION array.
	session_start();

	//There are two possibilities:
	//	1. The user went to this page (login.php) in a browser, meaning it was a GET request.
	//	2. The user ended up on this page (login.php) because they clicked "Submit" on this page, meaning it was a POST request.
	//We need to determine if it was because of a POST, and if so, handle the login attempt using the passed-in username and password.

	//Start off by assuming we didn't fail to log in.
	$login_failed = FALSE;

	//Check to see if the "user_name" or "password" variables exist in the $_POST PHP array (which holds all the variables from the POST request).
	if (isset($_POST["user_name"]) && isset($_POST["password"]))
	{
		//One or both do exist, so the user attempted to log in.
		//We need access to all of the PHP functions in "db.php", so include it now.
		include_once('db.php');

		//Validate the username and password by passing it into db.php's validate_user function, which queries our MySQL USERS table
		//  to see if everything is correct.
		//This PHP function returns NULL if the login was unsuccessful, or an array of key/value pairs containing information about the user if the login was successful.
		$user_info = validate_user($_POST["user_name"], $_POST["password"]);
		if (!is_null($user_info))
		{
			//The username and password was correct! Now save this information in the PHP $_SESSION array, which will be available to any
			//  of our pages for as long as the user is logged in. Once the user logs out, $_SESSION will not have anything in it anymore.
			//Other files (such as header.php) look at the $_SESSION array to see if there's anything in it (i.e. the user is logged in).
			//	It does this to know whether to display "Login" or "Logout" links.
			$_SESSION["USER_ID"] = $user_info["USER_ID"]; //USER_ID is a unique ID for the user. Even if the user's username changes, this ID won't change.
			$_SESSION["USER_NAME"] = $_POST["user_name"]; //USER_NAME is the user's username.
			$_SESSION["IS_ADMIN"] = $user_info["IS_ADMIN"]; //IS_ADMIN is a flag indicating whether they have admin rights (i.e. can use admin.php). It's either 0 (no) or 1 (yes).
		}
		else
		{
			//The username and password was incorrect. :(
			//We set the $login_failed PHP variable to TRUE, so that the code further on down knows the attempt was unsuccessful and should let the user know.
			$login_failed = TRUE;
		}
	}

	//This PHP function includes all of the HTML and PHP from the "header.php" file.
	//header.php does a lot of stuff for you, such as displaying the HTML at the top of the page,
	//	starting/resuming the PHP session (so you can access the user's session variables),
	//  and including db.php, which has all the code for interacting with MySQL.
	include('header.php');
?>

<div class="container">
<?php
	//If $user_info isn't NULL, then that means there was a successful login attempt.
	//Notice that we temporarily break out of "PHP mode" so we can include some HTML without having to use <?php ?\> tags.
	//	This isn't necessary, but it's easier because we don't have to use PHP "echo" statements.
	if (!is_null($user_info))
	{
?>
	<!--
		Display the yellow Bootstrap 4 "alert" box saying the user is logged in.
		Notice that because this is in the PHP if {} block, it's only displayed if $user_info was non-NULL (meaning, the user successfully logged in).
	-->
	<div class="container">
		<div class="alert alert-warning" role="alert">You have been logged in.</div>
	</div>
<?php
	}
	else
	{
		//If we got here, we know we aren't logged in.
		//Is it because we tried *and* failed to log in? If so, $login_failed would be TRUE, so we check for that.
		if ($login_failed)
		{
			//Yes, so let the user know they were unsuccessful.
			//Notice this was done with a PHP "echo" statement -- it isn't necessary, it's just easier because it's a single line of HTML.
			echo "<div class=\"container\"><div class=\"alert alert-warning\" role=\"alert\">Invalid credentials!</div></div>";
		}
?>

	<!--	This is the HTML form for logging in. It has username and password textboxes, and a "Submit" input button.
			When the "Submit" button is clicked, it performs a POST request to "login.php" (this very same page), which
				has code to detect that and try validating the username and password.
	-->
	<h2>Log In</h2>
	<form action="login.php" method="POST">
		<div class="form-group">
			<label for="user_name">User Name:</label>
			<input type="text" class="form-control" id="user_name" placeholder="Enter user name" name="user_name">
		</div>
		<div class="form-group">
			<label for="password">Password:</label>
			<input type="password" class="form-control" id="password" placeholder="Enter password" name="password">
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>
	</form>

<?php
	//This ends the PHP if {} block that started with: if (!is_null($user_info))
	}
	echo "</div>";

	//This includes all the HTML from "footer.php", which displays the navigation links and other stuff at the bottom of every page.
	include('footer.php');
?>
