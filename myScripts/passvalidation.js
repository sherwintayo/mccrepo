const passwordInput = document.querySelector("#password");
const cpasswordInput = document.querySelector("#cpassword");
const eyeIcons = document.querySelectorAll(".toggle-password");
const requirementList = document.querySelectorAll(".requirement-list li");
const contentContainer = document.querySelector(".content");

// Password requirements
const requirements = [
  { regex: /.{8,}/, index: 0 }, // Minimum 8 characters
  { regex: /[0-9]/, index: 1 }, // At least one number
  { regex: /[a-z]/, index: 2 }, // At least one lowercase letter
  { regex: /[^A-Za-z0-9]/, index: 3 }, // At least one special character
  { regex: /[A-Z]/, index: 4 }, // At least one uppercase letter
];

// Password strength validation
passwordInput.addEventListener("input", (e) => {
  // Show the content container
  if (e.target.value.length > 0) {
    contentContainer.classList.remove("hidden");
  } else {
    contentContainer.classList.add("hidden");
  }

  // Validate the password
  requirements.forEach((req) => {
    const isValid = req.regex.test(e.target.value);
    const requirementItem = requirementList[req.index];
    if (isValid) {
      requirementItem.classList.add("valid");
      requirementItem.firstElementChild.className = "fa-solid fa-check";
    } else {
      requirementItem.classList.remove("valid");
      requirementItem.firstElementChild.className = "fa-solid fa-circle";
    }
  });
});

// Check if all password requirements are met
function isPasswordValid() {
  return requirements.every((req) => req.regex.test(passwordInput.value));
}
