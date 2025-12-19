/* ==============================================
   CHECKOUT.JS - Checkout Page Scripts
   ============================================== */

document.addEventListener("DOMContentLoaded", function () {
    const useNewMethodRadio = document.getElementById("use_new_method");
    const savedMethodRadios = document.querySelectorAll(
        'input[name="payment_method"][id^="saved_"]'
    );
    const newMethodSection = document.getElementById("new-method-section");

    const form = document.getElementById("checkout-form");

    const cardNumberInput = document.querySelector('input[name="card_number"]');
    const expiryMonthInput = document.querySelector(
        'input[name="card_expiry_month"]'
    );

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

    if (cardNumberInput) {
        const onCardInput = () => {
            cardNumberInput.value = formatCardNumber(cardNumberInput.value);
        };
        cardNumberInput.addEventListener("input", onCardInput);
        cardNumberInput.addEventListener("blur", onCardInput);
    }

    if (expiryMonthInput) {
        expiryMonthInput.addEventListener("input", () => {
            const digits = expiryMonthInput.value
                .replace(/\D/g, "")
                .slice(0, 2);
            if (!digits) {
                expiryMonthInput.value = "";
                return;
            }
            expiryMonthInput.value = clampInt(digits, 1, 12);
        });

        expiryMonthInput.addEventListener("blur", () => {
            const digits = expiryMonthInput.value
                .replace(/\D/g, "")
                .slice(0, 2);
            if (!digits) return;
            const clamped = clampInt(digits, 1, 12);
            expiryMonthInput.value = clamped.padStart(2, "0");
        });
    }

    // Clamp any "day" fields (if present) to 1-31
    const dayInputs = Array.from(
        document.querySelectorAll(
            'input[name*="day" i], input[placeholder="DD"], input[placeholder="dd"]'
        )
    );
    dayInputs.forEach((input) => {
        input.addEventListener("input", () => {
            const digits = input.value.replace(/\D/g, "").slice(0, 2);
            if (!digits) {
                input.value = "";
                return;
            }
            input.value = clampInt(digits, 1, 31);
        });
    });

    // Toggle New Method Section
    function toggleNewMethod(show) {
        if (show) {
            newMethodSection.classList.remove("d-none");
            newMethodSection.classList.add(
                "animate__animated",
                "animate__fadeIn"
            );
        } else {
            newMethodSection.classList.add("d-none");
        }
    }

    if (useNewMethodRadio) {
        useNewMethodRadio.addEventListener("change", function () {
            if (this.checked) toggleNewMethod(true);
        });
    }

    savedMethodRadios.forEach((radio) => {
        radio.addEventListener("change", function () {
            if (this.checked) toggleNewMethod(false);
        });
    });

    // Toggle Card vs E-Wallet fields
    const typeCard = document.getElementById("type_card");
    const typeWallet = document.getElementById("type_ewallet");
    const cardFields = document.getElementById("card-fields");
    const walletFields = document.getElementById("ewallet-fields");

    function toggleFields() {
        if (typeCard && typeCard.checked) {
            cardFields.classList.remove("d-none");
            walletFields.classList.add("d-none");
        } else {
            cardFields.classList.add("d-none");
            walletFields.classList.remove("d-none");
        }
    }

    if (typeCard) typeCard.addEventListener("change", toggleFields);
    if (typeWallet) typeWallet.addEventListener("change", toggleFields);

    // Intercept Form Submit to handle "New Method" value
    if (form) {
        form.addEventListener("submit", function (e) {
            if (cardNumberInput) {
                cardNumberInput.value = stripToCardDigits(
                    cardNumberInput.value
                );
            }

            if (expiryMonthInput) {
                const digits = expiryMonthInput.value
                    .replace(/\D/g, "")
                    .slice(0, 2);
                if (digits) {
                    expiryMonthInput.value = clampInt(digits, 1, 12);
                }
            }

            const isNewSelected = useNewMethodRadio
                ? useNewMethodRadio.checked
                : true;

            if (isNewSelected) {
                if (useNewMethodRadio) {
                    useNewMethodRadio.disabled = true;
                }

                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "payment_method";
                hiddenInput.value =
                    typeCard && typeCard.checked ? "Credit Card" : "GCash";
                form.appendChild(hiddenInput);
            }
        });
    }
});
