/* ==============================================
   PROFILE.JS - Profile Page Scripts
   ============================================== */

document.addEventListener("DOMContentLoaded", function () {
    const paymentTypeSelect = document.getElementById("paymentType");

    if (paymentTypeSelect) {
        paymentTypeSelect.addEventListener("change", function () {
            const type = this.value;
            const cardFields = document.getElementById("cardFields");
            const walletFields = document.getElementById("walletFields");

            // Inputs
            const cardInputs = cardFields.querySelectorAll("input, select");
            const walletInputs = walletFields.querySelectorAll("input, select");

            if (type === "card") {
                cardFields.classList.remove("d-none");
                walletFields.classList.add("d-none");
                cardInputs.forEach((el) => (el.disabled = false));
                walletInputs.forEach((el) => (el.disabled = true));
            } else {
                cardFields.classList.add("d-none");
                walletFields.classList.remove("d-none");
                cardInputs.forEach((el) => (el.disabled = true));
                walletInputs.forEach((el) => (el.disabled = false));
            }
        });
    }
});
