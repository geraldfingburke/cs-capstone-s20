<?php

function get_pdo()
{
	//Hard-coded MySQL connection information
	$server_name = "localhost";
	$user_name = "CSCapstoneS20";
	$password = "Password1";
	$db = "capstone20";

	return new PDO("mysql:host=".$server_name.";dbname=".$db.";charset=utf8", $user_name, $password);
}

//If the user is validated, an associative array with user ID and admin flag is returned; otherwise NULL.
function validate_user($user_name, $user_password)
{
	$pdo = get_pdo();

	//Get information for the user based on user name (case-insensitive comparison)
	$stmt = $pdo->prepare("SELECT u.USER_ID, u.PASSWORD_HASH, u.IS_ADMIN FROM USERS u WHERE u.IS_ACTIVE = 1 AND lower(u.USER_NAME) = lower(:user_name)");
	$stmt->bindValue(":user_name", $user_name, PDO::PARAM_STR);
	$stmt->execute();

	$ret = NULL;
	if ($row = $stmt->fetchAll())
	{
		//We have a record; get the password hash
		$retrieved_hash = $row[0]['PASSWORD_HASH'];

		//Verify stored hash with plaintext input
		if (password_verify($user_password, $retrieved_hash))
		{
			//It's validated; return the user ID and whether they are an admin
			$ret = array("USER_ID" => $row[0]["USER_ID"], "IS_ADMIN" => $row[0]["IS_ADMIN"]);
		}
	}

	$pdo = NULL;
	
	return $ret;
}

//Get all courses with the specified search criteria (or NULL for none).
function get_courses($search_criteria)
{
	$ret = array();
	$pdo = get_pdo();

	//Retrieve all the courses and their topics/videos.
	//This won't return any courses that have no topics/videos.
	try
	{
		$sql = "SELECT c.COURSE_ID, c.NAME as COURSE_NAME, c.SUMMARY,
										t.COURSE_TOPIC_ID, t.TOPIC_NUMBER, t.DISPLAY_ORDER, t.HEADER, t.CONTENT, t.COURSE_VIDEO_ID, v.VIDEO_EMBED_CODE
									FROM COURSES c
										INNER JOIN COURSE_TOPICS t ON c.COURSE_ID = t.COURSE_ID
										LEFT OUTER JOIN COURSE_VIDEOS v ON t.COURSE_VIDEO_ID = v.COURSE_VIDEO_ID
									WHERE (:search_criteria IS NULL OR c.NAME REGEXP CONCAT('[[:<:]]', :search_criteria, '[[:>:]]'))
									  OR (:search_criteria IS NULL OR c.SUMMARY REGEXP CONCAT('[[:<:]]', :search_criteria, '[[:>:]]'))
									  OR (:search_criteria IS NULL OR t.HEADER REGEXP CONCAT('[[:<:]]', :search_criteria, '[[:>:]]'))
									  OR (:search_criteria IS NULL OR t.CONTENT REGEXP CONCAT('[[:<:]]', :search_criteria, '[[:>:]]'))
									ORDER BY c.DISPLAY_ORDER, t.DISPLAY_ORDER";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(":search_criteria", strlen($search_criteria) > 0 ? $search_criteria : NULL, PDO::PARAM_STR);
		$stmt->execute();

		$courseId = NULL;
		foreach ($stmt->fetchAll() as $row)
		{
			//Get the course information
			$course_id = (int)$row["COURSE_ID"];
			
			if ($course_id != $courseId)
			{
				$topicNumber = 1;
			}
			
			$courseId = $course_id;
			$r = array("COURSE_ID" => $course_id, "COURSE_NAME" => $row["COURSE_NAME"], "SUMMARY" => $row["SUMMARY"], "TOPICS" => array());

			//Add the course to our return value if we don't already have it
			if (!array_key_exists($course_id, $ret))
			{
				$ret += [$course_id => $r];
			}

			//Get the course topic information and add it to the "TOPICS" array on the course
			$course_topic_id = $row["COURSE_TOPIC_ID"];
			$ret[$course_id]["TOPICS"][$course_topic_id] = [$course_topic_id => array("COURSE_TOPIC_ID" => $row["COURSE_TOPIC_ID"],
				"TOPIC_NUMBER" => $topicNumber/*$row["TOPIC_NUMBER"]*/, "DISPLAY_ORDER" => $row["DISPLAY_ORDER"], "HEADER" => $row["HEADER"],
				"CONTENT" => $row["CONTENT"], "COURSE_VIDEO_ID" => $row["COURSE_VIDEO_ID"],
				"VIDEO_EMBED_CODE" => $row["VIDEO_EMBED_CODE"])];
			$topicNumber++;
		}
	}
	catch (Exception $e)
	{
		die("Failed to retrieve courses: ".$e->getMessage()."\n");
	}

	$pdo = NULL;
	
	return $ret;
}

