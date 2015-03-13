/*
* Hides the registration form
*/
function hideRegistration() {
  // Hide the registration form
  var regForm = document.getElementById('form-registration');
  regForm.style.display = 'none';
}

/*
* Shows the registration form
*/
function showRegistration() {
  var regForm = document.getElementById('form-registration');
  regForm.style.display = 'inline';

  var signInForm = document.getElementById('form-signin');
  signInForm.style.display = 'none';
}

/*
* Shows the sign-in form
*/
function showSignIn() {
  var regForm = document.getElementById('form-registration');
  regForm.style.display = 'none';

  var signInForm = document.getElementById('form-signin');
  signInForm.style.display = 'inline';
}

/*
* Validates if sign-in information is correct
*/
function validateSignOn() {

  // Get form values
  var username = document.getElementById('inputUsername').value;
  var password = document.getElementById('inputPassword').value;

  // Create Return function
  var signOnReturnFunc = function(request) {
    return function() {
      if (request.readyState == 4) {
        var errContainer = document.getElementById('signOnErrMessages');

        switch (request.responseText) {
          case 'emptyParams':
            errContainer.innerText = 'Please fill out all the values ' +
              ' and re-submit.';
            break;
          case 'authenFailed':
            clearSignOnInfo();
            errContainer.innerText = 'Username or password is ' +
              'incorrect. Please try again.';
            break;
          case 'loginSuccessful':
            location.replace('app.html');
            break;
          default:
            errContainer.innerText = 'Please retry or call an ' +
              'administrator. There was an error at the server.';
            break;
        }
      }
    }
  };

  // Create Php parameters
  var userParams = {
    validateSignOn: true,
    username: username,
    password: password
  };

  // callLoginPhp('validateSignOn', signOnReturnFunc, userParams);
  callLoginPhp(signOnReturnFunc, userParams);

  return false;
}

/*
* Clears the sign-in form
*/
function clearSignOnInfo() {
  var username = document.getElementById('inputUsername').value = '';
  var password = document.getElementById('inputPassword').value = '';
}

/*
* Attempts to register the user
*/
function registerUser() {

  // Get form values
  var username = document.getElementById('regUsername').value;
  var password = document.getElementById('regPassword').value;
  var passwordRepeated = document.getElementById('regRepeatPassword').value;
  var email = document.getElementById('regEmail').value;
  var emailRepeated = document.getElementById('regRepeatEmail').value;
  var birthday = document.getElementById('birthday').value;

  // Create Return function
  var regUserFunc = function(request) {
    return function() {
      if (request.readyState == 4) {
        var errContainer = document.getElementById('regErrMessages');

        switch (request.responseText) {
          case 'emptyParams':
            errContainer.innerText = 'Please fill out all ' +
              'the values and re-submit.';
            break;
          case 'passwordsNotMatching':
            clearRegPasswords();
            errContainer.innerText = 'Please re-enter your ' +
              'passwords. They do not match.';
            break;
          case 'emailsNotMatching':
            clearRegEmails();
            errContainer.innerText = 'Please re-enter your emails.' +
              ' They do not match.';
            break;
          case 'usernameDuplicate':
            clearUsername();
            errContainer.innerText = 'Username already exists. ' +
              'Please enter another username.';
            break;
          default:
            alert('Registration Successful. Please Login.');
            location.reload();
            break;
        }
      }
    }
  };

  // Create Php parameters
  var userRegParams = {
    registerUser: true,
    username: username,
    password: password,
    passwordRepeated: passwordRepeated,
    email: email,
    emailRepeated: emailRepeated,
    birthday: birthday
  };

  callLoginPhp(regUserFunc, userRegParams);

  return false;
}

/*
* Clears the registration passwords
*/
function clearRegPasswords() {
  document.getElementById('regPassword').value = '';
  document.getElementById('regRepeatPassword').value = '';
}

/*
* Clears the registration emails
*/
function clearRegEmails() {
  document.getElementById('regEmail').value = '';
  document.getElementById('regRepeatEmail').value = '';
}

/*
* Clears the user name
*/
function clearUsername() {
  document.getElementById('regUsername').value = '';
}

/*
* Calls the backend PHP code
* @param {object} returnFunc - function that is executed after PHP
*                              backend is done executing
* @param {object} postParams - params you want to pass to the PHP backend
*/
// Here optional parameters is supposed to be an array
// function callLoginPhp(phpFuncName, returnFunc, optionalParams) {
function callLoginPhp(returnFunc, postParams) {
  if (typeof(postParams) === 'undefined') {
    postParams = '';
  }

  var request = new XMLHttpRequest();
  var url = 'login.php';
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

  if (!request) {
    return false;
  }

  request.onreadystatechange = returnFunc(request);
  request.open('POST', url, true);
  request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  request.send(postParamsStr);
  return request;
}

/*
* Logs out the current signed in user
*/
function logout() {

  // Create Return function
  var signOffReturnFunc = function(request) {
    return function() {
      if (request.readyState == 4) {
        location.replace('index.html');
      }
    }
  };

  // Create Php parameters
  var userParams = {
    logoff: true
  };

  callLoginPhp(signOffReturnFunc, userParams);

  return false;
}

/*
* Redirects to the login page
*/
function goToLogin() {
  location.replace('login.html');
}
