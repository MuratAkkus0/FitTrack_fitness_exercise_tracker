import { Controller } from "@hotwired/stimulus";

/*
 * Exercise Library Controller
 * Handles exercise search, filtering, modal operations, and adding exercises to programs
 */
export default class extends Controller {
  static targets = [
    "searchInput",
    "exerciseGrid",
    "muscleGroupButtons",
    "addExerciseModal",
    "addExerciseModalContent",
    "addExerciseForm",
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
    console.log("Exercise Library Controller connected");
    this.selectedProgramId = null;
    this.currentExerciseId = null;
    this.selectedMuscleGroup = "all";
    this.setupEventListeners();

    // Only load exercises via AJAX if they're not already rendered server-side
    if (
      this.hasExerciseGridTarget &&
      this.exerciseGridTarget.children.length === 0
    ) {
      console.log("No exercises found in DOM, loading via AJAX");
      this.loadExercises();
    } else {
      console.log("Exercises already present in DOM, skipping AJAX load");
    }
  }

  disconnect() {
    this.cleanupEventListeners();
  }

  setupEventListeners() {
    // Search input debouncing
    this.searchDebounceTimer = null;

    // Click outside modal handler
    this.clickOutsideHandler = this.handleClickOutside.bind(this);
    document.addEventListener("click", this.clickOutsideHandler);
  }

  cleanupEventListeners() {
    if (this.clickOutsideHandler) {
      document.removeEventListener("click", this.clickOutsideHandler);
    }
    if (this.searchDebounceTimer) {
      clearTimeout(this.searchDebounceTimer);
    }
  }

  handleClickOutside(event) {
    // Close modals when clicking outside
    if (
      this.hasAddExerciseModalTarget &&
      this.addExerciseModalTarget.classList.contains("active") &&
      !this.addExerciseModalContentTarget.contains(event.target)
    ) {
      this.closeAddExerciseModal();
    }

    if (
      this.hasAddToProgramModalTarget &&
      this.addToProgramModalTarget.classList.contains("active") &&
      !this.addToProgramModalContentTarget.contains(event.target)
    ) {
      this.closeAddToProgramModal();
    }
  }

  // Search functionality
  search(event) {
    const query = event.target.value.trim();

    if (this.searchDebounceTimer) {
      clearTimeout(this.searchDebounceTimer);
    }

    this.searchDebounceTimer = setTimeout(() => {
      this.filterExercises(query, this.selectedMuscleGroup);
    }, 300);
  }

  // Muscle group filtering
  filterByMuscleGroup(event) {
    const muscleGroup = event.currentTarget.dataset.muscleGroup;

    // Update active button
    this.muscleGroupButtonsTargets.forEach((btn) => {
      btn.classList.remove("active");
    });
    event.currentTarget.classList.add("active");

    this.selectedMuscleGroup = muscleGroup;
    const searchQuery = this.hasSearchInputTarget
      ? this.searchInputTarget.value.trim()
      : "";
    this.filterExercises(searchQuery, muscleGroup);
  }

  async filterExercises(searchQuery = "", muscleGroup = "all") {
    try {
      const url = new URL(this.exercisesUrlValue, window.location.origin);
      if (searchQuery) url.searchParams.set("search", searchQuery);
      if (muscleGroup !== "all")
        url.searchParams.set("muscle_group", muscleGroup);

      const response = await fetch(url, {
        method: "GET",
        credentials: "same-origin",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      });
      const data = await response.json();

      if (data.success) {
        this.displayExercises(data.exercises);
      } else {
        this.showError("Error loading exercises");
      }
    } catch (error) {
      console.error("Error filtering exercises:", error);
      this.showError("Error loading exercises");
    }
  }

  async loadExercises() {
    try {
      console.log("Loading exercises from:", this.exercisesUrlValue);
      const response = await fetch(this.exercisesUrlValue, {
        method: "GET",
        credentials: "same-origin",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      });

      console.log("Response status:", response.status);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      console.log("API Response:", data);

      if (data.success) {
        console.log(
          "Exercises loaded successfully:",
          data.exercises.length,
          "exercises"
        );
        this.displayExercises(data.exercises);
      } else {
        console.error("API returned success=false:", data);
        this.showError("Error loading exercises");
      }
    } catch (error) {
      console.error("Error loading exercises:", error);
      this.showError("Error loading exercises");
    }
  }

