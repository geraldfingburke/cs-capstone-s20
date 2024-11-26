<?php
//Start/resume the session
session_start();

//Check to make sure we're an admin; otherwise get out now
if (!($_SESSION["IS_ADMIN"] > 0))
{
    die("You do not have permission to view this page.");
}

include_once "db.php";
$courses = get_courses(NULL);

// This checks to see if the post has already been added to the database, which determines whether submission updates or inserts the entry into the table
$isPublished = false;
// Grabs the courseID if it's set
if(isset($_GET["courseID"])) {
    $courseID = $_GET["courseID"];
}
// Here we grab the topicID and get some variables out of it. We use these to populate the form content if the topic has been published.
if (isset($_GET["topicID"])) {
    $isPublished = true;
	//echo "Existing topic being edited";
    foreach ($courses as $course) {
        foreach ($course["TOPICS"] as $topics) {
            foreach ($topics as $topic) {
                if ($topic["COURSE_TOPIC_ID"] == $_GET["topicID"]) {
                    $topicID = $topic["COURSE_TOPIC_ID"];
                    $topicNumber = $topic["TOPIC_NUMBER"];
                    $header = $topic["HEADER"];
                    $content = $topic["CONTENT"];
                    $video = $topic["VIDEO_EMBED_CODE"];
					$courseID = $course["COURSE_ID"];
                }
            }
        }
    }
    // Here is where we update a previously published topic. The two different method calls are determined by the presence of a working video link
    if (isset($_POST["topicContent"]) && isset($_POST["topicHeader"])) {
		//echo "Updating topic ID ".$topicID." and ".$courseID." to '".$_POST["topicHeader"]."', '".$_POST["topicContent"]."', '".$_POST["topicVideo"]."'\n";
        include_once "db.php";
		//echo "Size: ".strlen($_POST["topicContent"]);
		//echo "Content for topic ID ".$topicID.": ".base64_decode($_POST["topicContent"]);
        if (isset($_POST["topicVideo"]) && strlen($_POST["topicVideo"]) > 0) {
            edit_topic($topicID, $courseID, $_POST["topicHeader"], $_POST["topicContent"], $_POST["topicVideo"]);
        }
        else {
            edit_topic($topicID, $courseID, $_POST["topicHeader"], $_POST["topicContent"], NULL);
        }

        echo "<div class=\"alert alert-warning\" role=\"alert\">Topic Updated!</div>";
    }
}
else {
    $isPublished = false;
}

// If the course hasn't been published and we have info coming into the page, we create a new topic here.
if (isset($_POST["topicContent"]) && isset($_POST["topicHeader"]) && !isset($_GET["topicID"])) {
    include_once "db.php";
    if (isset($_POST["topicVideo"]) && $_POST["topicVideo"] != "") {
        $topicID = add_topic($courseID, $_POST["topicHeader"], $_POST["topicContent"], $_POST["topicVideo"]);
    }
    else {
        $topicID = add_topic($courseID, $_POST["topicHeader"], $_POST["topicContent"], NULL);
    }

	//Redirect to ourselves with the topicID in the URL, so things work just as if we got here from topics.php
	header('Location: topic.php?topicID='.$topicID);

    echo "<div class=\"alert alert-warning\" role=\"alert\">Topic Published!</div>";
}

//After everything, make sure we have the course ID. The reason this is last is that it allows the course content to refresh *AFTER* the database has been queried
$courses = get_courses(NULL);
if (isset($_GET["topicID"])) {
    $isPublished = true;
    foreach ($courses as $course) {
        foreach ($course["TOPICS"] as $topics) {
            foreach ($topics as $topic) {
                if ($topic["COURSE_TOPIC_ID"] == $_GET["topicID"]) {
					//echo "Found topic ID ".$topic["COURSE_TOPIC_ID"];
                    $topicID = $topic["COURSE_TOPIC_ID"];
                    $topicNumber = $topic["TOPIC_NUMBER"];
                    $header = $topic["HEADER"];
                    $content = $topic["CONTENT"];
					//echo "Retrieved content: ".base64_decode($content);
                    $video = $topic["VIDEO_EMBED_CODE"];
                    $courseID = $course["COURSE_ID"];
                }
            }
        }
    }
}

//After everything, get the video embed code and try to extract the link out of it, so the user can see/edit it.
if (strlen($video) > 0)
{
	//echo "Embed code: '".htmlspecialchars($video)."'";
	//Use regular expression to get the "src" HTTP link out of the embed code
	preg_match("/src=\"(.*?)\"/", $video, $matches);
	//Use regular expression to get the part after "https://www.youtube.com/embed/" (the actual code)
	preg_match("/embed\/(.*)/", $matches[1], $matches);
	//Now build the new link
	$video = "https://www.youtube.com/watch?v=".$matches[1];
}

include('header.php');
?>

<div class="card m-3">
    <div class="card-header">Editing Topic<a href="topics.php?courseID=<?php echo $courseID; ?>" class="btn btn-primary float-right">Back to Course</a></div>
    <div class="card-body">
        <!-- This is the form for submitting and updating the posts. Every part can be styled, but none can be removed.-->
        <form id="topic-form" method="post">
            <div class="form-group">
                <label for="topicHeader">Topic Title</label>
                <input type="text" class="form-control" id="headerText" name="topicHeader" aria-describedby="headerTextHelp" placeholder="Enter header text" value="<?php
                if ($isPublished) {
                    echo($header);
                }
                ?>" required/>
            </div>
            <div class="form-group">
                <label for="primaryMessage">Topic Content</label>
                <!-- This tag may look superfluous, but it's where the quill content lives after it's been submitted-->
                <input name="topicContent" id="quill-content-submit" type="hidden">
                    <div id="topic-content">
                        <?php
                        if ($isPublished) {
                            echo $content;
                        }
                        ?>
                    </div>
                <label for="videoLink">Link for Video</label>
                <input id="videoLink" name="topicVideo" type="text" class="form-control" id="headerText" placeholder="Enter YouTube Video Link or leave blank for none" value="<?php echo $video; ?>"/>
            </div>
            <button type="submit" onclick="submit()" name="update" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<script>
/**
 * ASCII to Unicode (decode Base64 to original data)
 * @param {string} b64
 * @return {string}
 */
function atou(b64) {
  return decodeURIComponent(escape(atob(b64)));
}

/**
 * Unicode to ASCII (encode data to Base64)
 * @param {string} data
 * @return {string}
 */
function utoa(data) {
  return btoa(unescape(encodeURIComponent(data)));
}

    // Sets up our rich text editor box. If we don't have this, the box will not work.
    var quill = new Quill('#topic-content', {
        modules: {
            toolbar: [
                [{ header: [1, 2, false] }],
                ['bold', 'italic', 'underline'],
                ['image', 'code-block']
            ]
        },
        placeholder: 'Compose an epic...',
        theme: 'snow'
    });

    // Updates a hidden input with the base 64 encoded contents of the rich text editor box
    $('#topic-form').submit(function() {
		var content = /*utoa(*/quill.container.querySelector('.ql-editor').innerHTML/*)*/;
        document.getElementById("quill-content-submit").value = content;
		//alert(length(content));
        //console.log(btoa(quill.container.querySelector('.ql-editor').innerHTML));
    });
</script>

<?php
include('footer.php');
?>
