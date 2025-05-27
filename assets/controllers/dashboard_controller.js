import { Controller } from "@hotwired/stimulus";

/*
 * Dashboard Controller
 * Handles dashboard template functionality including:
 * - Mobile sidebar toggle
 */
export default class extends Controller {
  static targets = [
    "mobileSidebarToggle",
    "mobileSidebarContent",
    "sidebarChevron",
  ];

  connect() {
    console.log("Dashboard Controller connected");
  }

  toggleMobileSidebar(event) {
    event.preventDefault();

    if (!this.hasMobileSidebarContentTarget || !this.hasSidebarChevronTarget)
      return;

    const isHidden =
      this.mobileSidebarContentTarget.classList.contains("hidden");

    if (isHidden) {
      this.mobileSidebarContentTarget.classList.remove("hidden");
      this.sidebarChevronTarget.classList.add("rotate-180");
    } else {
      this.mobileSidebarContentTarget.classList.add("hidden");
      this.sidebarChevronTarget.classList.remove("rotate-180");
    }
  }
}
