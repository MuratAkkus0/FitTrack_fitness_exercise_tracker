import { Controller } from "@hotwired/stimulus";

/*
 * Workout Timer Controller
 * Handles workout timing, progress tracking, and saving functionality
 */
export default class extends Controller {
  static targets = [
    "startButton",
    "pauseButton",
    "timer",
    "progressBar",
    "completedExercises",
    "saveButton",
    "cancelButton",
  ];
  static values = {
    programId: Number,
    totalExercises: Number,
  };

  connect() {
    console.log("Workout Timer Controller connected");
    this.startTime = null;
    this.timerInterval = null;
    this.isRunning = false;
    this.totalSeconds = 0;
    this.eventListenersAdded = false;
    this.initializeEventListeners();
  }

  disconnect() {
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
    }
    // Clean up event listeners
    if (this.clickHandler) {
      this.element.removeEventListener("click", this.clickHandler);
    }
  }

  initializeEventListeners() {
    // Prevent duplicate event listeners
    if (this.eventListenersAdded) {
      return;
    }

    // Create a single click handler for all click events
    this.clickHandler = (e) => {
      if (e.target.closest(".add-set-btn")) {
        this.addSet(e);
      } else if (e.target.closest(".exercise-complete-btn")) {
        this.toggleExerciseCompletion(e);
      }
    };

    this.element.addEventListener("click", this.clickHandler);
    this.eventListenersAdded = true;
  }

  // Timer functions
  startTimer() {
    if (!this.isRunning) {
      this.startTime = Date.now() - this.totalSeconds * 1000;
      this.timerInterval = setInterval(() => this.updateTimer(), 1000);
      this.isRunning = true;
      this.startButtonTarget.classList.add("hidden");
      this.pauseButtonTarget.classList.remove("hidden");

      // Add visual feedback
      this.startButtonTarget.disabled = true;
      this.pauseButtonTarget.disabled = false;
    }
  }

  pauseTimer() {
    if (this.isRunning) {
      clearInterval(this.timerInterval);
      this.isRunning = false;
      this.startButtonTarget.classList.remove("hidden");
      this.pauseButtonTarget.classList.add("hidden");

      // Add visual feedback
      this.startButtonTarget.disabled = false;
      this.pauseButtonTarget.disabled = true;
    }
  }

  updateTimer() {
    this.totalSeconds = Math.floor((Date.now() - this.startTime) / 1000);
    const hours = Math.floor(this.totalSeconds / 3600);
    const minutes = Math.floor((this.totalSeconds % 3600) / 60);
    const seconds = this.totalSeconds % 60;

    this.timerTarget.textContent = `${hours
      .toString()
      .padStart(2, "0")}:${minutes.toString().padStart(2, "0")}:${seconds
      .toString()
      .padStart(2, "0")}`;
  }

  addSet(event) {
    const exerciseItem = event.target.closest(".exercise-item");
    const setsContainer = exerciseItem.querySelector(".sets-container");
    const setRows = setsContainer.querySelectorAll(".set-row");
    const setNumber = setRows.length;

    const newSetRow = document.createElement("div");
    newSetRow.className = "set-row grid grid-cols-4 gap-2 mb-2";
    newSetRow.innerHTML = `
            <div class="flex items-center justify-center bg-gray-100 rounded p-2">${
              setNumber + 1
            }</div>
            <input type="number" class="weight-input border border-gray-300 rounded p-2" placeholder="0" step="0.5">
            <input type="number" class="reps-input border border-gray-300 rounded p-2" placeholder="0">
            <div class="flex items-center justify-center">
                <input type="checkbox" class="set-complete-checkbox w-5 h-5 text-indigo-600">
            </div>
        `;

    setsContainer.appendChild(newSetRow);
  }

  toggleExerciseCompletion(event) {
    const exerciseItem = event.target.closest(".exercise-item");
    exerciseItem.classList.toggle("completed");

    const icon = event.target.querySelector("i");
    if (exerciseItem.classList.contains("completed")) {
      exerciseItem.style.backgroundColor = "#f0f9ff";
      icon.style.color = "#10b981";
    } else {
      exerciseItem.style.backgroundColor = "";
      icon.style.color = "";
    }

    this.updateProgress();
  }

  updateProgress() {
    const totalExercises =
      this.element.querySelectorAll(".exercise-item").length;
    const completedExercises = this.element.querySelectorAll(
      ".exercise-item.completed"
    ).length;
    const progressPercentage = (completedExercises / totalExercises) * 100;

    this.progressBarTarget.style.width = progressPercentage + "%";
    this.completedExercisesTarget.textContent = completedExercises;
  }

  saveWorkout() {
    // Prevent multiple save attempts
    if (this.saveButtonTarget.disabled) {
      return;
    }

    if (!confirm("Are you sure you want to save the workout?")) {
      return;
    }

    // Pause timer if running
    if (this.isRunning) {
      this.pauseTimer();
    }

    const exercises = [];
    let totalVolume = 0;
    let totalReps = 0;

    this.element.querySelectorAll(".exercise-item").forEach((item) => {
      const exerciseId = item.dataset.exerciseId;
      const sets = [];

      item.querySelectorAll(".set-row").forEach((setRow) => {
        const weight =
          parseFloat(setRow.querySelector(".weight-input").value) || 0;
        const reps = parseInt(setRow.querySelector(".reps-input").value) || 0;
        const completed = setRow.querySelector(
          ".set-complete-checkbox"
        ).checked;

        if (weight > 0 || reps > 0) {
          sets.push({ weight, reps, completed });
          totalVolume += weight * reps;
          totalReps += reps;
        }
      });

      if (sets.length > 0) {
        exercises.push({
          exerciseId: parseInt(exerciseId),
          sets: sets.length,
          reps: sets.reduce((sum, set) => sum + set.reps, 0),
          weight: sets.length > 0 ? sets[0].weight : 0,
          notes: item.querySelector(".exercise-notes").value,
          volume: sets.reduce((sum, set) => sum + set.weight * set.reps, 0),
        });
      }
    });

    // Calculate estimated calories burned (rough estimation)
    const durationMinutes = this.totalSeconds / 60;
    const estimatedCalories = this.calculateCalories(
      durationMinutes,
      totalVolume,
      exercises.length
    );

    const workoutData = {
      programId: this.programIdValue,
      duration: (this.totalSeconds / 60).toFixed(2),
      isCompleted: true,
      exercises: exercises,
      notes: "",
      totalVolume: totalVolume.toFixed(2),
      totalReps: totalReps,
      estimatedCalories: Math.round(estimatedCalories),
    };

    // Disable save button to prevent double submission
    this.saveButtonTarget.disabled = true;
    this.saveButtonTarget.innerHTML =
      '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

    fetch("/workout/save", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(workoutData),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Workout saved successfully!");
          window.location.href = "/dashboard/today";
        } else {
          alert("Error: " + data.message);
          this.resetSaveButton();
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred while saving the workout.");
        this.resetSaveButton();
      });
  }

  resetSaveButton() {
    this.saveButtonTarget.disabled = false;
    this.saveButtonTarget.innerHTML =
      '<i class="fas fa-save mr-2"></i>Save Workout';
  }

  calculateCalories(durationMinutes, totalVolume, exerciseCount) {
    // Basic calorie calculation formula
    // This is a rough estimation based on duration, volume, and exercise count
    const baseCaloriesPerMinute = 8; // Base calories per minute for weight training
    const volumeMultiplier = totalVolume * 0.01; // Additional calories based on volume
    const exerciseMultiplier = exerciseCount * 2; // Additional calories per exercise

    return (
      durationMinutes * baseCaloriesPerMinute +
      volumeMultiplier +
      exerciseMultiplier
    );
  }

  cancelWorkout() {
    // Prevent multiple cancel attempts
    if (this.cancelButtonTarget.disabled) {
      return;
    }

    if (
      confirm(
        "Are you sure you want to cancel the workout? All data will be lost."
      )
    ) {
      // Disable button to prevent multiple clicks
      this.cancelButtonTarget.disabled = true;
      this.cancelButtonTarget.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i>Canceling...';

      if (this.timerInterval) {
        clearInterval(this.timerInterval);
      }
      window.location.href = "/dashboard/today";
    }
  }

  // Get current workout duration in minutes
  getCurrentDuration() {
    return this.totalSeconds / 60;
  }

  // Get workout statistics
  getWorkoutStats() {
    const exercises = this.element.querySelectorAll(".exercise-item");
    const completedExercises = this.element.querySelectorAll(
      ".exercise-item.completed"
    );

    return {
      totalExercises: exercises.length,
      completedExercises: completedExercises.length,
      duration: this.getCurrentDuration(),
      isRunning: this.isRunning,
    };
  }
}
