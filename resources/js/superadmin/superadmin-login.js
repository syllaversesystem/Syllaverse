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

// START: Toggle Password Visibility (two-way)
function togglePassword() {
  const passwordInput = document.getElementById("password");
  const toggleBtn = document.querySelector(".toggle-password");
  if (!passwordInput || !toggleBtn) return;
  const isPassword = passwordInput.getAttribute("type") === "password";
  passwordInput.setAttribute("type", isPassword ? "text" : "password");
  // Render the correct SVG directly to avoid relying on data-feather after replacement
  const iconName = isPassword ? 'eye-off' : 'eye';
  toggleBtn.innerHTML = feather.icons[iconName]?.toSvg() || `<i data-feather="${iconName}"></i>`;
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
  const passwordInput = document.getElementById("password");
  const wrapper = document.querySelector('.password-wrapper');
  if (passwordInput && wrapper) {
    const updateHasValue = () => {
      if (passwordInput.value && passwordInput.value.length > 0) {
        wrapper.classList.add('has-value');
      } else {
        wrapper.classList.remove('has-value');
        // Ensure hidden state when empty
        if (passwordInput.getAttribute('type') !== 'password') {
          passwordInput.setAttribute('type', 'password');
        }
        // Reset icon to eye (hidden)
        const toggleBtn = document.querySelector('.toggle-password');
        if (toggleBtn) {
          toggleBtn.innerHTML = feather.icons['eye']?.toSvg() || `<i data-feather="eye"></i>`;
        }
      }
    };
    passwordInput.addEventListener('input', updateHasValue);
    passwordInput.addEventListener('change', updateHasValue);
    updateHasValue();
  }
  if (toggleBtn) {
    // Ensure initial icon is SVG
    toggleBtn.innerHTML = feather.icons['eye']?.toSvg() || `<i data-feather="eye"></i>`;
    toggleBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      togglePassword();
      // Keep focus on the password field for better UX
      if (passwordInput) passwordInput.focus();
    });
  }
});
// END: Init on Load
