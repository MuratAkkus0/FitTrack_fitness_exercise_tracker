import { Controller } from "@hotwired/stimulus";

/*
 * Exercise Library Controller - Full Featured Version
 */
export default class extends Controller {
  static targets = [
    "addExerciseModal",
    "addExerciseModalContent",
    "addExerciseForm",
    "exerciseGrid",
    "searchInput",
    "muscleGroupButtons",
    "addToProgramModal",
    "addToProgramModalContent",
    "programsList",
    "confirmAddToProgramBtn",
  ];

  static values = {
    exercisesUrl: String,
    userProgramsUrl: String,
  };

  connect() {
    console.log("🚀 Exercise Library Controller connected - FULL VERSION");
    this.selectedProgramId = null;
    this.currentExerciseId = null;
    this.selectedMuscleGroup = "all";
    this.searchTimeout = null;
    this.currentPage = 1;
    this.isLoading = false;

    // Debug modal targets
    console.log("Modal targets available:", {
      addExerciseModal: this.hasAddExerciseModalTarget,
      addExerciseModalContent: this.hasAddExerciseModalContentTarget,
      addToProgramModal: this.hasAddToProgramModalTarget,
      addToProgramModalContent: this.hasAddToProgramModalContentTarget,
    });

    // Load exercises on page load
    this.loadExercises();
  }

  // Modal Management
  openAddExerciseModal() {
    console.log("🔥 Opening Add Exercise Modal");
    console.log("Modal target exists:", this.hasAddExerciseModalTarget);
    console.log(
      "Modal content target exists:",
      this.hasAddExerciseModalContentTarget
    );

    if (this.hasAddExerciseModalTarget) {
      console.log("Modal element:", this.addExerciseModalTarget);
      console.log("Modal content element:", this.addExerciseModalContentTarget);

      // Show modal immediately
      this.addExerciseModalTarget.style.display = "flex";
      this.addExerciseModalTarget.classList.remove("hidden");

      console.log("Modal display set to flex, hidden class removed");

      // Force reflow
      this.addExerciseModalTarget.offsetHeight;

      // Animate in
      this.addExerciseModalContentTarget.style.transform = "scale(1)";
      this.addExerciseModalContentTarget.style.opacity = "1";

      console.log("Modal animation applied");

      document.body.style.overflow = "hidden";

      // Reset form
      if (this.hasAddExerciseFormTarget) {
        this.addExerciseFormTarget.reset();
      }

      console.log("Modal should now be visible");
    } else {
      console.error("❌ Modal target not found!");
    }
  }

  closeAddExerciseModal() {
    console.log("🔒 Closing Add Exercise Modal");

    if (this.hasAddExerciseModalTarget) {
      // Animate out
      this.addExerciseModalContentTarget.style.transform = "scale(0.95)";
      this.addExerciseModalContentTarget.style.opacity = "0";

      setTimeout(() => {
        this.addExerciseModalTarget.style.display = "none";
        this.addExerciseModalTarget.classList.add("hidden");
        document.body.style.overflow = "";
      }, 300);
    }
  }

  openAddToProgramModal(event) {
    console.log("🔥 Opening Add to Program Modal");

    this.currentExerciseId = event.currentTarget.dataset.exerciseId;

    if (this.hasAddToProgramModalTarget) {
      // Show modal immediately
      this.addToProgramModalTarget.style.display = "flex";
      this.addToProgramModalTarget.classList.remove("hidden");

      // Force reflow
      this.addToProgramModalTarget.offsetHeight;

      // Animate in
      this.addToProgramModalContentTarget.style.transform = "scale(1)";
      this.addToProgramModalContentTarget.style.opacity = "1";

      document.body.style.overflow = "hidden";

      // Load user programs
      this.loadUserPrograms();
    }
  }

  closeAddToProgramModal() {
    console.log("🔒 Closing Add to Program Modal");

    if (this.hasAddToProgramModalTarget) {
      // Animate out
      this.addToProgramModalContentTarget.style.transform = "scale(0.95)";
      this.addToProgramModalContentTarget.style.opacity = "0";

      setTimeout(() => {
        this.addToProgramModalTarget.style.display = "none";
        this.addToProgramModalTarget.classList.add("hidden");
        document.body.style.overflow = "";
      }, 300);

      this.selectedProgramId = null;
      this.currentExerciseId = null;
    }
  }

