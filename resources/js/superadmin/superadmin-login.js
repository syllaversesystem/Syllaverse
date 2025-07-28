// -----------------------------------------------------------------------------
// File: resources/js/superadmin-login.js
// Description: Handles password toggle and login animation for Super Admin login – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Initial version – extracted inline script from Blade and added Vite compatibility.
// [2025-07-28] Switched from CDN to Vite-based feather-icons and Bootstrap imports.
// -----------------------------------------------------------------------------

import feather from 'feather-icons';
import 'bootstrap';

// START: Toggle Password Visibility
function togglePassword() {
  const passwordInput = document.getElementById("password");
  const icon = document.querySelector(".toggle-password");
  const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
  passwordInput.setAttribute("type", type);
  icon.setAttribute("data-feather", type === "password" ? "eye" : "eye-off");
  feather.replace();
}
// END: Toggle Password Visibility

// START: Login Button Loading Dots
function handleLoginSubmit(form) {
  const button = form.querySelector("#loginBtn");
  const text = button.querySelector("#loginText");
  button.disabled = true;
  text.textContent = "Logging in";
  text.classList.add("loading-dots");
}
// END: Login Button Loading Dots

// START: Init on Load
document.addEventListener("DOMContentLoaded", () => {
  feather.replace();

  const form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", (e) => handleLoginSubmit(form));
  }

  const toggleBtn = document.querySelector(".toggle-password");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", togglePassword);
  }
});
// END: Init on Load
