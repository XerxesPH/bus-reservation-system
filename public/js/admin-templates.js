/* ==============================================
   ADMIN-TEMPLATES.JS - Admin Templates Scripts
   ============================================== */

function selectDays(daysToSelect) {
    const checkboxes = document.querySelectorAll(".day-checkbox");
    checkboxes.forEach((cb) => {
        cb.checked = daysToSelect.includes(cb.value);
    });
}

function toggleTimes(check) {
    const checkboxes = document.querySelectorAll(".time-checkbox");
    checkboxes.forEach((cb) => {
        cb.checked = check;
    });
}