  // Form Submission
  async submitForm(event) {
    event.preventDefault();
    console.log("📝 Submitting exercise form");

    const formData = new FormData(event.target);
    const data = {
      name: formData.get("exerciseName"),
      description: formData.get("exerciseDescription"),
      muscleGroup: formData.get("exerciseMuscleGroup"),
      imageUrl: formData.get("exerciseImageUrl"),
      videoUrl: formData.get("exerciseVideoUrl"),
      instructions: formData.get("exerciseInstructions"),
    };

    try {
      const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

      const response = await fetch(
        "/dashboard/exercise-library/create-custom",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
          },
          body: JSON.stringify(data),
        }
      );

      const result = await response.json();

      if (result.success) {
        this.showToast(result.message, "success");
        this.closeAddExerciseModal();
        // Reload exercises
        this.loadExercises();
      } else {
        this.showToast(
          result.message || "Exercise could not be created",
          "error"
        );
      }
    } catch (error) {
      console.error("Error creating exercise:", error);
      this.showToast("An error occurred while creating the exercise", "error");
    }
  }

  // Search and Filter
  search(event) {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
      this.currentPage = 1;
      this.loadExercises();
    }, 500);
  }

  filterByMuscleGroup(event) {
    const muscleGroup = event.currentTarget.dataset.muscleGroup;
    this.selectedMuscleGroup = muscleGroup;
    this.currentPage = 1;

    // Update button states
    this.muscleGroupButtonsTarget
      .querySelectorAll(".muscle-group-btn")
      .forEach((btn) => {
        btn.classList.remove("active", "bg-orange-500", "text-white");
        btn.classList.add("bg-gray-200", "text-gray-700");
      });

    event.currentTarget.classList.remove("bg-gray-200", "text-gray-700");
    event.currentTarget.classList.add("active", "bg-orange-500", "text-white");

    this.loadExercises();
  }

  // Data Loading
  async loadExercises() {
    if (this.isLoading) return;

    this.isLoading = true;
    console.log("📊 Loading exercises...");

    try {
      const params = new URLSearchParams({
        page: this.currentPage,
        limit: 20,
      });

      if (this.selectedMuscleGroup && this.selectedMuscleGroup !== "all") {
        params.append("muscle_group", this.selectedMuscleGroup);
      }

      if (this.hasSearchInputTarget && this.searchInputTarget.value.trim()) {
        params.append("search", this.searchInputTarget.value.trim());
      }

      const response = await fetch(`${this.exercisesUrlValue}?${params}`);
      const data = await response.json();

      if (data.success) {
        this.renderExercises(data.exercises);
      } else {
        this.showToast("Error loading exercises", "error");
      }
    } catch (error) {
      console.error("Error loading exercises:", error);
      this.showToast("Error loading exercises", "error");
    } finally {
      this.isLoading = false;
    }
  }

  async loadUserPrograms() {
    console.log("📊 Loading user programs...");

    try {
      const response = await fetch(this.userProgramsUrlValue);
      const data = await response.json();

      if (data.success) {
        this.renderPrograms(data.programs);
      } else {
        this.programsListTarget.innerHTML =
          '<div class="text-center text-red-500 py-4">Error loading programs</div>';
      }
    } catch (error) {
      console.error("Error loading programs:", error);
      this.programsListTarget.innerHTML =
        '<div class="text-center text-red-500 py-4">Error loading programs</div>';
    }
  }

  // Rendering
  renderExercises(exercises) {
    if (!this.hasExerciseGridTarget) return;

    if (exercises.length === 0) {
      this.exerciseGridTarget.innerHTML = `
        <div class="col-span-3 text-center py-12 text-gray-500">
          <i class="fas fa-dumbbell text-6xl text-gray-300 mb-4"></i>
          <p class="text-lg font-medium mb-2">No exercises found</p>
          <p class="mb-4">Try adjusting your search or filter criteria.</p>
        </div>
      `;
      return;
    }

    this.exerciseGridTarget.innerHTML = exercises
      .map(
        (exercise) => `
      <div class="exercise-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-all duration-300">
        ${
          exercise.imageUrl
            ? `
          <div class="h-48 bg-gray-200 overflow-hidden">
            <img src="${exercise.imageUrl}" alt="${exercise.name}" class="w-full h-full object-cover" 
                 onerror="this.parentElement.innerHTML='<div class=\\'flex items-center justify-center h-full text-gray-400\\'>No Image</div>'">
          </div>
        `
            : `
          <div class="h-48 bg-gradient-to-br from-indigo-100 to-orange-100 flex items-center justify-center">
            <i class="fas fa-dumbbell text-4xl text-gray-400"></i>
          </div>
        `
        }
        <div class="p-6">
          <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-semibold text-gray-800">${
              exercise.name
            }</h3>
            <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
              ${exercise.muscleGroupLabel || "Unknown"}
            </span>
          </div>
          <p class="text-gray-600 text-sm mb-4 line-clamp-2">${
            exercise.description
          }</p>
          ${
            exercise.videoUrl
              ? `
            <div class="mb-4">
              <a href="${exercise.videoUrl}" target="_blank" class="inline-flex items-center text-red-600 hover:text-red-700 text-sm">
                <i class="fab fa-youtube mr-1"></i>
                Watch Video
              </a>
            </div>
          `
              : ""
          }
          <div class="flex space-x-2">
            <a href="/dashboard/exercise-library/exercise/${
              exercise.id
            }" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-4 rounded-lg transition text-sm">
              <i class="fas fa-eye mr-1"></i>
              View
            </a>
            <button data-action="click->exercise-library#openAddToProgramModal" data-exercise-id="${
              exercise.id
            }" 
                    class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-3 rounded-lg transition text-sm">
              <i class="fas fa-plus"></i>
            </button>
            <button data-action="click->exercise-library#deleteExercise" data-exercise-id="${
              exercise.id
            }" data-exercise-name="${exercise.name}" 
                    class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg transition text-sm" title="Delete Exercise">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
    `
      )
      .join("");
  }

  renderPrograms(programs) {
    if (!this.hasProgramsListTarget) return;

    if (programs.length === 0) {
      this.programsListTarget.innerHTML =
        '<div class="text-center text-gray-500 py-4">No programs found</div>';
      return;
    }

    this.programsListTarget.innerHTML = programs
      .map(
        (program) => `
      <div class="program-item border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition-colors"
           data-action="click->exercise-library#selectProgram" data-program-id="${program.id}">
        <div class="flex justify-between items-center">
          <div>
            <h4 class="font-medium text-gray-800">${program.name}</h4>
            <p class="text-sm text-gray-600">${program.exerciseCount} exercises</p>
          </div>
          <div class="w-4 h-4 border-2 border-gray-300 rounded-full program-radio"></div>
        </div>
      </div>
    `
      )
      .join("");
  }

  // Program Selection
  selectProgram(event) {
    const programId = event.currentTarget.dataset.programId;
    this.selectedProgramId = programId;

    // Update visual selection
    this.programsListTarget
      .querySelectorAll(".program-item")
      .forEach((item) => {
        const radio = item.querySelector(".program-radio");
        radio.classList.remove("bg-orange-500", "border-orange-500");
        radio.classList.add("border-gray-300");
      });

    const selectedItem = event.currentTarget;
    const selectedRadio = selectedItem.querySelector(".program-radio");
    selectedRadio.classList.remove("border-gray-300");
    selectedRadio.classList.add("bg-orange-500", "border-orange-500");

    // Enable confirm button
    if (this.hasConfirmAddToProgramBtnTarget) {
      this.confirmAddToProgramBtnTarget.disabled = false;
    }
  }

  // Actions
  async confirmAddToProgram() {
    if (!this.selectedProgramId || !this.currentExerciseId) {
      this.showToast("Please select a program", "error");
      return;
    }

    try {
      const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

      const response = await fetch(
        `/dashboard/my-programs/${this.selectedProgramId}/add-exercise/${this.currentExerciseId}`,
        {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
          },
        }
      );

      const data = await response.json();

      if (data.success) {
        this.showToast(data.message, "success");
        this.closeAddToProgramModal();
      } else {
        this.showToast(
          data.message || "Could not add exercise to program",
          "error"
        );
      }
    } catch (error) {
      console.error("Error adding exercise to program:", error);
      this.showToast(
        "An error occurred while adding exercise to program",
        "error"
      );
    }
  }

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
      const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

      const response = await fetch(
        `/dashboard/exercise-library/delete/${exerciseId}`,
        {
          method: "DELETE",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
          },
        }
      );

      const data = await response.json();

      if (data.success) {
        this.showToast(data.message, "success");
        // Reload exercises
        this.loadExercises();
      } else {
        this.showToast(
          data.message || "Exercise could not be deleted",
          "error"
        );
      }
    } catch (error) {
      console.error("Error deleting exercise:", error);
      this.showToast("An error occurred while deleting the exercise", "error");
    }
  }

  // Utility Methods
  showToast(message, type = "info") {
    // Create toast element
    const toast = document.createElement("div");
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 translate-x-full ${
      type === "success"
        ? "bg-green-500"
        : type === "error"
        ? "bg-red-500"
        : type === "warning"
        ? "bg-yellow-500"
        : "bg-blue-500"
    }`;
    toast.textContent = message;

    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
      toast.classList.remove("translate-x-full");
    }, 100);

    // Remove after 3 seconds
    setTimeout(() => {
      toast.classList.add("translate-x-full");
      setTimeout(() => {
        document.body.removeChild(toast);
      }, 300);
    }, 3000);
  }
}
