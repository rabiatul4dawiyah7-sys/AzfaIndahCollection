// ==========================================
// BOOKINGS FROM MYSQL
// ==========================================

let bookings = [];


// Load bookings from database
fetch("get_bookings.php")
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {

        bookings = data;

        console.log(
            "Bookings from database:",
            bookings
        );

        // Update slots if date is already selected
        updateTimeSlots();

    })
    .catch(function(error) {

        console.error(
            "Unable to load bookings:",
            error
        );

    });


// ==========================================
// CHECK IF TIME IS BOOKED
// ==========================================

function isTimeBooked(date, time) {

    return bookings.some(function(booking) {

        return booking.date === date &&
               booking.time === time;

    });

}


// ==========================================
// GET HTML ELEMENTS
// ==========================================

const bookingForm =
    document.getElementById("bookingForm");

const dateInput =
    document.getElementById("date");

const timeSlots =
    document.querySelectorAll(".time-slot");

const timeInput =
    document.getElementById("time");


// ==========================================
// UPDATE TIME SLOTS
// ==========================================

function updateTimeSlots() {

    if (!dateInput) {
        return;
    }

    const selectedDate = dateInput.value;

    if (!selectedDate) {
        return;
    }


    timeSlots.forEach(function(slot) {

        const selectedTime =
            slot.dataset.time;


        // Reset slot

        slot.classList.remove("booked");

        slot.classList.remove("selected");

        slot.disabled = false;


        // Check database

        if (
            isTimeBooked(
                selectedDate,
                selectedTime
            )
        ) {

            slot.classList.add("booked");

            slot.disabled = true;

        }

    });


    // Clear selected time

    timeInput.value = "";

}
// ==========================================
// PREVENT PAST DATES
// ==========================================

if (dateInput) {

    const today = new Date();

    const year = today.getFullYear();

    const month = String(
        today.getMonth() + 1
    ).padStart(2, "0");

    const day = String(
        today.getDate()
    ).padStart(2, "0");

    const todayString =
        year + "-" + month + "-" + day;

    dateInput.min = todayString;
}


// ==========================================
// DATE CHANGE
// ==========================================

if (dateInput) {

    dateInput.addEventListener(
        "change",
        function() {

            updateTimeSlots();

        }
    );

}


// ==========================================
// SELECT TIME SLOT
// ==========================================

timeSlots.forEach(function(slot) {

    slot.addEventListener(
        "click",
        function() {


            // Don't allow booked slot

            if (slot.disabled) {
                return;
            }


            // Remove previous selection

            timeSlots.forEach(function(item) {

                item.classList.remove(
                    "selected"
                );

            });


            // Select current slot

            slot.classList.add("selected");


            // Store selected time

            timeInput.value =
                slot.dataset.time;

        }
    );

});


// ==========================================
// FORM SUBMIT
// ==========================================

if (bookingForm) {

    bookingForm.addEventListener(
        "submit",
        function(event) {

            const date =
                dateInput.value;

            const time =
                timeInput.value;


            // Check time selected

            if (!time) {

                event.preventDefault();

                alert(
                    "Please select an available time slot."
                );

                return;

            }


            // Double-check booking

            if (
                isTimeBooked(
                    date,
                    time
                )
            ) {

                event.preventDefault();

                alert(
                    "Sorry! This time slot is already booked.\n\n" +
                    "Please choose another time."
                );

                updateTimeSlots();

                return;

            }

            // If available,
            // allow PHP to process the booking

        }
    );

}