//Retrieve settings for the site, such as intro text.
function get_settings()
{
	$ret = array();
	$pdo = get_pdo();

	//Get the settings key/value pairs (always strings)
	$stmt = $pdo->prepare("SELECT s.SETTING_KEY, s.SETTING_VALUE FROM SITE_SETTINGS s");
	$stmt->execute();

	$ret = array();
	foreach ($stmt->fetchAll() as $row)
	{
		$ret += [$row["SETTING_KEY"] => $row["SETTING_VALUE"]];
	}

	$pdo = NULL;
	
	return $ret;
}

//Update specified settings with the associated values.
function update_settings($settings)
{
	$pdo = get_pdo();

	foreach ($settings as $key => $value)
	{
		$stmt = $pdo->prepare("UPDATE SITE_SETTINGS SET SETTING_VALUE = :setting_value WHERE SETTING_KEY = :setting_key");
		$stmt->bindValue(":setting_key", $key);
		$stmt->bindValue(":setting_value", $value);
		$stmt->execute();
	}
	
	$pdo = null;
}

//Adds a new course to the database
function add_course ($name, $summary) {
	$pdo = get_pdo();
	$startingContent = "This is where you'll put the content for your topic.";

	$stmt = $pdo->prepare("INSERT INTO COURSES (NAME, SUMMARY, DISPLAY_ORDER) VALUES (:name_value, :summary_value, :display_value)");
	$stmt->bindValue(":name_value", $name, PDO::PARAM_STR);
	$stmt->bindValue(":summary_value", $summary, PDO::PARAM_STR);
	$stmt->bindValue(":display_value", count(get_courses(NULL)) + 1, PDO::PARAM_INT);
	$stmt->execute();

	/*
	 * We add a topic to each course we create. As it stands, the way we get courses throughout our code omits courses that don't have topics.
	 * Rather than changing the way we select topics and potentially changing all our uses of get_courses, I opted to ensure that every course has a topic on generation.
	 * This can and probably should be changed.
	 */
	$stmt = $pdo->prepare("INSERT INTO COURSE_TOPICS (TOPIC_NUMBER, COURSE_ID, DISPLAY_ORDER, HEADER, CONTENT) VALUES (:topic_number_value, :course_id_value, :display_value, :header_value, :content_value)");
	$stmt->bindValue(":topic_number_value", 1, PDO::PARAM_INT);
	$stmt->bindValue(":course_id_value", $pdo->lastInsertId(), PDO::PARAM_INT);
	$stmt->bindValue(":display_value", 1, PDO::PARAM_INT);
	$stmt->bindValue(":header_value", "This is your first topic!", PDO::PARAM_STR);
	$stmt->bindValue(":content_value", $startingContent, PDO::PARAM_STR);
	$stmt->execute();


	$pdo = null;
}

// Edits the header and summary of a course based on id
function edit_course ($name, $summary, $id) {
	$pdo = get_pdo();

	$stmt = $pdo->prepare("UPDATE COURSES SET NAME = :name_value, SUMMARY = :summary_value WHERE COURSE_ID=:id_value");
	$stmt->bindValue(":name_value", $name, PDO::PARAM_STR);
	$stmt->bindValue(":summary_value", $summary, PDO::PARAM_STR);
	$stmt->bindValue(":id_value", $id, PDO::PARAM_INT);
	$stmt->execute();

	$pdo = null;
}

