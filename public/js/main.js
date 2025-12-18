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
