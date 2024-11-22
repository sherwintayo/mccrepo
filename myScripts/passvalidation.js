const passwordInput = document.querySelector(".pass-field input");
const eyeIcon = document.querySelector(".pass-field i");
const requirementList = document.querySelectorAll(".requirement-list li");
const contentContainer = document.querySelector(".content");
// An array of password requirements with corresponding
// regular expressions and index of the requirement list item
// An array of password requirements with corresponding
// regular expressions and index of the requirement list item
const requirements = [
  { regex: /.{8,}/, index: 0 }, // Minimum of 8 characters
  { regex: /[0-9]/, index: 1 }, // At least one number
  { regex: /[a-z]/, index: 2 }, // At least one lowercase letter
  { regex: /[^A-Za-z0-9]/, index: 3 }, // At least one special character
  { regex: /[A-Z]/, index: 4 }, // At least one uppercase letter
];

passwordInput.addEventListener("keyup", (e) => {
  // Show the content container when the user starts typing
  if (e.target.value.length > 0) {
    contentContainer.classList.remove("hidden");
  } else {
    contentContainer.classList.add("hidden");
  }
  // Validate the password against requirements
  requirements.forEach((item) => {
    // Check if the password matches the requirement regex
    const isValid = item.regex.test(e.target.value);
    const requirementItem = requirementList[item.index];
    // Update the class and icon of requirement item if requirement matched or not
    if (isValid) {
      requirementItem.classList.add("valid");
      requirementItem.firstElementChild.className = "fa-solid fa-check";
    } else {
      requirementItem.classList.remove("valid");
      requirementItem.firstElementChild.className = "fa-solid fa-circle";
    }
  });
});
