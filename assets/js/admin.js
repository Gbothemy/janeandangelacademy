// Admin Panel JavaScript Enhancements

class AdminPanel {
  constructor() {
    this.init()
  }

  init() {
    this.initDataTables()
    this.initFileUpload()
    this.initFormValidation()
    this.initConfirmDialogs()
    this.initTooltips()
    this.initCharts()
    this.initAutoSave()
  }

  // Enhanced Data Tables
  initDataTables() {
    const tables = document.querySelectorAll(".admin-table")

    tables.forEach((table) => {
      this.makeTableSortable(table)
      this.addTableSearch(table)
      this.addTablePagination(table)
    })
  }

  makeTableSortable(table) {
    const headers = table.querySelectorAll("thead th")

    headers.forEach((header, index) => {
      if (!header.classList.contains("no-sort")) {
        header.style.cursor = "pointer"
        header.innerHTML += ' <span class="sort-indicator">↕</span>'

        header.addEventListener("click", () => {
          this.sortTable(table, index)
        })
      }
    })
  }

  sortTable(table, columnIndex) {
    const tbody = table.querySelector("tbody")
    const rows = Array.from(tbody.querySelectorAll("tr"))
    const header = table.querySelectorAll("thead th")[columnIndex]

    // Determine sort direction
    const isAscending = !header.classList.contains("sort-asc")

    // Clear all sort classes
    table.querySelectorAll("thead th").forEach((th) => {
      th.classList.remove("sort-asc", "sort-desc")
      const indicator = th.querySelector(".sort-indicator")
      if (indicator) indicator.textContent = "↕"
    })

    // Set current sort class
    header.classList.add(isAscending ? "sort-asc" : "sort-desc")
    const indicator = header.querySelector(".sort-indicator")
    if (indicator) indicator.textContent = isAscending ? "↑" : "↓"

    // Sort rows
    rows.sort((a, b) => {
      const aText = a.cells[columnIndex].textContent.trim()
      const bText = b.cells[columnIndex].textContent.trim()

      // Try to parse as numbers
      const aNum = Number.parseFloat(aText)
      const bNum = Number.parseFloat(bText)

      if (!isNaN(aNum) && !isNaN(bNum)) {
        return isAscending ? aNum - bNum : bNum - aNum
      }

      // Sort as strings
      return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText)
    })

