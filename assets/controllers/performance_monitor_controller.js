import { Controller } from "@hotwired/stimulus";

/*
 * Performance Monitor Controller
 * Tracks and reports performance metrics for debugging
 */
export default class extends Controller {
  static values = {
    enabled: { type: Boolean, default: false },
    reportInterval: { type: Number, default: 30000 }, // 30 seconds
  };

  connect() {
    if (!this.enabledValue) return;

    console.log("Performance Monitor Controller connected");

    this.metrics = {
      domMutations: 0,
      eventListeners: 0,
      memoryUsage: [],
      performanceEntries: [],
      errors: [],
      startTime: performance.now(),
    };

    this.setupMonitoring();
    this.startReporting();
  }

  disconnect() {
    this.cleanup();
  }

  setupMonitoring() {
    // Monitor DOM mutations
    this.mutationObserver = new MutationObserver((mutations) => {
      this.metrics.domMutations += mutations.length;

      // Log excessive mutations
      if (mutations.length > 10) {
        console.warn(`High DOM mutation count: ${mutations.length}`, mutations);
      }
    });

    this.mutationObserver.observe(document.body, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeOldValue: true,
    });

    // Monitor memory usage
    this.memoryInterval = setInterval(() => {
      if (performance.memory) {
        this.metrics.memoryUsage.push({
          timestamp: Date.now(),
          used: performance.memory.usedJSHeapSize,
          total: performance.memory.totalJSHeapSize,
          limit: performance.memory.jsHeapSizeLimit,
        });

        // Keep only last 100 entries
        if (this.metrics.memoryUsage.length > 100) {
          this.metrics.memoryUsage.shift();
        }
      }
    }, 5000);

    // Monitor performance entries
    this.performanceObserver = new PerformanceObserver((list) => {
      const entries = list.getEntries();
      this.metrics.performanceEntries.push(...entries);

      // Log slow operations
      entries.forEach((entry) => {
        if (entry.duration > 100) {
          console.warn(
            `Slow operation detected: ${entry.name} took ${entry.duration}ms`
          );
        }
      });

      // Keep only last 50 entries
      if (this.metrics.performanceEntries.length > 50) {
        this.metrics.performanceEntries.splice(
          0,
          this.metrics.performanceEntries.length - 50
        );
      }
    });

    this.performanceObserver.observe({
      entryTypes: ["measure", "navigation", "resource"],
    });

    // Monitor errors
    this.errorHandler = (event) => {
      this.metrics.errors.push({
        timestamp: Date.now(),
        message: event.error?.message || event.message,
        stack: event.error?.stack,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
      });

      // Keep only last 20 errors
      if (this.metrics.errors.length > 20) {
        this.metrics.errors.shift();
      }
    };

    window.addEventListener("error", this.errorHandler);
    window.addEventListener("unhandledrejection", this.errorHandler);

    // Monitor event listeners (approximate)
    this.originalAddEventListener = EventTarget.prototype.addEventListener;
    this.originalRemoveEventListener =
      EventTarget.prototype.removeEventListener;

    EventTarget.prototype.addEventListener = (...args) => {
      this.metrics.eventListeners++;
      return this.originalAddEventListener.apply(this, args);
    };

