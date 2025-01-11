document.addEventListener('DOMContentLoaded', function () {
  const booking_id = new URLSearchParams(window.location.search).get('id');

  if (!booking_id) {
    alert('Invalid booking ID');
    window.location.href = '/openspace/src/pages/dashboard/bookings.php';
    return;
  }

  fetchInvoiceData(booking_id);
});

async function fetchInvoiceData(booking_id) {
  try {
    const response = await fetch(
      `/openspace/src/api/bookings/getInvoice.php?id=${booking_id}`
    );
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to load invoice');
    }

    if (data.success) {
      displayInvoice(data.invoice);
    } else {
      throw new Error(data.message || 'Failed to load invoice');
    }
  } catch (error) {
    console.error('Error:', error);
    alert(error.message);
    window.location.href = '/openspace/src/pages/dashboard/bookings.php';
  }
}

function displayInvoice(invoice) {
  // Header
  document.getElementById('invoiceNumber').textContent = invoice.invoice_id;
  document.getElementById('dateIssued').textContent = invoice.date_issued;

  // Room Details
  const roomImage = document.getElementById('roomImage');
  roomImage.src = invoice.room_details.image;
  roomImage.onerror = () => {
    roomImage.src = '/openspace/src/assets/img/logo_black.jpg';
  };
  document.getElementById('roomTitle').textContent = invoice.room_details.title;
  document.getElementById('roomLocation').textContent =
    invoice.room_details.location;

  // Booking Details
  document.getElementById('checkIn').textContent = formatDateTime(
    invoice.booking_details.check_in
  );
  document.getElementById('checkOut').textContent = formatDateTime(
    invoice.booking_details.check_out
  );
  document.getElementById('duration').textContent =
    invoice.booking_details.duration;
  document.getElementById(
    'hourlyRate'
  ).textContent = `₱${invoice.booking_details.hourly_rate}/hour`;

  // Client and Owner
  document.getElementById('clientName').textContent = invoice.client_name;
  document.getElementById('ownerName').textContent = invoice.owner_name;

  // Payment Details
  document.getElementById(
    'basePrice'
  ).textContent = `₱${invoice.payment_details.base_price.toFixed(2)}`;
  document.getElementById(
    'serviceFee'
  ).textContent = `₱${invoice.payment_details.service_fee.toFixed(2)}`;
  document.getElementById(
    'totalAmount'
  ).textContent = `₱${invoice.payment_details.total_amount.toFixed(2)}`;

  // Status Badge
  const statusBadge = document.getElementById('statusBadge');
  statusBadge.textContent =
    invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1);
  statusBadge.classList.add(
    invoice.status === 'completed' ? 'bg-green-600' : 'bg-blue-600'
  );
}

function formatDateTime(dateString) {
  const options = {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  };
  return new Date(dateString).toLocaleDateString('en-US', options);
}
