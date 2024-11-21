function toggleVisibility(inputId) {
  const inputField = document.getElementById(inputId);
  const icon = document.getElementById(`eye-${inputId}`);
  if (inputField.type === "password") {
    inputField.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    inputField.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}

// Validate password strength
document.getElementById("password").addEventListener("input", function () {
  const password = this.value;
  const strengthBar = document.getElementById("password-strength-bar");
  const strengthText = document.getElementById("password-strength-text");

  let strength = 0;
  let strengthClass = "";
  let strengthMessage = "";

  // Validate rules
  const hasUpperCase = /[A-Z]/.test(password);
  const hasLowerCase = /[a-z]/.test(password);
  const hasNumbers = /\d/.test(password);
  const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);
  const isLongEnough = password.length >= 8;

  // Calculate strength
  if (isLongEnough) strength++;
  if (hasUpperCase) strength++;
  if (hasLowerCase) strength++;
  if (hasNumbers) strength++;
  if (hasSpecialChars) strength++;

  // Determine strength bar class and message
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

  // Update strength bar class and text
  strengthBar.className = `progress-bar ${strengthClass}`;
  strengthText.textContent = strengthMessage;
});
