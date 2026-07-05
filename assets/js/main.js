// Mobile Navigation Toggle
document.addEventListener("DOMContentLoaded", () => {
  initializeNavigation()
  initializeModals()
  initializeForms()
  initializeAnimations()

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Navbar background on scroll
  window.addEventListener("scroll", () => {
    const navbar = document.querySelector(".navbar")
    if (navbar) {
      if (window.scrollY > 100) {
        navbar.style.background = "rgba(255, 255, 255, 0.98)"
        navbar.style.boxShadow = "0 2px 20px rgba(0, 0, 0, 0.1)"
      } else {
        navbar.style.background = "rgba(255, 255, 255, 0.95)"
        navbar.style.boxShadow = "none"
      }
    }
  })
})

// Navigation functionality
function initializeNavigation() {
  const hamburger = document.querySelector(".hamburger")
  const navMenu = document.querySelector(".nav-menu")
  const navToggle = document.querySelector(".nav-toggle")
  const navLinks = document.querySelector(".nav-links")
  const navLinkItems = document.querySelectorAll(".nav-links a")

  if (hamburger && navMenu) {
    hamburger.addEventListener("click", () => {
      navMenu.classList.toggle("active")
      hamburger.classList.toggle("active")
    })
  }

  if (navToggle && navLinks) {
    navToggle.addEventListener("click", () => {
      navLinks.classList.toggle("active")
    })
  }

  // Close menu when clicking on a link
  document.querySelectorAll(".nav-link").forEach((link) => {
    link.addEventListener("click", () => {
      if (navMenu) {
        navMenu.classList.remove("active")
      }
      if (hamburger) {
        hamburger.classList.remove("active")
      }
    })
  })

  // Close mobile menu when clicking on links
  navLinkItems.forEach((link) => {
    link.addEventListener("click", () => {
      if (navLinks) {
        navLinks.classList.remove("active")
      }
    })
  })

  // Highlight active navigation item
  const currentPage = window.location.pathname.split("/").pop()
  navLinkItems.forEach((link) => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active")
    }
  })
}

// Modal functionality
function initializeModals() {
  const modals = document.querySelectorAll(".modal")
  const modalTriggers = document.querySelectorAll("[data-modal]")
  const modalCloses = document.querySelectorAll(".modal-close, [data-modal-close]")

  // Open modals
  modalTriggers.forEach((trigger) => {
    trigger.addEventListener("click", function (e) {
      e.preventDefault()
      const modalId = this.getAttribute("data-modal")
      const modal = document.getElementById(modalId)
      if (modal) {
        openModal(modal)
      }
    })
  })

  // Close modals
  modalCloses.forEach((close) => {
    close.addEventListener("click", function () {
      const modal = this.closest(".modal")
      if (modal) {
        closeModal(modal)
      }
    })
  })

  // Close modal when clicking outside
  modals.forEach((modal) => {
    modal.addEventListener("click", function (e) {
      if (e.target === this) {
        closeModal(this)
      }
    })
  })

  // Close modal with Escape key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      const openModal = document.querySelector(".modal.show")
      if (openModal) {
        closeModal(openModal)
      }
    }
  })
}

function openModal(modal) {
  modal.classList.add("show")
  document.body.style.overflow = "hidden"

  // Focus first input in modal
  const firstInput = modal.querySelector("input, textarea, select")
  if (firstInput) {
    setTimeout(() => firstInput.focus(), 100)
  }
}

function closeModal(modal) {
  modal.classList.remove("show")
  document.body.style.overflow = ""

  // Reset form if exists
  const form = modal.querySelector("form")
  if (form) {
    form.reset()
  }
}

// Form functionality
function initializeForms() {
  const forms = document.querySelectorAll("form")

  forms.forEach((form) => {
    // Add loading state to submit buttons
    form.addEventListener("submit", function () {
      const submitBtn = this.querySelector('button[type="submit"]')
      if (submitBtn && !submitBtn.disabled) {
        const originalText = submitBtn.innerHTML
        submitBtn.innerHTML = '<span class="loading"></span> Processing...'
        submitBtn.disabled = true

        // Re-enable after 5 seconds as fallback
        setTimeout(() => {
          submitBtn.innerHTML = originalText
          submitBtn.disabled = false
        }, 5000)
      }
    })

    // Real-time validation
    const inputs = form.querySelectorAll("input, textarea, select")
    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        validateField(this)
      })

      input.addEventListener("input", function () {
        clearFieldError(this)
      })
    })
  })

  // File upload preview
  const fileInputs = document.querySelectorAll('input[type="file"]')
  fileInputs.forEach((input) => {
    input.addEventListener("change", function () {
      handleFilePreview(this)
    })
  })
}

function validateField(field) {
  const value = field.value.trim()
  const type = field.type
  const required = field.hasAttribute("required")

  clearFieldError(field)

  if (required && !value) {
    showFieldError(field, "This field is required")
    return false
  }

  if (value) {
    switch (type) {
      case "email":
        if (!isValidEmail(value)) {
          showFieldError(field, "Please enter a valid email address")
          return false
        }
        break
      case "tel":
        if (!isValidPhone(value)) {
          showFieldError(field, "Please enter a valid phone number")
          return false
        }
        break
      case "number":
        if (isNaN(value) || value < 0) {
          showFieldError(field, "Please enter a valid number")
          return false
        }
        break
    }

    // Custom validation for matric number
    if (field.name === "matric_number" && !isValidMatricNumber(value)) {
      showFieldError(field, "Please enter a valid matric number")
      return false
    }
  }

  return true
}

