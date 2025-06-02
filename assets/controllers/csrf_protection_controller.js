import { Controller } from "@hotwired/stimulus";

const nameCheck = /^[-_a-zA-Z0-9]{4,22}$/;
const tokenCheck = /^[-_\/+a-zA-Z0-9]{24,}$/;

/*
 * CSRF Protection Controller
 * Handles CSRF token generation and management for forms
 */
export default class extends Controller {
  static targets = ["token"];

  connect() {
    console.log("CSRF Protection Controller connected");
    this.setupEventListeners();
  }

  disconnect() {
    this.cleanupEventListeners();
  }

  setupEventListeners() {
    // Bind event handlers
    this.submitHandler = this.handleSubmit.bind(this);
    this.turboSubmitStartHandler = this.handleTurboSubmitStart.bind(this);
    this.turboSubmitEndHandler = this.handleTurboSubmitEnd.bind(this);

    // Add event listeners
    document.addEventListener("submit", this.submitHandler, true);
    document.addEventListener(
      "turbo:submit-start",
      this.turboSubmitStartHandler
    );
    document.addEventListener("turbo:submit-end", this.turboSubmitEndHandler);
  }

  cleanupEventListeners() {
    if (this.submitHandler) {
      document.removeEventListener("submit", this.submitHandler, true);
    }
    if (this.turboSubmitStartHandler) {
      document.removeEventListener(
        "turbo:submit-start",
        this.turboSubmitStartHandler
      );
    }
    if (this.turboSubmitEndHandler) {
      document.removeEventListener(
        "turbo:submit-end",
        this.turboSubmitEndHandler
      );
    }
  }

  handleSubmit(event) {
    this.generateCsrfToken(event.target);
  }

  handleTurboSubmitStart(event) {
    const headers = this.generateCsrfHeaders(
      event.detail.formSubmission.formElement
    );
    Object.keys(headers).forEach((key) => {
      event.detail.formSubmission.fetchRequest.headers[key] = headers[key];
    });
  }

  handleTurboSubmitEnd(event) {
    this.removeCsrfToken(event.detail.formSubmission.formElement);
  }

  generateCsrfToken(formElement) {
    const csrfField = formElement.querySelector(
      'input[data-controller="csrf-protection"], input[name="_csrf_token"]'
    );

    if (!csrfField) {
      return;
    }

    let csrfCookie = csrfField.getAttribute(
      "data-csrf-protection-cookie-value"
    );
    let csrfToken = csrfField.value;

    if (!csrfCookie && nameCheck.test(csrfToken)) {
      csrfField.setAttribute(
        "data-csrf-protection-cookie-value",
        (csrfCookie = csrfToken)
      );
      csrfField.defaultValue = csrfToken = btoa(
        String.fromCharCode.apply(
          null,
          (window.crypto || window.msCrypto).getRandomValues(new Uint8Array(18))
        )
      );
      csrfField.dispatchEvent(new Event("change", { bubbles: true }));
    }

    if (csrfCookie && tokenCheck.test(csrfToken)) {
      const cookie = `${csrfCookie}_${csrfToken}=${csrfCookie}; path=/; samesite=strict`;
      document.cookie =
        window.location.protocol === "https:"
          ? `__Host-${cookie}; secure`
          : cookie;
    }
  }

  generateCsrfHeaders(formElement) {
    const headers = {};
    const csrfField = formElement.querySelector(
      'input[data-controller="csrf-protection"], input[name="_csrf_token"]'
    );

    if (!csrfField) {
      return headers;
    }

    const csrfCookie = csrfField.getAttribute(
      "data-csrf-protection-cookie-value"
    );

    if (tokenCheck.test(csrfField.value) && nameCheck.test(csrfCookie)) {
      headers[csrfCookie] = csrfField.value;
    }

    return headers;
  }

  removeCsrfToken(formElement) {
    const csrfField = formElement.querySelector(
      'input[data-controller="csrf-protection"], input[name="_csrf_token"]'
    );

    if (!csrfField) {
      return;
    }

    const csrfCookie = csrfField.getAttribute(
      "data-csrf-protection-cookie-value"
    );

    if (tokenCheck.test(csrfField.value) && nameCheck.test(csrfCookie)) {
      const cookie = `${csrfCookie}_${csrfField.value}=0; path=/; samesite=strict; max-age=0`;
      document.cookie =
        window.location.protocol === "https:"
          ? `__Host-${cookie}; secure`
          : cookie;
    }
  }
}
