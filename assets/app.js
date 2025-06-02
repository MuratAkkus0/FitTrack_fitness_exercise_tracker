import "./bootstrap.js";
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.css";

// Global app state
let appInitialized = false;

// Initialize app functionality
function initializeApp() {
  // Prevent multiple initializations
  if (appInitialized) {
    return;
  }

  console.log("App initialized successfully");
  appInitialized = true;

  // Add any global app initialization logic here
  // This will only run once per page load
}

// Primary initialization on DOM ready
document.addEventListener("DOMContentLoaded", function () {
  console.log("App.js: DOM Content Loaded");
  initializeApp();
});

// Handle Turbo navigation - reset state for new pages
document.addEventListener("turbo:load", function () {
  console.log("App.js: Turbo Load");

  // Reset initialization state for new page
  appInitialized = false;
  initializeApp();
});

// Cleanup on page unload
document.addEventListener("turbo:before-cache", function () {
  console.log("App.js: Before Cache - Cleaning up");
  appInitialized = false;
});
