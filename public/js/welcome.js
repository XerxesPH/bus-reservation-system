/* ==============================================
   WELCOME.JS - Welcome/Home Page Scripts
   ============================================== */

function toggleLayout(isRoundTrip) {
    const returnRow = document.getElementById("returnRow");
    const retOrigin = document.querySelector('select[name="return_origin"]');
    const retDest = document.querySelector('select[name="return_destination"]');
    const retDate = document.querySelector('input[name="return_date"]');

    const onewayBtn = document.getElementById("onewayBtn");
    const roundtripBtn = document.getElementById("roundtripBtn");

    if (onewayBtn && roundtripBtn) {
        if (isRoundTrip) {
            roundtripBtn.classList.add("active");
            onewayBtn.classList.remove("active");
        } else {
            onewayBtn.classList.add("active");
            roundtripBtn.classList.remove("active");
        }
    }

    if (returnRow) {
        if (isRoundTrip) {
            returnRow.classList.remove("d-none");
        } else {
            returnRow.classList.add("d-none");
        }
    }

    if (isRoundTrip) {
        returnRow?.classList.add("animate__animated", "animate__fadeIn");
        retOrigin.setAttribute("required", "required");
        retDest.setAttribute("required", "required");
        retDate.setAttribute("required", "required");

        const origin = document.querySelector('select[name="origin"]');
        const destination = document.querySelector(
            'select[name="destination"]'
        );
        if (destination?.value) retOrigin.value = destination.value;
    } else {
        retOrigin.removeAttribute("required");
        retDest.removeAttribute("required");
        retDate.removeAttribute("required");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const bookingForm = document.getElementById("welcomeBookingForm");
    const openModalBtn = document.getElementById(
        "welcomeOpenPassengerModalBtn"
    );
    const passengerModalEl = document.getElementById("welcomePassengerModal");
    const passengerModalForm = document.getElementById(
        "welcomePassengerModalForm"
    );

    if (
        !bookingForm ||
        !openModalBtn ||
        !passengerModalEl ||
        !passengerModalForm
    )
        return;

    const hiddenAdults = document.getElementById("welcome-adults");
    const hiddenChildren = document.getElementById("welcome-children");
    const hiddenWholeBus = document.getElementById("welcome-whole-bus");
    const passengerLabel = document.getElementById("welcome-passenger-label");

    const modalAdults = document.getElementById("welcome-modal-adults");
    const modalChildren = document.getElementById("welcome-modal-children");
    const wholeBusToggle = document.getElementById("welcome-whole-bus-toggle");

    const passengerModal = new bootstrap.Modal(passengerModalEl);

    const originSelect = document.querySelector('select[name="origin"]');
    const destinationSelect = document.querySelector(
        'select[name="destination"]'
    );
    const retOriginSelect = document.querySelector(
        'select[name="return_origin"]'
    );
    const retDestSelect = document.querySelector(
        'select[name="return_destination"]'
    );

    let returnOriginManuallySet = false;
    let returnDestManuallySet = false;

    function isRoundTripSelected() {
        return !!document.querySelector(
            'input[name="trip_type"][value="roundtrip"]:checked'
        );
    }

    function syncReturnRoute() {
        if (!isRoundTripSelected()) return;
        if (
            destinationSelect?.value &&
            retOriginSelect &&
            (!returnOriginManuallySet || !retOriginSelect.value)
        ) {
            retOriginSelect.value = destinationSelect.value;
        }
    }

    if (retOriginSelect) {
        retOriginSelect.addEventListener("change", function () {
            returnOriginManuallySet = true;
        });
    }
    if (retDestSelect) {
        retDestSelect.addEventListener("change", function () {
            returnDestManuallySet = true;
        });
    }

    if (originSelect) originSelect.addEventListener("change", syncReturnRoute);
    if (destinationSelect)
        destinationSelect.addEventListener("change", syncReturnRoute);

    document.querySelectorAll('input[name="trip_type"]').forEach((radio) => {
        radio.addEventListener("change", function () {
            toggleLayout(this.value === "roundtrip");
            if (this.value === "roundtrip") {
                returnOriginManuallySet = false;
                returnDestManuallySet = false;
                syncReturnRoute();
            }
        });
    });

    function updatePassengerLabel() {
        if (!passengerLabel) return;
        const isWholeBus = hiddenWholeBus && hiddenWholeBus.value === "1";
        const a = parseInt(hiddenAdults?.value || "1", 10);
        const c = parseInt(hiddenChildren?.value || "0", 10);

        if (isWholeBus) {
            passengerLabel.innerText = "Whole Bus";
            return;
        }

        const parts = [];
        parts.push(`${a} Adult${a > 1 ? "s" : ""}`);
        if (c > 0) parts.push(`${c} Child${c > 1 ? "ren" : ""}`);
        passengerLabel.innerText = parts.join(" • ");
    }

    function syncModalFromHidden() {
        const isWholeBus = hiddenWholeBus && hiddenWholeBus.value === "1";
        const a = hiddenAdults?.value || "1";
        const c = hiddenChildren?.value || "0";

        modalAdults.value = a;
        modalChildren.value = c;
        wholeBusToggle.checked = isWholeBus;
        modalAdults.disabled = isWholeBus;
        modalChildren.disabled = isWholeBus;
    }

    function applyWholeBusToggleState() {
        const enabled = wholeBusToggle.checked;
        modalAdults.disabled = enabled;
        modalChildren.disabled = enabled;
        if (enabled) {
            modalAdults.value = "1";
            modalChildren.value = "0";
        }
    }

    // Open modal when user clicks Find Tickets.
    openModalBtn.addEventListener("click", function () {
        // Validate base fields first (origin/destination/date + return fields if roundtrip)
        if (!bookingForm.checkValidity()) {
            bookingForm.reportValidity();
            return;
        }

        syncModalFromHidden();
        passengerModal.show();
    });

    // Also keep modal synced if opened via the passenger trigger button.
    passengerModalEl.addEventListener("show.bs.modal", function () {
        syncModalFromHidden();
    });

    wholeBusToggle.addEventListener("change", function () {
        applyWholeBusToggleState();
    });

    passengerModalForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const isWholeBus = wholeBusToggle.checked;
        const a = parseInt(modalAdults.value || "1", 10);
        const c = parseInt(modalChildren.value || "0", 10);

        if (!isWholeBus && a < 1) return;

        hiddenWholeBus.value = isWholeBus ? "1" : "0";
        hiddenAdults.value = String(a);
        hiddenChildren.value = String(c);

        updatePassengerLabel();
        passengerModal.hide();
        bookingForm.submit();
    });

    updatePassengerLabel();
});