  displayExercises(exercises) {
    if (!this.hasExerciseGridTarget) return;

    if (!exercises || exercises.length === 0) {
      this.exerciseGridTarget.innerHTML = `
        <div class="col-span-full text-center text-gray-500 py-8">
          <i class="fas fa-search text-4xl mb-4"></i>
          <p>No exercises found</p>
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
          <img src="${exercise.imageUrl}" alt="${exercise.name}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<div class=\\'flex items-center justify-center h-full text-gray-400\\'>No Image</div>'">
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
              ${exercise.muscleGroupLabel || exercise.muscleGroup}
            </span>
          </div>
          <p class="text-gray-600 text-sm mb-4 line-clamp-2">
            ${exercise.description || "No description available"}
          </p>
          ${
            exercise.videoUrl
              ? `
          <div class="mb-4">
            <a href="${exercise.videoUrl}" target="_blank" class="inline-flex items-center text-red-600 hover:text-red-700 text-sm">
              <i class="fab fa-youtube mr-1"></i> Watch Video
            </a>
          </div>
          `
              : ""
          }
          <div class="flex space-x-2">
            <a href="/dashboard/exercise-library/${exercise.id}" 
               class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-4 rounded-lg transition text-sm">
              <i class="fas fa-eye mr-1"></i> View
            </a>
            <button data-action="click->exercise-library#toggleFavorite" 
                    data-exercise-id="${exercise.id}"
                    class="favorite-btn ${
                      exercise.isFavorite
                        ? "bg-red-500 hover:bg-red-600 text-white"
                        : "bg-gray-200 hover:bg-gray-300 text-gray-600"
                    } py-2 px-3 rounded-lg transition text-sm">
              <i class="fas fa-heart"></i>
            </button>
            <button data-action="click->exercise-library#openAddToProgramModal" 
                    data-exercise-id="${exercise.id}"
                    class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg transition text-sm">
              <i class="fas fa-plus mr-1"></i> Add
            </button>
          </div>
        </div>
      </div>
    `
      )
      .join("");
  }

  showError(message) {
    if (this.hasExerciseGridTarget) {
      this.exerciseGridTarget.innerHTML = `
        <div class="col-span-full text-center text-red-500 py-8">
          <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
          <p>${message}</p>
        </div>
      `;
    }
  }

