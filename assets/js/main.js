// Football Academy Website - Main JavaScript File

class FootballAcademyApp {
  constructor() {
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.initMobileMenu();
    this.initScrollEffects();
    this.initLightbox();
    this.initFormValidation();
    this.initResponsiveTables();
    this.initLazyLoading();
    this.initSmoothScrolling();
    this.initScrollToTop();
    this.initSliders();
  }

  setupEventListeners() {
    // Window resize handler
    window.addEventListener(
      "resize",
      this.debounce(() => {
        this.handleResize();
      }, 250)
    );

    // Window scroll handler
    window.addEventListener(
      "scroll",
      this.throttle(() => {
        this.handleScroll();
      }, 16)
    );

    // Page load handler
    window.addEventListener("load", () => {
      this.handlePageLoad();
    });
  }

  // Mobile Menu Functionality
  initMobileMenu() {
    const mobileToggle = document.querySelector(".mobile-menu-toggle");
    const navMenu = document.querySelector(".nav-menu");
    const body = document.body;

    if (mobileToggle && navMenu) {
      // Create hamburger spans if they don't exist
      if (mobileToggle.children.length === 0) {
        for (let i = 0; i < 3; i++) {
          const span = document.createElement("span");
          mobileToggle.appendChild(span);
        }
      }

      mobileToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        this.toggleMobileMenu();
      });

      // Close menu when clicking outside
      document.addEventListener("click", (e) => {
        if (
          navMenu.classList.contains("active") &&
          !e.target.closest(".nav-menu") &&
          !e.target.closest(".mobile-menu-toggle")
        ) {
          this.closeMobileMenu();
        }
      });

