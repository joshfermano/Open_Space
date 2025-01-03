document.addEventListener('DOMContentLoaded', function () {
  const bookingForm = document.getElementById('bookingForm');

  if (bookingForm) {
    bookingForm.addEventListener('submit', async function (e) {
      e.preventDefault();

      try {
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';

        const response = await fetch(this.action, {
          method: 'POST',
          body: new FormData(this),
        });

        const data = await response.json();

        if (data.success) {
          window.location.href = data.redirect;
        } else {
          alert(data.message || 'Failed to create booking');
          submitButton.disabled = false;
          submitButton.textContent = 'Book Now';
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while creating the booking');
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = false;
        submitButton.textContent = 'Book Now';
      }
    });
  }
});
