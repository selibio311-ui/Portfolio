document.addEventListener("DOMContentLoaded", function () {

    // Mobile sidebar
    var toggle = document.getElementById("admin-menu-toggle");
    var sidebar = document.getElementById("admin-sidebar");
    if (toggle && sidebar) {
        toggle.addEventListener("click", function () {
            sidebar.classList.toggle("open");
        });
    }

    // Confirm before deleting (forms marked with data-confirm)
    document.querySelectorAll("form[data-confirm]").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            if (!window.confirm(form.getAttribute("data-confirm"))) {
                event.preventDefault();
            }
        });
    });

});
