// Dashboard specific JavaScript functionality

document.addEventListener("DOMContentLoaded", () => {
  initializeHostelSelection()
  initializeRoomSelection()
  initializeAllocationForm()
  loadDashboardData()
})

// Hostel selection functionality
function initializeHostelSelection() {
  const hostelOptions = document.querySelectorAll(".hostel-option")
  const roomSelection = document.getElementById("roomSelection")
  const selectedHostelInput = document.getElementById("selectedHostel")

  hostelOptions.forEach((option) => {
    option.addEventListener("click", function () {
      // Remove selection from other hostels
      hostelOptions.forEach((opt) => opt.classList.remove("selected"))

      // Select current hostel
      this.classList.add("selected")

      // Get hostel ID
      const hostelId = this.getAttribute("data-hostel-id")
      if (selectedHostelInput) {
        selectedHostelInput.value = hostelId
      }

      // Load rooms for selected hostel
      loadRooms(hostelId)

      // Show room selection
      if (roomSelection) {
        roomSelection.style.display = "block"
        roomSelection.scrollIntoView({ behavior: "smooth" })
      }
    })
  })
}

// Room selection functionality
function initializeRoomSelection() {
  const selectedRoomInput = document.getElementById("selectedRoom")

  // Event delegation for dynamically loaded room options
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("room-option") || e.target.closest(".room-option")) {
      const roomOption = e.target.classList.contains("room-option") ? e.target : e.target.closest(".room-option")

      // Remove selection from other rooms
      document.querySelectorAll(".room-option").forEach((opt) => opt.classList.remove("selected"))

      // Select current room
      roomOption.classList.add("selected")

      // Get room ID
      const roomId = roomOption.getAttribute("data-room-id")
      if (selectedRoomInput) {
        selectedRoomInput.value = roomId
      }

      // Enable allocation button
      const allocateBtn = document.getElementById("allocateBtn")
      if (allocateBtn) {
        allocateBtn.disabled = false
      }
    }
  })
}

