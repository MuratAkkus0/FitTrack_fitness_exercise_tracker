import { Controller } from "@hotwired/stimulus";

/*
 * Blog Post Controller
 * Handles blog post functionality including:
 * - Content toggling (read more/less)
 * - Post deletion
 * - Message display
 */
export default class extends Controller {
  static targets = ["messageContainer"];

  static values = {
    deleteUrl: String,
  };

  connect() {
    console.log("Blog Post Controller connected");
  }

  // Toggle content visibility
  toggleContent(event) {
    const postId = event.params.postId;
    const fullContent = document.getElementById(`fullContent${postId}`);
    const parentDiv = fullContent.parentElement;
    const shortContent = parentDiv.querySelector("p:first-child");
    const readMoreBtn = parentDiv.querySelector("button:first-of-type");

    if (fullContent.classList.contains("hidden")) {
      fullContent.classList.remove("hidden");
      shortContent.style.display = "none";
      readMoreBtn.style.display = "none";
    } else {
      fullContent.classList.add("hidden");
      shortContent.style.display = "block";
      readMoreBtn.style.display = "inline-block";
    }
  }

  // Delete post
  async deletePost(event) {
    const postId = event.params.postId;

    if (
      !confirm(
        "Are you sure you want to delete this post? This action cannot be undone."
      )
    ) {
      return;
    }

    try {
      const response = await fetch(`/blog/post/${postId}/delete`, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
        },
      });

      const data = await response.json();

      if (data.success) {
        this.showMessage("Post deleted successfully", "success");
        // Remove the post from the page or reload
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        this.showMessage("Error deleting post: " + data.message, "error");
      }
    } catch (error) {
      console.error("Error:", error);
      this.showMessage("An error occurred while deleting the post", "error");
    }
  }

  // Share workout
  shareWorkout(event) {
    const workoutId = event.params.workoutId;
    window.location.href = `/blog/create-from-workout/${workoutId}`;
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
