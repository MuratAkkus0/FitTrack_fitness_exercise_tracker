import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    console.log("Test controller connected!");
    window.testSimpleController = () => {
      console.log("Simple test controller method called!");
      alert("Test controller is working!");
    };
  }

  testMethod() {
    console.log("Test method called via data-action");
    alert("Stimulus data-action is working!");
  }
}
