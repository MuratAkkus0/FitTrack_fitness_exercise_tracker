import { Controller } from "@hotwired/stimulus";

/*
 * Blog Controller
 * Handles blog post creation, editing, and management
 */
export default class extends Controller {
  static targets = [
    "form",
    "titleInput",
    "contentTextarea",
    "submitBtn",
    "previewBtn",
    "previewModal",
    "previewContent",
    "characterCount",
    "wordCount",
  ];

  static values = {
    maxCharacters: { type: Number, default: 5000 },
    createUrl: String,
    updateUrl: String,
    postId: Number,
    postsUrl: String,
  };

  connect() {
    console.log("Blog Controller connected");
    this.isEditMode = this.hasPostIdValue;
    this.currentPage = 1;
    this.setupEventListeners();
    this.updateCounts();
  }

  disconnect() {
    this.cleanupEventListeners();
  }

  setupEventListeners() {
    // Auto-save functionality (optional)
    this.autoSaveTimer = null;

    // Click outside modal handler
    this.clickOutsideHandler = this.handleClickOutside.bind(this);
    document.addEventListener("click", this.clickOutsideHandler);
  }

  cleanupEventListeners() {
    if (this.clickOutsideHandler) {
      document.removeEventListener("click", this.clickOutsideHandler);
    }
    if (this.autoSaveTimer) {
      clearTimeout(this.autoSaveTimer);
    }
  }

  handleClickOutside(event) {
    if (
      this.hasPreviewModalTarget &&
      this.previewModalTarget.classList.contains("active") &&
      !this.previewModalTarget
        .querySelector(".modal-content")
        .contains(event.target)
    ) {
      this.closePreview();
    }
  }

  // Content tracking
  updateCounts() {
    if (this.hasContentTextareaTarget) {
      const content = this.contentTextareaTarget.value;
      const characterCount = content.length;
      const wordCount =
        content.trim() === "" ? 0 : content.trim().split(/\s+/).length;

      if (this.hasCharacterCountTarget) {
        this.characterCountTarget.textContent = characterCount;

        // Update color based on limit
        if (characterCount > this.maxCharactersValue * 0.9) {
          this.characterCountTarget.classList.add("text-red-500");
          this.characterCountTarget.classList.remove(
            "text-gray-500",
            "text-yellow-500"
          );
        } else if (characterCount > this.maxCharactersValue * 0.7) {
          this.characterCountTarget.classList.add("text-yellow-500");
          this.characterCountTarget.classList.remove(
            "text-gray-500",
            "text-red-500"
          );
        } else {
          this.characterCountTarget.classList.add("text-gray-500");
          this.characterCountTarget.classList.remove(
            "text-yellow-500",
            "text-red-500"
          );
        }
      }

      if (this.hasWordCountTarget) {
        this.wordCountTarget.textContent = wordCount;
      }
    }
  }

  // Content input handler
  onContentInput() {
    this.updateCounts();
    this.scheduleAutoSave();
  }

  // Auto-save functionality
  scheduleAutoSave() {
    if (this.autoSaveTimer) {
      clearTimeout(this.autoSaveTimer);
    }

    // Auto-save after 3 seconds of inactivity
    this.autoSaveTimer = setTimeout(() => {
      this.autoSave();
    }, 3000);
  }

  async autoSave() {
    if (!this.isEditMode || !this.hasFormTarget) return;

    try {
      const formData = new FormData(this.formTarget);
      const postData = {
        title: formData.get("title"),
        content: formData.get("content"),
        isDraft: true,
      };

      const response = await fetch(
        `${this.updateUrlValue}/${this.postIdValue}`,
        {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          body: JSON.stringify(postData),
        }
      );

      if (response.ok) {
        this.showAutoSaveIndicator();
      }
    } catch (error) {
      console.error("Auto-save failed:", error);
    }
  }

  showAutoSaveIndicator() {
    // Create a temporary indicator
    const indicator = document.createElement("div");
    indicator.className =
      "fixed top-4 right-4 bg-green-500 text-white px-3 py-1 rounded text-sm z-50";
    indicator.textContent = "Auto-saved";
    document.body.appendChild(indicator);

    setTimeout(() => {
      indicator.remove();
    }, 2000);
  }

