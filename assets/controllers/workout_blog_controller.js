import { Controller } from "@hotwired/stimulus";

/*
 * Workout Blog Controller
 * Handles workout blog creation functionality including:
 * - Preview toggle
 * - Form submission
 * - Message display
 */
export default class extends Controller {
  static targets = [
    "form",
    "titleInput",
    "contentTextarea",
    "previewBtn",
    "previewSection",
    "previewTitle",
    "previewContent",
    "submitBtn",
    "messageContainer",
  ];

  static values = {
    createUrl: String,
  };

  connect() {
    console.log("Workout Blog Controller connected");
    this.setupEventListeners();
  }

  setupEventListeners() {
    // Auto-update preview when typing
    if (this.hasTitleInputTarget) {
      this.titleInputTarget.addEventListener("input", () =>
        this.updatePreview()
      );
    }
    if (this.hasContentTextareaTarget) {
      this.contentTextareaTarget.addEventListener("input", () =>
        this.updatePreview()
      );
    }
  }

  // Toggle preview
  togglePreview() {
    if (!this.hasPreviewSectionTarget || !this.hasPreviewBtnTarget) return;

    if (this.previewSectionTarget.classList.contains("hidden")) {
      this.updatePreview();
      this.previewSectionTarget.classList.remove("hidden");
      this.previewBtnTarget.textContent = "Hide Preview";
    } else {
      this.previewSectionTarget.classList.add("hidden");
      this.previewBtnTarget.textContent = "Show Preview";
    }
  }

  // Update preview content
  updatePreview() {
    if (!this.hasPreviewTitleTarget || !this.hasPreviewContentTarget) return;

    const title = this.hasTitleInputTarget ? this.titleInputTarget.value : "";
    const content = this.hasContentTextareaTarget
      ? this.contentTextareaTarget.value
      : "";

    this.previewTitleTarget.textContent = title || "Untitled Post";
    this.previewContentTarget.textContent = content || "No content yet...";
  }

  // Submit form
  async submitForm(event) {
    event.preventDefault();

    if (!this.hasFormTarget || !this.hasSubmitBtnTarget) return;

    const formData = new FormData(this.formTarget);
    const data = {
      title: formData.get("title"),
      content: formData.get("content"),
      is_public: formData.get("is_public") ? true : false,
    };

    this.submitBtnTarget.disabled = true;
    this.submitBtnTarget.textContent = "Sharing...";

    try {
      const response = await fetch(this.createUrlValue, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      });

      const result = await response.json();

      if (result.success) {
        this.showMessage("Workout shared successfully! 🎉", "success");
        setTimeout(() => {
          window.location.href = "/dashboard/blog/my-posts";
        }, 1500);
      } else {
        this.showMessage("Error: " + result.message, "error");
        this.submitBtnTarget.disabled = false;
        this.submitBtnTarget.textContent = "Share Workout";
      }
    } catch (error) {
      console.error("Error:", error);
      this.showMessage(
        "An error occurred while sharing your workout.",
        "error"
      );
      this.submitBtnTarget.disabled = false;
      this.submitBtnTarget.textContent = "Share Workout";
    }
  }

  // Show message
  showMessage(message, type) {
    let messageContainer = this.hasMessageContainerTarget
      ? this.messageContainerTarget
      : document.getElementById("messageContainer");

    if (!messageContainer) {
      messageContainer = document.createElement("div");
      messageContainer.id = "messageContainer";
      messageContainer.className = "fixed top-4 right-4 z-50";
      document.body.appendChild(messageContainer);
    }

    const messageDiv = document.createElement("div");
    messageDiv.className = `p-4 rounded-md shadow-lg ${
      type === "success"
        ? "bg-green-100 text-green-800 border border-green-200"
        : "bg-red-100 text-red-800 border border-red-200"
    }`;
    messageDiv.textContent = message;

    messageContainer.appendChild(messageDiv);

    setTimeout(() => {
      messageDiv.remove();
    }, 5000);
  }
}
