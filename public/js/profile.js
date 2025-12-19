/* ==============================================
   PROFILE.JS - Profile Page Scripts
   ============================================== */

document.addEventListener("DOMContentLoaded", function () {
    const paymentTypeSelect = document.getElementById("paymentType");

    const profileCardNumberInput = document.getElementById(
        "profile_card_number"
    );
    const profileExpiryInput = document.getElementById("profile_expiry_date");
    const profileWalletInput = document.getElementById("profile_wallet_number");

    const avatarInput = document.getElementById("avatarInput");
    const avatarPreview = document.getElementById("avatarPreview");
    const avatarPlaceholder = document.getElementById("avatarPlaceholder");

    let avatarObjectUrl = null;

    if (avatarInput && avatarPreview && avatarPlaceholder) {
        avatarInput.addEventListener("change", function () {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }

            if (avatarObjectUrl) {
                URL.revokeObjectURL(avatarObjectUrl);
            }

            avatarObjectUrl = URL.createObjectURL(file);
            avatarPreview.src = avatarObjectUrl;
            avatarPreview.classList.remove("d-none");
            avatarPlaceholder.classList.add("d-none");
        });
    }

    const successToastEl = document.getElementById("profileSuccessToast");
    if (successToastEl && window.bootstrap && bootstrap.Toast) {
        const toast = new bootstrap.Toast(successToastEl);
        toast.show();
    }

    function clampInt(value, min, max) {
        const num = parseInt(value, 10);
        if (Number.isNaN(num)) return "";
        return String(Math.min(Math.max(num, min), max));
    }

    function formatCardNumber(value) {
        const digits = String(value || "")
            .replace(/\D/g, "")
            .slice(0, 16);
        return digits.replace(/(\d{4})(?=\d)/g, "$1 ");
    }

    function stripToCardDigits(value) {
        return String(value || "")
            .replace(/\D/g, "")
            .slice(0, 16);
    }

    function formatExpiry(value) {
        const digits = String(value || "")
            .replace(/\D/g, "")
            .slice(0, 4);
        if (!digits) return "";
        const mmRaw = digits.slice(0, 2);
        const yyRaw = digits.slice(2, 4);
        const mm = clampInt(mmRaw, 1, 12);
        if (!yyRaw) return mm;
        return `${mm.padStart(2, "0")}/${yyRaw}`;
    }

    if (profileCardNumberInput) {
        const onCardInput = () => {
            profileCardNumberInput.value = formatCardNumber(
                profileCardNumberInput.value
            );
        };
        profileCardNumberInput.addEventListener("input", onCardInput);
        profileCardNumberInput.addEventListener("blur", onCardInput);
    }

    if (profileExpiryInput) {
        const onExpiryInput = () => {
            profileExpiryInput.value = formatExpiry(profileExpiryInput.value);
        };
        profileExpiryInput.addEventListener("input", onExpiryInput);
        profileExpiryInput.addEventListener("blur", onExpiryInput);
    }

    if (profileWalletInput) {
        const onWalletInput = () => {
            profileWalletInput.value = String(profileWalletInput.value || "")
                .replace(/\D/g, "")
                .slice(0, 11);
        };
        profileWalletInput.addEventListener("input", onWalletInput);
        profileWalletInput.addEventListener("blur", onWalletInput);
    }

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

    const paymentModal = document.getElementById("addPaymentModal");
    if (paymentModal) {
        paymentModal.addEventListener("show.bs.modal", () => {
            if (paymentTypeSelect) {
                paymentTypeSelect.dispatchEvent(new Event("change"));
            }
        });

        const form = paymentModal.querySelector("form[action]");
        if (form) {
            form.addEventListener("submit", () => {
                if (
                    profileCardNumberInput &&
                    !profileCardNumberInput.disabled
                ) {
                    profileCardNumberInput.value = stripToCardDigits(
                        profileCardNumberInput.value
                    );
                }

                if (profileExpiryInput && !profileExpiryInput.disabled) {
                    const formatted = formatExpiry(profileExpiryInput.value);
                    profileExpiryInput.value = formatted;
                }
            });
        }
    }
});
