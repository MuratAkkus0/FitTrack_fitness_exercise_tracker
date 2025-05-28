import { Controller } from "@hotwired/stimulus";

/*
 * Program Management Controller
 * Handles creating, editing, and managing training programs
 */
export default class extends Controller {
  static targets = [
    "modal",
    "modalContent",
    "modalTitle",
    "form",
    "submitBtn",
    "exerciseList",
    "selectedExercisesCount",
  ];

  static values = {
    exercisesUrl: String,
    createUrl: String,
    editUrl: String,
  };

  connect() {
    console.log("Program Management Controller connected");
    this.selectedExercises = new Set();
    this.isEditMode = false;
    this.currentProgramId = null;
    this.setupEventListeners();
  }

  disconnect() {
    this.cleanupEventListeners();
  }

  setupEventListeners() {
    // Click outside modal handler
    this.clickOutsideHandler = this.handleClickOutside.bind(this);
    document.addEventListener("click", this.clickOutsideHandler);
  }

  cleanupEventListeners() {
    if (this.clickOutsideHandler) {
      document.removeEventListener("click", this.clickOutsideHandler);
    }
  }

  handleClickOutside(event) {
    if (
      this.hasModalTarget &&
      this.modalTarget.classList.contains("active") &&
      this.hasModalContentTarget &&
      !this.modalContentTarget.contains(event.target)
    ) {
      this.closeModal();
    }
  }

  // Modal operations
  openNewProgramModal() {
    this.isEditMode = false;
    this.currentProgramId = null;

    if (this.hasModalTitleTarget) {
      this.modalTitleTarget.textContent = "Create New Program";
    }
    if (this.hasSubmitBtnTarget) {
      this.submitBtnTarget.textContent = "Create Program";
    }
    if (this.hasFormTarget) {
      this.formTarget.reset();
    }

    this.selectedExercises.clear();
    this.updateSelectedExercisesCount();
    this.openModal();
  }

  openEditProgramModal(event) {
    const programId = event.currentTarget.dataset.programId;
    const programName = event.currentTarget.dataset.programName;
    const programDescription = event.currentTarget.dataset.programDescription;

    this.isEditMode = true;
    this.currentProgramId = programId;

    if (this.hasModalTitleTarget) {
      this.modalTitleTarget.textContent = "Edit Program";
    }
    if (this.hasSubmitBtnTarget) {
      this.submitBtnTarget.textContent = "Update Program";
    }

    // Fill form with existing data
    if (this.hasFormTarget) {
      const nameInput = this.formTarget.querySelector('[name="name"]');
      const descriptionInput = this.formTarget.querySelector(
        '[name="description"]'
      );

      if (nameInput) nameInput.value = programName || "";
      if (descriptionInput) descriptionInput.value = programDescription || "";
    }

    this.selectedExercises.clear();
    this.updateSelectedExercisesCount();
    this.openModal();
    this.loadProgramExercises(programId);
  }

  openModal() {
    if (!this.hasModalTarget) return;

    this.modalTarget.classList.remove("hidden");
    void this.modalTarget.offsetWidth;
    this.modalTarget.classList.add("active");
    document.body.style.overflow = "hidden";
    this.loadExercises();
  }

  closeModal() {
    if (!this.hasModalTarget) return;

    this.modalTarget.classList.remove("active");
    setTimeout(() => {
      this.modalTarget.classList.add("hidden");
      if (this.hasFormTarget) {
        this.formTarget.reset();
      }
      this.selectedExercises.clear();
      document.body.style.overflow = "";
      this.isEditMode = false;
      this.currentProgramId = null;
    }, 300);
  }

  // Exercise management
  async loadExercises() {
    if (!this.hasExerciseListTarget) return;

    try {
      const response = await fetch(this.exercisesUrlValue);
      const data = await response.json();

      if (data.success && data.exercises) {
        this.displayExercises(data.exercises);
      } else {
        this.showExerciseError("Failed to load exercises");
      }
    } catch (error) {
      console.error("Error loading exercises:", error);
      this.showExerciseError("Error loading exercises");
    }
  }

  async loadProgramExercises(programId) {
    try {
      const response = await fetch(
        `/dashboard/my-programs/${programId}/exercises`
      );
      const data = await response.json();

      if (data.success && data.exercises) {
        data.exercises.forEach((exercise) => {
          this.selectedExercises.add(exercise.id.toString());
        });
        this.updateSelectedExercisesCount();
        this.updateExerciseDisplay();
      }
    } catch (error) {
      console.error("Error loading program exercises:", error);
    }
  }

