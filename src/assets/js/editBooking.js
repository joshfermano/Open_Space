document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const checkInDate = document.querySelector('input[name="check_in_date"]');
  const checkOutDate = document.querySelector('input[name="check_out_date"]');
  const startTime = document.querySelector('input[name="start_time"]');
  const endTime = document.querySelector('input[name="end_time"]');

  // Update minimum check-out date when check-in date changes
  checkInDate.addEventListener('change', function () {
    checkOutDate.min = this.value;
    if (checkOutDate.value && checkOutDate.value < this.value) {
      checkOutDate.value = this.value;
    }
  });

  // Validate time when on same day
  startTime.addEventListener('change', function () {
    if (checkInDate.value === checkOutDate.value) {
      endTime.min = this.value;
      if (endTime.value && endTime.value <= this.value) {
        endTime.value = '';
      }
    }
  });

  // Form submission
  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerText = 'Saving...';

    try {
      const response = await fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
      });

      const data = await response.json();

      if (data.success) {
        window.location.href =
          '/openspace/src/pages/dashboard/bookings.php?success=Booking updated successfully';
      } else {
        alert(data.message || 'Failed to update booking');
        submitBtn.disabled = false;
        submitBtn.innerText = 'Save Changes';
      }
    } catch (error) {
      console.error('Error:', error);
      alert('An error occurred while updating the booking');
      submitBtn.disabled = false;
      submitBtn.innerText = 'Save Changes';
    }
  });
});
