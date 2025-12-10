/**
 * File: script.js
 * Project Management Website Script handles client-side form validation and 
 * modal display to enhance user experience while using the website.
 */

window.onload = () => console.log("Script loaded!");

/* ----- GLOBAL REFERENCES ----- */

// Registration
const registrationForm = document.getElementById("registration-form");
const reviewDetailsBtnReg = document.getElementById("reviewDetailsBtn-reg");
const confirmSubmitBtnReg = document.getElementById("confirmSubmitBtn-reg");

// Change Password
const changePasswordForm = document.getElementById("change-password-form");

// Add Project
const addProjectForm = document.getElementById("add-project-form");
const reviewDetailsBtnAdd = document.getElementById("reviewDetailsBtn-add");
const confirmSubmitBtnAdd = document.getElementById("confirmSubmitBtn-add");

// Edit Project
const editProjectForm = document.getElementById("edit-project-form");
const reviewDetailsBtnEdit = document.getElementById("reviewDetailsBtn-edit");
const confirmSubmitBtnEdit = document.getElementById("confirmSubmitBtn-edit");

/* ----- INPUT FIELDS ----- */
// Registration form fields
const firstName = document.getElementById("firstname");
const lastName = document.getElementById("lastname");
const email = document.getElementById("email");
const username = document.getElementById("username");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");

// Change password form fields
const currentPassword = document.getElementById("current_password");
const newPassword = document.getElementById("new_password");
const confirmNewPassword = document.getElementById("confirm_new_password");

// Add project form fields
const titleAdd = document.getElementById("title-add");
const shortDescriptionAdd = document.getElementById("short_description-add");
const startDateAdd = document.getElementById("start_date-add");
const endDateAdd = document.getElementById("end_date-add");
const phaseAdd = document.getElementById("phase-add");

// Edit project form fields
const titleEdit = document.getElementById("title-edit");
const shortDescriptionEdit = document.getElementById("short_description-edit");
const startDateEdit = document.getElementById("start_date-edit");
const endDateEdit = document.getElementById("end_date-edit");
const phaseEdit = document.getElementById("phase-edit");

/* ----- Validation Helpers ------ */
/**
 * Marks a form field as invalid and displays the provided message.
 * 
 * @param {HTMLElement} element - The input element to mark as invalid.
 * @param {string} message - The error message to display.
 * @returns 
 */
const setInvalid = (element, message) => {
  if (!element) return; // skip if field doesn't exist
  let container = element.parentElement;
  let feedback = container.querySelector(".invalid-feedback");

  if (feedback) {
    feedback.textContent = message;
    feedback.style.display = "block";
  }

  element.classList.add("is-invalid");
  element.classList.remove("is-valid");
};

/**
 * Marks a form field as valid and clears any error messages.
 * @param {HTMLElement} element - The input element to mark as valid.
 * @returns {void}
 */
const setValid = (element) => {
  if (!element) return;
  let container = element.parentElement;
  let feedback = container.querySelector(".invalid-feedback");

  if (feedback) {
    feedback.textContent = "";
    feedback.style.display = "none";
  }

  element.classList.remove("is-invalid");
  element.classList.add("is-valid");
};

const isValidPassword = (pw) => // Minimum eight characters, at least one uppercase letter, one lowercase letter, one number and one special character
  /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(
    pw
  );

const isValidEmail = (email) => // Simple email regex validation
  /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,}$/.test(email);

/* ----- REGISTRATION VALIDATION ----- */

/**
 * Validates the registration form fields.
 * @returns {boolean} - True if the form is valid, false otherwise.
 */
const validateRegistration = () => {
  if (!registrationForm) return true;

  let valid = true;

  if (firstName.value.trim() === "") {
    setInvalid(firstName, "Please enter your first name.");
    valid = false;
  } else setValid(firstName);

  if (lastName.value.trim() === "") {
    setInvalid(lastName, "Please enter your last name.");
    valid = false;
  } else setValid(lastName);

  if (!isValidEmail(email.value.trim())) {
    setInvalid(email, "Please enter a valid email.");
    valid = false;
  } else setValid(email);

  if (username.value.trim() === "") {
    setInvalid(username, "Username is required.");
    valid = false;
  } else setValid(username);

  if (!isValidPassword(password.value.trim())) {
    setInvalid(password, "Password does not meet requirements.");
    valid = false;
  } else setValid(password);

  if (confirmPassword.value.trim() !== password.value.trim()) {
    setInvalid(confirmPassword, "Passwords do not match.");
    valid = false;
  } else setValid(confirmPassword);

  return valid;
};

/* ----- CHANGE PASSWORD VALIDATION ----- */
/**
 * Validates the change password form fields (client-side only).
 * @returns {boolean} - True if the form is valid, false otherwise.
 */
const validateChangePassword = () => {
  if (!changePasswordForm) return true; 

  let valid = true;

  if (currentPassword.value.trim() === "") {
    setInvalid(currentPassword, "Please enter your current password.");
    valid = false;
  } else setValid(currentPassword);

  if (!isValidPassword(newPassword.value.trim())) {
    setInvalid(newPassword, "Password must be 8+ characters including upper case, lower case, number, and special character.");
    valid = false;
  } else setValid(newPassword);

  if (confirmNewPassword.value.trim() === "") {
    setInvalid(confirmNewPassword, "Please confirm your new password.");
    valid = false;
  } else if (confirmNewPassword.value.trim() !== newPassword.value.trim()) {
    setInvalid(confirmNewPassword, "Passwords do not match.");
    valid = false;
  } else setValid(confirmNewPassword);

  return valid;
};

/* ----- ADD PROJECT VALIDATION ----- */
/**
 * Validates the add project form fields.
 * @returns {boolean} - True if the form is valid, false otherwise.
 */
