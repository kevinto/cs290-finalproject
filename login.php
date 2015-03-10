<?php
  //session_start();
  error_reporting(E_ALL);
  ini_set('display_errors', 'On');
  include  'storedInfo.php';
  
  // Test MYSQL connection. The authentication information is in a separate file
  $mysqli = new mysqli("oniddb.cws.oregonstate.edu", $myUsername, $myPassword, $myUsername);
  if ($mysqli->connect_errno) {
    echo "Failed to connect to MYSQL <br>";
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET' and count($_GET) > 0) {
    // Insert one new customer
    if (isset($_GET['registerUser']) && isset($_GET['username'])
      && isset($_GET['password']) && isset($_GET['passwordRepeated'])
      && isset($_GET['email']) && isset($_GET['emailRepeated'])) {

      // Check if there are any empty parameters
      if ($_GET['username'] !== '' && $_GET['password'] !== ''
        && $_GET['passwordRepeated'] !== '' && $_GET['email'] !== ''
        && $_GET['emailRepeated'] !== '') {

        registerUser();
        die();
      }
      else {
        echo 'EmptyParams';
        die();
      }
    }
  }

  function registerUser() {
    echo 'bleh';
    die();
  }

?>