// Load rooms for selected hostel
function loadRooms(hostelId) {
  const roomGrid = document.getElementById("roomGrid")
  if (!roomGrid) return

  // Show loading state
  roomGrid.innerHTML = '<div class="loading-rooms"><i class="fas fa-spinner fa-spin"></i> Loading rooms...</div>'

  // Make AJAX request to get rooms
  fetch(`get_rooms.php?hostel_id=${hostelId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayRooms(data.rooms)
      } else {
        roomGrid.innerHTML = '<div class="no-rooms">No available rooms found for this hostel.</div>'
      }
    })
    .catch((error) => {
      console.error("Error loading rooms:", error)
      roomGrid.innerHTML = '<div class="error-loading">Error loading rooms. Please try again.</div>'
    })
}

// Display rooms in the grid
function displayRooms(rooms) {
  const roomGrid = document.getElementById("roomGrid")
  if (!roomGrid) return

  if (rooms.length === 0) {
    roomGrid.innerHTML = '<div class="no-rooms">No available rooms in this hostel.</div>'
    return
  }

  let roomsHTML = ""
  rooms.forEach((room) => {
    const availableSpaces = room.capacity - room.occupied
    const isAvailable = availableSpaces > 0

    roomsHTML += `
            <div class="room-option ${!isAvailable ? "unavailable" : ""}" 
                 data-room-id="${room.id}" 
                 ${!isAvailable ? 'style="opacity: 0.5; cursor: not-allowed;"' : ""}>
                <h5>Room ${room.room_number}</h5>
                <p><i class="fas fa-bed"></i> ${room.room_type}</p>
                <p><i class="fas fa-users"></i> ${availableSpaces}/${room.capacity} available</p>
                <p><i class="fas fa-naira-sign"></i> ₦${formatNumber(room.price)}</p>
                ${room.facilities ? `<p><i class="fas fa-list"></i> ${room.facilities}</p>` : ""}
                ${!isAvailable ? '<p style="color: #ef4444; font-weight: bold;"><i class="fas fa-times"></i> Full</p>' : ""}
            </div>
        `
  })

  roomGrid.innerHTML = roomsHTML
}

// Allocation form functionality
function initializeAllocationForm() {
  const allocationForm = document.getElementById("allocationForm")
  if (!allocationForm) return

  allocationForm.addEventListener("submit", function (e) {
    const selectedHostel = document.getElementById("selectedHostel").value
    const selectedRoom = document.getElementById("selectedRoom").value

    if (!selectedHostel || !selectedRoom) {
      e.preventDefault()
      showAlert("Please select both a hostel and a room before submitting your application.", "warning")
      return
    }

    // Show confirmation dialog
    if (!confirm("Are you sure you want to submit your hostel application? This action cannot be undone.")) {
      e.preventDefault()
      return
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]')
    if (submitBtn) {
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting Application...'
      submitBtn.disabled = true
    }
  })
}

// Load dashboard data
function loadDashboardData() {
  // Load hostel statistics
  loadHostelStats()

  // Load recent activities
  loadRecentActivities()

  // Check for notifications
  checkNotifications()
}

// Load hostel statistics
function loadHostelStats() {
  fetch("get_stats.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateStatsDisplay(data.stats)
      }
    })
    .catch((error) => {
      console.error("Error loading stats:", error)
    })
}

// Update statistics display
function updateStatsDisplay(stats) {
  const statElements = {
    totalHostels: document.getElementById("totalHostels"),
    totalRooms: document.getElementById("totalRooms"),
    occupiedRooms: document.getElementById("occupiedRooms"),
    availableSpaces: document.getElementById("availableSpaces"),
  }

  Object.keys(statElements).forEach((key) => {
    if (statElements[key] && stats[key] !== undefined) {
      animateNumber(statElements[key], stats[key])
    }
  })
}

// Animate number counting
function animateNumber(element, target) {
  const start = Number.parseInt(element.textContent) || 0
  const duration = 1000
  const startTime = performance.now()

  function updateNumber(currentTime) {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)

    const current = Math.floor(start + (target - start) * progress)
    element.textContent = current

    if (progress < 1) {
      requestAnimationFrame(updateNumber)
    }
  }

  requestAnimationFrame(updateNumber)
}

// Load recent activities
function loadRecentActivities() {
  const activitiesContainer = document.getElementById("recentActivities")
  if (!activitiesContainer) return

  fetch("get_activities.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayActivities(data.activities)
      }
    })
    .catch((error) => {
      console.error("Error loading activities:", error)
    })
}

// Display recent activities
function displayActivities(activities) {
  const activitiesContainer = document.getElementById("recentActivities")
  if (!activitiesContainer) return

  if (activities.length === 0) {
    activitiesContainer.innerHTML = "<p>No recent activities.</p>"
    return
  }

  let activitiesHTML = ""
  activities.forEach((activity) => {
    activitiesHTML += `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-${getActivityIcon(activity.type)}"></i>
                </div>
                <div class="activity-content">
                    <p>${activity.description}</p>
                    <small>${formatDateTime(activity.created_at)}</small>
                </div>
            </div>
        `
  })

  activitiesContainer.innerHTML = activitiesHTML
}

// Get activity icon based on type
function getActivityIcon(type) {
  const icons = {
    allocation: "bed",
    payment: "credit-card",
    approval: "check-circle",
    rejection: "times-circle",
    login: "sign-in-alt",
  }
  return icons[type] || "info-circle"
}

// Check for notifications
function checkNotifications() {
  fetch("get_notifications.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.notifications.length > 0) {
        displayNotifications(data.notifications)
      }
    })
    .catch((error) => {
      console.error("Error loading notifications:", error)
    })
}

// Display notifications
function displayNotifications(notifications) {
  notifications.forEach((notification) => {
    showAlert(notification.message, notification.type)
  })
}

// Utility functions
function formatNumber(number) {
  return new Intl.NumberFormat("en-NG").format(number)
}

function formatDateTime(dateString) {
  return new Date(dateString).toLocaleString("en-NG", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

function showAlert(message, type = "info") {
  // Use the global showAlert function from main.js
  if (window.HostelSystem && window.HostelSystem.showAlert) {
    window.HostelSystem.showAlert(message, type)
  } else {
    alert(message) // Fallback
  }
}

// Payment status checker
function checkPaymentStatus() {
  const paymentStatusElement = document.getElementById("paymentStatus")
  if (!paymentStatusElement) return

  fetch("check_payment_status.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updatePaymentStatus(data.status)
      }
    })
    .catch((error) => {
      console.error("Error checking payment status:", error)
    })
}

// Update payment status display
function updatePaymentStatus(status) {
  const paymentStatusElement = document.getElementById("paymentStatus")
  if (!paymentStatusElement) return

  const statusConfig = {
    pending: { class: "badge-warning", icon: "clock", text: "Pending" },
    paid: { class: "badge-success", icon: "check-circle", text: "Paid" },
    failed: { class: "badge-danger", icon: "times-circle", text: "Failed" },
  }

  const config = statusConfig[status] || statusConfig.pending

  paymentStatusElement.innerHTML = `
        <span class="badge ${config.class}">
            <i class="fas fa-${config.icon}"></i> ${config.text}
        </span>
    `
}

// Auto-refresh functionality for real-time updates
function startAutoRefresh() {
  // Refresh every 30 seconds
  setInterval(() => {
    loadDashboardData()
    checkPaymentStatus()
  }, 30000)
}

// Initialize auto-refresh if on dashboard page
if (window.location.pathname.includes("dashboard.php")) {
  startAutoRefresh()
}

// Export functions for global use
window.DashboardSystem = {
  loadRooms,
  displayRooms,
  checkPaymentStatus,
  formatNumber,
  formatDateTime,
}
