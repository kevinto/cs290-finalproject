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
        var errContainer = document.getElementById('errorMessages');

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
          default:
            alert('Registration Successful.');
            break;
        }
        
        // location.reload();
      }
    }
  };

  // Create Php parameters
  var userRegParams = {
    username: username,
    password: password,
    passwordRepeated: passwordRepeated,
    email: email,
    emailRepeated: emailRepeated,
    birthday: birthday
  };

  callLoginPhp('registerUser', regUserFunc, userRegParams);

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
function callLoginPhp(phpFuncName, returnFunc, optionalParams) {
  if (typeof(optionalParams) === 'undefined') {
    optionalParams = '';
  }

  var request = new XMLHttpRequest();
  var url = 'login.php?' + phpFuncName + '=true';

  // Need to find a way to iterate through the properties of the JS
  if (optionalParams.length !== 0) {
    for (var property in optionalParams) {
      if (optionalParams.hasOwnProperty(property)) {
        url += '&' + property + '=' + optionalParams[property];
      }
    }
  }

  if (!request){
    return false;
  }

  request.onreadystatechange = returnFunc(request);
  request.open('GET', url, true);
  request.send(null);
  return request;
}