    // Reorder DOM
    rows.forEach((row) => tbody.appendChild(row))
  }

  addTableSearch(table) {
    const tableContainer = table.parentNode

    // Create search input
    const searchContainer = document.createElement("div")
    searchContainer.className = "table-search"
    searchContainer.style.cssText = "margin-bottom: 1rem; text-align: right;"

    const searchInput = document.createElement("input")
    searchInput.type = "text"
    searchInput.placeholder = "Search table..."
    searchInput.style.cssText = "padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;"

    searchContainer.appendChild(searchInput)
    tableContainer.insertBefore(searchContainer, table)

    // Add search functionality
    searchInput.addEventListener("input", (e) => {
      this.filterTable(table, e.target.value)
    })
  }

  filterTable(table, searchTerm) {
    const rows = table.querySelectorAll("tbody tr")
    const term = searchTerm.toLowerCase()

    rows.forEach((row) => {
      const text = row.textContent.toLowerCase()
      row.style.display = text.includes(term) ? "" : "none"
    })
  }

  // File Upload Enhancements
  initFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]')

    fileInputs.forEach((input) => {
      this.enhanceFileInput(input)
    })
  }

  enhanceFileInput(input) {
    // Create custom file upload area
    const wrapper = document.createElement("div")
    wrapper.className = "file-upload-wrapper"
    wrapper.style.cssText = `
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        `

    wrapper.innerHTML = `
            <div class="file-upload-icon" style="font-size: 2rem; margin-bottom: 1rem;">📁</div>
            <div class="file-upload-text">
                <strong>Click to upload</strong> or drag and drop<br>
                <small style="color: #666;">Max file size: 5MB</small>
            </div>
            <div class="file-upload-preview" style="margin-top: 1rem;"></div>
        `

    input.parentNode.insertBefore(wrapper, input.nextSibling)
    input.style.display = "none"

    // Click handler
    wrapper.addEventListener("click", () => input.click())

    // Drag and drop
    wrapper.addEventListener("dragover", (e) => {
      e.preventDefault()
      wrapper.style.borderColor = "#007bff"
      wrapper.style.backgroundColor = "#f8f9fa"
    })

    wrapper.addEventListener("dragleave", () => {
      wrapper.style.borderColor = "#ddd"
      wrapper.style.backgroundColor = ""
    })

    wrapper.addEventListener("drop", (e) => {
      e.preventDefault()
      wrapper.style.borderColor = "#ddd"
      wrapper.style.backgroundColor = ""

      const files = e.dataTransfer.files
      if (files.length > 0) {
        input.files = files
        this.handleFileSelect(input, files[0])
      }
    })

    // File selection handler
    input.addEventListener("change", (e) => {
      if (e.target.files.length > 0) {
        this.handleFileSelect(input, e.target.files[0])
      }
    })
  }

  handleFileSelect(input, file) {
    const wrapper = input.nextSibling
    const preview = wrapper.querySelector(".file-upload-preview")

    // Validate file
    const maxSize = 5 * 1024 * 1024 // 5MB
    if (file.size > maxSize) {
      this.showAlert("File size exceeds 5MB limit", "error")
      return
    }

    // Show file info
    preview.innerHTML = `
            <div style="background: #e9ecef; padding: 0.5rem; border-radius: 4px; margin-top: 1rem;">
                <strong>${file.name}</strong><br>
                <small>${this.formatFileSize(file.size)} - ${file.type}</small>
            </div>
        `

    // Show image preview if it's an image
    if (file.type.startsWith("image/")) {
      const reader = new FileReader()
      reader.onload = (e) => {
        preview.innerHTML += `
                    <img src="${e.target.result}" style="max-width: 200px; max-height: 150px; margin-top: 0.5rem; border-radius: 4px;">
                `
      }
      reader.readAsDataURL(file)
    }
  }

  formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes"
    const k = 1024
    const sizes = ["Bytes", "KB", "MB", "GB"]
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Number.parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
  }

  // Enhanced Form Validation
  initFormValidation() {
    const forms = document.querySelectorAll("form")

    forms.forEach((form) => {
      // Add loading states
      form.addEventListener("submit", (e) => {
        const submitBtn = form.querySelector('button[type="submit"]')
        if (submitBtn) {
          submitBtn.disabled = true
          submitBtn.innerHTML = '<span class="spinner"></span> Processing...'
        }
      })
    })
  }

  // Confirmation Dialogs
  initConfirmDialogs() {
    document.addEventListener("click", (e) => {
      if (e.target.matches('[onclick*="confirm"]') || e.target.closest('[onclick*="confirm"]')) {
        e.preventDefault()
        const element = e.target.matches('[onclick*="confirm"]') ? e.target : e.target.closest('[onclick*="confirm"]')
        const message = element.getAttribute("data-confirm") || "Are you sure?"

        this.showConfirmDialog(message, () => {
          // Execute the original action
          if (element.tagName === "FORM") {
            element.submit()
          } else if (element.href) {
            window.location.href = element.href
          }
        })
      }
    })
  }

  showConfirmDialog(message, onConfirm) {
    const dialog = document.createElement("div")
    dialog.className = "confirm-dialog"
    dialog.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        `

    dialog.innerHTML = `
            <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px; text-align: center;">
                <h3 style="margin-bottom: 1rem;">Confirm Action</h3>
                <p style="margin-bottom: 2rem;">${message}</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button class="btn btn-danger confirm-yes">Yes, Continue</button>
                    <button class="btn btn-secondary confirm-no">Cancel</button>
                </div>
            </div>
        `

    document.body.appendChild(dialog)

    dialog.querySelector(".confirm-yes").addEventListener("click", () => {
      document.body.removeChild(dialog)
      onConfirm()
    })

    dialog.querySelector(".confirm-no").addEventListener("click", () => {
      document.body.removeChild(dialog)
    })

    dialog.addEventListener("click", (e) => {
      if (e.target === dialog) {
        document.body.removeChild(dialog)
      }
    })
  }

  // Auto-save functionality
  initAutoSave() {
    const textareas = document.querySelectorAll("textarea")

    textareas.forEach((textarea) => {
      let timeout
      textarea.addEventListener("input", () => {
        clearTimeout(timeout)
        timeout = setTimeout(() => {
          this.autoSave(textarea)
        }, 2000)
      })
    })
  }

  autoSave(element) {
    const key = `autosave_${window.location.pathname}_${element.name || element.id}`
    localStorage.setItem(key, element.value)

    // Show auto-save indicator
    this.showAutoSaveIndicator(element)
  }

  showAutoSaveIndicator(element) {
    let indicator = element.parentNode.querySelector(".autosave-indicator")

    if (!indicator) {
      indicator = document.createElement("small")
      indicator.className = "autosave-indicator"
      indicator.style.cssText = "color: #28a745; font-size: 0.8rem; margin-left: 0.5rem;"
      element.parentNode.appendChild(indicator)
    }

    indicator.textContent = "✓ Auto-saved"

    setTimeout(() => {
      indicator.textContent = ""
    }, 2000)
  }

  showAlert(message, type = "info") {
    const alert = document.createElement("div")
    alert.className = `alert alert-${type}`
    alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        `
    alert.textContent = message

    document.body.appendChild(alert)

    setTimeout(() => {
      alert.style.animation = "slideOutRight 0.3s ease"
      setTimeout(() => {
        if (alert.parentNode) {
          alert.parentNode.removeChild(alert)
        }
      }, 300)
    }, 5000)
  }
}

// Initialize admin panel when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new AdminPanel()
})

// Add admin-specific CSS
const adminStyle = document.createElement("style")
adminStyle.textContent = `
    .spinner {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #333;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    .sort-indicator {
        font-size: 0.8em;
        margin-left: 0.5rem;
        opacity: 0.6;
    }
    
    .sort-asc .sort-indicator,
    .sort-desc .sort-indicator {
        opacity: 1;
        color: var(--bronze-brown);
    }
    
    @media (max-width: 768px) {
        .table-search {
            text-align: left !important;
        }
        
        .table-search input {
            width: 100%;
        }
    }
`
document.head.appendChild(adminStyle)