  displayExercises(exercises) {
    if (!this.hasExerciseListTarget) return;

    this.exerciseListTarget.innerHTML = exercises
      .map(
        (exercise) => `
      <div class="exercise-item flex items-center p-3 hover:bg-gray-50 rounded cursor-pointer border border-transparent ${
        this.selectedExercises.has(exercise.id.toString()) ? "selected" : ""
      }" data-exercise-id="${exercise.id}">
        <input type="checkbox" 
               class="exercise-checkbox mr-3" 
               value="${exercise.id}" 
               ${
                 this.selectedExercises.has(exercise.id.toString())
                   ? "checked"
                   : ""
               }
               data-action="change->program-management#toggleExercise">
        <div class="flex-1">
          <div class="font-medium text-gray-800">${exercise.name}</div>
          <div class="text-sm text-gray-500">${exercise.muscleGroup}</div>
          <div class="text-xs text-gray-400">${
            exercise.description || "No description"
          }</div>
        </div>
      </div>
    `
      )
      .join("");
  }

  updateExerciseDisplay() {
    if (!this.hasExerciseListTarget) return;

    const exerciseItems =
      this.exerciseListTarget.querySelectorAll(".exercise-item");
    exerciseItems.forEach((item) => {
      const exerciseId = item.dataset.exerciseId;
      const checkbox = item.querySelector(".exercise-checkbox");

      if (this.selectedExercises.has(exerciseId)) {
        item.classList.add("selected");
        checkbox.checked = true;
      } else {
        item.classList.remove("selected");
        checkbox.checked = false;
      }
    });
  }

  showExerciseError(message) {
    if (this.hasExerciseListTarget) {
      this.exerciseListTarget.innerHTML = `
        <div class="text-center text-red-500 py-4">
          <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
          <p>${message}</p>
        </div>
      `;
    }
  }

  toggleExercise(event) {
    const checkbox = event.target;
    const exerciseItem = checkbox.closest(".exercise-item");
    const exerciseId = checkbox.value;

    if (checkbox.checked) {
      this.selectedExercises.add(exerciseId);
      exerciseItem.classList.add("selected");
    } else {
      this.selectedExercises.delete(exerciseId);
      exerciseItem.classList.remove("selected");
    }

    this.updateSelectedExercisesCount();
  }

  toggleExerciseByClick(event) {
    // Handle clicking on exercise item (not checkbox)
    if (event.target.type === "checkbox") return;

    const exerciseItem = event.currentTarget;
    const checkbox = exerciseItem.querySelector(".exercise-checkbox");

    checkbox.checked = !checkbox.checked;
    checkbox.dispatchEvent(new Event("change", { bubbles: true }));
  }

  updateSelectedExercisesCount() {
    if (this.hasSelectedExercisesCountTarget) {
      this.selectedExercisesCountTarget.textContent =
        this.selectedExercises.size;
    }
  }

  // Form submission
  async submitForm(event) {
    event.preventDefault();

    if (!this.hasFormTarget) return;

    const formData = new FormData(this.formTarget);
    const programData = {
      programName: formData.get("programName"),
      programDescription: formData.get("programDescription"),
      workoutsPerWeek: formData.get("workoutsPerWeek")
        ? parseInt(formData.get("workoutsPerWeek"))
        : null,
      workoutDuration: formData.get("workoutDuration")
        ? parseInt(formData.get("workoutDuration"))
        : null,
      difficultyLevel: formData.get("difficultyLevel"),
      selectedExercises: Array.from(this.selectedExercises).map((id) =>
        parseInt(id)
      ),
    };

    // Validation
    if (!programData.programName || programData.programName.trim() === "") {
      this.showError("Program name is required");
      return;
    }

    if (programData.selectedExercises.length === 0) {
      this.showError("Please select at least one exercise");
      return;
    }

    try {
      const url = this.isEditMode
        ? this.editUrlValue.replace("__ID__", this.currentProgramId)
        : this.createUrlValue;

      const method = "POST";

      const response = await fetch(url, {
        method: method,
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(programData),
      });

      const data = await response.json();

      if (data.success) {
        this.showSuccess(data.message || "Program saved successfully!");
        this.closeModal();
        // Reload the page to show updated programs
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      } else {
        this.showError(data.message || "Error saving program");
      }
    } catch (error) {
      console.error("Error saving program:", error);
      this.showError("Error saving program");
    }
  }

