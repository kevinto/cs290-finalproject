/*
* Checks if the user is signed in
*/
function checkIfSignedInForLandingPage() {
  // Create Return function
  var signOnReturnFunc = function(request) {
    return function() {
      if (request.readyState == 4) {
        var resultObj = JSON.parse(request.responseText);
        var container = document.getElementById('signInIndicator');

        switch (resultObj.status) {
          case 'notLoggedIn':
            container.innerHTML = '<button class="btn btn-lg btn-primary ' +
              'btn-block" type="button" onclick="goToLogin();">' +
              'Login or Register to continue to app.</button>';
            break;
          case 'loggedIn':
            container.innerHTML = '<button class="btn btn-lg btn-primary ' +
              'btn-block" type="button" onclick="continueToApp();">' +
              'It looks like you are logged in. Click this button to ' +
              ' continue to the app.' +
              '</button><button class="btn btn-lg btn-primary btn-block" ' +
              'type="button" onclick="logout();">Logout</button>';
            break;
          default:
            container.innerText = 'It seems like the server is not ' +
              'responding. Please try again later.';
            break;
        }
      }
    }
  };

  // Create Php parameters
  var userParams = {
    checkIfSignedIn: true
  };

  callLoginPhp(signOnReturnFunc, userParams);

  return false;
}

/*
* Navigates to the app page
*/
function continueToApp() {
  location.replace('app.html');
}
