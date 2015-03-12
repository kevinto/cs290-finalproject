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

}

function addUserStock() {
  // Get form values
  var stockSymbol = document.getElementById('inputStockSym').value;
  var stockAmt = document.getElementById('inputStockAmount').value;

  // Create Return function
  var addStockReturnFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        // var resultObj = JSON.parse(request.responseText);

        // switch (resultObj.status) {
        //   case 'notLoggedIn':
        //     EnableNotLoggedInMode();
        //     break;
        //   case 'loggedIn':
        //     EnableLoggedInMode(resultObj.username); 

        //     generateUserStkTable();
        //     break;
        //   default:
        //     var signedOutHeader = document.getElementById('not-loggedin-intro');
        //     signedOutHeader.innerText = 'Something is wrong at the server. Please try again later.';
        //     break;
        // }
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