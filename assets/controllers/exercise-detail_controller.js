import { Controller } from "@hotwired/stimulus";

/*
 * Exercise Detail Controller
 * Handles exercise detail page functionality including:
 * - Adding exercises to programs
 * - Modal management
 */
export default class extends Controller {
  static targets = [
    "addToProgramModal",
    "addToProgramModalContent",
    "addToProgramBtn",
    "closeAddToProgramModalBtn",
    "cancelAddToProgramBtn",
    "confirmAddToProgramBtn",
    "programsList",
  ];

  static values = {
    exerciseId: Number,
    programsApiUrl: String,
    addToProgramUrl: String,
  };

  connect() {
    console.log("🚀 Exercise Detail Controller connected");
    console.log("Available targets:", {
      addToProgramModal: this.hasAddToProgramModalTarget,
      addToProgramModalContent: this.hasAddToProgramModalContentTarget,
      addToProgramBtn: this.hasAddToProgramBtnTarget,
      programsList: this.hasProgramsListTarget,
      confirmAddToProgramBtn: this.hasConfirmAddToProgramBtnTarget,
    });
    console.log("Values:", {
      exerciseId: this.exerciseIdValue,
      programsApiUrl: this.programsApiUrlValue,
      addToProgramUrl: this.addToProgramUrlValue,
    });
    this.selectedProgramId = null;
    this.setupEventListeners();
  }

  disconnect() {
    this.removeEventListeners();
  }

  setupEventListeners() {
    // ESC key to close modal
    this.escapeHandler = this.handleEscape.bind(this);
    document.addEventListener("keydown", this.escapeHandler);
  }

  removeEventListeners() {
    if (this.escapeHandler) {
      document.removeEventListener("keydown", this.escapeHandler);
    }
  }

  handleEscape(event) {
    if (event.key === "Escape" && this.hasAddToProgramModalTarget) {
      if (this.addToProgramModalTarget.classList.contains("active")) {
        this.closeAddToProgramModal();
      }
    }
  }

  // Modal functions
  openAddToProgramModal() {
    console.log("🔓 Opening add to program modal...");
    console.log("hasAddToProgramModalTarget:", this.hasAddToProgramModalTarget);
    console.log("All targets check:", {
      modal: this.hasAddToProgramModalTarget,
      content: this.hasAddToProgramModalContentTarget,
      btn: this.hasAddToProgramBtnTarget,
      list: this.hasProgramsListTarget,
    });

    if (this.hasAddToProgramModalTarget) {
      console.log("✅ Modal target found!");
      console.log("Modal element:", this.addToProgramModalTarget);
    } else {
      console.error("❌ Modal target NOT found!");
      console.log(
        "Looking for element with selector: [data-exercise-detail-target='addToProgramModal']"
      );
      const manualFind = document.querySelector(
        "[data-exercise-detail-target='addToProgramModal']"
      );
      console.log("Manual search result:", manualFind);
      return;
    }

    console.log(
      "Modal classes before:",
      this.addToProgramModalTarget.className
    );

    // Show modal immediately
    this.addToProgramModalTarget.style.display = "flex";
    this.addToProgramModalTarget.classList.remove("hidden");
    console.log("✅ Set display to flex and removed hidden class");

    // Force reflow
    this.addToProgramModalTarget.offsetHeight;
    console.log("✅ Forced reflow");

    // Animate in
    if (this.hasAddToProgramModalContentTarget) {
      this.addToProgramModalContentTarget.style.transform = "scale(1)";
      this.addToProgramModalContentTarget.style.opacity = "1";
      console.log("✅ Applied animation styles");
    } else {
      console.error("❌ Modal content target not found!");
    }

    document.body.style.overflow = "hidden";
    console.log("✅ Set body overflow to hidden");

    this.loadUserPrograms();
    console.log("✅ Called loadUserPrograms");
  }

  closeAddToProgramModal() {
    console.log("🔒 Closing add to program modal...");
    if (!this.hasAddToProgramModalTarget) return;

    // Animate out
    if (this.hasAddToProgramModalContentTarget) {
      this.addToProgramModalContentTarget.style.transform = "scale(0.95)";
      this.addToProgramModalContentTarget.style.opacity = "0";
    }

    setTimeout(() => {
      this.addToProgramModalTarget.style.display = "none";
      this.addToProgramModalTarget.classList.add("hidden");
      document.body.style.overflow = "";
      this.selectedProgramId = null;
      if (this.hasConfirmAddToProgramBtnTarget) {
        this.confirmAddToProgramBtnTarget.disabled = true;
      }
    }, 300);
  }

  // Modal backdrop click
  modalBackdropClick(event) {
    if (
      event.target === this.addToProgramModalTarget ||
      event.target.classList.contains("fixed")
    ) {
      this.closeAddToProgramModal();
    }
  }

  // Load user programs
  async loadUserPrograms() {
    console.log("📚 Loading user programs...");
    if (!this.hasProgramsListTarget) return;

    try {
      const response = await fetch(this.programsApiUrlValue);
      const data = await response.json();

      if (data.success) {
        this.displayPrograms(data.programs || []);
      } else {
        this.programsListTarget.innerHTML = `
          <div class="text-center text-red-500 py-4">
            <p>${data.message || "Error loading programs"}</p>
          </div>
        `;
      }
    } catch (error) {
      console.error("❌ Error loading programs:", error);
      this.programsListTarget.innerHTML = `
        <div class="text-center text-red-500 py-4">
          <p>Error loading programs</p>
        </div>
      `;
    }
  }

