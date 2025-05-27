import "./bootstrap.js";
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.css";

// Ensure all JavaScript functionality is properly initialized
document.addEventListener("DOMContentLoaded", function () {
  console.log("App.js: DOM Content Loaded");

  // Initialize any global app functionality here
  initializeApp();
});

// Handle Turbo navigation
document.addEventListener("turbo:load", function () {
  console.log("App.js: Turbo Load");

  // Re-initialize app functionality on Turbo navigation
  initializeApp();
});

// Fallback for regular page loads
window.addEventListener("load", function () {
  console.log("App.js: Window Load");

  // Ensure app is initialized even if other events didn't fire
  if (!window.appInitialized) {
    initializeApp();
  }
});

function initializeApp() {
  // Mark app as initialized
  window.appInitialized = true;

  // Add any global app initialization logic here
  console.log("App initialized successfully");
}
