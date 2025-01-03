document.addEventListener('DOMContentLoaded', function () {
  // Form elements
  const form = document.querySelector('form');
  const checkInDate = document.querySelector('input[name="check_in_date"]');
  const checkOutDate = document.querySelector('input[name="check_out_date"]');
  const startTimeInput = document.querySelector('input[name="start_time"]');
  const endTimeInput = document.querySelector('input[name="end_time"]');
  const roomId = document.querySelector('input[name="room_id"]').value;

  // Price elements
  const priceText = document.querySelector('.text-2xl.font-bold').textContent;
  const pricePerHour = priceText.includes('Free')
    ? 0
    : parseFloat(priceText.replace('₱', '').replace(',', ''));
  const priceCalculator = document.getElementById('priceCalculator');
  const basePriceElement = document.getElementById('basePrice');
  const serviceFeeElement = document.getElementById('serviceFee');
  const totalPriceElement = document.getElementById('totalPrice');

  // Calculate price function
  function calculatePrice() {
    if (
      checkInDate.value &&
      checkOutDate.value &&
      startTimeInput.value &&
      endTimeInput.value
    ) {
      const start = new Date(`${checkInDate.value} ${startTimeInput.value}`);
      const end = new Date(`${checkOutDate.value} ${endTimeInput.value}`);

      const diffInHours = (end - start) / (1000 * 60 * 60);

      if (diffInHours <= 0) {
        alert('End time must be after start time');
        endTimeInput.value = '';
        return;
      }

      // Show calculator for all bookings, even if free
      priceCalculator.classList.remove('hidden');

      if (pricePerHour > 0) {
        const basePrice = pricePerHour * diffInHours;
        const serviceFee = basePrice * 0.1;
        const totalPrice = basePrice + serviceFee;

        if (basePriceElement)
          basePriceElement.textContent = basePrice.toFixed(2);
        if (serviceFeeElement)
          serviceFeeElement.textContent = serviceFee.toFixed(2);
        if (totalPriceElement)
          totalPriceElement.textContent = totalPrice.toFixed(2);
      }
    }
  }

  checkInDate.addEventListener('change', calculatePrice);
  checkOutDate.addEventListener('change', calculatePrice);
  startTimeInput.addEventListener('change', calculatePrice);
  endTimeInput.addEventListener('change', calculatePrice);
});
