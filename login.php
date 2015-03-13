<?php
  session_start();
  error_reporting(E_ALL);
  ini_set('display_errors', 'On');
  include  'storedInfo.php';

  // If logout parameter is get, destroy the current session cookie
  if (isset($_POST['logoff']) && $_POST['logoff'] === 'true') {
    $_SESSION = array();
    session_destroy();
  }

  // Test MYSQL connection. The authentication information is in a separate file
  $mysqli = new mysqli("oniddb.cws.oregonstate.edu", $myUsername, $myPassword, $myUsername);
  if ($mysqli->connect_errno) {
    echo "Failed to connect to MYSQL <br>";
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' and count($_POST) > 0) {

    // Register a user
    if (isset($_POST['registerUser']) && isset($_POST['username'])
      && isset($_POST['password']) && isset($_POST['passwordRepeated'])
      && isset($_POST['email']) && isset($_POST['emailRepeated']) 
      && isset($_POST['birthday'])) {

      // Check for any empty parameters
      if ($_POST['username'] !== '' && $_POST['password'] !== ''
        && $_POST['passwordRepeated'] !== '' && $_POST['email'] !== ''
        && $_POST['emailRepeated'] !== '' && $_POST['birthday'] !== '') {

        registerUser($_POST['username'], $_POST['password'], $_POST['passwordRepeated'], $_POST['email'], 
          $_POST['emailRepeated'], $_POST['birthday']);
        die();
      }
      else {
        echo 'emptyParams';
        die();
      }
    }
    
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

    // Check if user is already signed in
    if (isset($_POST['checkIfSignedIn']) && session_status() == PHP_SESSION_ACTIVE) {

      $returnArr = array('status' => "", 'username' => "");

      if (!isset($_SESSION['username'])) {
        $returnArr['status'] = "notLoggedIn";
      }
      else {
        $returnArr['status'] = "loggedIn";
        $returnArr['username'] = $_SESSION['username'];
      }

      $jsonReturn = json_encode($returnArr);
      echo $jsonReturn;
      die();
    }
  }

  /*
  * Purpose: Checks login info for user
  * @param {string} $username - the user name
  * @param {string} $password - the password
  */
  function validateSignOn($username, $password) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT id from users where username=? and password=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      return false;
    }

    if (!$stmt->bind_param("ss", $username, $password)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      return false;
    }

    if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      return false;
    }

    if (!($res = $stmt->get_result())) {
      echo "Getting result set failed: (" . $stmt->errno . ") " . $stmt->error;
      return false;
    }

    // if no results were returned, invalid user.
    if ($res->num_rows === 0) {
      echo "authenFailed";
      return false;
    }

    echo "loginSuccessful";
    return true;
  }

  /*
  * Purpose: Registers a user
  * @param {string} $username - the user name
  * @param {string} $password - the password
  * @param {string} $passwordRepeated - the password repeated
  * @param {string} $email - the email
  * @param {string} $emailRepeated - the email repeated 
  * @param {string} $birthday - the birthday date
  */
  function registerUser($username, $password, $passwordRepeated, $email, $emailRepeated, $birthday) {
    global $mysqli;

    // Check that both passwords match
    if ($password !== $passwordRepeated) {
      echo "passwordsNotMatching";
      die();
    } 

    // Check that email's match
    if ($email !== $emailRepeated) {
      echo "emailsNotMatching";
      die();
    }

    // Check if user name already exists
    if (usernameExists($username)) {
      echo "usernameDuplicate";
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

    echo "registrationSuccessful";
  }

  /*
  * Purpose: Checks if a user name already exists
  * @param {string} $username - the user name
  */
  function usernameExists($username) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT id from users where username=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    if (!$stmt->bind_param("s", $username)) {
          echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    if (!($res = $stmt->get_result())) {
      echo "Getting result set failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    // No results were returned, exit.
    if ($res->num_rows === 0) {
      return false;
    }
    
    return true;
  }
?>
