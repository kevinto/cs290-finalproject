function checkIfSignedInForAppPage() {
  // Create Return function
  var signOnReturnFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var resultObj = JSON.parse(request.responseText);

        switch (resultObj.status) {
          case 'notLoggedIn':
            var signedOutHeader = document.getElementById('not-loggedin-intro');
            signedOutHeader.innerText = 'You are not currently signed in. Please click the sign in button on the top of the page.';

            var logoutBtn = document.getElementById('logout-button');
            logoutBtn.style.display = 'none';
            break;
          case 'loggedIn':
            // container.innerHTML = '<button class="btn btn-lg btn-primary btn-block" type="button" onclick="continueToApp();">It looks like you are logged in. Click this button to continue to the app.</button>' +
                                  // '<button class="btn btn-lg btn-primary btn-block" type="button" onclick="logout();">Logout</button>';
            break;
          default:
            // container.innerText = 'It seems like the server is not responding. Please try again later.';
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