import { Controller } from "@hotwired/stimulus";

/*
 * Exercise Detail Controller
 * Handles exercise detail page functionality including:
 * - Adding exercises to programs
 * - Favorite toggle
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
    "favoriteBtn",
  ];

  static values = {
    exerciseId: Number,
    programsApiUrl: String,
    addToProgramUrl: String,
    toggleFavoriteUrl: String,
  };

  connect() {
    console.log("🚀 Exercise Detail Controller connected");
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
    if (!this.hasAddToProgramModalTarget) return;

    this.addToProgramModalTarget.classList.remove("hidden");
    // Force a reflow
    void this.addToProgramModalTarget.offsetWidth;
    this.addToProgramModalTarget.classList.add("active");
    document.body.style.overflow = "hidden";
    this.loadUserPrograms();
  }

  closeAddToProgramModal() {
    console.log("🔒 Closing add to program modal...");
    if (!this.hasAddToProgramModalTarget) return;

    this.addToProgramModalTarget.classList.remove("active");
    setTimeout(() => {
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
        item.classList.remove("selected", "bg-blue-50", "border-blue-500");
      });
    this.programsListTarget
      .querySelectorAll(".program-radio")
      .forEach((radio) => {
        radio.checked = false;
      });

    // Select this item
    programItem.classList.add("selected", "bg-blue-50", "border-blue-500");
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

  // Toggle favorite
  async toggleFavorite() {
    console.log("❤️ Toggle favorite for exercise:", this.exerciseIdValue);

    if (!this.hasFavoriteBtnTarget) return;

    try {
      this.favoriteBtnTarget.disabled = true;
      const originalText = this.favoriteBtnTarget.innerHTML;
      this.favoriteBtnTarget.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';

      const response = await fetch(this.toggleFavoriteUrlValue, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const result = await response.json();

      if (result.success) {
        if (result.isFavorite) {
          this.favoriteBtnTarget.innerHTML =
            '<i class="fas fa-heart mr-2"></i>Favorite';
          this.favoriteBtnTarget.classList.remove(
            "bg-gray-100",
            "text-gray-700",
            "hover:bg-gray-200"
          );
          this.favoriteBtnTarget.classList.add(
            "bg-red-500",
            "text-white",
            "hover:bg-red-600"
          );
        } else {
          this.favoriteBtnTarget.innerHTML =
            '<i class="fas fa-heart mr-2"></i>Favorite';
          this.favoriteBtnTarget.classList.remove(
            "bg-red-500",
            "text-white",
            "hover:bg-red-600"
          );
          this.favoriteBtnTarget.classList.add(
            "bg-gray-100",
            "text-gray-700",
            "hover:bg-gray-200"
          );
        }
        this.showToast(result.message, "success");
      } else {
        this.showToast(
          result.message || "Favori durumu değiştirilemedi.",
          "error"
        );
        this.favoriteBtnTarget.innerHTML = originalText;
      }
    } catch (error) {
      console.error("❌ Favorite toggle error:", error);
      this.showToast("Bağlantı hatası oluştu. Lütfen tekrar deneyin.", "error");
      this.favoriteBtnTarget.innerHTML = originalText;
    } finally {
      this.favoriteBtnTarget.disabled = false;
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
}
