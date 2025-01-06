function ownerDeleteBooking(booking_id) {
  if (
    confirm(
      'Are you sure you want to permanently delete this booking? This action cannot be undone.'
    )
  ) {
    const formData = new FormData();
    formData.append('booking_id', booking_id);

    fetch('/openspace/src/api/bookings/ownerDeleteBooking.php', {
      method: 'POST',
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          window.location.reload();
        } else {
          alert(data.message || 'Failed to delete booking');
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred while deleting the booking');
      });
  }
}
