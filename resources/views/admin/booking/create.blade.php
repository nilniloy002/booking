<x-admin>
    @section('title', 'Create Booking')
    <div class="card">
        <div class="card-header">Create Booking</div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <form action="{{ route('admin.booking.store') }}" method="POST" id="bookingForm">
                @csrf
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" id="bookingDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Time Slot</label>
                    <select name="time_slot_id" id="timeSlot" class="form-control" required>
                        <option value="">Select Time Slot</option>
                        @foreach($timeSlots as $timeSlot)
                            <option value="{{ $timeSlot->id }}">{{ $timeSlot->time_slot }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="std_id" id="studentId" class="form-control" required>
                    <div id="studentMessage" class="mt-1"></div>
                </div>
                
                <div class="form-group">
                    <label>Available Seats</label>
                    <div class="seat-container d-flex flex-wrap">
                        @for($i = 1; $i <= 15; $i++)
                            <div class="seat-option m-2" data-seat="{{ $i }}">
                                <input type="radio" name="seat" id="seat{{ $i }}" value="{{ $i }}" class="d-none">
                                <label for="seat{{ $i }}" class="seat-label btn">{{ $i }}</label>
                            </div>
                        @endfor
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save</button>
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
            // Check student booking on input
            $('#studentId').on('blur', function() {
                const studentId = $(this).val();
                const date = $('#bookingDate').val();
                
                if(studentId && date) {
                    checkStudentBooking(studentId, date);
                }
                if(studentId) {
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
                        date: date
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
                        date: date
                    },
                    success: function(response) {
                        $('.seat-label').removeClass('booked available');
                        
                        response.bookedSeats.forEach(seat => {
                            $(`.seat-label[for="seat${seat}"]`)
                                .addClass('booked')
                                .prev('input').prop('disabled', true);
                        });
                        
                        response.availableSeats.forEach(seat => {
                            $(`.seat-label[for="seat${seat}"]`)
                                .addClass('available')
                                .prev('input').prop('disabled', false);
                        });
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