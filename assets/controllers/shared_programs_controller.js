import { Controller } from "@hotwired/stimulus";

/*
 * Shared Programs Controller
 * Handles copying shared programs functionality
 */
export default class extends Controller {
  static targets = ["copySuccessModal", "copySuccessMessage"];

  connect() {
    console.log("Shared Programs Controller connected");
  }

  async copyProgram(event) {
    event.preventDefault();
    const button = event.currentTarget;
    const shareCode = button.dataset.shareCode;
    const programName = button.dataset.programName;

    // Disable button during request
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Copying...';
    button.disabled = true;

    try {
      const response = await fetch(
        `/dashboard/my-programs/shared/${shareCode}/copy`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );

      const result = await response.json();

      if (result.success) {
        if (this.hasCopySuccessMessageTarget) {
          this.copySuccessMessageTarget.textContent = `"${programName}" program successfully copied to your account.`;
        }
        if (this.hasCopySuccessModalTarget) {
          this.copySuccessModalTarget.classList.remove("hidden");
        }
        this.showToast("Program copied successfully!", "success");
      } else {
        this.showToast(
          result.message || "Error occurred while copying program",
          "error"
        );
      }
    } catch (error) {
      console.error("Copy error:", error);
      this.showToast("Error occurred while copying program", "error");
    } finally {
      // Re-enable button
      button.innerHTML = originalText;
      button.disabled = false;
    }
  }

  closeSuccessModal() {
    if (this.hasCopySuccessModalTarget) {
      this.copySuccessModalTarget.classList.add("hidden");
    }
  }

  goToMyPrograms() {
    window.location.href = "/dashboard/my-programs";
  }

  showToast(message, type = "info") {
    // Create toast notification
    const toast = document.createElement("div");
    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
      type === "success"
        ? "bg-green-500 text-white"
        : type === "error"
        ? "bg-red-500 text-white"
        : "bg-blue-500 text-white"
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

    // Animate in
    setTimeout(() => {
      toast.classList.remove("translate-x-full");
    }, 100);

    // Auto remove after 3 seconds
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
