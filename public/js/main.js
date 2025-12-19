/* ==============================================
   MAIN.JS - Global Shared Scripts
   ============================================== */

// Utility: Format currency
function formatCurrency(amount) {
    return amount.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

// Utility: Confirm action
function confirmAction(message) {
    return confirm(message || "Are you sure?");
}

document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("mobileDrawerToggle");
    const closeBtn = document.getElementById("mobileDrawerClose");
    const overlay = document.getElementById("mobileDrawerOverlay");
    const drawer = document.getElementById("mobileDrawer");

    if (!toggleBtn || !closeBtn || !overlay || !drawer) return;

    const openDrawer = () => {
        document.body.classList.add("mobile-drawer-open");
        toggleBtn.setAttribute("aria-expanded", "true");
        overlay.setAttribute("aria-hidden", "false");
        drawer.setAttribute("aria-hidden", "false");
    };

    const closeDrawer = () => {
        document.body.classList.remove("mobile-drawer-open");
        toggleBtn.setAttribute("aria-expanded", "false");
        overlay.setAttribute("aria-hidden", "true");
        drawer.setAttribute("aria-hidden", "true");
    };

    toggleBtn.addEventListener("click", openDrawer);
    closeBtn.addEventListener("click", closeDrawer);
    overlay.addEventListener("click", closeDrawer);

    drawer.addEventListener("click", (e) => {
        const link = e.target.closest("a");
        if (link) closeDrawer();
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeDrawer();
    });
});
