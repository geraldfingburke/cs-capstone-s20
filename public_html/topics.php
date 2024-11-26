<?php
//Start/resume the session
session_start();

include('header.php');

//Check to make sure we're an admin; otherwise get out now
if (!($_SESSION["IS_ADMIN"] > 0))
{
    die("You do not have permission to view this page.");
}

// We delete whatever course has been fed into the value of the delete button.
if (isset($_POST["delete"])) {
    include_once("db.php");
    // We check to see if the topic is the last. If it is, we don't delete it. Refer to add_course in db.php for more information on this.
    if ($_SESSION["isLastTopic"]) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">Cannot delete last topic! Try deleting course or editing this topic!</div>";
    }
    else {
        delete_topic($_POST["delete"]);
    }
}

// Here we're checking if the move button has been hit. This is the only time we'll pass these particular variables using get
if (isset($_GET["topic_id"]) && isset($_GET["direction"])) {
    include_once ("db.php");
    move_topic($_GET["topic_id"], $_GET["direction"]);
}

// We grab our courses last so that we have time for database operations to take effect
$courses = get_courses(NULL);
foreach ($courses as $course) {
	if ($course["COURSE_ID"] == $_GET["courseID"] || $course["COURSE_ID"] == $_POST["courseID"]) {
		$currentCourse = $course;
		$_SESSION["current_course"] = $currentCourse;
	}
}

?>
<script>
    // This puts the value of our course id into our get and post submissions
    function updateTopicSelection (selection) {
        document.getElementById("deleteButton").value = selection;
		document.getElementById("courseID").value = <?php echo $currentCourse["COURSE_ID"]; ?>;
    }
</script>
<div class="container">
    <?php
    if ($currentCourse != NULL) {
        echo "<h2>".$currentCourse["COURSE_NAME"]."</h2>";
    }
    ?>

    <div class="card m-3">
        <div class="card-header">Manage Topics</div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <a href="topic.php?courseID=<?php echo $currentCourse["COURSE_ID"]?>" class="btn btn-success"><span>Add New Topic</span></a>
                <a href="admin.php" class="btn btn-primary float-right">Back to Courses</a>
                <div id=""></div>
                <div class="container m-3">
                    <div class="table-wrapper">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Topic</th>
                                <th>Actions</th>
                                <th>Has Video</th>
                                <th colspan="2">Order</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Here we loop through each topic and generate a table using the info.
                                // One reason we generate all of the html in this section is it affords us the opportunity to pass values between pages using html tags
								$i = 0;
                                foreach ($currentCourse["TOPICS"] as $topics) {
                                    if (count($currentCourse["TOPICS"]) <= 1) {
                                        $_SESSION["isLastTopic"] = true;
                                    }
                                    else {
                                        $_SESSION["isLastTopic"] = false;
                                    }
                                    foreach ($topics as $topic) {
                                        $id = $topic["COURSE_TOPIC_ID"];
                                        $topicNumber = $topic["TOPIC_NUMBER"];
                                        $header = $topic["HEADER"];
                                        $content = $topic["CONTENT"];
                                        $video = $topic["VIDEO_EMBED_CODE"];
                                        $course_id = $currentCourse["COURSE_ID"];

                                        echo "<tr>";
                                        echo "<td>$header</td>";
                                        echo "<td>";
                                        echo "<a href=\"topic.php?topicID=$id\"><i class=\"material-icons\" data-toggle=\"tooltip\" title=\"\" data-original-title=\"Edit\">edit</i></a>";
                                        echo "<a href=\"#deleteTopicModal\" onclick=\"updateTopicSelection($id)\" class=\"delete\" data-toggle=\"modal\"><i class=\"material-icons\" data-toggle=\"tooltip\" title=\"\" data-original-title=\"Delete\">delete</i></a>";
                                        echo "</td>";
                                        echo "<td>";
                                        if ($video != NULL) {
                                            echo "Yes";
                                        }
                                        else {
                                            echo "No";
                                        }
                                        echo "</td>";
                                        echo "<td>";
										if ($i > 0)
										{
											echo "<a href=\"topics.php?courseID=$course_id&topic_id=$id&direction=up\">Move up</a>";
										}
                                        echo "</td>";
                                        echo "<td>";
										if ($i < count($currentCourse["TOPICS"]) - 1)
										{
											echo "<a href=\"topics.php?courseID=$course_id&topic_id=$id&direction=down\">Move down</a>";
										}
                                        echo "</td>";
                                        echo "</tr>";

                                    }

									$i++;
                                }
                                ?>

                            </tbody>
                        </table>
                        <!--<div class="clearfix">
                            <div class="hint-text">Showing <b>1</b> out of <b>1</b> topics</div>
                            <ul class="pagination">
                                <li class="page-item disabled"><a href="#" class="page-link">Previous</a></li>
                                <li class="page-item active"><a href="#" class="page-link">1</a></li>
                                <li class="page-item"><a href="#" class="page-link">Next</a></li>
                            </ul>
                        </div>-->
                    </div>
            </li>
        </ul>
    </div>
</div>

<!-- This is the little window that pops up when you go to delete a topic -->
<div id="deleteTopicModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="topics.php" method="post">
                <div class="modal-header">
                    <h4 class="modal-title">Delete Topic</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this topic?</p>
                    <p class="text-warning"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
					<input type="hidden" name="courseID" id="courseID" value="" />
                    <button id="deleteButton" type="submit" name="delete" class="btn btn-danger" value="">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