      // Close menu on escape key
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && navMenu.classList.contains("active")) {
          this.closeMobileMenu();
        }
      });

      // Handle menu item clicks
      navMenu.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", (e) => {
          if (window.innerWidth <= 768) {
            // Add a small delay for better UX
            setTimeout(() => {
              this.closeMobileMenu();
            }, 150);
          }
        });
      });

      // Handle window resize
      window.addEventListener("resize", () => {
        if (window.innerWidth > 768 && navMenu.classList.contains("active")) {
          this.closeMobileMenu();
        }
      });
    }
  }

  toggleMobileMenu() {
    const mobileToggle = document.querySelector(".mobile-menu-toggle");
    const navMenu = document.querySelector(".nav-menu");
    const body = document.body;

    if (mobileToggle && navMenu) {
      const isActive = navMenu.classList.contains("active");

      if (isActive) {
        this.closeMobileMenu();
      } else {
        this.openMobileMenu();
      }
    }
  }

  openMobileMenu() {
    const mobileToggle = document.querySelector(".mobile-menu-toggle");
    const navMenu = document.querySelector(".nav-menu");
    const body = document.body;

    mobileToggle.classList.add("active");
    navMenu.classList.add("active");
    body.style.overflow = "hidden";

    // Add aria attributes for accessibility
    mobileToggle.setAttribute("aria-expanded", "true");
    navMenu.setAttribute("aria-hidden", "false");
  }

  closeMobileMenu() {
    const mobileToggle = document.querySelector(".mobile-menu-toggle");
    const navMenu = document.querySelector(".nav-menu");
    const body = document.body;

    if (mobileToggle && navMenu) {
      mobileToggle.classList.remove("active");
      navMenu.classList.remove("active");
      body.style.overflow = "";

      // Add aria attributes for accessibility
      mobileToggle.setAttribute("aria-expanded", "false");
      navMenu.setAttribute("aria-hidden", "true");
    }
  }

  // Scroll Effects
  initScrollEffects() {
    this.observeElements();
  }

  observeElements() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px",
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("animate-in");
        }
      });
    }, observerOptions);

    // Observe elements for animation
    document
      .querySelectorAll(".program-card, .player-card, .stat-card")
      .forEach((el) => {
        observer.observe(el);
      });
  }

  // Lightbox Functionality
  initLightbox() {
    // Create lightbox HTML
    if (!document.querySelector(".lightbox")) {
      const lightboxHTML = `
                <div class="lightbox" id="lightbox">
                    <div class="lightbox-content">
                        <span class="lightbox-close">&times;</span>
                        <img id="lightbox-image" src="/placeholder.svg" alt="">
                        <div class="lightbox-info">
                            <h3 id="lightbox-title"></h3>
                            <p id="lightbox-description"></p>
                        </div>
                    </div>
                </div>
            `;
      document.body.insertAdjacentHTML("beforeend", lightboxHTML);
    }

    // Add click handlers to gallery items
    document.querySelectorAll(".gallery-item img").forEach((img) => {
      img.addEventListener("click", (e) => {
        this.openLightbox(e.target);
      });
    });

    // Close lightbox handlers
    const lightbox = document.getElementById("lightbox");
    const closeBtn = document.querySelector(".lightbox-close");

    if (lightbox && closeBtn) {
      closeBtn.addEventListener("click", () => this.closeLightbox());
      lightbox.addEventListener("click", (e) => {
        if (e.target === lightbox) {
          this.closeLightbox();
        }
      });
    }

    // Keyboard navigation
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        this.closeLightbox();
      }
    });
  }

  openLightbox(img) {
    const lightbox = document.getElementById("lightbox");
    const lightboxImg = document.getElementById("lightbox-image");
    const lightboxTitle = document.getElementById("lightbox-title");
    const lightboxDesc = document.getElementById("lightbox-description");

    if (lightbox && lightboxImg) {
      lightboxImg.src = img.src;
      lightboxImg.alt = img.alt;

      // Get title and description from parent elements
      const galleryItem = img.closest(".gallery-item");
      if (galleryItem) {
        const title = galleryItem.querySelector("h4")?.textContent || img.alt;
        const desc = galleryItem.querySelector("p")?.textContent || "";

        if (lightboxTitle) lightboxTitle.textContent = title;
        if (lightboxDesc) lightboxDesc.textContent = desc;
      }

      lightbox.style.display = "block";
      document.body.style.overflow = "hidden";
    }
  }

  closeLightbox() {
    const lightbox = document.getElementById("lightbox");
    if (lightbox) {
      lightbox.style.display = "none";
      document.body.style.overflow = "";
    }
  }

  // Form Validation
  initFormValidation() {
    const forms = document.querySelectorAll("form");

    forms.forEach((form) => {
      // Real-time validation
      form.querySelectorAll("input, textarea, select").forEach((field) => {
        field.addEventListener("blur", () => {
          this.validateField(field);
        });

        field.addEventListener("input", () => {
          if (field.classList.contains("error")) {
            this.validateField(field);
          }
        });
      });

      // Form submission
      form.addEventListener("submit", (e) => {
        if (!this.validateForm(form)) {
          e.preventDefault();
        }
      });
    });
  }

  validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute("required");
    let isValid = true;
    let errorMessage = "";

    // Remove existing error states
    field.classList.remove("error", "success");
    this.hideFieldError(field);

    // Required field validation
    if (required && !value) {
      isValid = false;
      errorMessage = "This field is required";
    }

    // Email validation
    if (type === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        errorMessage = "Please enter a valid email address";
      }
    }

    // Phone validation
    if (type === "tel" && value) {
      const phoneRegex = /^[+]?[1-9][\d]{0,15}$/;
      if (!phoneRegex.test(value.replace(/[\s\-$$$$]/g, ""))) {
        isValid = false;
        errorMessage = "Please enter a valid phone number";
      }
    }

    // Apply validation result
    if (isValid) {
      field.classList.add("success");
    } else {
      field.classList.add("error");
      this.showFieldError(field, errorMessage);
    }

    return isValid;
  }

  validateForm(form) {
    const fields = form.querySelectorAll(
      "input[required], textarea[required], select[required]"
    );
    let isValid = true;

    fields.forEach((field) => {
      if (!this.validateField(field)) {
        isValid = false;
      }
    });

    return isValid;
  }

  showFieldError(field, message) {
    let errorElement = field.parentNode.querySelector(".form-error");

    if (!errorElement) {
      errorElement = document.createElement("div");
      errorElement.className = "form-error";
      field.parentNode.appendChild(errorElement);
    }

    errorElement.textContent = message;
    errorElement.style.display = "block";
  }

  hideFieldError(field) {
    const errorElement = field.parentNode.querySelector(".form-error");
    if (errorElement) {
      errorElement.style.display = "none";
    }
  }

  // Responsive Tables
  initResponsiveTables() {
    const tables = document.querySelectorAll("table");

    tables.forEach((table) => {
      if (!table.parentNode.classList.contains("table-responsive")) {
        const wrapper = document.createElement("div");
        wrapper.className = "table-responsive";
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
      }
    });
  }

  // Lazy Loading for Images
  initLazyLoading() {
    if ("IntersectionObserver" in window) {
      const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src || img.src;
            img.classList.remove("lazy");
            imageObserver.unobserve(img);
          }
        });
      });

      document.querySelectorAll("img[data-src]").forEach((img) => {
        imageObserver.observe(img);
      });
    }
  }

  // Smooth Scrolling
  initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener("click", (e) => {
        e.preventDefault();
        const target = document.querySelector(anchor.getAttribute("href"));

        if (target) {
          const headerHeight =
            document.querySelector(".header")?.offsetHeight || 0;
          const targetPosition = target.offsetTop - headerHeight - 20;

          window.scrollTo({
            top: targetPosition,
            behavior: "smooth",
          });
        }
      });
    });
  }

  // Scroll to Top Button
  initScrollToTop() {
    // Create scroll to top button
    if (!document.querySelector(".scroll-to-top")) {
      const scrollBtn = document.createElement("button");
      scrollBtn.className = "scroll-to-top";
      scrollBtn.innerHTML = "↑";
      scrollBtn.setAttribute("aria-label", "Scroll to top");
      document.body.appendChild(scrollBtn);

      scrollBtn.addEventListener("click", () => {
        window.scrollTo({
          top: 0,
          behavior: "smooth",
        });
      });
    }
  }

  // Sliders Functionality
  initSliders() {
    const sliders = document.querySelectorAll(".slider");

    sliders.forEach((slider) => {
      const sliderImages = slider.querySelectorAll("img");
      let currentIndex = 0;

      sliderImages.forEach((img, index) => {
        if (index !== currentIndex) {
          img.style.display = "none";
        }
      });

      setInterval(() => {
        sliderImages[currentIndex].style.display = "none";
        currentIndex = (currentIndex + 1) % sliderImages.length;
        sliderImages[currentIndex].style.display = "block";
      }, 3000);
    });
  }

  // Event Handlers
  handleResize() {
    // Close mobile menu on resize to desktop
    if (window.innerWidth > 768) {
      this.closeMobileMenu();
    }

    // Recalculate responsive elements
    this.updateResponsiveElements();
  }

  handleScroll() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    // Show/hide scroll to top button
    const scrollBtn = document.querySelector(".scroll-to-top");
    if (scrollBtn) {
      if (scrollTop > 300) {
        scrollBtn.classList.add("visible");
      } else {
        scrollBtn.classList.remove("visible");
      }
    }

    // Header scroll effect
    const header = document.querySelector(".header");
    if (header) {
      if (scrollTop > 100) {
        header.classList.add("scrolled");
      } else {
        header.classList.remove("scrolled");
      }
    }
  }

  handlePageLoad() {
    // Remove loading states
    document.querySelectorAll(".loading").forEach((el) => {
      el.classList.remove("loading");
    });

    // Initialize any page-specific functionality
    this.initPageSpecific();
  }

  updateResponsiveElements() {
    // Update any elements that need recalculation on resize
    const cards = document.querySelectorAll(".program-card, .player-card");
    cards.forEach((card) => {
      // Reset any inline styles that might interfere with responsive design
      card.style.height = "";
    });
  }

  initPageSpecific() {
    const currentPage =
      window.location.pathname.split("/").pop() || "index.php";

    switch (currentPage) {
      case "gallery.php":
        this.initGalleryFilters();
        break;
      case "contact.php":
        this.initContactForm();
        break;
      case "schedule.php":
        this.initScheduleView();
        break;
    }
  }

  initGalleryFilters() {
    // Add filter functionality for gallery
    const categories = [
      ...new Set(
        Array.from(document.querySelectorAll(".gallery-item")).map(
          (item) => item.dataset.category || "all"
        )
      ),
    ];

    if (categories.length > 1) {
      this.createGalleryFilters(categories);
    }
  }

  createGalleryFilters(categories) {
    const gallery = document.querySelector(".gallery-grid");
    if (!gallery) return;

    const filterContainer = document.createElement("div");
    filterContainer.className = "gallery-filters";
    filterContainer.style.cssText = `
            text-align: center;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
        `;

    // Add "All" filter
    const allBtn = this.createFilterButton("All", "all", true);
    filterContainer.appendChild(allBtn);

    // Add category filters
    categories.forEach((category) => {
      if (category !== "all") {
        const btn = this.createFilterButton(
          category.charAt(0).toUpperCase() + category.slice(1),
          category
        );
        filterContainer.appendChild(btn);
      }
    });

    gallery.parentNode.insertBefore(filterContainer, gallery);
  }

  createFilterButton(text, category, active = false) {
    const btn = document.createElement("button");
    btn.textContent = text;
    btn.className = `btn btn-small ${active ? "active" : ""}`;
    btn.dataset.filter = category;

    btn.addEventListener("click", () => {
      this.filterGallery(category);

      // Update active state
      document
        .querySelectorAll(".gallery-filters button")
        .forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
    });

    return btn;
  }

  filterGallery(category) {
    const items = document.querySelectorAll(".gallery-item");

    items.forEach((item) => {
      const itemCategory = item.dataset.category || "all";

      if (category === "all" || itemCategory === category) {
        item.style.display = "block";
        item.style.animation = "fadeIn 0.3s ease";
      } else {
        item.style.display = "none";
      }
    });
  }

  initContactForm() {
    const form = document.querySelector(".contact-form");
    if (form) {
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        this.submitContactForm(form);
      });
    }
  }

  async submitContactForm(form) {
    if (!this.validateForm(form)) return;

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    // Show loading state
    submitBtn.textContent = "Sending...";
    submitBtn.disabled = true;
    form.classList.add("loading");

    try {
      const formData = new FormData(form);
      const response = await fetch(form.action || "contact_process.php", {
        method: "POST",
        body: formData,
      });

      if (response.ok) {
        this.showMessage(
          "Thank you! Your message has been sent successfully.",
          "success"
        );
        form.reset();
      } else {
        throw new Error("Network response was not ok");
      }
    } catch (error) {
      this.showMessage(
        "Sorry, there was an error sending your message. Please try again.",
        "error"
      );
    } finally {
      // Reset button state
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
      form.classList.remove("loading");
    }
  }

  initScheduleView() {
    // Make schedule table more mobile-friendly
    const scheduleTable = document.querySelector(".admin-table");
    if (scheduleTable && window.innerWidth <= 768) {
      this.makeTableResponsive(scheduleTable);
    }
  }

  makeTableResponsive(table) {
    const rows = table.querySelectorAll("tbody tr");
    const headers = Array.from(table.querySelectorAll("thead th")).map(
      (th) => th.textContent
    );

    rows.forEach((row) => {
      const cells = row.querySelectorAll("td");
      cells.forEach((cell, index) => {
        if (headers[index]) {
          cell.setAttribute("data-label", headers[index]);
        }
      });
    });
  }

  showMessage(message, type = "info") {
    // Create and show a toast message
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${
              type === "success"
                ? "#28a745"
                : type === "error"
                ? "#dc3545"
                : "#007bff"
            };
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            max-width: 300px;
        `;
    toast.textContent = message;

    document.body.appendChild(toast);

    // Auto remove after 5 seconds
    setTimeout(() => {
      toast.style.animation = "slideOutRight 0.3s ease";
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    }, 5000);

    // Add click to dismiss
    toast.addEventListener("click", () => {
      toast.style.animation = "slideOutRight 0.3s ease";
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    });
  }

  // Utility functions
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  throttle(func, limit) {
    let inThrottle;
    return function () {
      const args = arguments;

      if (!inThrottle) {
        func.apply(this, args);
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  }
}

// Initialize the app when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new FootballAcademyApp();
});

// Add CSS animations
const style = document.createElement("style");
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-in {
        animation: fadeIn 0.6s ease forwards;
    }
    
    @media (max-width: 768px) {
        .admin-table {
            border: 0;
        }
        
        .admin-table thead {
            display: none;
        }
        
        .admin-table tr {
            border-bottom: 3px solid #ddd;
            display: block;
            margin-bottom: 0.625em;
        }
        
        .admin-table td {
            border: none;
            display: block;
            font-size: 0.8em;
            text-align: right;
            padding-left: 50%;
            position: relative;
        }
        
        .admin-table td:before {
            content: attr(data-label) ": ";
            position: absolute;
            left: 6px;
            width: 45%;
            text-align: left;
            font-weight: bold;
        }
    }
`;
document.head.appendChild(style);
