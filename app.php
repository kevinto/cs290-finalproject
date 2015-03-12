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

  // Check if session is active
  if (session_status() != PHP_SESSION_ACTIVE) {
    echo "sessionInactive";
    die();
  }

  // Check if a user is signed on
  if (isset($_SESSION['username']) && $_SESSION['username'] === '') {
    echo "userNotLoggedOn";
    die();
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' and count($_POST) > 0) {

    // Add a stock for the user 
    if (isset($_POST['addUserStock']) && isset($_POST['stockSymbol'])
      && isset($_POST['stockAmt'])) {

      addUserStock($_SESSION['username'], $_POST['stockSymbol'], $_POST['stockAmt']);
    }

    // Get all user's stock
    if (isset($_POST['getUserStocks'])) {
      getUserStocks($_SESSION['username']);
    }
  }

  function addUserStock($username, $stockSymbol, $stockAmt) {
    if ($stockSymbol === '' || $stockAmt === '') {
      echo 'emptyParams';
      die();
    }
    else {
      $stockAmt = intval($stockAmt);

      addStockToStocksTable($stockSymbol);
      associateStockToUser($username, $stockSymbol, $stockAmt);
      echo 'stockAssociationSuccessful';
      die();
    } 
  }

  function associateStockToUser($username, $stockSymbol, $stockAmt) {
    global $mysqli;

    // Exit this function if stock doesnt not exist in the DB 
    if (!stockAlreadyExists($stockSymbol)) {
      return;
    }

    // Exit this workflow if association already exists
    if (stockAlreadyAssociatedToUser($username, $stockSymbol)) {
      echo "stockAssociationAlreadyExists";
      die();
    }

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO user_has_stocks(user_id, stock_id, amount) 
      VALUES ((SELECT id FROM users WHERE username=? LIMIT 1), 
        (SELECT id FROM user_stocks WHERE stock_symbol=? LIMIT 1), 
        ?);"))) {

      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("ssi", $username, $stockSymbol, $stockAmt)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  function stockAlreadyAssociatedToUser($username, $stockSymbol) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT uhs.id 
      FROM users u INNER JOIN user_has_stocks uhs ON u.id=uhs.user_id 
      INNER JOIN user_stocks us ON uhs.stock_id=us.id 
      WHERE u.username=? and us.stock_symbol=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    if (!$stmt->bind_param("ss", $username, $stockSymbol)) {
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

  function addStockToStocksTable($stockSymbol) {
    global $mysqli;

    // Exit this function if stock already exists
    if (stockAlreadyExists($stockSymbol)) {
      return;
    }

    // Make Yahoo API call to get stock info
    $stockInfo = getStockInfo($stockSymbol);

    if ($stockInfo['stockName'] === 'N/A') {
      echo "invalidStockEntered";
      die();
    }

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO user_stocks(stock_name, stock_symbol, stock_price) 
      VALUES (?, ?, ?);"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("ssd", $stockInfo['stockName'], $stockInfo['stockSymbol'], $stockInfo['stockPrice'])) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  function stockAlreadyExists($stockSymbol) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT id FROM user_stocks WHERE stock_symbol=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    if (!$stmt->bind_param("s", $stockSymbol)) {
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

  function getStockInfo($stockSymbol) {
    // Make Yahoo API call
    $outfilename = "quote.csv";
    $url = "http://download.finance.yahoo.com/d/quotes.csv?s=" . $stockSymbol . "&f=nsl1&e=.csv";
    $downloadFile = file_get_contents($url);
    file_put_contents($outfilename, $downloadFile);

    // Convert csv data into an array
    if (($handle = fopen("quote.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            for ($c=0; $c < $num; $c++) {
                switch ($c) {
                  case 0:
                    $resultArr["stockName"] = $data[$c];
                    break;
                  case 1:
                    $resultArr["stockSymbol"] = $data[$c];
                    break;
                  case 2:
                    $resultArr["stockPrice"] = floatval($data[$c]);
                    break; 
                }

            }
        }
        fclose($handle);
    }

    return $resultArr;
  }

  /*
  * Purpose: Gets all the users's stocks
  * @param {int} $username - the user name
  * @return {object} - a JSON object all the customer's stocks 
  */
  function getUserStocks($username) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT us.stock_name as 'Stock Name', us.stock_symbol as 'Stock Symbol', 
      us.stock_price as 'Stock Price', uhs.amount as 'Quantity Monitored' 
      FROM users u INNER JOIN user_has_stocks uhs ON u.id=uhs.user_id 
      INNER JOIN user_stocks us ON uhs.stock_id=us.id 
      WHERE u.username=?;"))) {
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
      echo "noRecords";
      return;
    }

    // Generate the return object
    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      $row["Price x Quantity"] = $row["Stock Price"] * $row["Quantity Monitored"];

      $stocksArr[] = json_encode($row);
    }

    $finaljson = json_encode($stocksArr);
    echo $finaljson;
  }
?>
