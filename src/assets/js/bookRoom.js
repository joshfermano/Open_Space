function confirmCancel(booking_id) {
  if (confirm('Are you sure you want to cancel this booking?')) {
    const formData = new FormData();
    formData.append('booking_id', booking_id);

    fetch('/openspace/src/api/bookings/cancelBooking.php', {
      method: 'POST',
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          window.location.reload();
        } else {
          alert(data.message || 'Failed to cancel booking');
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred while cancelling the booking');
      });
  }
}

// Add function to periodically check cancellation availability
function updateCancellationAvailability() {
  const approvedBookings = document.querySelectorAll(
    '[data-booking-status="approved"]'
  );
  approvedBookings.forEach((booking) => {
    const checkInTime = new Date(
      booking.querySelector('[data-check-in]').dataset.checkIn
    );
    const oneHourBefore = new Date(checkInTime.getTime() - 3600000);
    const cancelButton = booking.querySelector(
      'button[onclick^="confirmCancel"]'
    );

    if (cancelButton && new Date() >= oneHourBefore) {
      cancelButton.remove();
      const statusDiv = booking.querySelector('.space-y-4.text-right div');
      if (!statusDiv.querySelector('.text-red-500')) {
        statusDiv.insertAdjacentHTML(
          'beforeend',
          '<p class="text-sm text-red-500">Cannot cancel within 1 hour of check-in</p>'
        );
      }
    }
  });
}

// Check every minute
setInterval(updateCancellationAvailability, 60000);

// Initial check
document.addEventListener('DOMContentLoaded', updateCancellationAvailability);