function showFieldError(field, message) {
  field.classList.add("error")

  let errorElement = field.parentNode.querySelector(".field-error")
  if (!errorElement) {
    errorElement = document.createElement("div")
    errorElement.className = "field-error"
    field.parentNode.appendChild(errorElement)
  }

  errorElement.textContent = message
  errorElement.style.color = "#ef4444"
  errorElement.style.fontSize = "0.875rem"
  errorElement.style.marginTop = "0.25rem"
}

function clearFieldError(field) {
  field.classList.remove("error")
  const errorElement = field.parentNode.querySelector(".field-error")
  if (errorElement) {
    errorElement.remove()
  }
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function isValidPhone(phone) {
  const phoneRegex = /^(\+234|0)[789][01]\d{8}$/
  return phoneRegex.test(phone.replace(/\s+/g, ""))
}

function isValidMatricNumber(matric) {
  // Format: [dept_code][year][mode][unique_number]
  const matricRegex = /^(cs|sl|ee|me|ce|ba|mc)\d{4}(01|02)\d{3}$/i
  return matricRegex.test(matric)
}

function handleFilePreview(input) {
  const file = input.files[0]
  if (!file) return

  // Check file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
    showAlert("File size must be less than 5MB", "error")
    input.value = ""
    return
  }

  // Check file type for images
  if (input.accept && input.accept.includes("image/")) {
    if (!file.type.startsWith("image/")) {
      showAlert("Please select a valid image file", "error")
      input.value = ""
      return
    }

    // Show preview
    const reader = new FileReader()
    reader.onload = (e) => {
      let preview = input.parentNode.querySelector(".file-preview")
      if (!preview) {
        preview = document.createElement("div")
        preview.className = "file-preview"
        input.parentNode.appendChild(preview)
      }

      preview.innerHTML = `
          <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 10px; margin-top: 10px;">
          <button type="button" onclick="this.parentNode.remove(); document.querySelector('input[type=file]').value = '';" style="display: block; margin-top: 5px; background: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">Remove</button>
      `
    }
    reader.readAsDataURL(file)
  }
}

// Animation functionality
function initializeAnimations() {
  // Fade in animation for cards
  const cards = document.querySelectorAll(".feature-card, .admin-card, .hostel-option, .room-option")

  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "0"
        entry.target.style.transform = "translateY(20px)"
        entry.target.style.transition = "opacity 0.6s ease, transform 0.6s ease"

        setTimeout(() => {
          entry.target.style.opacity = "1"
          entry.target.style.transform = "translateY(0)"
        }, 100)

        observer.unobserve(entry.target)
      }
    })
  }, observerOptions)

  cards.forEach((card) => {
    observer.observe(card)
  })

  // Counter animation for stats
  const counters = document.querySelectorAll(".stat-item h3")
  counters.forEach((counter) => {
    const target = Number.parseInt(counter.textContent)
    if (!isNaN(target)) {
      animateCounter(counter, target)
    }
  })
}

function animateCounter(element, target) {
  let current = 0
  const increment = target / 50
  const timer = setInterval(() => {
    current += increment
    if (current >= target) {
      current = target
      clearInterval(timer)
    }
    element.textContent = Math.floor(current)
  }, 50)
}

// Utility functions
function showAlert(message, type = "info") {
  const alertContainer = document.querySelector(".alert-container") || createAlertContainer()

  const alert = document.createElement("div")
  alert.className = `alert alert-${type}`
  alert.innerHTML = `
      <i class="fas fa-${getAlertIcon(type)}"></i>
      <span>${message}</span>
      <button type="button" class="alert-close" onclick="this.parentNode.remove()">
          <i class="fas fa-times"></i>
      </button>
  `

  alertContainer.appendChild(alert)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (alert.parentNode) {
      alert.remove()
    }
  }, 5000)
}

function createAlertContainer() {
  const container = document.createElement("div")
  container.className = "alert-container"
  container.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      max-width: 400px;
  `
  document.body.appendChild(container)
  return container
}

function getAlertIcon(type) {
  const icons = {
    success: "check-circle",
    error: "exclamation-circle",
    warning: "exclamation-triangle",
    info: "info-circle",
  }
  return icons[type] || "info-circle"
}

function formatCurrency(amount) {
  return new Intl.NumberFormat("en-NG", {
    style: "currency",
    currency: "NGN",
  }).format(amount)
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString("en-NG", {
    year: "numeric",
    month: "long",
    day: "numeric",
  })
}

function formatDateTime(dateString) {
  return new Date(dateString).toLocaleString("en-NG", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

// AJAX helper function
function makeRequest(url, options = {}) {
  const defaultOptions = {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  }

  const config = { ...defaultOptions, ...options }

  return fetch(url, config)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .catch((error) => {
      console.error("Request failed:", error)
      showAlert("An error occurred. Please try again.", "error")
      throw error
    })
}

// Export functions for use in other scripts
window.HostelSystem = {
  showAlert,
  openModal,
  closeModal,
  validateField,
  formatCurrency,
  formatDate,
  formatDateTime,
  makeRequest,
}
