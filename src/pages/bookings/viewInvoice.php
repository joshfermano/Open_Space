<?php
require_once '../../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: /openspace/src/pages/dashboard/bookings.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/openspace/src/assets/css/main.css" rel="stylesheet">
    <link rel="shortcut icon" href="/openspace/src/assets/img/logo_white.jpg" type="image/x-icon">
    <script src="https://kit.fontawesome.com/61b27e08e6.js" crossorigin="anonymous"></script>
    <title>Invoice - OpenSpace</title>
</head>

<body class="bg-gray-50 font-poppins">
    <?php include_once '../../components/navbar.php'; ?>

    <div class="max-w-3xl mx-auto p-8">
        <div id="invoiceContainer" class="bg-white shadow-lg rounded-xl p-8">
            <div class="flex justify-between items-start mb-8">
                <div class="flex items-center space-x-4">
                    <img class="w-[100px] h-[100px]" src="/openspace/src/assets/img/logo_black.jpg" alt="OpenSpace Logo" class="w-16">
                    <div>
                        <h1 class="text-2xl font-bold">INVOICE</h1>
                        <p id="invoiceNumber" class="text-gray-600"></p>
                    </div>
                </div>
                <p id="dateIssued" class="text-gray-600"></p>
            </div>

            <!-- Room Details -->
            <div class="mb-8 bg-white rounded-lg p-6">
                <div class="flex items-start space-x-6">
                    <div class="flex-shrink-0">
                        <img id="roomImage"
                            class="w-32 h-32 object-cover rounded-lg shadow-sm"
                            src=""
                            alt="Room Image">
                    </div>
                    <div class="flex-grow min-w-0">
                        <h2 id="roomTitle" class="text-2xl font-bold truncate mb-2"></h2>
                        <p id="roomLocation" class="text-gray-600 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span class="truncate"></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="border-t border-b py-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Booking Details</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Check In:</p>
                        <p id="checkIn" class="font-medium"></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Check Out:</p>
                        <p id="checkOut" class="font-medium"></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Duration:</p>
                        <p id="duration" class="font-medium"></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Rate:</p>
                        <p id="hourlyRate" class="font-medium"></p>
                    </div>
                </div>
            </div>

            <!-- Client and Owner Details -->
            <div class="grid grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Client</h3>
                    <p id="clientName" class="font-medium"></p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-2">Host</h3>
                    <p id="ownerName" class="font-medium"></p>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold mb-4">Payment Summary</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Base Price:</span>
                        <span id="basePrice"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Service Fee (10%):</span>
                        <span id="serviceFee"></span>
                    </div>
                    <div class="flex justify-between font-bold text-lg pt-2 border-t">
                        <span>Total Amount:</span>
                        <span id="totalAmount"></span>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-8">
                <button onclick="window.print()"
                    class="px-6 py-2 bg-black text-white rounded-full hover:bg-gray-800 transition duration-300">
                    Print Invoice
                </button>
                <div id="statusBadge" class="px-4 py-1 rounded-full text-white font-medium"></div>
            </div>
        </div>
    </div>

    <script src="/openspace/src/assets/js/viewInvoice.js"></script>
</body>

</html>