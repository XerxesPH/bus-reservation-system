/* ==============================================
   SEATS.JS - Seat Selection Scripts
   ============================================== */

let selectedSeats = [];
let adults = 0;
let children = 0;
let totalPax = 0;
let basePrice = 0;
let childPrice = 0;
let finalTotal = 0;
let isWholeBus = false;

// Auto-initialize on DOM ready by reading data attributes
document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("seat-selection");
    if (!container) return;

    // Read values from data attributes
    adults = parseInt(container.getAttribute("data-adults")) || 0;
    children = parseInt(container.getAttribute("data-children")) || 0;
    basePrice = parseFloat(container.getAttribute("data-base-price")) || 0;
    isWholeBus = container.getAttribute("data-whole-bus") === "1";

    totalPax = adults + children;
    childPrice = basePrice * 0.8;
    finalTotal = adults * basePrice + children * childPrice;

    // Init price display
    document.getElementById("total-price").innerText = formatPrice(finalTotal);

    if (isWholeBus) {
        // Auto-select every available seat and lock the grid.
        const availableButtons = Array.from(
            document.querySelectorAll(".seat-grid .seat-btn[data-seat]")
        ).filter((btn) => !btn.disabled);

        selectedSeats = availableButtons
            .map((btn) => btn.getAttribute("data-seat"))
            .filter(Boolean);

        availableButtons.forEach((btn) => {
            btn.classList.remove("btn-outline-primary");
            btn.classList.add("btn-warning");
            btn.setAttribute("disabled", "true");
        });

        updateSeatUI();
    }
});

function toggleSeat(button) {
    if (isWholeBus) return;
    const seatNum = button.getAttribute("data-seat");

    if (selectedSeats.includes(seatNum)) {
        // Deselect
        selectedSeats = selectedSeats.filter((s) => s !== seatNum);
        button.classList.remove("btn-warning");
        button.classList.add("btn-outline-primary");
    } else {
        // Select (Enforce Limit)
        if (selectedSeats.length >= totalPax) {
            alert(`You can only select ${totalPax} seat(s).`);
            return;
        }
        selectedSeats.push(seatNum);
        button.classList.remove("btn-outline-primary");
        button.classList.add("btn-warning");
    }
    updateSeatUI();
}

function updateSeatUI() {
    const count = selectedSeats.length;
    document.getElementById("display-seats").innerText =
        count > 0 ? selectedSeats.join(", ") : "-";

    // Update Hidden Input
    document.getElementById("input-seats").value =
        JSON.stringify(selectedSeats);

    // Enable Button only if exact number of seats selected
    const btn = document.getElementById("checkout-btn");
    const priceDisplay = document.getElementById("total-price");

    // Always show the target price
    priceDisplay.innerText = formatPrice(finalTotal);

    if (count === totalPax) {
        btn.removeAttribute("disabled");
    } else {
        btn.setAttribute("disabled", "true");
    }
}

function formatPrice(amount) {
    return amount.toLocaleString("en-US", { minimumFractionDigits: 2 });
}