  // Preview functionality
  openPreview() {
    if (!this.hasPreviewModalTarget || !this.hasPreviewContentTarget) return;

    const title = this.hasTitleInputTarget ? this.titleInputTarget.value : "";
    const content = this.hasContentTextareaTarget
      ? this.contentTextareaTarget.value
      : "";

    if (!title.trim() && !content.trim()) {
      this.showError("Please add some content to preview");
      return;
    }

    // Convert line breaks to HTML
    const htmlContent = content.replace(/\n/g, "<br>");

    this.previewContentTarget.innerHTML = `
      <h1 class="text-3xl font-bold text-gray-800 mb-4">${
        title || "Untitled Post"
      }</h1>
      <div class="prose max-w-none">
        ${htmlContent || '<p class="text-gray-500">No content</p>'}
      </div>
    `;

    this.previewModalTarget.classList.remove("hidden");
    void this.previewModalTarget.offsetWidth;
    this.previewModalTarget.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  closePreview() {
    if (!this.hasPreviewModalTarget) return;

    this.previewModalTarget.classList.remove("active");
    setTimeout(() => {
      this.previewModalTarget.classList.add("hidden");
      document.body.style.overflow = "";
    }, 300);
  }

  // Form submission
  async submitForm(event) {
    event.preventDefault();

    if (!this.hasFormTarget) return;

    const formData = new FormData(this.formTarget);
    const postData = {
      title: formData.get("title"),
      content: formData.get("content"),
      isDraft: false,
    };

    // Validation
    if (!postData.title || postData.title.trim() === "") {
      this.showError("Title is required");
      return;
    }

    if (!postData.content || postData.content.trim() === "") {
      this.showError("Content is required");
      return;
    }

    if (postData.content.length > this.maxCharactersValue) {
      this.showError(
        `Content exceeds maximum length of ${this.maxCharactersValue} characters`
      );
      return;
    }

    // Disable submit button
    if (this.hasSubmitBtnTarget) {
      this.submitBtnTarget.disabled = true;
      this.submitBtnTarget.textContent = "Saving...";
    }

    try {
      const url = this.isEditMode
        ? `${this.updateUrlValue}/${this.postIdValue}`
        : this.createUrlValue;

      const method = this.isEditMode ? "PUT" : "POST";

      const response = await fetch(url, {
        method: method,
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(postData),
      });

      const data = await response.json();

      if (data.success) {
        this.showSuccess(data.message || "Post saved successfully!");

        // Redirect to post or blog list
        setTimeout(() => {
          if (data.redirectUrl) {
            window.location.href = data.redirectUrl;
          } else {
            window.location.href = "/dashboard/blog";
          }
        }, 1000);
      } else {
        this.showError(data.message || "Error saving post");
      }
    } catch (error) {
      console.error("Error saving post:", error);
      this.showError("Error saving post");
    } finally {
      // Re-enable submit button
      if (this.hasSubmitBtnTarget) {
        this.submitBtnTarget.disabled = false;
        this.submitBtnTarget.textContent = this.isEditMode
          ? "Update Post"
          : "Publish Post";
      }
    }
  }

  // Save as draft
  async saveDraft() {
    if (!this.hasFormTarget) return;

    const formData = new FormData(this.formTarget);
    const postData = {
      title: formData.get("title") || "Untitled Draft",
      content: formData.get("content") || "",
      isDraft: true,
    };

    try {
      const url = this.isEditMode
        ? `${this.updateUrlValue}/${this.postIdValue}`
        : this.createUrlValue;

      const method = this.isEditMode ? "PUT" : "POST";

      const response = await fetch(url, {
        method: method,
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(postData),
      });

      const data = await response.json();

      if (data.success) {
        this.showSuccess("Draft saved successfully!");

        // If this was a new post, update to edit mode
        if (!this.isEditMode && data.postId) {
          this.isEditMode = true;
          this.postIdValue = data.postId;
        }
      } else {
        this.showError(data.message || "Error saving draft");
      }
    } catch (error) {
      console.error("Error saving draft:", error);
      this.showError("Error saving draft");
    }
  }

  // Delete post
  async deletePost(event) {
    const postId = event.currentTarget.dataset.postId;
    const postTitle = event.currentTarget.dataset.postTitle;

    if (
      !confirm(
        `Are you sure you want to delete "${postTitle}"? This action cannot be undone.`
      )
    ) {
      return;
    }

    try {
      const response = await fetch(`/dashboard/blog/${postId}`, {
        method: "DELETE",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const data = await response.json();

      if (data.success) {
        this.showSuccess("Post deleted successfully!");

        // Remove the post card from DOM or redirect
        const postCard = event.currentTarget.closest(".post-card");
        if (postCard) {
          postCard.remove();
        } else {
          setTimeout(() => {
            window.location.href = "/dashboard/blog";
          }, 1000);
        }
      } else {
        this.showError(data.message || "Error deleting post");
      }
    } catch (error) {
      console.error("Error deleting post:", error);
      this.showError("Error deleting post");
    }
  }

  // Utility methods
  showSuccess(message) {
    this.showNotification(message, "success");
  }

  showError(message) {
    this.showNotification(message, "error");
  }

  showNotification(message, type) {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50 ${
      type === "success" ? "bg-green-500 text-white" : "bg-red-500 text-white"
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Auto-remove after 3 seconds
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }

  // Load more posts functionality
  async loadMorePosts() {
    if (!this.hasPostsUrlValue) return;

    try {
      this.currentPage++;
      const url = new URL(this.postsUrlValue, window.location.origin);
      url.searchParams.set("page", this.currentPage);

      const response = await fetch(url);
      const data = await response.json();

      if (data.success && data.posts) {
        // Implementation would depend on how posts are structured in the template
        // For now, just show a message
        this.showNotification(
          `Loaded ${data.posts.length} more posts`,
          "success"
        );
      } else {
        this.showError("No more posts to load");
      }
    } catch (error) {
      console.error("Error loading more posts:", error);
      this.showError("Error loading more posts");
    }
  }
}
