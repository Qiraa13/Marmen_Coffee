// Cart functionality
function updateQuantity(cartId, action) {
  const quantityInput = document.getElementById("quantity_" + cartId)
  let currentQuantity = Number.parseInt(quantityInput.value)

  if (action === "increase") {
    currentQuantity++
  } else if (action === "decrease" && currentQuantity > 1) {
    currentQuantity--
  }

  quantityInput.value = currentQuantity

  // Submit form to update cart
  document.getElementById("update_form_" + cartId).submit()
}

// Confirm delete actions
function confirmDelete(message) {
  return confirm(message || "Apakah Anda yakin ingin menghapus item ini?")
}

// Auto-hide alerts
document.addEventListener("DOMContentLoaded", () => {
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    if (alert.classList.contains("alert-success")) {
      setTimeout(() => {
        alert.style.opacity = "0"
        setTimeout(() => {
          alert.remove()
        }, 300)
      }, 3000)
    }
  })
})

// Form validation
function validateForm(formId) {
  const form = document.getElementById(formId)
  const inputs = form.querySelectorAll("input[required], select[required], textarea[required]")
  let isValid = true

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      input.classList.add("is-invalid")
      isValid = false
    } else {
      input.classList.remove("is-invalid")
    }
  })

  return isValid
}

// Price formatting
function formatPrice(price) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(price)
}

// Search functionality
function searchProducts() {
  const searchInput = document.getElementById("searchInput")
  const searchTerm = searchInput.value.toLowerCase()
  const productCards = document.querySelectorAll(".product-card")

  productCards.forEach((card) => {
    const productName = card.querySelector(".card-title").textContent.toLowerCase()
    const productDescription = card.querySelector(".card-text").textContent.toLowerCase()

    if (productName.includes(searchTerm) || productDescription.includes(searchTerm)) {
      card.style.display = "block"
    } else {
      card.style.display = "none"
    }
  })
}

// Loading state for buttons
function showLoading(button) {
  const originalText = button.innerHTML
  button.innerHTML =
    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...'
  button.disabled = true

  setTimeout(() => {
    button.innerHTML = originalText
    button.disabled = false
  }, 2000)
}

// Handle 404 errors for AJAX requests
function handleAjaxError(xhr, status, error) {
  if (xhr.status === 404) {
    window.location.href = "/404.php"
  } else if (xhr.status === 403) {
    alert("Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.")
  } else if (xhr.status === 500) {
    alert("Terjadi kesalahan server. Silakan coba lagi nanti.")
  } else {
    alert("Terjadi kesalahan: " + error)
  }
}

// Check if page exists before navigation
function navigateToPage(url) {
  fetch(url, { method: "HEAD" })
    .then((response) => {
      if (response.ok) {
        window.location.href = url
      } else {
        window.location.href = "/404.php"
      }
    })
    .catch(() => {
      window.location.href = "/404.php"
    })
}

// Handle browser back button for SPA-like behavior
window.addEventListener("popstate", (event) => {
  if (event.state && event.state.page) {
    navigateToPage(event.state.page)
  }
})

// Add error boundary for JavaScript errors
window.addEventListener("error", (event) => {
  console.error("JavaScript Error:", event.error)
  // Optionally send error to logging service
})

// Handle unhandled promise rejections
window.addEventListener("unhandledrejection", (event) => {
  console.error("Unhandled Promise Rejection:", event.reason)
  // Optionally send error to logging service
})
