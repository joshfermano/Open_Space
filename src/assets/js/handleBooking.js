function handleBookingAction(booking_id, action) {
  const formData = new FormData();
  formData.append('booking_id', booking_id);

  const endpoint =
    action === 'approve'
      ? '/openspace/src/api/bookings/approveBooking.php'
      : '/openspace/src/api/bookings/rejectBooking.php';

  fetch(endpoint, {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || `Failed to ${action} booking`);
      }
    })
    .catch((error) => {
      console.error('Error:', error);
      alert(`An error occurred while ${action}ing the booking`);
    });
}
