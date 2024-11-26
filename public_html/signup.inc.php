<?php

if (isset($_POST['signup-submit'])) {

//        require 'dbh.inc.php';

        $username = $_POST['uid'];
        $email = $_POST['mail'];
        $password = $_POST['pwd'];
        $passwordRepeat = $_POST['pwd-repeat'];

        //error handlers

        //checks for empty fields
        if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
            //goes back a directory and ? behind url adds extra info
            header("Location: admin.php?error=emptyfields&uid=".username."&mail".$email);
            exit();
        }


        //checks both valid email and user name
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !preg_match("/^[a-zA-Z0-9]*$/", $username)) {
            header("Location: admin.php?error=invalidmailuid");
            exit();
        }


        //checks for valid email address
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: admin.php?error=invalidmail&uid=".username);
            exit();
        }


        //checks for valid username
        elseif (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
            header("Location: admin.php?error=invaliduid&mail=".$email);
            exit();
        }


        //checks that passwords match
        elseif ($password !== $passwordRepeat) {
            header("Location: ../signup.php?error=passwordcheck&uid=".username."&mail".$email);
            exit();
        }


        //checks if username is unique
        else {

            $sql = "SELECT uidUsers FROM users WHERE uidUsers=?";
            $stmt = mysqli_stmt_init($conn);

            if(!mysqli_stmt_prepare($stmt, $sql)){
                header("Location: admin.php?error=sqlerror");
                exit();
            }
            else {
                mysqli_stmt_bind_param($stmt,"s", $username);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                $resultCheck = mysqli_stmt_num_rows($stmt);
                if ($resultCheck > 0) {
                    header("Location: admin.php?error=usertaken&mail=".$email);
                    exit();
                }
                else {

                    $sql = "INSERT INTO users (uidUsers, emailUsers,pwdUsers) VALUES (?,?,?)";
                    $stmt = mysqli_stmt_init($conn);
                    if(!mysqli_stmt_prepare($stmt, $sql)){
                        header("Location: admin.php?error=sqlerror");
                        exit();
                    }
                    else {

                        $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

                        mysqli_stmt_bind_param($stmt,"sss", $username, $email, $hashedPwd);
                        mysqli_stmt_execute($stmt);
                        header("Location: admin.php?signup=success");
                        exit();
                    }

                }

            }

        }//end else

    mysqli_stmt_close($stmt);
        mysqli_close($conn);

}//end if

