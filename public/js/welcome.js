/* ==============================================
   WELCOME.JS - Welcome/Home Page Scripts
   ============================================== */

function toggleLayout(isRoundTrip) {
    const returnRow = document.getElementById("returnRow");
    const retOrigin = document.querySelector('select[name="return_origin"]');
    const retDest = document.querySelector('select[name="return_destination"]');
    const retDate = document.querySelector('input[name="return_date"]');

    returnRow.style.display = isRoundTrip ? "flex" : "none";

    if (isRoundTrip) {
        returnRow.classList.add("animate__animated", "animate__fadeIn");
        retOrigin.setAttribute("required", "required");
        retDest.setAttribute("required", "required");
        retDate.setAttribute("required", "required");
    } else {
        retOrigin.removeAttribute("required");
        retDest.removeAttribute("required");
        retDate.removeAttribute("required");
    }
}
