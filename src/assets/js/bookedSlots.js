document.addEventListener('DOMContentLoaded', function () {
  const roomId = document.querySelector('input[name="room_id"]').value;
  const bookedSlotsDiv = document.getElementById('bookedSlots');
  const checkInDate = document.querySelector('input[name="check_in_date"]');

  async function fetchAndDisplayBookedSlots(month) {
    try {
      const response = await fetch(
        `/openspace/src/api/bookings/getBookedSlots.php?room_id=${roomId}&month=${month}`
      );
      const data = await response.json();

      if (data.success) {
        displayBookedSlots(data.bookedSlots);
      } else {
        bookedSlotsDiv.innerHTML =
          '<p class="text-red-500">Error loading availability</p>';
      }
    } catch (error) {
      console.error('Error:', error);
      bookedSlotsDiv.innerHTML =
        '<p class="text-red-500">Error loading availability</p>';
    }
  }

  function displayBookedSlots(slots) {
    const dates = Object.keys(slots);

    if (dates.length === 0) {
      bookedSlotsDiv.innerHTML =
        '<p class="text-green-500">All slots available</p>';
      return;
    }

    let html = '<div class="space-y-3">';

    // Sort dates chronologically
    dates.sort((a, b) => new Date(a) - new Date(b));

    dates.forEach((date) => {
      const bookingsByDate = groupBookingsByDate(slots[date]);

      bookingsByDate.forEach((booking) => {
        const startDate = new Date(booking.startDateTime);
        const endDate = new Date(booking.endDateTime);

        html += `
                    <div class="border-l-4 border-red-500 pl-3 py-2">
                        <div class="font-medium">${formatDate(startDate)}</div>
                        ${
                          startDate.toDateString() !== endDate.toDateString()
                            ? `<div class="text-xs text-gray-500">to ${formatDate(
                                endDate
                              )}</div>`
                            : ''
                        }
                        <div class="text-sm text-gray-600">
                            ${formatTime(startDate)} - ${formatTime(endDate)}
                        </div>
                    </div>
                `;
      });
    });

    html += '</div>';
    bookedSlotsDiv.innerHTML = html;
  }

  function groupBookingsByDate(dateSlots) {
    return dateSlots.map((slot) => ({
      startDateTime: slot.start_datetime,
      endDateTime: slot.end_datetime,
    }));
  }

  function formatDate(date) {
    return date.toLocaleDateString('en-US', {
      weekday: 'short',
      month: 'short',
      day: 'numeric',
    });
  }

  function formatTime(date) {
    return date.toLocaleTimeString('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true,
    });
  }

  // Initial load
  fetchAndDisplayBookedSlots(new Date().toISOString().slice(0, 7));

  // Update when date changes
  checkInDate.addEventListener('change', function () {
    const month = this.value.slice(0, 7);
    fetchAndDisplayBookedSlots(month);
  });
});