//Deletes a selected course and its topics
function delete_course ($id) {
	$pdo = get_pdo();

	$stmt = $pdo->prepare("DELETE FROM COURSES WHERE COURSE_ID=:id_number");
	$stmt->bindValue(":id_number", $id, PDO::PARAM_INT);
	$stmt->execute();

	// This goes through the topics in the course, one by one, and applies the delete_topic method to them
	$courses = get_courses(NULL);
	foreach ($courses as $course) {
		if ($course["COURSE_ID"] == $id) {
			foreach ($course as $topics) {
				foreach ($topics as $topic) {
					delete_topic($topic["COURSE_TOPIC_ID"]);
				}
			}
		}
	}

	$pdo = null;
}

// Adds a topic given a particular ID
function add_topic ($courseID, $header, $content, $videoLink) {
	$pdo = get_pdo();
	$courses = get_courses(NULL);
	$hasVideo = true;
	// Double checking to make sure we have our video link
	if ($videoLink == "" or $videoLink == null) {
		$hasVideo = false;
	}
	// This keeps a tally of total topics so we can calculate the cumulative topic number
	$totalTopics = 0;
	foreach ($courses as $course) {
		// Grabs the course wee need to insert into by the id
		if ($course["COURSE_ID"] == $courseID) {
			$totalTopics = count($course["TOPICS"]);
			$current_course = $course;
		}
	}
	if ($hasVideo) {
		// This grabs the youtube link and formats it into an embed code for our site
		$link_pieces = explode("=", $videoLink);
		$embed_code = "https://www.youtube.com/embed/".$link_pieces[1];
	}

	try
	{
		// Depending on whether or not we have a video, we do one or the other, this way we don't have to add null data to the video table and can nullify the video column of the topics table
		if ($hasVideo) {
			$stmt = $pdo->prepare("INSERT INTO COURSE_VIDEOS (DESCRIPTION, VIDEO_EMBED_CODE) VALUES (:description, :embed_code)");
			$stmt->bindValue(":description", "", PDO::PARAM_STR);
			$stmt->bindValue(":embed_code", "<iframe width=\"560\" height=\"315\" src=\"$embed_code\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>", PDO::PARAM_STR);
			$stmt->execute();

			$stmt = $pdo->prepare("INSERT INTO COURSE_TOPICS (TOPIC_NUMBER, COURSE_ID, DISPLAY_ORDER, HEADER, CONTENT, COURSE_VIDEO_ID) 
											VALUES(:topic_number, :course_id, :display_order, :header, :content, :video_id)");
			$stmt->bindValue(":topic_number", $totalTopics + 1, PDO::PARAM_INT);
			$stmt->bindValue(":course_id", $courseID, PDO::PARAM_INT);
			$stmt->bindValue(":display_order", count($current_course["TOPICS"]) + 1, PDO::PARAM_INT);
			$stmt->bindValue(":header", $header, PDO::PARAM_STR);
			$stmt->bindValue(":content", $content, PDO::PARAM_STR);
			$stmt->bindValue(":video_id", $pdo->lastInsertId(), PDO::PARAM_INT);
			$stmt->execute();

			$topicID = $pdo->lastInsertId();
		}
		else {
			$stmt = $pdo->prepare("INSERT INTO COURSE_TOPICS (TOPIC_NUMBER, COURSE_ID, DISPLAY_ORDER, HEADER, CONTENT) 
											VALUES(:topic_number, :course_id, :display_order, :header, :content)");
			$stmt->bindValue(":topic_number", $totalTopics + 1, PDO::PARAM_INT);
			$stmt->bindValue(":course_id", $courseID, PDO::PARAM_INT);
			$stmt->bindValue(":display_order", count($current_course["TOPICS"]) + 1, PDO::PARAM_INT);
			$stmt->bindValue(":header", $header, PDO::PARAM_STR);
			$stmt->bindValue(":content", $content, PDO::PARAM_STR);
			$stmt->execute();

			$topicID = $pdo->lastInsertId();
		}
	}
	catch (Exception $e)
	{
		echo "Failed to add topic: ".$e->getMessage()."\n";
	}

	$pdo = null;
	
	return $topicID;
}

function edit_topic ($topicID, $courseID, $header, $content, $videoLink) {
	$pdo = get_pdo();
	$courses = get_courses(NULL);
	$hasVideo = TRUE;
	$existingVideo = FALSE;
	$existingVideoID = 0;
	// Double checking to make sure we have our video link
	if (strlen($videoLink) == 0 or $videoLink == null) {
		$hasVideo = FALSE;
	}
	// This keeps a tally of total topics so we can calculate the cumulative topic number
	$totalTopics = 0;
	foreach ($courses as $course) {
		$totalTopics += count($course["TOPICS"]);
		// Grabs the course we need to insert into by the id
		if ($course["COURSE_ID"] == $courseID) {
			$current_course = $course;
		}
		foreach ($course["TOPICS"] as $topics) {
			foreach ($topics as $topic) {
				if ($topic["COURSE_TOPIC_ID"] == $topicID) {
					//var_dump($topic);
					if ($topic["COURSE_VIDEO_ID"] != NULL) {
						$existingVideoID = $topic["COURSE_VIDEO_ID"];
						$existingVideo = TRUE;
					}
				}
			}
		}
	}
	if ($hasVideo) {
		// This grabs the youtube link and formats it into an embed code for our site
		$link_pieces = explode("=", $videoLink);
		$embed_code = "https://www.youtube.com/embed/".$link_pieces[1];
	}

	try
	{
		//echo "Had existing video: '".$existingVideo."' '".$existingVideoID."'";
		// Depending on whether or not we have a video, we do one or the other, this way we don't have to add null data to the video table and can nullify the video column of the topics table
		if ($hasVideo && !$existingVideo) {
			$stmt = $pdo->prepare("INSERT INTO COURSE_VIDEOS (DESCRIPTION, VIDEO_EMBED_CODE) VALUES (:description, :embed_code)");
			$stmt->bindValue(":description", "", PDO::PARAM_STR);
			$stmt->bindValue(":embed_code", "<iframe width=\"560\" height=\"315\" src=\"$embed_code\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>", PDO::PARAM_STR);
			$stmt->execute();

			//echo "From DB3: ".$content;
			$stmt = $pdo->prepare("UPDATE COURSE_TOPICS SET HEADER = :header, CONTENT = :content, COURSE_VIDEO_ID = :video_id WHERE COURSE_TOPIC_ID = :topicID");
			$stmt->bindValue(":header", $header, PDO::PARAM_STR);
			$stmt->bindValue(":content", $content, PDO::PARAM_STR);
			$stmt->bindValue(":video_id", $pdo->lastInsertId(), PDO::PARAM_INT);
			$stmt->bindValue(":topicID", $topicID, PDO::PARAM_INT);
			$stmt->execute();

		}
		else if ($hasVideo && $existingVideo) {
			$stmt = $pdo->prepare("UPDATE COURSE_VIDEOS SET VIDEO_EMBED_CODE = :embed_code WHERE COURSE_VIDEO_ID = :video_id");
			$stmt->bindValue(":embed_code", "<iframe width=\"560\" height=\"315\" src=\"$embed_code\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>", PDO::PARAM_STR);
			$stmt->bindValue(":video_id", $existingVideoID, PDO::PARAM_INT);
			$stmt->execute();

			//echo "From DB: ".$content;
			$stmt = $pdo->prepare("UPDATE COURSE_TOPICS SET HEADER = :header, CONTENT = :content WHERE COURSE_TOPIC_ID = :topicID");
			$stmt->bindValue(":header", $header, PDO::PARAM_STR);
			$stmt->bindValue(":content", $content, PDO::PARAM_STR);
			$stmt->bindValue(":topicID", $topicID, PDO::PARAM_INT);
			$stmt->execute();

		}
		else {
			//echo "From DB2: ".$content;
			$stmt = $pdo->prepare("UPDATE COURSE_TOPICS SET HEADER = :header, CONTENT = :content, COURSE_VIDEO_ID = NULL WHERE COURSE_TOPIC_ID = :topicID");
			$stmt->bindValue(":header", $header, PDO::PARAM_STR);
			$stmt->bindValue(":content", $content, PDO::PARAM_STR);
			$stmt->bindValue(":topicID", $topicID, PDO::PARAM_INT);
			$stmt->execute();
		}
	}
	catch (Exception $e)
	{
		echo "Failed to edit topic: ".$e->getMessage()."\n";
	}
	
	$pdo = null;
}

function delete_topic ($topic_id) {
	$pdo = get_pdo();
	$courses = get_courses(NULL);
	$currentTopic = null;
	foreach ($courses as $course) {
		foreach ($course["TOPICS"] as $topics) {
			foreach ($topics as $topic) {
				if ($topic["COURSE_TOPIC_ID"] == $topic_id) {
					$currentTopic = $topic;
					$video_id = $topic["COURSE_VIDEO_ID"];


					if ($video_id != NULL) {
						$stmt = $pdo->prepare("DELETE FROM COURSE_VIDEOS WHERE COURSE_VIDEO_ID=:video_id");
						$stmt->bindValue(":video_id", $video_id, PDO::PARAM_INT);
						$stmt->execute();
					}
					$stmt = $pdo->prepare("DELETE FROM COURSE_TOPICS WHERE COURSE_TOPIC_ID=:topic_id");
					$stmt->bindValue(":topic_id", $topic_id, PDO::PARAM_INT);
					$stmt->execute();
				// When a topic is deleted, this shifts up all the topics below it
				} else if ($topic["TOPIC_NUMBER"] > $currentTopic["TOPIC_NUMBER"] && $currentTopic != null) {
					$stmt = $pdo->prepare("UPDATE COURSE_TOPICS SET TOPIC_NUMBER = :number, DISPLAY_ORDER = :number WHERE COURSE_TOPIC_ID = :topic_id");
					$stmt->bindValue(":number", $topic["TOPIC_NUMBER"] - 1, PDO::PARAM_INT);
					$stmt->bindValue(":topic_id", $topic["COURSE_TOPIC_ID"], PDO::PARAM_INT);
					$stmt->execute();
				}
			}
		}
	}

	$pdo = null;
}

function add_user($user_name, $user_password, $is_admin)
{
	$pdo = get_pdo();
	
	$stmt = $pdo->prepare("INSERT INTO USERS (USER_NAME, PASSWORD_HASH, IS_ADMIN, IS_ACTIVE) VALUES (:user_name, :password_hash, :is_admin, 1)");
	$stmt->bindValue(":user_name", strtolower($user_name));
	$stmt->bindValue(":password_hash", password_hash($user_password, PASSWORD_DEFAULT));
	$stmt->bindValue(":is_admin", $is_admin);
	$stmt->execute();
	
	$pdo = null;
}

function set_user_password($user_id, $user_password)
{
	$pdo = get_pdo();
	
	$stmt = $pdo->prepare("UPDATE USERS u SET u.PASSWORD_HASH = :password_hash WHERE USER_ID = :user_id");
	$stmt->bindValue(":password_hash", password_hash($user_password, PASSWORD_DEFAULT));
	$stmt->bindValue(":user_id", $user_id);
	$stmt->execute();
	
	$pdo = null;
}

function get_users()
{
	$pdo = get_pdo();

	$stmt = $pdo->prepare("SELECT u.USER_ID, u.USER_NAME, u.IS_ACTIVE, u.IS_ADMIN
								FROM USERS u
								ORDER BY USER_ID");
	$stmt->execute();

	$ret = array();
	foreach ($stmt->fetchAll() as $row)
	{
		$user_id = (int)$row["USER_ID"];
		$r = array("USER_ID" => $user_id, "USER_NAME" => $row["USER_NAME"], "IS_ACTIVE" => boolval($row["IS_ACTIVE"]), "IS_ADMIN" => boolval($row["IS_ADMIN"]));
		$ret += [$user_id => $r];
	}

	$pdo = NULL;
	
	return $ret;
}

function delete_user($user_id)
{
	$pdo = get_pdo();
	
	$stmt = $pdo->prepare("DELETE FROM USERS WHERE USER_ID = :user_id");
	$stmt->bindValue(":user_id", $user_id);
	$stmt->execute();
	
	$pdo = null;
}

function edit_user($user_id, $user_name, $is_active, $is_admin)
{
	$pdo = get_pdo();
	
	$stmt = $pdo->prepare("UPDATE USERS SET USER_NAME = :user_name, IS_ACTIVE = :is_active, IS_ADMIN = :is_admin WHERE USER_ID = :user_id");
	$stmt->bindValue(":user_name", $user_name);
	$stmt->bindValue(":is_active", $is_active);
	$stmt->bindValue(":is_admin", $is_admin);
	$stmt->bindValue(":user_id", $user_id);
	$stmt->execute();
	
	$pdo = null;
}

// This takes in a topic id and swaps the display order of the topic with the number above or below it
function move_topic ($topic_id, $direction) {
	$pdo = get_pdo();
	$courses = get_courses(NULL);
	$topic_a = NULL;
	$topic_b = NULL;
	
	/*$total_topics = 0; // Grabbing this so I can use it to determine the lower bounds of the 'list'
	foreach ($courses as $course) {
		foreach ($course["TOPICS"] as $topics) {
			foreach ($topics as $topic) {
				if ($topic["COURSE_TOPIC_ID"] == $topic_id) {
					$topic_a = $topic;
					$total_topics = count($course["TOPICS"]);
				}
			}

		}
	}

	// Checks to see which direction we're moving in and grabs the neighbor above or below
	foreach ($courses as $course) {
		foreach ($course["TOPICS"] as $topics) {
			foreach ($topics as $topic) {
				if (strtolower($direction) == "up") {
					if ($topic_a["DISPLAY_ORDER"] == $topic["DISPLAY_ORDER"] + 1) {
						$topic_b = $topic;
					}
				} else if (strtolower($direction) == "down") {
					if ($topic_a["DISPLAY_ORDER"] == $topic["DISPLAY_ORDER"] - 1) {
						$topic_b = $topic;
					}
				}
			}

		}
	}*/

	//Get the previous and next values for this topic ID
	$courses = get_courses(NULL);
	$get_out = FALSE;
	foreach ($courses as $course) {
		foreach ($course["TOPICS"] as $topics) {
			$t = array_values($course["TOPICS"]);
			for ($x = 0; $x < count($t); $x++)
			{
				$v = array_values($t[$x])[0];
				if ($v["COURSE_TOPIC_ID"] == $topic_id)
				{
					$topic_a = $v;
					$p = array_values($t[max(0, $x - 1)])[0];
					$n = array_values($t[min($x + 1, count($t))])[0];
					
					if (strtolower($direction) == "up")
					{
						$topic_b = $p;
					}
					else if (strtolower($direction) == "down")
					{
						$topic_b = $n;
					}
					
					$get_out = TRUE;
					break;
				}
			}
			
			if ($get_out)
			{
				break;
			}
		}
		
		if ($get_out)
		{
			break;
		}
	}

	$topic_a_placeholder = $topic_a["DISPLAY_ORDER"];
	$topic_b_placeholder = $topic_b["DISPLAY_ORDER"];
	//echo "Swapping topics ".$topic_a["COURSE_TOPIC_ID"]." and ".$topic_b["COURSE_TOPIC_ID"];
    
	/*if ((strtolower($direction) == "up" && $topic_a_placeholder == 1) || (strtolower($direction) == "down" && $topic_a_placeholder == $total_topics)) {
		echo ("<div class=\"alert alert-warning\" role=\"alert\">Cannot move in that direction!</div>");
	}
	else {
	*/
		$stmt = $pdo->prepare("UPDATE COURSE_TOPICS SET DISPLAY_ORDER = :display_order, TOPIC_NUMBER = :display_order WHERE COURSE_TOPIC_ID = :topic_id");
		$stmt->bindValue(":display_order", $topic_b_placeholder, PDO::PARAM_INT);
		$stmt->bindValue(":topic_id", $topic_a["COURSE_TOPIC_ID"], PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $pdo->prepare("UPDATE COURSE_TOPICS SET DISPLAY_ORDER = :display_order, TOPIC_NUMBER = :display_order WHERE COURSE_TOPIC_ID = :topic_id");
		$stmt->bindValue(":display_order", $topic_a_placeholder, PDO::PARAM_INT);
		$stmt->bindValue(":topic_id", $topic_b["COURSE_TOPIC_ID"], PDO::PARAM_INT);
		$stmt->execute();
	//}

	$pdo = null;
}
?>