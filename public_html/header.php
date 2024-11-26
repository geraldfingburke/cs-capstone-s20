<?php
	//Start/resume the session
	//After this line of code, you can access anything stored in this user's session via $_SESSION[].
	session_start();

	//Retrieve site settings, for displaying header text, etc.
	include_once('db.php');
	$settings = get_settings();

?>
<html>
<head>
<title>Northeast State CSCA</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<!-- DO NOT REMOVE ANY OF THE ITEMS BELOW!!! IT BREAKS THE COURSE TOPICS EDITOR WHEN YOU DO. -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<link rel="stylesheet" href="main.css" />
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"><script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<!-- DO NOT REMOVE ANY OF THE ITEMS ABOVE!!! IT BREAKS THE COURSE TOPICS EDITOR WHEN YOU DO. -->
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light" style0="background-color: #00937E">
  <a class="navbar-brand" href="index.php"><img src="logo.png" /></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
	<ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <h1 style="color:#07466b" ; style="text-align:center" class="float margin-auto"> <?php echo $settings["HeaderText"]; ?></h1>
      </li>
    </ul>

    <form class="form-inline my-2 my-lg-0" method="GET" action="/~CSCapstoneS20/index.php">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" name="search" aria-label="Search">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
  </div>
</nav>

<nav class="navbar navbar-light" style="background-color: #00937f">
	<span class="navbar-brand mb-0 h1"></span>
<?php
	//Determine if we are logged in (meaning session has the user ID)
	if (!is_null($_SESSION["USER_ID"]))
	{
		echo "<div style=\"color: white\">Hello, <a href=\"user.php\" style=\"color: white\">".$_SESSION["USER_NAME"]."</a>&nbsp;|&nbsp;";
		
		//Determine if we are an admin (and display admin link if so)
		if ($_SESSION["IS_ADMIN"] > 0)
		{
			echo "<a href=\"admin.php\" style=\"color: white\">Admin</a>&nbsp;|&nbsp;";
		}

		echo "<a href=\"logout.php\" style=\"color: white\">Logout</a></div>";
	}
	else
	{
		echo "<a href=\"login.php\" style=\"color: white\">Login</a>";
	}
?>
</nav>
