function determineLoginOrReg() {
  // Hide the registration form
  var regForm = document.getElementById('form-registration');
  regForm.style.display = 'none';

  // Hide the 
}

function showRegistration() {
  var regForm = document.getElementById('form-registration');
  regForm.style.display = 'inline';

  var signInForm = document.getElementById('form-signin');
  signInForm.style.display = 'none';
}

function showSignIn() {
  var regForm = document.getElementById('form-registration');
  regForm.style.display = 'none';

  var signInForm = document.getElementById('form-signin');
  signInForm.style.display = 'inline';
}

function validateSignOn() {

  // Get form values 
  var username = document.getElementById('inputUsername').value;
  var password = document.getElementById('inputPassword').value;

  // Create Return function
  var signOnReturnFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var errContainer = document.getElementById('signOnErrMessages');

        switch (request.responseText) {
          case 'emptyParams':
            errContainer.innerText = 'Please fill out all the values and re-submit.';
            break;
          case 'authenFailed':
            clearSignOnInfo();
            errContainer.innerText = 'Username or password is incorrect. Please try again.';
            break;
          case 'loginSuccessful':
            alert('Sign-In Successful.');
            location.reload();
            break;
          default:
            errContainer.innerText = 'Please retry or call an administrator. There was an error at the server.';
            break;
        }
      }
    }
  };

  // Create Php parameters
  var userParams = {
    validateSignOn: true,
    username: username,
    password: password,
  };

  // callLoginPhp('validateSignOn', signOnReturnFunc, userParams);
  callLoginPhp(signOnReturnFunc, userParams);

  return false;
}

function clearSignOnInfo() {
  var username = document.getElementById('inputUsername').value = '';
  var password = document.getElementById('inputPassword').value = '';
}

function registerUser() {

  // Get form values
  var username = document.getElementById('regUsername').value;
  var password = document.getElementById('regPassword').value;
  var passwordRepeated = document.getElementById('regRepeatPassword').value;
  var email = document.getElementById('regEmail').value;
  var emailRepeated = document.getElementById('regRepeatEmail').value;
  var birthday = document.getElementById('birthday').value;

  // Create Return function
  var regUserFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var errContainer = document.getElementById('regErrMessages');

        switch (request.responseText) {
          case 'emptyParams':
            errContainer.innerText = 'Please fill out all the values and re-submit.';
            break;
          case 'passwordsNotMatching':
            clearRegPasswords();
            errContainer.innerText = 'Please re-enter your passwords. They do not match.';
            break;
          case 'emailsNotMatching':
            clearRegEmails();
            errContainer.innerText = 'Please re-enter your emails. They do not match.';
            break;
          case 'usernameDuplicate':
            clearUsername();
            errContainer.innerText = 'Username already exists. Please enter another username.';
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

  // callLoginPhp('registerUser', regUserFunc, userRegParams);
  callLoginPhp(regUserFunc, userRegParams);

  return false;
}

function clearRegPasswords() {
  document.getElementById('regPassword').value = '';
  document.getElementById('regRepeatPassword').value = '';
}

function clearRegEmails() {
  document.getElementById('regEmail').value = '';
  document.getElementById('regRepeatEmail').value = '';
}

function clearUsername() {
  document.getElementById('regUsername').value = '';
}

/*
* Calls the backend PHP code
* @param {string} phpFuncName - action you want the backend PHP
*                                                       to perform
* @param {object} returnFunc - function that is executed after PHP
*                                                  function is done executing
* @param {object} optionalParams - optional params you want to pass
*                                                         to the PHP backend
*/
// Here optional parameters is supposed to be an array
// function callLoginPhp(phpFuncName, returnFunc, optionalParams) {
function callLoginPhp(returnFunc, postParams) {
  if (typeof(postParams) === 'undefined') {
    postParams = '';
  }

  var request = new XMLHttpRequest();
  // var url = 'login.php?' + phpFuncName + '=true';
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

  if (!request){
    return false;
  }

  request.onreadystatechange = returnFunc(request);
  request.open('POST', url, true);
  request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  request.send(postParamsStr);
  return request;
}
