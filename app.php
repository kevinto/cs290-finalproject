<?php
  session_start();
  error_reporting(E_ALL);
  ini_set('display_errors', 'On');
  include  'storedInfo.php';

  // Test MYSQL connection. The authentication information is in a separate file
  $mysqli = new mysqli("oniddb.cws.oregonstate.edu", $myUsername, $myPassword, $myUsername);
  if ($mysqli->connect_errno) {
    echo "Failed to connect to MYSQL <br>";
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' and count($_POST) > 0) {

    // Authenticate a user 
    if (isset($_POST['validateSignOn']) && isset($_POST['username'])
      && isset($_POST['password'])) {

      // Check for any empty parameters
      if ($_POST['username'] !== '' && $_POST['password'] !== '') {

        if (validateSignOn($_POST['username'], $_POST['password']) 
          && session_status() == PHP_SESSION_ACTIVE) {
          // Set login cookies
          $_SESSION['username'] = $_POST['username'];
        }

        die();
      }
      else {
        echo 'emptyParams';
        die();
      }
    die();
    }
  }

?>
