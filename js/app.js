function checkIfSignedInForAppPage() {
  // Create Return function
  var signOnReturnFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var resultObj = JSON.parse(request.responseText);

        switch (resultObj.status) {
          case 'notLoggedIn':
            EnableNotLoggedInMode();
            break;
          case 'loggedIn':
            EnableLoggedInMode(resultObj.username); 

            generateUserStkTable();
            break;
          default:
            var signedOutHeader = document.getElementById('not-loggedin-intro');
            signedOutHeader.innerText = 'Something is wrong at the server. Please try again later.';
            break;
        }
      }
    }
  };

  // Create Php parameters
  var userParams = {
    checkIfSignedIn : true,
  };

  callLoginPhp(signOnReturnFunc, userParams);

  return false;
}

function EnableLoggedInMode(username) {
 var signedInHeader = document.getElementById('user-intro');
  signedInHeader.innerText = 'Hi, ' + username;

  var signInBtn = document.getElementById('signin-button');
  signInBtn.style.display = 'none';
}

function EnableNotLoggedInMode() {
  var signedOutHeader = document.getElementById('not-loggedin-intro');
  signedOutHeader.innerText = 'You are not currently signed in. Please click the sign in button on the top of the page.';

  var logoutBtn = document.getElementById('logout-button');
  logoutBtn.style.display = 'none';

  var addStockSection = document.getElementById('new-stock-add');
  addStockSection.style.display = 'none';

  var viewStockSection = document.getElementById('current-stocks-held');
  viewStockSection.style.display = 'none';

  var viewFriendSection = document.getElementById('friends-stocks');
  viewFriendSection.style.display = 'none';
}

function generateUserStkTable() {
  // UI Table columns: stock name, stock symbol, price per share, quantity monitored, price x quantity

  // Define a return function
  var userStockTableFunc = function(request){
    return function() {
      if(request.readyState == 4) {

        // Get div to populate with data
        var containerId = 'current-stocks-held';
        var container = document.getElementById(containerId);

        // If no data is returned, exit the function
        if (request.responseText === 'noRecords') {
          var errText = document.createElement('p');
          errText.id = 'errorMsgs';
          errText.innerText = "No stocks currently monitored";
          container.appendChild(errText);
          return;
        }

        // Result is an array of JSON objects
        var resultObj = JSON.parse(request.responseText);

        // Create an array of JSON objects that we will make rows
        //    out of
        var tableParamObj = new Array();
        for (var i = 0; i < resultObj.length; i++) {
          tableParamObj.push(JSON.parse(resultObj[i]));
        }

        // Generate a table
        addTable(containerId, tableParamObj, 'userStockTable');
      }
    }
  };

  // Create object that holds the SQL query parameters
  var userParams = {
    getUserStocks: true,
  };

  callAppPhp(userStockTableFunc, userParams);
}

/*
* Adds a table for the database records. The table will contain
* header columns based off the JSON property names.
* @param {string} targetDiv - id of the Div you want to insert the
*                                              table into.
* @param {array} dispObjArray - an array of JSON objects containing
*                                                     database tuples
* @param {array} tableID - the id of the table you want to creates
*/
function addTable(targetDiv, dispObjArray, tableID) {

  var myTableDiv = document.getElementById(targetDiv);

  var table = document.createElement('table');
  table.className = 'table';
  table.id = tableID;

  // Create the header columns
  var headersAlreadyCreated = false
  for (var i = 0; i < dispObjArray.length; i++){
    if (!headersAlreadyCreated) {
      var tr = document.createElement('tr');
      table.appendChild(tr);
      for (var property in dispObjArray[i]) {
        if (dispObjArray[i].hasOwnProperty(property)) {

          var th = document.createElement('th');
          th.appendChild(document.createTextNode(property));
          tr.appendChild(th);
        }
      }

      headersAlreadyCreated = true;
    }

    // Create the rows for the data
    tr = document.createElement('tr');
    table.appendChild(tr);
    for (var property in dispObjArray[i]) {
      if (dispObjArray[i].hasOwnProperty(property)) {

        var td = document.createElement('td');
        td.appendChild(document.createTextNode(dispObjArray[i][property]));
        tr.appendChild(td);
      }
    }
  }

  myTableDiv.appendChild(table);
}

function addUserStock() {
  // Get form values
  var stockSymbol = document.getElementById('inputStockSym').value;
  var stockAmt = document.getElementById('inputStockAmount').value;

  // Create Return function
  var addStockReturnFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var errContainer = document.getElementById('errorMsgs');
        errContainer.innerText = '';

        switch (request.responseText) {
          case 'invalidStockEntered':
            errContainer.innerText = 'Please enter another stock symbol. The one you entered cannot be found.';
            clearNewStockFields();
            break;
          case 'emptyParams':
            errContainer.innerText = 'Please enter a stock symbol and amount owned.';
            break;
          case 'stockAssociationAlreadyExists':
            errContainer.innerText = 'Stock is already associated with user. If you want to modify the quantity owned for a currently monitored stock, then you can modify the quanity below.';
          case 'stockAssociationSuccessful':
            clearNewStockFields();
            // Add code here to refresh the table
        }
        console.log(request.responseText);
      }
    }
  };

  // Create Php parameters
  var stockAddParams = {
    addUserStock : true,
    stockSymbol : stockSymbol,
    stockAmt : stockAmt
  };

  callAppPhp(addStockReturnFunc, stockAddParams);

  return false;
}

function clearNewStockFields() {
  document.getElementById('inputStockSym').value = '';
  document.getElementById('inputStockAmount').value = ''; 
}

/*
* Calls the backend PHP code for the 'app' page
* @param {object} returnFunc - function that is executed after PHP
*                              backend is done executing
* @param {object} postParams - params you want to pass to the PHP backend
*/
function callAppPhp(returnFunc, postParams) {
  if (typeof(postParams) === 'undefined') {
    postParams = '';
  }

  var request = new XMLHttpRequest();
  var url = 'app.php';
  var postParamsStr = '';

  // Create the post parameter string
  if (postParams.length !== 0) {
    var i = 0;
    for (var property in postParams) {
      if (postParams.hasOwnProperty(property)) {
        if (i === 0) {postParamsStr += property + '=' + postParams[property];
        }
        else {
          postParamsStr += '&' + property + '=' + postParams[property];
        }
        i++;
      }
    }
  }

  if (!request){
    return false;
  }

  request.onreadystatechange = returnFunc(request);
  request.open('POST', url, true);
  request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  request.send(postParamsStr);
  return request;
}