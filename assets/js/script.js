document.addEventListener("DOMContentLoaded", function () {

    /* ========== 1. MOBILE NAVIGATION ========== */
    const menuToggle = document.getElementById("menu-toggle");
    const navLinks = document.getElementById("nav-links");

    if (menuToggle && navLinks) {
        menuToggle.addEventListener("click", function () {
            navLinks.classList.toggle("show");
        });
    }

    /* ========== 2. DARK / LIGHT MODE ========== */
    const themeToggle = document.getElementById("theme-toggle");
    const root = document.documentElement;

    function updateThemeIcon() {
        if (themeToggle) {
            themeToggle.textContent = root.classList.contains("dark-mode") ? "☀️" : "🌙";
        }
    }

    if (themeToggle) {
        updateThemeIcon();

        themeToggle.addEventListener("click", function () {
            root.classList.toggle("dark-mode");
            try {
                localStorage.setItem("theme", root.classList.contains("dark-mode") ? "dark" : "light");
            } catch (e) { /* ignore */ }
            updateThemeIcon();
        });
    }

    /* ========== 3. PROJECT FILTERING ========== */
    const filterButtons = document.querySelectorAll(".filter-btn");
    const projectCards = document.querySelectorAll(".project-card");

    filterButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const filter = button.getAttribute("data-filter");

            filterButtons.forEach(function (btn) { btn.classList.remove("active"); });
            button.classList.add("active");

            projectCards.forEach(function (card) {
                const category = card.getAttribute("data-category");
                card.style.display = (filter === "all" || category === filter) ? "block" : "none";
            });
        });
    });

    /* ========== 4. SKILL BAR ANIMATION ========== */
    document.querySelectorAll(".skill-progress").forEach(function (bar) {
        const width = bar.getAttribute("data-width");
        setTimeout(function () { bar.style.width = width; }, 300);
    });

    /* ========== 5. CONTACT FORM (friendly client-side checks) ========== */
    /* The server validates everything again - this only saves a round trip. */
    const contactForm = document.getElementById("contact-form");

    if (contactForm) {
        contactForm.addEventListener("submit", function (event) {
            const fields = {
                name: document.getElementById("name"),
                email: document.getElementById("email"),
                subject: document.getElementById("subject"),
                message: document.getElementById("message")
            };
            let valid = true;

            function setError(key, text) {
                const box = document.getElementById(key + "-error");
                if (box) { box.textContent = text; }
                if (text) { valid = false; }
            }

            setError("name", fields.name.value.trim() === "" ? "Please enter your name." : "");

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (fields.email.value.trim() === "") {
                setError("email", "Please enter your email.");
            } else if (!emailPattern.test(fields.email.value.trim())) {
                setError("email", "Please enter a valid email address.");
            } else {
                setError("email", "");
            }

            setError("subject", fields.subject.value.trim() === "" ? "Please enter a subject." : "");

            if (fields.message.value.trim() === "") {
                setError("message", "Please enter your message.");
            } else if (fields.message.value.trim().length < 10) {
                setError("message", "Message must contain at least 10 characters.");
            } else {
                setError("message", "");
            }

            if (!valid) {
                event.preventDefault();   // only block when something is wrong
            }
        });
    }

});
