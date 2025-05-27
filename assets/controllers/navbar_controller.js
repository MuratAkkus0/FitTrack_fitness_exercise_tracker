import { Controller } from "@hotwired/stimulus";

/*
 * Navbar controller for handling mobile menu and user dropdown functionality
 * This ensures JavaScript works reliably across page loads and navigation
 */
export default class extends Controller {
  static targets = [
    "mobileMenu",
    "mobileMenuButton",
    "userDropdown",
    "userMenuButton",
    "userMenuChevron",
  ];
  static values = { mobileMenuOpen: Boolean };

  connect() {
    console.log("Navbar controller connected");
    this.setupEventListeners();
  }

  disconnect() {
    console.log("Navbar controller disconnected");
    this.removeEventListeners();
  }

  setupEventListeners() {
    // Bind click outside handler
    this.clickOutsideHandler = this.handleClickOutside.bind(this);
    document.addEventListener("click", this.clickOutsideHandler);
  }

  removeEventListeners() {
    if (this.clickOutsideHandler) {
      document.removeEventListener("click", this.clickOutsideHandler);
    }
  }

  // Mobile menu toggle
  toggleMobileMenu() {
    if (!this.hasMobileMenuTarget || !this.hasMobileMenuButtonTarget) return;

    const isMenuHidden = this.mobileMenuTarget.classList.contains("hidden");

    if (isMenuHidden) {
      this.mobileMenuTarget.classList.remove("hidden");
      // Force a reflow
      void this.mobileMenuTarget.offsetWidth;
      this.mobileMenuTarget.classList.add("active");
    } else {
      this.mobileMenuTarget.classList.remove("active");
      setTimeout(() => {
        this.mobileMenuTarget.classList.add("hidden");
      }, 300);
    }

    // Toggle icon
    const icon = this.mobileMenuButtonTarget.querySelector("i");
    if (icon) {
      icon.classList.toggle("fa-bars");
      icon.classList.toggle("fa-times");
    }
    this.mobileMenuButtonTarget.classList.toggle("text-orange-400");

    // Update value
    this.mobileMenuOpenValue = !isMenuHidden;
  }

  // User menu toggle (for mobile)
  toggleUserMenu(event) {
    if (!this.hasUserDropdownTarget || !this.hasUserMenuButtonTarget) return;

    if (window.innerWidth < 768) {
      event.preventDefault();
      event.stopPropagation();

      const isVisible = this.userDropdownTarget.classList.contains("active");

      if (isVisible) {
        this.userDropdownTarget.classList.remove("active");
        if (this.hasUserMenuChevronTarget) {
          this.userMenuChevronTarget.classList.remove("rotate-180");
        }
      } else {
        this.userDropdownTarget.classList.add("active");
        if (this.hasUserMenuChevronTarget) {
          this.userMenuChevronTarget.classList.add("rotate-180");
        }
      }
    }
  }

  // Handle clicks outside of menus
  handleClickOutside(event) {
    // Close user dropdown if clicking outside
    if (this.hasUserDropdownTarget && this.hasUserMenuButtonTarget) {
      if (this.userDropdownTarget.classList.contains("active")) {
        if (
          !this.userMenuButtonTarget.contains(event.target) &&
          !this.userDropdownTarget.contains(event.target)
        ) {
          this.userDropdownTarget.classList.remove("active");
          if (this.hasUserMenuChevronTarget) {
            this.userMenuChevronTarget.classList.remove("rotate-180");
          }
        }
      }
    }

    // Close mobile menu if clicking outside
    if (this.hasMobileMenuTarget && this.hasMobileMenuButtonTarget) {
      if (
        !this.mobileMenuTarget.classList.contains("hidden") &&
        !this.mobileMenuButtonTarget.contains(event.target) &&
        !this.mobileMenuTarget.contains(event.target)
      ) {
        this.mobileMenuTarget.classList.remove("active");
        setTimeout(() => {
          this.mobileMenuTarget.classList.add("hidden");
        }, 300);

        // Reset icon
        const icon = this.mobileMenuButtonTarget.querySelector("i");
        if (icon) {
          icon.classList.remove("fa-times");
          icon.classList.add("fa-bars");
        }
        this.mobileMenuButtonTarget.classList.remove("text-orange-400");
        this.mobileMenuOpenValue = false;
      }
    }
  }
}
