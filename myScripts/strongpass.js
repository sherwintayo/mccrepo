function updatePasswordStrength(password) {
  const validationContainer = document.getElementById("password-validation"); // Validation list
  const strengthContainer = document.getElementById(
    "password-strength-container"
  );
  const strengthBar = document.getElementById("password-strength-bar");
  const strengthText = document.getElementById("password-strength-text");

  // Real-time validation elements
  const minLength = document.getElementById("min-length");
  const uppercase = document.getElementById("uppercase");
  const lowercase = document.getElementById("lowercase");
  const number = document.getElementById("number");
  const specialChar = document.getElementById("special-char");

  // Show validation hints and strength bar if user starts typing
  if (password.length > 0) {
    validationContainer.classList.add("show");
    strengthContainer.style.display = "block";
  } else {
    validationContainer.classList.remove("show");
    strengthContainer.style.display = "none";
    return; // Exit early if password is empty
  }

  // Validation checks
  const hasMinLength = password.length >= 8;
  const hasUpperCase = /[A-Z]/.test(password);
  const hasLowerCase = /[a-z]/.test(password);
  const hasNumbers = /\d/.test(password);
  const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);

  // Update validation hints dynamically
  minLength.textContent = hasMinLength
    ? "✅ At least 8 characters"
    : "❌ At least 8 characters";
  minLength.className = hasMinLength ? "valid" : "invalid";

  uppercase.textContent = hasUpperCase
    ? "✅ At least one uppercase letter"
    : "❌ At least one uppercase letter";
  uppercase.className = hasUpperCase ? "valid" : "invalid";

  lowercase.textContent = hasLowerCase
    ? "✅ At least one lowercase letter"
    : "❌ At least one lowercase letter";
  lowercase.className = hasLowerCase ? "valid" : "invalid";

  number.textContent = hasNumbers
    ? "✅ At least one number"
    : "❌ At least one number";
  number.className = hasNumbers ? "valid" : "invalid";

  specialChar.textContent = hasSpecialChars
    ? "✅ At least one special character"
    : "❌ At least one special character";
  specialChar.className = hasSpecialChars ? "valid" : "invalid";

  // Calculate strength
  let strength = 0;
  if (hasMinLength) strength++;
  if (hasUpperCase) strength++;
  if (hasLowerCase) strength++;
  if (hasNumbers) strength++;
  if (hasSpecialChars) strength++;

  // Update strength bar dynamically
  let strengthClass = "";
  let strengthMessage = "";
  switch (strength) {
    case 1:
      strengthClass = "weak";
      strengthMessage = "Weak";
      strengthBar.style.width = "25%";
      break;
    case 2:
      strengthClass = "moderate";
      strengthMessage = "Moderate";
      strengthBar.style.width = "50%";
      break;
    case 3:
      strengthClass = "good";
      strengthMessage = "Good";
      strengthBar.style.width = "75%";
      break;
    case 4:
    case 5:
      strengthClass = "strong";
      strengthMessage = "Strong";
      strengthBar.style.width = "100%";
      break;
    default:
      strengthClass = "weak";
      strengthMessage = "Too weak";
      strengthBar.style.width = "0%";
  }

  strengthBar.className = `progress-bar ${strengthClass}`;
  strengthText.textContent = strengthMessage;
}

// Event listeners for password fields
document.getElementById("password").addEventListener("input", function () {
  const password = this.value;
  updatePasswordStrength(password);
});

// Confirm Password validation for additional feedback
document.getElementById("cpassword").addEventListener("input", function () {
  const confirmPassword = this.value;
  const password = document.getElementById("password").value;

  const validationContainer = document.getElementById("password-validation");

  // Show validation hints if "Confirm Password" is active and password isn't empty
  if (confirmPassword.length > 0 && password.length > 0) {
    validationContainer.classList.add("show");
  }

  // Check for password match
  if (confirmPassword !== password) {
    this.classList.add("is-invalid");
  } else {
    this.classList.remove("is-invalid");
  }
});
