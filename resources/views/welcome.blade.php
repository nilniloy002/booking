<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>STS Institute | IELTS Computer Lab Booking System - Reserve Your Seat Online</title>
    
    <!-- Primary Meta Tags -->
    <meta name="title" content="STS Institute | IELTS Computer Lab Booking System - Reserve Your Seat Online">
    <meta name="description" content="Book your IELTS computer lab session at STS Institute. Reserve seats online for convenient practice and testing sessions with our state-of-the-art facilities.">
    <meta name="keywords" content="IELTS, computer lab, STS Institute, seat booking, test preparation, English test, IELTS practice, lab reservation">
    <meta name="author" content="STS Institute">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://booking.sts.institute">
    <meta property="og:title" content="STS Institute | IELTS Computer Lab Booking System">
    <meta property="og:description" content="Reserve your IELTS computer lab session online at STS Institute">
    <meta property="og:image" content="https://cdielts.sts.institute/wp-content/uploads/2025/07/cropped-favicon-01.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://booking.sts.institute">
    <meta property="twitter:title" content="STS Institute | IELTS Computer Lab Booking System">
    <meta property="twitter:description" content="Reserve your IELTS computer lab session online at STS Institute">
    <meta property="twitter:image" content="https://cdielts.sts.institute/wp-content/uploads/2025/07/logo-01-300x162.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://cdielts.sts.institute/wp-content/uploads/2025/07/cropped-favicon-01.jpg">
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .seat {
            transition: all 0.2s ease;
            min-width: 40px;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            position: relative;
        }
        .seat.available:hover {
            transform: scale(1.05);
            box-shadow: 0 0 8px #f3802080;
        }
        .seat.booked {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .modal {
            transition: all 0.3s ease;
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .seat-map-container {
            min-height: 300px;
        }
        .date-btn.active {
            background-color: #ed2227;
            color: white;
            border-color: #ed2227;
        }
        .laptop-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 18px;
        }
        .seat-number {
            position: absolute;
            bottom: 2px;
            right: 2px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-50">
  <!-- Header -->
    <header class="bg-[#1C2A39] text-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col items-center justify-center">
                <img src="https://cdielts.sts.institute/wp-content/uploads/2025/07/logo-01-300x162.png" 
                     alt="CD-IELTS Logo" 
                     class="h-16 mb-2">
                <h1 class="text-2xl font-bold text-center">IELTS on Computer LAB Booking System</h1>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Book Your Seat</h2>
            
            <!-- Date Selection -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Select Date</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @php
                        $today = now();
                        $dates = [
                            $today->format('Y-m-d') => 'Today',
                            $today->addDay()->format('Y-m-d') => 'Tomorrow',
                            $today->addDay()->format('Y-m-d') => $today->format('l')
                        ];
                    @endphp
                    
                    @foreach($dates as $date => $label)
                        <button 
                            class="date-btn bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 px-4 rounded-lg border border-gray-300 transition-colors text-left flex flex-col"
                            data-date="{{ $date }}"
                        >
                            <span class="font-medium block">{{ $label }}</span>
                            <span class="text-sm text-black-bold">{{ \Carbon\Carbon::parse($date)->format('D, M j') }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            
            <!-- Time Slots and Seats -->
            <div id="timeSlotsContainer" class="hidden mb-8">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-semibold text-gray-700">Available Time Slots</h3>
                    <div id="selectedDateDisplay" class="text-sm text-gray-500"></div>
                </div>
                <div id="timeSlotsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6 seat-map-container">
                    <!-- Time slots will be loaded here -->
                </div>
            </div>
            
            <!-- Messages -->
            <div id="messageContainer" class="hidden mb-4 p-4 rounded-lg border-l-4"></div>
        </div>
    </main>

    <!-- Booking Modal -->
    <div id="bookingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4 modal">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Confirm Booking</h3>
                <button id="cancelBooking" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="bookSeatForm">
                @csrf
                <input type="hidden" id="modalDate" name="date">
                <input type="hidden" id="modalTimeSlot" name="time_slot_id">
                <input type="hidden" id="modalSeat" name="seat">
                
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span id="selectedSlotInfo" class="font-medium"></span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-900 rounded-full mr-2"></div>
                        <span id="selectedSeatInfo" class="font-medium"></span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="studentId" class="block text-gray-700 mb-2">Student ID <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        id="studentId" 
                        name="std_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                        placeholder="Enter your student ID"
                        autocomplete="off"
                    >
                    <div id="studentMessage" class="mt-1 text-sm"></div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        id="cancelBookingBtn" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        id="submitBtn"
                        class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-800 transition-colors flex items-center justify-center"
                    >
                        <span id="submitBtnText">Confirm Booking</span>
                    </button>
                </div>

                <div id="bookingStatusMessage" class="hidden mt-4 p-3 rounded-lg"></div>

            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} CD-IELTS LAB Booking System. All rights reserved.</p>
        </div>
    </footer>

    <script>
$(document).ready(function() {
    // Initialize with today's date
    const today = new Date().toISOString().split('T')[0];
    $('.date-btn[data-date="'+today+'"]').addClass('active');
    loadTimeSlots(today);
    
    // Date selection handler
    $('.date-btn').on('click', function() {
        const date = $(this).data('date');
        const formattedDate = new Date(date).toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Update UI
        $('.date-btn').removeClass('active bg-blue-900 text-white border-blue-900')
                      .addClass('bg-gray-100 text-gray-800 hover:bg-gray-200');
        $(this).removeClass('bg-gray-100 hover:bg-gray-200 text-gray-800')
               .addClass('active bg-blue-900 text-white border-blue-900');
        
        // Show selected date
        $('#selectedDateDisplay').text(formattedDate);
        
        // Load time slots
        loadTimeSlots(date);
        $('#timeSlotsContainer').removeClass('hidden');
    });
    
    function loadTimeSlots(date) {
        console.log('Loading slots for date:', date);

        // Show loading state
        $('#timeSlotsGrid').html(`
            <div class="col-span-full flex flex-col items-center justify-center py-8">
                <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500 mb-3"></div>
                <p class="text-gray-600">Loading available slots...</p>
            </div>
        `);

        $.ajax({
            url: '{{ route("booking.check-seat") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                date: date
            },
            success: function(response) {
                console.log('API response:', response);
                
                // Check for Friday/Saturday restriction
                if (response.message && response.message.includes('not available')) {
                    $('#timeSlotsGrid').html(`
                        <div class="col-span-full text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-times fa-2x mb-3"></i>
                            <p>${response.message}</p>
                            <p class="text-sm mt-2">Please select a different date.</p>
                        </div>
                    `);
                    return;
                }
                
                // Check if no time slots are available
                if (!response.timeSlots || response.timeSlots.length === 0) {
                    let message = response.message || 'No time slots available for selected date';
                    $('#timeSlotsGrid').html(`
                        <div class="col-span-full text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-times fa-2x mb-3"></i>
                            <p>${message}</p>
                        </div>
                    `);
                    return;
                }

                // Display available time slots
                let timeSlotsHtml = '';
                
                response.timeSlots.forEach(slot => {
                    timeSlotsHtml += `
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h3 class="font-medium text-lg mb-3 text-gray-800">
                                <i class="far fa-clock mr-2"></i>
                                ${slot.time_slot}
                            </h3>
                            <div class="grid grid-cols-5 gap-2">
                    `;
                    
                    // Generate 15 seats
                    for (let seat = 1; seat <= 15; seat++) {
                        const isBooked = slot.bookings.some(b => b.seat === seat);
                        const bookingInfo = slot.bookings.find(b => b.seat === seat);
                        
                        if (isBooked) {
                            timeSlotsHtml += `
                                <div class="seat booked bg-red-600 text-[#ffffff] rounded cursor-not-allowed relative"
                                     title="Booked by ${bookingInfo?.std_id || 'another student'}">
                                    <i class="fas fa-laptop laptop-icon"></i>
                                    <span class="seat-number">${seat}</span>
                                </div>
                            `;
                        } else {
                            timeSlotsHtml += `
                                <div class="seat available bg-green-500 text-white rounded cursor-pointer relative"
                                     data-seat="${seat}" 
                                     data-time-slot="${slot.id}"
                                     data-time-slot-name="${slot.time_slot}">
                                    <i class="fas fa-laptop laptop-icon"></i>
                                    <span class="seat-number">${seat}</span>
                                </div>
                            `;
                        }
                    }
                    
                    timeSlotsHtml += `
                            </div>
                            <div class="mt-2 text-sm text-gray-600">
                                Available seats: ${slot.available_seats}/15
                            </div>
                        </div>
                    `;
                });
                
                $('#timeSlotsGrid').html(timeSlotsHtml);
                
                // Rebind click events for available seats
                $('.seat.available').on('click', function() {
                    const timeSlotId = $(this).data('time-slot');
                    const timeSlotName = $(this).data('time-slot-name');
                    const seat = $(this).data('seat');
                    const date = $('.date-btn.active').data('date');
                    const formattedDate = new Date(date).toLocaleDateString('en-US', { 
                        weekday: 'short', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                    
                    $('#modalDate').val(date);
                    $('#modalTimeSlot').val(timeSlotId);
                    $('#modalSeat').val(seat);
                    $('#selectedSlotInfo').text(`${timeSlotName} • ${formattedDate}`);
                    $('#selectedSeatInfo').text(`Seat ${seat}`);
                    $('#studentId').val('').focus();
                    $('#studentMessage').text('').removeClass('text-green-600 text-red-600');
                    $('#bookingModal').removeClass('hidden');
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr.responseText);
                
                $('#timeSlotsGrid').html(`
                    <div class="col-span-full text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                        <p>Error loading time slots. Please try again.</p>
                        <button onclick="loadTimeSlots('${date}')" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Retry
                        </button>
                    </div>
                `);
            }
        });
    }
    
    // Student ID validation
    $('#studentId').on('blur', function() {
        const studentId = $(this).val().trim();
        if (studentId) {
            checkStudentExists(studentId);
        } else {
            $('#studentMessage').text('').removeClass('text-green-600 text-red-600');
        }
    });
    
    function checkStudentExists(studentId) {
        $('#studentMessage').text('Checking...').removeClass('text-green-600 text-red-600').addClass('text-gray-600');
        
        $.ajax({
            url: '{{ route("booking.check-student") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                std_id: studentId
            },
            success: function(data) {
                if (data.exists) {
                    $('#studentMessage').text(`Valid student: ${data.student_name}`)
                                      .removeClass('text-gray-600 text-red-600')
                                      .addClass('text-green-600');
                } else {
                    $('#studentMessage').text('Student ID not found in database')
                                      .removeClass('text-gray-600 text-green-600')
                                      .addClass('text-red-600');
                }
            },
            error: function() {
                $('#studentMessage').text('Error checking student ID')
                                  .removeClass('text-gray-600 text-green-600')
                                  .addClass('text-red-600');
            }
        });
    }
    
    // Modal controls
    $('#cancelBooking, #cancelBookingBtn').on('click', function() {
        $('#bookingModal').addClass('hidden');
    });
    
    // Close modal when clicking outside
    $('#bookingModal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden');
        }
    });
    
    // Form submission
    // $('#bookSeatForm').on('submit', function(e) {
    //     e.preventDefault();
        
    //     const submitBtn = $('#submitBtn');
    //     const originalBtnText = submitBtn.html();
        
    //     submitBtn.prop('disabled', true);
    //     submitBtn.html(`
    //         <i class="fas fa-spinner animate-spin mr-2"></i>
    //         Processing...
    //     `);
        
    //     $.ajax({
    //         url: '{{ route("booking.book-seat") }}',
    //         method: 'POST',
    //         data: $(this).serialize(),
    //         success: function(response) {
    //             $('#messageContainer').removeClass('hidden bg-red-100 border-red-500 text-red-700')
    //                                 .addClass('bg-green-100 border-l-4 border-green-500 text-green-700')
    //                                 .html(`
    //                                     <div class="flex items-center">
    //                                         <i class="fas fa-check-circle mr-2"></i>
    //                                         <p>${response.success}</p>
    //                                     </div>
    //                                 `);
                
    //             $('#bookingModal').addClass('hidden');
                
    //             // Refresh the slots after booking
    //             loadTimeSlots($('#modalDate').val());
    //         },
    //         error: function(xhr) {
    //             let errorMessage = 'You already booked on this date';
    //             if (xhr.responseJSON && xhr.responseJSON.error) {
    //                 errorMessage = xhr.responseJSON.error;
    //             }
                
    //             $('#messageContainer').removeClass('hidden bg-green-100 border-green-500 text-green-700')
    //                                 .addClass('bg-red-100 border-l-4 border-red-500 text-red-700')
    //                                 .html(`
    //                                     <div class="flex items-center">
    //                                         <i class="fas fa-exclamation-circle mr-2"></i>
    //                                         <p>${errorMessage}</p>
    //                                     </div>
    //                                 `);
    //         },
    //         complete: function() {
    //             submitBtn.prop('disabled', false).html(originalBtnText);
    //         }
    //     });
    // });

    // Form submission
$('#bookSeatForm').on('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = $('#submitBtn');
    const originalBtnText = submitBtn.html();
    const statusMessage = $('#bookingStatusMessage');
    
    submitBtn.prop('disabled', true);
    submitBtn.html(`
        <i class="fas fa-spinner animate-spin mr-2"></i>
        Processing...
    `);
    statusMessage.addClass('hidden').removeClass('bg-green-100 border-green-500 text-green-700 bg-red-100 border-red-500 text-red-700');
    
    $.ajax({
        url: '{{ route("booking.book-seat") }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            statusMessage.removeClass('hidden')
                .addClass('bg-green-100 border-l-4 border-green-500 text-green-700')
                .html(`
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <p>${response.success}</p>
                    </div>
                `);
            
            // Clear form and close modal after 2 seconds
            setTimeout(() => {
                $('#bookingModal').addClass('hidden');
                $('#studentId').val('');
                statusMessage.addClass('hidden');
                
                // Refresh the slots after booking
                loadTimeSlots($('#modalDate').val());
            }, 2000);
        },
        error: function(xhr) {
            let errorMessage = 'You already have a booking for this date.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            
            statusMessage.removeClass('hidden')
                .addClass('bg-red-100 border-l-4 border-red-500 text-red-700')
                .html(`
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p>${errorMessage}</p>
                    </div>
                `);
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
});
});
    </script>
</body>
</html>