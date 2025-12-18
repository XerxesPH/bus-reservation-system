/* ==============================================
   CHECKOUT.JS - Checkout Page Scripts
   ============================================== */

document.addEventListener("DOMContentLoaded", function () {
    const useNewMethodRadio = document.getElementById("use_new_method");
    const savedMethodRadios = document.querySelectorAll(
        'input[name="payment_method"][id^="saved_"]'
    );
    const newMethodSection = document.getElementById("new-method-section");

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
    const form = document.getElementById("checkout-form");

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
