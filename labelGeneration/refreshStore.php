<?php
    include 'functionsToCreateLabels.php'; 

    // We need to use sessions, so you should always start sessions using the below code.
    session_start();
    // If the user is not logged in redirect to the login page...
    // if (!isset($_SESSION['loggedin_'])) {
    //     header('Location: https://digitweb.vintageinterior.co.uk/index.html');
    //     exit();
    // }

    //require_once('authenticate.php');
    $DATABASE_HOST   = 'localhost';
    $DATABASE_USER   = 'root';
    $DATABASE_PASS   = '';
    $DATABASE_NAME = 'u525933064_dashboard';
    
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    if (mysqli_connect_errno()) {
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }
    // We don't have the password or email info stored in sessions so instead we can get the results from the database.
    $stmt = $con->prepare('SELECT password, email FROM accounts WHERE id = ?');
    // In this case we can use the account ID to get the account info.
    $stmt->bind_param('i', $_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($password, $email);
    $stmt->fetch();
    $stmt->close();
    
    refreshStore();

  