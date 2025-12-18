/* ==============================================
   ADMIN.JS - Admin Layout Scripts
   ============================================== */

document.addEventListener("DOMContentLoaded", function () {
    // Simple sidebar toggle for mobile
    const toggleBtn = document.getElementById("sidebarToggle");
    const sidebar = document.querySelector(".sidebar");

    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("active");
        });
    }
});
