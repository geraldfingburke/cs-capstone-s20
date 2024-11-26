<?php
	//This site is a mixture of HTML and CSS, which is interpreted and rendered by the web browser,
	//  and server-side PHP code, which the web server executes as the page is retrieved by the browser.
	//PHP code exists between <?php and ?\> tags. You can add PHP code in the middle of any HTML on this page using these tags.
	//A common thing PHP code will do is "echo"/output HTML tags, which becomes part of the HTML of the page, allowing for dynamic content.

	//This PHP function includes all of the HTML and PHP from the "header.php" file.
	//header.php does a lot of stuff for you, such as starting/resuming the PHP session (so you can access the user's session variables),
	//  and including db.php, which has all the code for interacting with MySQL. It even retrieves all the key/value pairs from the
	//  MySQL "SETTINGS" table we made, for easy access and use here.
	include('header.php');
?>

<!-- THIS IS THE START OF THE HTML BODY THAT APPEARS ON THE MAIN PAGE OF THE SITE. CHANGE IT HOWEVER YOU LIKE. -->

<?php
	//Get all the courses from MySQL, using any specified search criteria.
	//If this page was accessed via HTTP POST (from the search box), then $_POST["search"] will have what was typed into the box.
	//If we weren't POSTed to, then $_POST["search"] will be NULL.
	//We'll pass it into the get_courses PHP function from db.php, which retrieves all the course content from our MySQL tables.
	$courses = get_courses($_GET["search"]);
	if (count($courses) > 0)
	{
?>

<!--	We'll take this opportunity to "echo"/output the text from the "PrimaryMessage" value from the MySQL SETTINGS table.
		Since it's between these <div> and </div> tags, it will be displayed in a light-colored Bootstrap 4 "alert" box.
		This setting can be changed from the admin page by logging into the site and going to admin.php in your browser.
		Otherwise, you can just hard-code content by replacing $settings["PrimaryMessage"] with something else (in quotes),
		  or you could erase the entire <\? php ?\> block and just type some text, like any other HTML page.
 -->
<div class="alert alert-light" role="alert"><?php echo $settings["PrimaryMessage"]; ?></div>

<!-- THIS IS THE START OF THE Bootstrap 4 CARD (WITH TABS) THAT DISPLAYS ALL THE COURSE CONTENT. -->
<div class="card">
	<div class="card-body">
		<ul class="nav nav-tabs" id="myTab" role="tablist">
<?php
	//Display the course tabs
	$i = 0;
	foreach ($courses as $course)
	{
		echo "<li class=\"nav-item\"><a class=\"nav-link";

		//Display the first one as active
		if ($i == 0)
		{
			echo " active";
			$i = $course["COURSE_ID"];
		}

		echo "\" id=\"";
		echo "home".$course["COURSE_ID"]."-tab\" data-toggle=\"tab\" href=\"#";
		echo "course_".$course["COURSE_ID"]."\" role=\"tab\" aria-controls=\"";
		echo "course_".$course["COURSE_ID"]."\" aria-selected=\"true\">";
		echo $course["COURSE_NAME"]."</a></li>";
	}
?>
		</ul>
		<div class="tab-content" id="myTabContent">
<?php
	//Output the course tab data
	foreach ($courses as $course)
	{
		echo "<div class=\"tab-pane fade show";
		if ($course["COURSE_ID"] == $i)
		{
			echo " active";
		}

		echo "\" id=\"";
		echo "course_".$course["COURSE_ID"]."\" role=\"tabpanel\" aria-labelledby=\"";
		echo "home".$course["COURSE_ID"]."-tab\">";
	
		echo "<div class=\"alert alert-light\" role=\"alert\">";
		echo $course["SUMMARY"];
		echo "</div>";

		//Output the course topics
		$j = 1;
		echo "<div id=\"accordion".$course["COURSE_ID"]."\" class=\"accordion-group\">";
		foreach ($course["TOPICS"] as $topics)
		{
			$topic = array_values($topics)[0];
			echo "<div class=\"accordion\" id=\"accordion".$course["COURSE_ID"]."_".$topic["COURSE_TOPIC_ID"]."\">";
			echo "<div class=\"card\">";

			echo "<div class=\"card-header\" id=\"heading".$topic["COURSE_TOPIC_ID"]."\">";
			echo "<h2 class=\"mb-0\">";
			echo "<button class=\"btn btn-link\" type=\"button\" data-toggle=\"collapse\" data-target=\"";
			echo "#collapse".$topic["COURSE_TOPIC_ID"]."\" aria-expanded=\"true\" aria-controls=\"";
			echo "collapse".$topic["COURSE_TOPIC_ID"]."\">";
			echo $topic["TOPIC_NUMBER"].": ".$topic["HEADER"];
			echo "</button></h2></div>";

			echo "<div id=\"collapse".$topic["COURSE_TOPIC_ID"]."\" class=\"collapse";
			if ($j == 1)
				echo " show";
			echo "\" aria-labelledby=\"heading".$topic["COURSE_TOPIC_ID"]."\" data-parent=\"#";
			echo "accordion".$course["COURSE_ID"]."\">";

			echo "<div class=\"card-body\">";

			echo "<div class=\"container-fluid\">";
			echo "<div class=\"row\">";
			/*
			Topic content is stored as a base64 encoded string on the server. This makes it easier to pass information back and forth.
			When we get it back here, we need to decode it using the method below. The output will be pure html, so just pop it into a div and you're done.
			*/
			echo "<div class=\"col-sm-6\">".$topic["CONTENT"]."</div>";
			echo "<div class=\"col-sm-6\">";
			echo $topic["VIDEO_EMBED_CODE"];

			echo "</div>";
			echo "</div>";
			echo "</div>";

			echo "</div>";

			echo "</div>";

			echo "</div>";
			echo "</div>";
			$j++;
		}
		echo "</div>";
?>

<?php
		echo "</div>";
	}
?>
		</div>
	</div>
</div>
<?php
	}
	else
	{
?>
<div class="alert alert-light" role="alert"><?php echo "No search results found."; ?></div>
<?php
	}
?>
<!-- THIS IS THE END OF THE Bootstrap 4 CARD (WITH TABS) THAT DISPLAYS ALL THE COURSE CONTENT. -->

<!-- THIS IS THE END OF THE HTML BODY THAT APPEARS ON THE MAIN PAGE OF THE SITE. THIS IS YOUR LAST CHANCE TO DISPLAY CONTENT (BELOW THE COURSE CONTENT). -->

<?php
	//This includes all the HTML from "footer.php", which displays the navigation links and other stuff at the bottom of every page.
	include('footer.php');
?>