  // Display programs
  displayPrograms(programs) {
    if (!programs || programs.length === 0) {
      this.programsListTarget.innerHTML = `
        <div class="text-center text-gray-500 py-4">
          <i class="fas fa-info-circle text-2xl mb-2"></i>
          <p>You don't have any programs yet.</p>
          <a href="/dashboard/my-programs" class="text-orange-500 hover:text-orange-700 text-sm">
            Create your first program
          </a>
        </div>
      `;
      return;
    }

    this.programsListTarget.innerHTML = programs
      .map(
        (program) => `
        <div class="program-item border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition-colors" 
             data-program-id="${program.id}"
             data-action="click->exercise-detail#selectProgram">
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <h4 class="font-medium text-gray-800">${program.name}</h4>
              <p class="text-sm text-gray-600 mt-1">${
                program.description || "No description"
              }</p>
              <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                <span><i class="fas fa-dumbbell mr-1"></i>${
                  program.exerciseCount || 0
                } exercises</span>
                ${
                  program.difficultyLevel
                    ? `<span><i class="fas fa-signal mr-1"></i>${program.difficultyLevel}</span>`
                    : ""
                }
              </div>
            </div>
            <div class="ml-3">
              <input type="radio" name="selectedProgram" value="${
                program.id
              }" class="program-radio">
            </div>
          </div>
        </div>
      `
      )
      .join("");
  }

  // Select program
  selectProgram(event) {
    const programItem = event.currentTarget;
    const programId = programItem.dataset.programId;

    // Remove selection from all items
    this.programsListTarget
      .querySelectorAll(".program-item")
      .forEach((item) => {
        item.classList.remove("selected", "bg-orange-50", "border-orange-500");
      });
    this.programsListTarget
      .querySelectorAll(".program-radio")
      .forEach((radio) => {
        radio.checked = false;
      });

    // Select this item
    programItem.classList.add("selected", "bg-orange-50", "border-orange-500");
    programItem.querySelector(".program-radio").checked = true;
    this.selectedProgramId = programId;

    if (this.hasConfirmAddToProgramBtnTarget) {
      this.confirmAddToProgramBtnTarget.disabled = false;
    }
  }

  // Add to program
  async addToProgram() {
    if (!this.selectedProgramId || !this.exerciseIdValue) return;

    console.log("➕ Adding exercise to program:", {
      exerciseId: this.exerciseIdValue,
      selectedProgramId: this.selectedProgramId,
    });

    try {
      if (this.hasConfirmAddToProgramBtnTarget) {
        this.confirmAddToProgramBtnTarget.disabled = true;
        this.confirmAddToProgramBtnTarget.textContent = "Adding...";
      }

      const url = this.addToProgramUrlValue
        .replace("__PROGRAM_ID__", this.selectedProgramId)
        .replace("__EXERCISE_ID__", this.exerciseIdValue);

      const response = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const result = await response.json();
      console.log("📨 Response:", result);

      if (result.success) {
        console.log("✅ Exercise added to program successfully");
        this.showToast(
          result.message || "Exercise added to program successfully!",
          "success"
        );
        this.closeAddToProgramModal();
      } else {
        console.error("❌ Add to program failed:", result.message);
        this.showToast(
          result.message || "Failed to add exercise to program.",
          "error"
        );
      }
    } catch (error) {
      console.error("❌ Network error:", error);
      this.showToast("Connection error occurred. Please try again.", "error");
    } finally {
      if (this.hasConfirmAddToProgramBtnTarget) {
        this.confirmAddToProgramBtnTarget.disabled = false;
        this.confirmAddToProgramBtnTarget.textContent = "Add to Program";
      }
    }
  }

  // Toast notification function
  showToast(message, type = "info") {
    const toast = document.createElement("div");
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 translate-x-full ${
      type === "success"
        ? "bg-green-500"
        : type === "error"
        ? "bg-red-500"
        : "bg-blue-500"
    }`;
    toast.innerHTML = `
      <div class="flex items-center">
        <i class="fas ${
          type === "success"
            ? "fa-check-circle"
            : type === "error"
            ? "fa-exclamation-circle"
            : "fa-info-circle"
        } mr-2"></i>
        <span>${message}</span>
      </div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.remove("translate-x-full");
    }, 100);

    setTimeout(() => {
      toast.classList.add("translate-x-full");
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    }, 3000);
  }

  // Delete Exercise
  async deleteExercise(event) {
    const exerciseId = event.currentTarget.dataset.exerciseId;
    const exerciseName =
      event.currentTarget.dataset.exerciseName || "This exercise";

    if (
      !confirm(
        `Are you sure you want to delete ${exerciseName}? This action cannot be undone.`
      )
    ) {
      return;
    }

    try {
      const response = await fetch(
        `/dashboard/exercise-library/delete/${exerciseId}`,
        {
          method: "DELETE",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
        }
      );

      const data = await response.json();

      if (data.success) {
        this.showToast(data.message, "success");
        // Redirect to exercise library after successful deletion
        setTimeout(() => {
          window.location.href = "/dashboard/exercise-library";
        }, 1500);
      } else {
        this.showToast(data.message || "Could not delete exercise", "error");
      }
    } catch (error) {
      console.error("Error deleting exercise:", error);
      this.showToast("An error occurred while deleting the exercise", "error");
    }
  }
}
