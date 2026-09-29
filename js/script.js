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

    if (themeToggle) {
        const savedTheme = localStorage.getItem("theme");

        // Default is dark (tech theme). "light" removes dark-mode class.
        if (savedTheme === "light") {
            document.body.classList.remove("dark-mode");
            themeToggle.textContent = "🌙";
        } else {
            document.body.classList.add("dark-mode");
            themeToggle.textContent = "☀️";
        }

        themeToggle.addEventListener("click", function () {
            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("theme", "dark");
                themeToggle.textContent = "☀️";
            } else {
                localStorage.setItem("theme", "light");
                themeToggle.textContent = "🌙";
            }
        });
    }

    /* ========== 3. PROJECT FILTERING ========== */
    const filterButtons = document.querySelectorAll(".filter-btn");
    const projectCards = document.querySelectorAll(".project-card");

    if (filterButtons.length > 0) {
        filterButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                const filter = button.getAttribute("data-filter");

                filterButtons.forEach(function (btn) {
                    btn.classList.remove("active");
                });
                button.classList.add("active");

                projectCards.forEach(function (card) {
                    const category = card.getAttribute("data-category");

                    if (filter === "all" || category === filter) {
                        card.style.display = "block";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        });
    }

    /* ========== 4. SKILL BAR ANIMATION ========== */
    const skillProgress = document.querySelectorAll(".skill-progress");

    if (skillProgress.length > 0) {
        skillProgress.forEach(function (bar) {
            const width = bar.getAttribute("data-width");
            setTimeout(function () {
                bar.style.width = width;
            }, 300);
        });
    }

    /* ========== 5. CONTACT FORM VALIDATION ========== */
    const contactForm = document.getElementById("contact-form");

    if (contactForm) {
        contactForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const name = document.getElementById("name");
            const email = document.getElementById("email");
            const subject = document.getElementById("subject");
            const message = document.getElementById("message");

            const nameError = document.getElementById("name-error");
            const emailError = document.getElementById("email-error");
            const subjectError = document.getElementById("subject-error");
            const messageError = document.getElementById("message-error");
            const formSuccess = document.getElementById("form-success");

            // Clear previous messages
            nameError.textContent = "";
            emailError.textContent = "";
            subjectError.textContent = "";
            messageError.textContent = "";
            formSuccess.textContent = "";

            let valid = true;

            if (name.value.trim() === "") {
                nameError.textContent = "Please enter your name.";
                valid = false;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email.value.trim() === "") {
                emailError.textContent = "Please enter your email.";
                valid = false;
            } else if (!emailPattern.test(email.value)) {
                emailError.textContent = "Please enter a valid email address.";
                valid = false;
            }

            if (subject.value.trim() === "") {
                subjectError.textContent = "Please enter a subject.";
                valid = false;
            }

            if (message.value.trim() === "") {
                messageError.textContent = "Please enter your message.";
                valid = false;
            } else if (message.value.trim().length < 10) {
                messageError.textContent = "Message must contain at least 10 characters.";
                valid = false;
            }

            if (valid) {
                formSuccess.textContent = "Thank you! Your message has been validated successfully.";
                contactForm.reset();
            }
        });
    }

});
