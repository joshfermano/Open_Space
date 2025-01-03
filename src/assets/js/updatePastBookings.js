function checkPastBookings() {
  const approvedBookings = document.querySelectorAll(
    '[data-booking-status="approved"]'
  );
  let needsRefresh = false;

  approvedBookings.forEach((booking) => {
    const checkOutTime = new Date(booking.dataset.checkOut);
    if (checkOutTime < new Date()) {
      needsRefresh = true;
    }
  });

  if (needsRefresh) {
    fetch('/openspace/src/api/bookings/updatePastBookings.php')
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          window.location.reload();
        }
      })
      .catch((error) => console.error('Error:', error));
  }
}

setInterval(checkPastBookings, 300000);

document.addEventListener('DOMContentLoaded', () => {
  const approvedBookings = document.querySelectorAll(
    '[data-booking-status="approved"]'
  );
  if (approvedBookings.length > 0) {
    checkPastBookings();
  }
});
