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

    // Add/Modify a stock for the user 
    if (isset($_POST['addOrModUserStock']) && isset($_POST['stockSymbol'])
      && isset($_POST['stockAmt'])) {

      addOrModUserStock($_SESSION['username'], $_POST['stockSymbol'], $_POST['stockAmt']);
    }

    // Get all user's stock. This refreshes all the stock prices also
    if (isset($_POST['getUserStocks'])) {
      refreshStockPrices($_SESSION['username']);
      getUserStocks($_SESSION['username']);
    }
    
    // Deletes a specified user's stock
    if (isset($_POST['deleteStockAssociation'])) {
      deleteStockAssociation($_SESSION['username'], $_POST['stockSymbol']);
    }
  }

  /*
  * Purpose: Deletes a specified user's stock association
  * @param {string} $username - the user name
  * @param {string} $stockSymbol - the stock symbol
  */
  function deleteStockAssociation($username, $stockSymbol) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("DELETE FROM user_has_stocks 
      WHERE user_id IN (SELECT id FROM users WHERE username=?) 
      AND stock_id IN (SELECT id FROM user_stocks WHERE stock_symbol=?);"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("ss", $username, $stockSymbol)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    echo "userStockDeleteSuccessful";
  }

  /*
  * Purpose: Adds or modifies an existing stock for a user
  * @param {string} $username - the user name
  * @param {string} $stockSymbol - the stock symbol
  * @param {int} $stockAmt - the stock 
  */
  function addOrModUserStock($username, $stockSymbol, $stockAmt) {
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

  /*
  * Purpose: Associate a stock to a user 
  * @param {string} $username - the user name
  * @param {string} $stockSymbol - the stock symbol
  * @param {int} $stockAmt - the stock 
  */
  function associateStockToUser($username, $stockSymbol, $stockAmt) {
    global $mysqli;

    // Exit this function if stock doesnt not exist in the DB 
    if (!stockAlreadyExists($stockSymbol)) {
      return;
    }

    // Exit this workflow if association already exists
    if (stockAlreadyAssociatedToUser($username, $stockSymbol)) {
      updateUserStockQuantity($username, $stockSymbol, $stockAmt);
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

  /*
  * Purpose: Updates a user's stock quantity
  * @param {string} $username - the user name
  * @param {string} $stockSymbol - the stock symbol
  * @param {int} $stockAmt - the stock 
  */
  function updateUserStockQuantity($username, $stockSymbol, $stockAmt) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("UPDATE user_has_stocks SET amount=?
      WHERE user_id IN (SELECT id FROM users WHERE username=?) 
      AND stock_id IN (SELECT id FROM user_stocks WHERE stock_symbol=?);"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("iss", $stockAmt, $username, $stockSymbol)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    echo "quantityUpdateSuccessful";
  }

  /*
  * Purpose: Checks if a stock is already associated to a user
  * @param {string} $username - the user name
  * @param {string} $stockSymbol - the stock symbol
  */
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

  /*
  * Purpose: Adds a stock to the stocks table
  * @param {string} $stockSymbol - the stock symbol
  */
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

  /*
  * Purpose: Checks to see if a stock already exists in the stocks table
  * @param {string} $stockSymbol - the stock symbol
  */
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

  /*
  * Purpose: Gets stock info (symbol, name, price) for a single stock
  * @param {string} $stockSymbol - the stock symbol
  * @returns {array} - the result stock object
  */
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
  * @param {string} $username - the user name
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

      if (floatval($row["Stock Price"]) === 0.0000) {
        $row["Stock Price"] = "Stock Price Not Found";
        $row["Price x Quantity"] = "N/A";
      }
      else {
        $row["Price x Quantity"] = $row["Stock Price"] * $row["Quantity Monitored"];
        // echo var_dump($row["Stock Price"]);
      }


      $stocksArr[] = json_encode($row);
    }

    $finaljson = json_encode($stocksArr);
    echo $finaljson;
  }

  /*
  * Purpose: Refreshes the stock price of all of a user's stocks
  * @param {string} $username - the user name
  */
  function refreshStockPrices($username) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT us.stock_symbol 
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
      return;
    }

    // Generate the return object
    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      updateStockPrice($row["stock_symbol"]);
    }
  }

  /*
  * Purpose: Updates the stock price
  * @param {string} $username - the user name
  */
  function updateStockPrice($stockSymbol) {
    global $mysqli;

    $resultArr = getStockInfo($stockSymbol);
    $newStockPrice = $resultArr["stockPrice"];

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("UPDATE user_stocks SET stock_price=?
      WHERE stock_symbol=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("ds", $newStockPrice, $stockSymbol)) {
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
