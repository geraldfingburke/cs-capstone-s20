<?php
//Start/resume the session
session_start();

include('header.php');

//Check to make sure we're an admin; otherwise get out now
if (!($_SESSION["IS_ADMIN"] > 0))
{
    die("You do not have permission to view this page.");
}

//Are we performing a settings update?
if (isset($_POST["update"]))
{
    //Yes, so get the POSTed values
    $headerText = $_POST["headerText"];
    $primaryMessage = $_POST["primaryMessage"];

    //Now update them in the database
    update_settings(["HeaderText" => $headerText, "PrimaryMessage" => $primaryMessage]);

    ?>
    <div class="container">
        <div class="alert alert-warning" role="alert">Settings updated.</div>
    </div>
    <?php
}

//Are we adding a new user?
if (isset($_POST["add_user_submit"]))
{
	$error_message = '';
	$user_name = strtolower(trim($_POST["add_user_name"]));

	//First, make sure the username and password are there
	if (strlen($user_name) == 0 || strlen(trim($_POST["add_user_password1"])) == 0)
	{
		$error_message = 'You must specify a user name and password.';
	}

	//Now make sure the two passwords match
	if (strlen($error_message) == 0 && $_POST["add_user_password1"] != $_POST["add_user_password2"])
	{
		$error_message = 'Passwords do not match!';
	}

	//Now make sure a user with this username doesn't already exist
	//This could be improved instead of looping through *every* user
	if (strlen($error_message) == 0)
	{
		$users = get_users();
		foreach ($users as $key => $value)
		{
			if (strtolower(trim($value["USER_NAME"])) == $user_name)
			{
				$error_message = "User name already exists!";
				break;
			}
		}
	}
	
	//Still no errors?
	if (strlen($error_message) == 0)
	{
		//Yes, so create the user
		try
		{
			add_user($user_name, $_POST["add_user_password1"], $_POST["add_is_admin"] == "on" ? 1 : 0);
		}
		catch (Exception $e)
		{
			$error_message = "Error: ".$e->getMessage();
		}
	}
	
	//Display success or error
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
		<div class="alert alert-warning" role="alert">User added.</div>
	</div>
<?php
	}
}

//Are we editing an existing user?
if (isset($_POST["edit_user_submit"]))
{
	$error_message = '';
	$user_name = strtolower(trim($_POST["edit_user_name"]));

	//First, make sure the username is there
	if (strlen($user_name) == 0)
	{
		$error_message = 'You must specify a user name.';
	}

	//Now make sure a user with this username doesn't already exist
	//This could be improved instead of looping through *every* user
	if (strlen($error_message) == 0)
	{
		$users = get_users();
		foreach ($users as $key => $value)
		{
			if (strtolower(trim($value["USER_NAME"])) == $user_name && $value["USER_ID"] != $_POST["edit_user_id"])
			{
				$error_message = "User name already exists!";
				break;
			}
		}
	}

	//Are we trying to change the password?
	if (strlen($error_message) == 0 && (strlen($_POST["edit_user_password1"]) > 0 || strlen($_POST["edit_user_password2"]) > 0))
	{
		//Yes, so make sure they match
		if (strlen($error_message) == 0 && $_POST["edit_user_password1"] != $_POST["edit_user_password2"])
		{
			$error_message = 'Passwords do not match!';
		}

		if (strlen($error_message) == 0)
		{
			//Now go ahead and change it separately
			set_user_password($_POST["edit_user_id"], $_POST["edit_user_password1"]);
		}
	}
	
	//Still no errors?
	if (strlen($error_message) == 0)
	{
		//Yes, so edit the user
		try
		{
			edit_user($_POST["edit_user_id"], $user_name, $_POST["edit_is_active"] == "on" ? 1 : 0,	$_POST["edit_is_admin"] == "on" ? 1 : 0);
		}
		catch (Exception $e)
		{
			$error_message = "Error: ".$e->getMessage();
		}
	}
	
	//Display success or error
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
		<div class="alert alert-warning" role="alert">User edited.</div>
	</div>
<?php
	}
}

//Are we deleting a user?
if (isset($_POST["delete_user_submit"]))
{
	//Get the user ID
	$user_id = $_POST["delete_user_id"];
	
	if (isset($user_id))
	{
		//Delete it
		delete_user($user_id);

?>
	<div class="container">
		<div class="alert alert-warning" role="alert">User deleted.</div>
	</div>
<?php
	}
}

if (isset($_POST["courseName"]) && isset($_POST["courseDescription"])) {
    add_course($_POST["courseName"], $_POST["courseDescription"]);
?>
	<div class="container">
		<div class="alert alert-warning" role="alert">Course added.</div>
	</div>
<?php
}

if (isset($_POST["courseNameEdit"]) && isset($_POST["courseDescriptionEdit"]) && isset($_POST["edit"])) {
    edit_course($_POST["courseNameEdit"], $_POST["courseDescriptionEdit"], $_POST["edit"]);
?>
	<div class="container">
		<div class="alert alert-warning" role="alert">Course updated.</div>
	</div>
<?php
}