const validateAddProject = () => {
  if (!addProjectForm) return true;

  let valid = true;

  if (titleAdd.value.trim() === "") {
    setInvalid(titleAdd, "Please enter a project title.");
    valid = false;
  } else setValid(titleAdd);

  if (shortDescriptionAdd.value.trim() === "") {
    setInvalid(shortDescriptionAdd, "Please enter a description.");
    valid = false;
  } else setValid(shortDescriptionAdd);

  if (startDateAdd.value.trim() === "") {
    setInvalid(startDateAdd, "Please select a start date.");
    valid = false;
  } else setValid(startDateAdd);

  if (endDateAdd.value.trim() !== "" && endDateAdd.value < startDateAdd.value) {
    setInvalid(endDateAdd, "End date cannot be earlier than start date.");
    valid = false;
  } else setValid(endDateAdd);

  return valid;
};

/* ----- EDIT PROJECT VALIDATION ----- */
/**
 * Validates the edit project form fields.
 * @returns {boolean} - True if the form is valid, false otherwise.
 */
const validateEditProject = () => {
  if (!editProjectForm) return true;

  let valid = true;

  if (titleEdit.value.trim() === "") {
    setInvalid(titleEdit, "Please enter a project title.");
    valid = false;
  } else setValid(titleEdit);

  if (shortDescriptionEdit.value.trim() === "") {
    setInvalid(shortDescriptionEdit, "Please enter a description.");
    valid = false;
  } else setValid(shortDescriptionEdit);

  if (startDateEdit.value.trim() === "") {
    setInvalid(startDateEdit, "Please select a start date.");
    valid = false;
  } else setValid(startDateEdit);

  if (
    endDateEdit.value.trim() !== "" &&
    endDateEdit.value < startDateEdit.value
  ) {
    setInvalid(endDateEdit, "End date cannot be earlier than start date.");
    valid = false;
  } else setValid(endDateEdit);

  return valid;
};

/* ----- REGISTRATION BUTTONS ----- */
/**
 * Handles the review details and confirm submission for the registration form.
 */
if (reviewDetailsBtnReg) {
  reviewDetailsBtnReg.addEventListener("click", (e) => {
    e.preventDefault();
    if (!validateRegistration()) return;

    document.getElementById("confirmFullName").textContent =
      firstName.value + " " + lastName.value;
    document.getElementById("confirmEmail").textContent = email.value;
    document.getElementById("confirmUsername").textContent = username.value;
    document.getElementById("confirmPassword").textContent = "*".repeat(
      password.value.length
    );

    $("#confirmationModal-reg").modal("show");
  });

  confirmSubmitBtnReg.addEventListener("click", () => {
    $("#confirmationModal-reg").modal("hide");
    registrationForm.requestSubmit();
  });
}

/* ----- CHANGE PASSWORD BUTTON -----*/
/**
 * Handles change password form submission with validation.
 */
if (changePasswordForm) {
  changePasswordForm.addEventListener("submit", (e) => {
    if (!validateChangePassword()) {
      e.preventDefault();
    }
  });
}

/* ----- ADD PROJECT BUTTONS ----- */
/**
 * Handles the review details and confirm submission for the add project form.
 */
if (reviewDetailsBtnAdd) {
  reviewDetailsBtnAdd.addEventListener("click", (e) => {
    e.preventDefault();
    if (!validateAddProject()) return;

    document.getElementById("confirmTitleAdd").textContent = titleAdd.value;
    document.getElementById("confirmShortDescriptionAdd").textContent =
      shortDescriptionAdd.value;
    document.getElementById("confirmStartDateAdd").textContent =
      startDateAdd.value;
    document.getElementById("confirmEndDateAdd").textContent = endDateAdd.value;
    document.getElementById("confirmPhaseAdd").textContent = phaseAdd.value;

    $("#confirmationModal-add").modal("show");
  });

  confirmSubmitBtnAdd.addEventListener("click", () => {
    $("#confirmationModal-add").modal("hide");
    addProjectForm.requestSubmit();
  });
}

/* ----- EDIT PROJECT BUTTONS ----- */
/**
 * Handles the review details and confirm submission for the edit project form.
 */
if (reviewDetailsBtnEdit) {
  reviewDetailsBtnEdit.addEventListener("click", (e) => {
    e.preventDefault();
    if (!validateEditProject()) return;

    document.getElementById("confirmTitleEdit").textContent = titleEdit.value;
    document.getElementById("confirmShortDescriptionEdit").textContent =
      shortDescriptionEdit.value;
    document.getElementById("confirmStartDateEdit").textContent =
      startDateEdit.value;
    document.getElementById("confirmEndDateEdit").textContent =
      endDateEdit.value;
    document.getElementById("confirmPhaseEdit").textContent = phaseEdit.value;

    $("#confirmationModal-edit").modal("show");
  });

  confirmSubmitBtnEdit.addEventListener("click", () => {
    $("#confirmationModal-edit").modal("hide");
    editProjectForm.requestSubmit();
  });
}

/* ----- PROJECT DELETE BUTTON ----- */
/**
 * Handles delete-button click events and links the selected delete URL
 * to the confirmation modal's Delete button.
 *
 * Stores the appropriate delete URL when a delete button is clicked,
 * then redirects the user when they confirm deletion.
 */

document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".deleteBtn");
  const confirmDeleteBtn = document.getElementById("confirmDelete");

  let deleteUrl = "";

  deleteButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      deleteUrl = btn.dataset.deleteUrl;
    });
  });

  confirmDeleteBtn.addEventListener("click", function () {
    if (deleteUrl !== "") {
      window.location.href = deleteUrl;
    }
  });
});