    EventTarget.prototype.removeEventListener = (...args) => {
      this.metrics.eventListeners = Math.max(
        0,
        this.metrics.eventListeners - 1
      );
      return this.originalRemoveEventListener.apply(this, args);
    };
  }

  startReporting() {
    this.reportInterval = setInterval(() => {
      this.generateReport();
    }, this.reportIntervalValue);

    // Also report on page unload
    this.unloadHandler = () => this.generateReport();
    window.addEventListener("beforeunload", this.unloadHandler);
  }

  generateReport() {
    const now = performance.now();
    const uptime = now - this.metrics.startTime;

    const report = {
      timestamp: new Date().toISOString(),
      uptime: Math.round(uptime),
      domMutations: this.metrics.domMutations,
      eventListeners: this.metrics.eventListeners,
      errors: this.metrics.errors.length,
      memoryTrend: this.getMemoryTrend(),
      slowOperations: this.getSlowOperations(),
      recommendations: this.getRecommendations(),
    };

    console.group("🔍 Performance Report");
    console.log("Uptime:", `${Math.round(uptime / 1000)}s`);
    console.log("DOM Mutations:", this.metrics.domMutations);
    console.log("Event Listeners:", this.metrics.eventListeners);
    console.log("Errors:", this.metrics.errors.length);

    if (report.memoryTrend) {
      console.log("Memory Trend:", report.memoryTrend);
    }

    if (report.slowOperations.length > 0) {
      console.warn("Slow Operations:", report.slowOperations);
    }

    if (report.recommendations.length > 0) {
      console.warn("Recommendations:", report.recommendations);
    }

    console.groupEnd();

    // Send to server if endpoint is available
    this.sendReportToServer(report);
  }

  getMemoryTrend() {
    if (this.metrics.memoryUsage.length < 2) return null;

    const recent = this.metrics.memoryUsage.slice(-10);
    const first = recent[0];
    const last = recent[recent.length - 1];

    const trend = last.used - first.used;
    const percentage = ((last.used / last.total) * 100).toFixed(1);

    return {
      trend: trend > 0 ? "increasing" : trend < 0 ? "decreasing" : "stable",
      change: Math.abs(trend),
      usage: `${percentage}%`,
      current: Math.round(last.used / 1024 / 1024), // MB
    };
  }

  getSlowOperations() {
    return this.metrics.performanceEntries
      .filter((entry) => entry.duration > 100)
      .map((entry) => ({
        name: entry.name,
        duration: Math.round(entry.duration),
        type: entry.entryType,
      }))
      .slice(-5); // Last 5 slow operations
  }

  getRecommendations() {
    const recommendations = [];

    // High DOM mutations
    if (this.metrics.domMutations > 1000) {
      recommendations.push(
        "Consider reducing DOM manipulations by using DocumentFragment or virtual DOM techniques"
      );
    }

    // High event listener count
    if (this.metrics.eventListeners > 100) {
      recommendations.push(
        "High number of event listeners detected. Ensure proper cleanup in disconnect() methods"
      );
    }

    // Memory usage
    const memoryTrend = this.getMemoryTrend();
    if (
      memoryTrend &&
      memoryTrend.trend === "increasing" &&
      memoryTrend.change > 10 * 1024 * 1024
    ) {
      recommendations.push(
        "Memory usage is increasing. Check for memory leaks in event listeners or cached data"
      );
    }

    // Errors
    if (this.metrics.errors.length > 5) {
      recommendations.push(
        "Multiple errors detected. Check console for details and fix underlying issues"
      );
    }

    return recommendations;
  }

  async sendReportToServer(report) {
    try {
      // Only send if there's a performance endpoint
      const response = await fetch("/dashboard/progress/performance-report", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(report),
      });

      if (!response.ok && response.status !== 404) {
        console.warn("Failed to send performance report to server");
      }
    } catch (error) {
      // Silently fail - performance monitoring shouldn't break the app
    }
  }

  cleanup() {
    // Stop monitoring
    if (this.mutationObserver) {
      this.mutationObserver.disconnect();
    }

    if (this.memoryInterval) {
      clearInterval(this.memoryInterval);
    }

    if (this.performanceObserver) {
      this.performanceObserver.disconnect();
    }

    if (this.reportInterval) {
      clearInterval(this.reportInterval);
    }

    // Restore original methods
    if (this.originalAddEventListener) {
      EventTarget.prototype.addEventListener = this.originalAddEventListener;
    }

    if (this.originalRemoveEventListener) {
      EventTarget.prototype.removeEventListener =
        this.originalRemoveEventListener;
    }

    // Remove event listeners
    if (this.errorHandler) {
      window.removeEventListener("error", this.errorHandler);
      window.removeEventListener("unhandledrejection", this.errorHandler);
    }

    if (this.unloadHandler) {
      window.removeEventListener("beforeunload", this.unloadHandler);
    }
  }

  // Manual methods for debugging
  logCurrentMetrics() {
    console.table({
      "DOM Mutations": this.metrics.domMutations,
      "Event Listeners": this.metrics.eventListeners,
      Errors: this.metrics.errors.length,
      "Memory Entries": this.metrics.memoryUsage.length,
      "Performance Entries": this.metrics.performanceEntries.length,
    });
  }

  clearMetrics() {
    this.metrics.domMutations = 0;
    this.metrics.eventListeners = 0;
    this.metrics.memoryUsage = [];
    this.metrics.performanceEntries = [];
    this.metrics.errors = [];
    this.metrics.startTime = performance.now();
    console.log("Performance metrics cleared");
  }
}
