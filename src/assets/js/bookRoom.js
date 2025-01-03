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
