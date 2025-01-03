document.addEventListener('DOMContentLoaded', function () {
  const roomId = document.querySelector('input[name="room_id"]').value;
  const checkInDate = document.querySelector('input[name="check_in_date"]');
  const checkOutDate = document.querySelector('input[name="check_out_date"]');
  const startTime = document.querySelector('input[name="start_time"]');
  const endTime = document.querySelector('input[name="end_time"]');

  // Check availability when dates/times are selected
  [checkInDate, checkOutDate, startTime, endTime].forEach((input) => {
    input.addEventListener('change', checkAvailability);
  });

  function checkAvailability() {
    if (
      checkInDate.value &&
      checkOutDate.value &&
      startTime.value &&
      endTime.value
    ) {
      const params = new URLSearchParams({
        room_id: roomId,
        check_in: `${checkInDate.value} ${startTime.value}`,
        check_out: `${checkOutDate.value} ${endTime.value}`,
      });

      fetch(`/openspace/src/api/bookings/checkAvailability.php?${params}`)
        .then((response) => response.json())
        .then((data) => {
          if (!data.success) {
            alert(data.message);
            resetForm();
          } else if (!data.available) {
            alert('This time slot is not available');
            resetForm();
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          alert('Error checking availability');
        });
    }
  }

  function resetForm() {
    checkOutDate.value = '';
    endTime.value = '';
  }

  // Initialize date inputs
  const today = new Date().toISOString().split('T')[0];
  checkInDate.min = today;
  checkOutDate.min = today;

  checkInDate.addEventListener('change', function () {
    checkOutDate.min = this.value;
    if (checkOutDate.value && checkOutDate.value < this.value) {
      checkOutDate.value = this.value;
    }
  });

  // Time validation
  startTime.addEventListener('change', function () {
    if (checkInDate.value === checkOutDate.value) {
      const startHour = parseInt(this.value.split(':')[0]);
      endTime.min = this.value;
      if (endTime.value && endTime.value <= this.value) {
        endTime.value = '';
      }
    }
  });
});
