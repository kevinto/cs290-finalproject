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
  }

  function addUserStock($username, $stockSymbol, $stockAmt) {
    if ($stockSymbol === '' || $stockAmt === '') {
      echo 'emptyParams';
      die();
    }
    else {
      addStockToStocksTable($stockSymbol);
      //associateStockToUser();
      die();
    } 
  }

  function addStockToStocksTable($stockSymbol) {
    global $mysqli;

    // stockAlreadyExists();


    // Make Yahoo API call to get JSON Result set back
    // Echo out to test
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

    echo "addStockSuccessful";
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
?>