  // Delete program
  async deleteProgram(event) {
    const programId = event.currentTarget.dataset.programId;

    if (confirm("Are you sure you want to delete this program?")) {
      try {
        const response = await fetch(
          `/dashboard/my-programs/${programId}/delete`,
          {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
            },
          }
        );

        const result = await response.json();

        if (result.success) {
          this.showMessage(result.message, "success");
          // Reload page
          window.location.reload();
        } else {
          this.showMessage(result.message, "error");
        }
      } catch (error) {
        console.error("Delete error:", error);
        this.showMessage(
          "An error occurred while deleting the program",
          "error"
        );
      }
    }
  }

  async shareProgram(event) {
    const programId = event.currentTarget.dataset.programId;

    try {
      const response = await fetch(
        `/dashboard/my-programs/${programId}/share`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );

      const result = await response.json();

      if (result.success) {
        this.showMessage(result.message, "success");

        // Show share URL
        if (result.shareUrl) {
          const shareMessage = `Program share link: ${result.shareUrl}`;
          navigator.clipboard
            .writeText(result.shareUrl)
            .then(() => {
              this.showMessage("Share link copied to clipboard!", "success");
            })
            .catch(() => {
              alert(shareMessage);
            });
        }

        // Reload page
        setTimeout(() => window.location.reload(), 2000);
      } else {
        this.showMessage(result.message, "error");
      }
    } catch (error) {
      console.error("Share error:", error);
      this.showMessage("An error occurred while sharing the program", "error");
    }
  }

  async unshareProgram(event) {
    const programId = event.currentTarget.dataset.programId;

    if (confirm("Are you sure you want to remove sharing for this program?")) {
      try {
        const response = await fetch(
          `/dashboard/my-programs/${programId}/unshare`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
          }
        );

        const result = await response.json();

        if (result.success) {
          this.showMessage(result.message, "success");
          // Reload page
          setTimeout(() => window.location.reload(), 1000);
        } else {
          this.showMessage(result.message, "error");
        }
      } catch (error) {
        console.error("Unshare error:", error);
        this.showMessage("An error occurred while removing sharing", "error");
      }
    }
  }

  // Edit program
  async editProgram(event) {
    const programId = event.currentTarget.dataset.programId;

    try {
      // Fetch program data
      const response = await fetch(`/dashboard/my-programs/${programId}/edit`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });

      const result = await response.json();

      if (result.success && result.program) {
        this.isEditMode = true;
        this.currentProgramId = programId;

        if (this.hasModalTitleTarget) {
          this.modalTitleTarget.textContent = "Edit Program";
        }
        if (this.hasSubmitBtnTarget) {
          this.submitBtnTarget.textContent = "Update Program";
        }

        // Fill form with existing data
        if (this.hasFormTarget) {
          const nameInput = this.formTarget.querySelector("#programName");
          const descriptionInput = this.formTarget.querySelector(
            "#programDescription"
          );
          const workoutsPerWeekSelect =
            this.formTarget.querySelector("#workoutsPerWeek");
          const durationInput =
            this.formTarget.querySelector("#workoutDuration");
          const difficultySelect =
            this.formTarget.querySelector("#difficultyLevel");

          if (nameInput) nameInput.value = result.program.name || "";
          if (descriptionInput)
            descriptionInput.value = result.program.description || "";
          if (workoutsPerWeekSelect)
            workoutsPerWeekSelect.value = result.program.workoutsPerWeek || "";
          if (durationInput)
            durationInput.value = result.program.durationMinutes || "";
          if (difficultySelect)
            difficultySelect.value = result.program.difficultyLevel || "";
        }

        // Set selected exercises
        this.selectedExercises.clear();
        if (result.program.selectedExercises) {
          result.program.selectedExercises.forEach((exerciseId) => {
            this.selectedExercises.add(exerciseId.toString());
          });
        }

        this.updateSelectedExercisesCount();
        this.openModal();
      } else {
        this.showError(result.message || "Could not load program information");
      }
    } catch (error) {
      console.error("Edit program error:", error);
      this.showError("An error occurred while editing the program");
    }
  }

  async toggleProgramStatus(event) {
    const programId = event.currentTarget.dataset.programId;

    try {
      const response = await fetch(
        `/dashboard/my-programs/${programId}/toggle-status`,
        {
          method: "PATCH",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );

      const result = await response.json();

      if (result.success) {
        this.showMessage(result.message, "success");
        // Reload page
        setTimeout(() => window.location.reload(), 1000);
      } else {
        this.showMessage(result.message, "error");
      }
    } catch (error) {
      console.error("Toggle status error:", error);
      this.showMessage(
        "An error occurred while changing program status",
        "error"
      );
    }
  }

  // Utility methods
  showSuccess(message) {
    // You can implement a toast notification system here
    alert(message); // Temporary solution
  }

  showError(message) {
    // You can implement a toast notification system here
    alert(message); // Temporary solution
  }

  showMessage(message, type) {
    // Implement a toast notification system here
    alert(message); // Temporary solution
  }
}