if (isset($_POST["delete"])) {
    delete_course($_POST["delete"]);
?>
	<div class="container">
		<div class="alert alert-warning" role="alert">Course deleted.</div>
	</div>
<?php
}

?>
<script>
    var courseSelection = 0;
    // Gets info from course list to pass to post request for modal form
    function updateCourseSelection (selection, name, summary) {
        document.getElementById("deleteButton").value = selection;
        document.getElementById("editButton").value = selection;
        document.getElementById("courseNameField").value = atob(name);
        document.getElementById("courseSummaryField").innerHTML = atob(summary);
    }

	//This function accepts the user ID and sets it on a hidden input, along with all the other user information, and then displays the modal via JavaScript.
	function editUser(user_id, user_name, is_active, is_admin)
	{
		$("#edit_user_id").val(user_id);
		$("#edit_user_name").val(user_name);
		$("#edit_is_active").prop("checked", is_active);
		$("#edit_is_admin").prop("checked", is_admin);

		$("#editUserModal").modal();
	}
	
	//This function accepts the user ID, sets it on a hidden input, and then displays the modal via JavaScript.
	function deleteUser(user_id)
	{
		$("#delete_user_id").val(user_id);
		$("#deleteUserModal").modal();
	}
</script>
<div class="container">
    <h2>Administration</h2>

    <div class="card m-3">
        <div class="card-header">Settings</div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <form action="admin.php" method="POST">
                    <div class="form-group">
                        <label for="headerText">Header Text</label>
                        <input type="text" class="form-control" id="headerText" name="headerText" aria-describedby="headerTextHelp" placeholder="Enter header text" value="<?php echo $settings["HeaderText"] ?>"/>
                    </div>
                    <div class="form-group">
                        <label for="primaryMessage">Primary Message</label>
                        <textarea class="form-control" id="primaryMessage" name="primaryMessage" rows="5"><?php echo $settings["PrimaryMessage"] ?></textarea>
                    </div>
                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                </form>
            </li>
        </ul>
    </div>

<!-- THIS IS THE USER FUNCTIONALITY. -->
<?php
	//Get all the users from MySQL
	$users = get_users();
?>
    <div class="card m-3">
        <div class="card-header">Manage Users</div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <a href="#addUserModal" class="btn btn-success" data-toggle="modal"><span>Add New User</span></a>
                <div class="container m-3">
                    <div class="table-wrapper">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>User Name</th>
                                <th>Is Admin</th>
								<th>Is Active</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
<?php
	//Display one row per user
	foreach ($users as $key => $value)
	{
		echo "<tr><td>".$value["USER_NAME"]."</td><td>";
		if ($value["IS_ADMIN"])
		{
			echo "Yes";
		}
		else
		{
			echo "No";
		}
		
		echo "</td><td>";
		if ($value["IS_ACTIVE"])
		{
			echo "Yes";
		}
		else
		{
			echo "No";
		}

		echo "</td><td>";
?>
                                    <a href="#" class="edit" onclick="editUser(<?php echo $key; ?>, <?php echo '\''.$value["USER_NAME"].'\''; ?>, <?php echo $value["IS_ACTIVE"] ?: 0; ?>, <?php echo $value["IS_ADMIN"] ?: 0; ?>);"><i class="material-icons" data-toggle="tooltip" title="" data-original-title="Edit">edit</i></a>
                                    <a href="#" class="delete" onclick="deleteUser(<?php echo $key; ?>);"><i class="material-icons" data-toggle="tooltip" title="" data-original-title="Delete">delete</i></a>
                                </td></tr>
<?php
	}
