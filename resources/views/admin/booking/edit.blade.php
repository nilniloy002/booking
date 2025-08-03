<x-admin>
    @section('title', 'Edit Booking')
    <div class="card">
        <div class="card-header">Edit Booking</div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <form action="{{ route('admin.booking.update', $booking) }}" method="POST" id="bookingForm">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" id="bookingDate" class="form-control" 
                           value="{{ $booking->date->format('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Time Slot</label>
                    <select name="time_slot_id" id="timeSlot" class="form-control" required>
                        <option value="">Select Time Slot</option>
                        @foreach($timeSlots as $timeSlot)
                            <option value="{{ $timeSlot->id }}" 
                                {{ $booking->time_slot_id == $timeSlot->id ? 'selected' : '' }}>
                                {{ $timeSlot->time_slot }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="std_id" id="studentId" class="form-control" 
                           value="{{ $booking->std_id }}" required>
                    <div id="studentMessage" class="mt-1">
                        @isset($student)
                            <span class="text-success">Valid student: <strong>{{ $student->std_name }}</strong></span>
                        @endisset
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Available Seats</label>
                    <div class="seat-container d-flex flex-wrap">
                        @for($i = 1; $i <= 15; $i++)
                            @php
                                $isBooked = in_array($i, $bookedSeats) && $i != $booking->seat;
                            @endphp
                            <div class="seat-option m-2" data-seat="{{ $i }}">
                                <input type="radio" name="seat" id="seat{{ $i }}" value="{{ $i }}" 
                                       class="d-none" {{ $isBooked ? 'disabled' : '' }}
                                       {{ $booking->seat == $i ? 'checked' : '' }}>
                                <label for="seat{{ $i }}" class="seat-label btn 
                                    {{ $booking->seat == $i ? 'selected' : '' }}
                                    {{ $isBooked ? 'booked' : 'available' }}">
                                    {{ $i }}
                                </label>
                            </div>
                        @endfor
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="on" {{ $booking->status === 'on' ? 'selected' : '' }}>On</option>
                        <option value="off" {{ $booking->status === 'off' ? 'selected' : '' }}>Off</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>

    @section('css')
    <style>
        .seat-label {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .seat-label.available {
            background-color: #28a745;
            color: white;
        }
        .seat-label.booked {
            background-color: #dc3545;
            color: white;
            cursor: not-allowed;
        }
        .seat-label.selected {
            background-color: #007bff;
            color: white;
        }
    </style>
    @endsection

    @section('js')
    <script>
        $(document).ready(function() {
            // Initialize with current booking data
            const initialDate = $('#bookingDate').val();
            const initialTimeSlot = $('#timeSlot').val();
            const initialStudentId = $('#studentId').val();
            
            if(initialDate && initialTimeSlot) {
                updateSeatAvailability(initialTimeSlot, initialDate);
            }
            
            // Check student booking on input
            $('#studentId').on('blur', function() {
                const studentId = $(this).val();
                const date = $('#bookingDate').val();
                const originalStudentId = '{{ $booking->std_id }}';
                
                if(studentId && date && studentId != initialStudentId) {
                    checkStudentBooking(studentId, date);
                }
                 if(studentId && studentId !== originalStudentId) {
                    checkStudentExists(studentId);
                }
            });
            
            // Check seat availability when time slot or date changes
            $('#timeSlot, #bookingDate').change(function() {
                const timeSlotId = $('#timeSlot').val();
                const date = $('#bookingDate').val();
                
                if(timeSlotId && date) {
                    updateSeatAvailability(timeSlotId, date);
                }
            });

             function checkStudentExists(studentId) {
                $.ajax({
                    url: '{{ route("admin.booking.check-student") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        std_id: studentId
                    },
                    success: function(response) {
                        if(response.exists) {
                            $('#studentMessage').html('Valid student: <strong>' + response.student_name + '</strong>')
                                              .removeClass('text-danger').addClass('text-success');
                        } else {
                            $('#studentMessage').text('Student ID not found in database')
                                              .removeClass('text-success').addClass('text-danger');
                        }
                    }
                });
            }
            
            function checkStudentBooking(studentId, date) {
                $.ajax({
                    url: '{{ route("admin.booking.check") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        std_id: studentId,
                        date: date,
                        exclude_id: '{{ $booking->id }}'
                    },
                    success: function(response) {
                        if(response.booked) {
                            $('#studentMessage').text('This student already has a booking for this date');
                        } else {
                            $('#studentMessage').text('');
                        }
                    }
                });
            }
            
            function updateSeatAvailability(timeSlotId, date) {
                $.ajax({
                    url: '{{ route("admin.booking.seats") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        time_slot_id: timeSlotId,
                        date: date,
                        exclude_id: '{{ $booking->id }}'
                    },
                    success: function(response) {
                        $('.seat-label').removeClass('booked available selected');
                        
                        response.bookedSeats.forEach(seat => {
                            const seatElement = $(`.seat-label[for="seat${seat}"]`);
                            seatElement.addClass('booked');
                            
                            // Don't disable if it's the current booking's seat
                            if(seat != {{ $booking->seat }}) {
                                seatElement.prev('input').prop('disabled', true);
                            }
                        });
                        
                        response.availableSeats.forEach(seat => {
                            $(`.seat-label[for="seat${seat}"]`)
                                .addClass('available')
                                .prev('input').prop('disabled', false);
                        });
                        
                        // Re-apply selected class to current seat
                        $(`#seat{{ $booking->seat }}`).next('.seat-label').addClass('selected');
                    }
                });
            }
            
            // Seat selection
            $('.seat-label').click(function() {
                if(!$(this).hasClass('booked')) {
                    $('.seat-label').removeClass('selected');
                    $(this).addClass('selected');
                }
            });
        });
    </script>
    @endsection
</x-admin>