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
  { regex: /[A-Z]/, index: 3 }, // At least one uppercase letter
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

// Toggle visibility for password fields
eyeIcons.forEach((icon) => {
  icon.addEventListener("click", (e) => {
    const targetInput =
      e.target.id === "eye-password" ? passwordInput : cpasswordInput;
    const inputType = targetInput.type === "password" ? "text" : "password";
    targetInput.type = inputType;
    e.target.className =
      inputType === "password" ? "fa fa-eye" : "fa fa-eye-slash";
  });
});
