<?php
	//This site is a mixture of HTML and CSS, which is interpreted and rendered by the web browser,
	//  and server-side PHP code, which the web server executes as the page is retrieved by the browser.
	//PHP code exists between <?php and ?\> tags. You can add PHP code in the middle of any HTML on this page using these tags.
	//A common thing PHP code will do is "echo"/output HTML tags, which becomes part of the HTML of the page, allowing for dynamic content.

	//This PHP function starts a new session, or resumes the current session if one already exists.
	//It's able to identify who the user is because of a PHPSESSID browser cookie that the user's browser sent to the server.
	//After this call, you'll be able to access PHP session variables in the $_SESSION array.
	session_start();

	//Now that the session is started/resumed, we can access the user ID and username from the PHP $_SESSION (if we're logged in).
	//If these aren't set or are null, then we aren't logged in.
	$user_id = $_SESSION["USER_ID"]; //USER_ID is a unique ID for the user. Even if the user's username changes, this ID won't change.
	$user_name = $_SESSION["USER_NAME"]; //USER_NAME is the user's username.

	//This PHP function includes all of the HTML and PHP from the "header.php" file.
	//header.php does a lot of stuff for you, such as displaying the HTML at the top of the page,
	//	starting/resuming the PHP session (so you can access the user's session variables),
	//  and including db.php, which has all the code for interacting with MySQL.
	include('header.php');
	
	//If the "Change Password" button was clicked, then we got here via POST request, and we can access
	//	the two passwords to check if they're equal and then use them to change the password.
	if (isset($_POST["submit"]))
	{
        include_once "db.php";
        $error_message = '';
        $password = $_POST['user_password1'];
        $passwordCheck = $_POST['user_password2'];



        //Check for empty fields
        if(empty($password)){
            $error_message = 'Please fill in all fields.';
        }

        if(empty($passwordCheck)){
            $error_message = 'Please fill in all fields.';
        }


        //checks if password are matching
        if ($password !== $passwordCheck)
        {
            $error_message = 'Passwords do not match!';
        }

        if (strlen($error_message) == 0){
            $passwordSet = set_user_password($user_id,$password);
        }


        //checks the error messages and displays appropriate message if detected.
        //If no error message is detected displays success message.
        if (strlen($error_message) > 0)
        {
            ?>
            <div class="container">
                <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
            </div>
            <?php
        }
		else
		{
            ?>
            <div class="container">
                <div class="alert alert-danger" role="alert"><?php echo 'Password Successfully Changed'; ?></div>
            </div>
            <?php
		}
	}
?>

<div class="container">
<?php
	//If $user_id isn't NULL, then that means we are logged in.
	//Notice that we temporarily break out of "PHP mode" so we can include some HTML without having to use <?php ?\> tags.
	//	This isn't necessary, but it's easier because we don't have to use PHP "echo" statements.
	if (!is_null($user_id))
	{
?>
	<!--	This is the HTML form for changing the password. It has two password textboxes, and a "Change Password" input button.
			When the "Change Password" button is clicked, it performs a POST request to "user.php" (this very same page), which
				would have code to detect that and try changing the password.
	-->
	<h2>Change Password</h2>
	<form action="user.php" method="POST">
		<!-- TODO: Prompt for original password too and verify it, in case someone else was able to get to this page as you. -->
		<div class="form-group">
			<label for="password1">Password:</label>
			<input type="password" class="form-control" id="user_password1" placeholder="Enter password" name="user_password1">
		</div>
		<div class="form-group">
			<label for="password2">Password (again):</label>
			<input type="password" class="form-control" id="user_password2" placeholder="Confirm password" name="user_password2">
		</div>
		<button type="submit" name="submit" class="btn btn-primary">Change Password</button>
	</form>

<?php
	//This ends the PHP if {} block that started with: if (!is_null($user_id))
	}
	else
	{
		//TODO: Display "not logged in" stuff
        ?>
        <div class="container">
            <div class="alert alert-danger" role="alert"><?php echo 'Please login to continue'; ?></div>
        </div>
        <?php
	}

	echo "</div>";

	//This includes all the HTML from "footer.php", which displays the navigation links and other stuff at the bottom of every page.
	include('footer.php');
?>
