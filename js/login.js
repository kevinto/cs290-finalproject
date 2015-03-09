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