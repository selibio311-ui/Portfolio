// Runs in <head> before the page paints, so there is no light/dark flash.
(function () {
    try {
        var saved = localStorage.getItem("theme");
        var root = document.documentElement;
        if (saved === "light") {
            root.classList.remove("dark-mode");
        } else if (saved === "dark") {
            root.classList.add("dark-mode");
        }
    } catch (e) { /* storage blocked - keep server default */ }
})();