?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </li>
        </ul>
    </div>
    <!-- This is the modal for adding users. -->
    <div id="addUserModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="admin.php" method="post">
                    <div class="modal-header">
                        <h4 class="modal-title">Add User</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-body">
						<table>
							<tr>
								<div class="form-group"><td style="padding: 15px"><label>User Name</label></td></div>
								<div class="form-group"><td><input class="form-control" type="text" name="add_user_name" placeholder="User Name"></td></div>
							</tr>
							<tr>
								<div class="form-group"><td style="padding: 15px"><label>Password</label></td></div>
								<div class="form-group"><td><input class="form-control" type="password" name="add_user_password1" placeholder="Password"></td></div>
							</div>
							</tr>
							<tr>
								<div class="form-group"><td style="padding: 15px"><label>Confirm password</label></td></div>
								<div class="form-group"><td><input class="form-control" type="password" name="add_user_password2" placeholder="Repeat password"></td></div>
							</tr>
							<tr>
								<div class="form-group"><td style="padding: 15px"><label>Is admin?</label></td></div>
								<div class="form-group"><td><input id="add_is_admin" class="form-control" type="checkbox" name="add_is_admin"/></td>
							</tr>
						</table>
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
                        <input type="submit" class="btn btn-success" name="add_user_submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- This is the modal for editing users. -->
    <div id="editUserModal" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="admin.php" method="post">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit User</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-body">
						<table>
							<tr>
								<div class="form-group"><td style="padding: 15px"><label>User Name</label></td></div>
								<div class="form-group"><td><input id="edit_user_name" class="form-control" type="text" name="edit_user_name" placeholder="User Name"></td></div>
							</tr>
							<tr>
								<div class="form-group"><td style="padding: 15px"><label>Change password (or leave blank)</label></td></div>
								<div class="form-group"><td><input id="edit_user_password1" class="form-control" type="password" name="edit_user_password1" placeholder="Password"></td></div>
							</div>
							</tr>
							<tr>
								<div class="form-group"><td style="padding: 15px"><label>Confirm password</label></td></div>
								<div class="form-group"><td><input id="edit_user_password2" class="form-control" type="password" name="edit_user_password2" placeholder="Repeat password"></td></div>
							</tr>
							<tr>
								<div class="form-group"><td style="padding: 15px"><label>Is active?</label></td></div>
								<div class="form-group"><td><input id="edit_is_active" class="form-control" type="checkbox" name="edit_is_active"/></td>
							</tr>
							<tr>
								<div class="form-group"><td style="padding: 15px"><label>Is admin?</label></td></div>
								<div class="form-group"><td><input id="edit_is_admin" class="form-control" type="checkbox" name="edit_is_admin"/></td>
							</tr>
						</table>
                    </div>
                    <div class="modal-footer">
						<input type="hidden" id="edit_user_id" name="edit_user_id"/>
                        <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
                        <input type="submit" class="btn btn-success" name="edit_user_submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
	<!-- This is the modal for deleting a user. -->
	<div id="deleteUserModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<form action="admin.php" method="post">
					<div class="modal-header">
						<h4 class="modal-title">Delete User</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					</div>
					<div class="modal-body">
						<p>Are you sure you want to delete this user?</p>
						<p class="text-warning"><small>This action cannot be undone.</small></p>
					</div>
					<div class="modal-footer">
						<input type="hidden" id="delete_user_id" name="delete_user_id"/>
						<input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
						<button id="deleteUserButton" type="submit" name="delete_user_submit" class="btn btn-danger">Delete</button>
					</div>
				</form>
			</div>
		</div>
	</div>

    <!-- Course management functionality doesn't work yet. -->

    <div class="card m-3">
        <div class="card-header">Manage Courses</div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <a href="#addCourseModal" class="btn btn-success" data-toggle="modal"><span>Add New Course</span></a>
                <div id=""></div>
                <div class="container m-3">
                    <div class="table-wrapper">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Topics</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>

                                <?php
                                include_once('db.php');
                                $courses = get_courses(NULL);
                                foreach ($courses as $course) {
                                    $id = $course['COURSE_ID'];
                                    $name = $course['COURSE_NAME'];
                                    $summary = $course['SUMMARY'];
                                    echo "<tr>";
                                    echo "<td>";
                                    //echo "<form action='topics.php' method='post'>";
                                    //echo "<button type='submit' name='courseID' class='btn btn-link' value='$id'>$name</button>";
                                    echo "<a href='topics.php?courseID=".$id."'>$name</a>";
                                    //echo "</form>";
                                    echo "</td>";
                                    echo "<td>".count($course['TOPICS'])."</td>";
                                    echo "<td>";
									$n = base64_encode($name);
									$s = base64_encode($summary);
                                    echo "<a href=\"#editCourseModal\" onclick=\"updateCourseSelection($id, '$n', '$s')\" class=\"edit\" data-toggle=\"modal\"><i class=\"material-icons\" data-toggle=\"tooltip\" title=\"\" data-original-title=\"Edit\">edit</i></a>";
                                    echo "<a href=\"#deleteCourseModal\" onclick=\"updateCourseSelection($id, '$n', '$s')\" class=\"delete\" data-toggle=\"modal\"><i class=\"material-icons\" data-toggle=\"tooltip\" title=\"\" data-original-title=\"Delete\">delete</i></a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>

                            </tbody>
                        </table>
                        <!--<div class="clearfix">
                            <div class="hint-text">Showing <b>1</b> out of <b>1</b> courses</div>
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
</div>

<!-- This is the Modal that pops up when you add a course -->

<div id="addCourseModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="admin.php" method="post">
                <div class="modal-header">
                    <h4 class="modal-title">Add Course</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Course Name</label>
                        <input name="courseName" type="text" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="courseDescription">Course Description</label>
                        <textarea name="courseDescription" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
                    <input type="submit" class="btn btn-success" value="Add">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- This is the Modal that pops up when you edit a course -->

<div id="editCourseModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="admin.php" method="post">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Course</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Course Name</label>
                        <input id="courseNameField" name="courseNameEdit" type="text" class="form-control" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="courseDescriptionEdit">Course Description</label>
                        <textarea id="courseSummaryField" name="courseDescriptionEdit" class="form-control" rows="5" value="" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
                    <button id="editButton" type="submit" name="edit" class="btn btn-info" value="">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- This is the Modal that pops up when you delete a course -->

<div id="deleteCourseModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="admin.php" method="post">
                <div class="modal-header">
                    <h4 class="modal-title">Delete Course</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this course?</p>
                    <p class="text-warning"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
                    <button id="deleteButton" type="submit" name="delete" class="btn btn-danger" value="">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