  // Add Exercise Modal
  openAddExerciseModal() {
    if (!this.hasAddExerciseModalTarget) return;

    this.addExerciseModalTarget.classList.remove("hidden");
    void this.addExerciseModalTarget.offsetWidth;
    this.addExerciseModalTarget.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  closeAddExerciseModal() {
    if (!this.hasAddExerciseModalTarget) return;

    this.addExerciseModalTarget.classList.remove("active");
    setTimeout(() => {
      this.addExerciseModalTarget.classList.add("hidden");
      if (this.hasAddExerciseFormTarget) {
        this.addExerciseFormTarget.reset();
      }
      document.body.style.overflow = "";
    }, 300);
  }

  // Add to Program Modal
  openAddToProgramModal(event) {
    const exerciseId = event.currentTarget.dataset.exerciseId;
    if (!this.hasAddToProgramModalTarget || !exerciseId) return;

    this.currentExerciseId = exerciseId;
    this.addToProgramModalTarget.classList.remove("hidden");
    void this.addToProgramModalTarget.offsetWidth;
    this.addToProgramModalTarget.classList.add("active");
    document.body.style.overflow = "hidden";
    this.loadUserPrograms();
  }

  closeAddToProgramModal() {
    if (!this.hasAddToProgramModalTarget) return;

    this.addToProgramModalTarget.classList.remove("active");
    setTimeout(() => {
      this.addToProgramModalTarget.classList.add("hidden");
      document.body.style.overflow = "";
      this.selectedProgramId = null;
      this.currentExerciseId = null;
      if (this.hasConfirmAddToProgramBtnTarget) {
        this.confirmAddToProgramBtnTarget.disabled = true;
      }
    }, 300);
  }

  async loadUserPrograms() {
    if (!this.hasProgramsListTarget) return;

    try {
      const response = await fetch(this.userProgramsUrlValue, {
        method: "GET",
        credentials: "same-origin",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      });
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
      console.error("Error loading programs:", error);
      this.programsListTarget.innerHTML = `
        <div class="text-center text-red-500 py-4">
          <p>Error loading programs</p>
        </div>
      `;
    }
  }

  displayPrograms(programs) {
    if (!this.hasProgramsListTarget) return;

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
      <div class="program-item border border-gray-200 rounded-lg p-3 cursor-pointer" 
           data-program-id="${program.id}"
           data-action="click->exercise-library#selectProgram">
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

  selectProgram(event) {
    const programItem = event.currentTarget;
    const programId = programItem.dataset.programId;

    // Remove selection from all items
    this.element.querySelectorAll(".program-item").forEach((item) => {
      item.classList.remove("selected");
    });
    this.element.querySelectorAll(".program-radio").forEach((radio) => {
      radio.checked = false;
    });

    // Select this item
    programItem.classList.add("selected");
    programItem.querySelector(".program-radio").checked = true;
    this.selectedProgramId = programId;

    if (this.hasConfirmAddToProgramBtnTarget) {
      this.confirmAddToProgramBtnTarget.disabled = false;
    }
  }

  async confirmAddToProgram() {
    if (!this.selectedProgramId || !this.currentExerciseId) return;

    try {
      // Build URL with programId and exerciseId in the path
      const url = `/dashboard/my-programs/${this.selectedProgramId}/add-exercise/${this.currentExerciseId}`;

      const response = await fetch(url, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const data = await response.json();

      if (data.success) {
        this.showSuccessMessage("Exercise added to program successfully!");
        this.closeAddToProgramModal();
      } else {
        this.showErrorMessage(
          data.message || "Error adding exercise to program"
        );
      }
    } catch (error) {
      console.error("Error adding exercise to program:", error);
      this.showErrorMessage("Error adding exercise to program");
    }
  }

  showSuccessMessage(message) {
    this.showToast(message, "success");
  }

  showErrorMessage(message) {
    this.showToast(message, "error");
  }

  showToast(message, type = "info") {
    // Create toast element
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

    // Add to DOM
    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
      toast.classList.remove("translate-x-full");
    }, 100);

    // Remove after 3 seconds
    setTimeout(() => {
      toast.classList.add("translate-x-full");
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    }, 3000);
  }

  // Toggle Favorite
  async toggleFavorite(event) {
    const exerciseId = event.currentTarget.dataset.exerciseId;
    const button = event.currentTarget;

    try {
      button.disabled = true;
      const originalContent = button.innerHTML;
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

      const response = await fetch(
        `/dashboard/exercise-library/toggle-favorite/${exerciseId}`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
        }
      );

      const result = await response.json();

      if (result.success) {
        if (result.isFavorite) {
          button.classList.remove(
            "bg-gray-200",
            "hover:bg-gray-300",
            "text-gray-600"
          );
          button.classList.add("bg-red-500", "hover:bg-red-600", "text-white");
        } else {
          button.classList.remove(
            "bg-red-500",
            "hover:bg-red-600",
            "text-white"
          );
          button.classList.add(
            "bg-gray-200",
            "hover:bg-gray-300",
            "text-gray-600"
          );
        }
        button.innerHTML = '<i class="fas fa-heart"></i>';
        this.showSuccessMessage(result.message);
      } else {
        button.innerHTML = originalContent;
        this.showErrorMessage(
          result.message || "Favori durumu değiştirilemedi"
        );
      }
    } catch (error) {
      console.error("Error toggling favorite:", error);
      button.innerHTML = '<i class="fas fa-heart"></i>';
      this.showErrorMessage("Bağlantı hatası oluştu");
    } finally {
      button.disabled = false;
    }
  }

  // Custom Exercise Form Submission
  async submitForm(event) {
    event.preventDefault();

    if (!this.hasAddExerciseFormTarget) return;

    const formData = new FormData(this.addExerciseFormTarget);
    const exerciseData = {
      name: formData.get("exerciseName"),
      description: formData.get("exerciseDescription"),
      muscleGroup: formData.get("exerciseMuscleGroup"),
      imageUrl: formData.get("exerciseImageUrl"),
      videoUrl: formData.get("exerciseVideoUrl"),
      instructions: formData.get("exerciseInstructions"),
    };

    // Validation
    if (
      !exerciseData.name ||
      !exerciseData.description ||
      !exerciseData.muscleGroup
    ) {
      this.showErrorMessage("Please fill in all required fields");
      return;
    }

    try {
      const response = await fetch(
        "/dashboard/exercise-library/create-custom",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          body: JSON.stringify(exerciseData),
        }
      );

      const data = await response.json();

      if (data.success) {
        this.showSuccessMessage("Custom exercise created successfully!");
        this.closeAddExerciseModal();
        // Reload exercises to show the new one
        this.loadExercises();
      } else {
        this.showErrorMessage(data.message || "Error creating exercise");
      }
    } catch (error) {
      console.error("Error creating exercise:", error);
      this.showErrorMessage("Error creating exercise");
    }
  }
}
