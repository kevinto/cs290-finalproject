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
      && isset($_GET['email']) && isset($_GET['emailRepeated']) 
      && isset($_GET['birthday'])) {

      // Check if there are any empty parameters
      if ($_GET['username'] !== '' && $_GET['password'] !== ''
        && $_GET['passwordRepeated'] !== '' && $_GET['email'] !== ''
        && $_GET['emailRepeated'] !== '' && $_GET['birthday'] !== '') {

        registerUser($_GET['username'], $_GET['password'], $_GET['passwordRepeated'], $_GET['email'], 
          $_GET['emailRepeated'], $_GET['birthday']);
        die();
      }
      else {
        echo 'emptyParams';
        die();
      }
    }
  }

  function registerUser($username, $password, $passwordRepeated, $email, $emailRepeated, $birthday) {
    global $mysqli;

    // Check that both passwords match
    if ($password !== $passwordRepeated) {
      echo "passwordsNotMatching";
      die();
    } 

    // Check that email's match
    if ($email !== $emailRepeated) {
      echo $email;
      echo $emailRepeated;
      echo "emailsNotMatching";
      die();
    }

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO users(username, password, email, birthday)
      VALUES (?, ?, ?, ?);"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("ssss", $username, $password, $email, $birthday)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

?>
