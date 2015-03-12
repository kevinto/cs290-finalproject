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
}

function generateUserStkTable() {

}