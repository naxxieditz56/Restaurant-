// Reservations Page Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker
    initDatePicker();
    
    // Populate time slots
    populateTimeSlots();
    
    // Form validation
    initReservationForm();
    
    // Real-time availability check
    initAvailabilityCheck();
    
    // Special requests character counter
    initCharacterCounter();
});

function initDatePicker() {
    const dateInput = document.getElementById('reservationDate');
    const today = new Date();
    const maxDate = new Date();
    maxDate.setDate(today.getDate() + 90); // 90 days in advance
    
    // Set min and max dates
    dateInput.min = today.toISOString().split('T')[0];
    dateInput.max = maxDate.toISOString().split('T')[0];
    
    // Set initial value to tomorrow
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    dateInput.value = tomorrow.toISOString().split('T')[0];
    
    // Disable weekends for lunch (Mon-Fri only)
    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const day = selectedDate.getDay();
        const timeSelect = document.getElementById('reservationTime');
        
        // Clear existing options
        while (timeSelect.options.length > 0) {
            timeSelect.remove(0);
        }
        
        // Add placeholder
        const placeholder = new Option('Select time', '');
        placeholder.disabled = true;
        placeholder.selected = true;
        timeSelect.add(placeholder);
        
        // Generate time slots based on day and meal time
        generateTimeSlots(selectedDate);
    });
}

function generateTimeSlots(date) {
    const timeSelect = document.getElementById('reservationDate');
    const day = date.getDay();
    const isWeekday = day >= 1 && day <= 5; // Monday to Friday
    
    // Dinner hours (every day)
    const dinnerStart = 17 * 60; // 5:00 PM in minutes
    const dinnerEnd = (day === 5 || day === 6) ? 23 * 60 : 22 * 60; // 11 PM Fri/Sat, 10 PM others
    const interval = 30; // 30 minute intervals
    
    // Generate dinner slots
    for (let time = dinnerStart; time <= dinnerEnd; time += interval) {
        const hours = Math.floor(time / 60);
        const minutes = time % 60;
        const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:00`;
        const displayTime = formatTime(hours, minutes);
        
        const option = new Option(displayTime, timeString);
        timeSelect.add(option);
    }
    
    // Add lunch slots for weekdays
    if (isWeekday) {
        const lunchStart = 11 * 60; // 11:00 AM
        const lunchEnd = 14 * 30; // 2:30 PM
        
        for (let time = lunchStart; time <= lunchEnd; time += interval) {
            const hours = Math.floor(time / 60);
            const minutes = time % 60;
            const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:00`;
            const displayTime = formatTime(hours, minutes);
            
            const option = new Option(`${displayTime} (Lunch)`, timeString);
            timeSelect.add(option);
        }
    }
}

function formatTime(hours, minutes) {
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 || 12;
    return `${displayHours}:${minutes.toString().padStart(2, '0')} ${period}`;
}

function populateTimeSlots() {
    const dateInput = document.getElementById('reservationDate');
    if (dateInput.value) {
        generateTimeSlots(new Date(dateInput.value));
    }
}

function initReservationForm() {
    const form = document.getElementById('reservationForm');
    const successMessage = document.getElementById('successMessage');
    const reservationIdSpan = document.getElementById('reservationId');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateReservationForm()) {
            // Simulate API call
            const formData = new FormData(form);
            const reservationData = Object.fromEntries(formData);
            
            // Generate reservation ID
            const reservationId = 'JF' + Date.now().toString().slice(-8);
            reservationIdSpan.textContent = reservationId;
            
            // Show success message
            form.style.display = 'none';
            successMessage.style.display = 'block';
            
            // Scroll to success message
            successMessage.scrollIntoView({ behavior: 'smooth' });
            
            // Log reservation (in real app, this would go to server)
            console.log('Reservation submitted:', {
                id: reservationId,
                ...reservationData,
                timestamp: new Date().toISOString()
            });
            
            // Send confirmation email (simulated)
            sendConfirmationEmail(reservationData, reservationId);
        }
    });
    
    // Real-time validation
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^\d\s\-()]/g, '');
    });
    
    // Party size validation
    const partySizeSelect = document.getElementById('partySize');
    partySizeSelect.addEventListener('change', function() {
        const value = this.value;
        if (value === '11-14') {
            showLargePartyWarning();
        }
    });
}

function validateReservationForm() {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    // Clear previous errors
    clearErrors();
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showError(field, 'This field is required');
            isValid = false;
        }
        
        // Email validation
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                showError(field, 'Please enter a valid email address');
                isValid = false;
            }
        }
        
        // Phone validation
        if (field.id === 'phone' && field.value) {
            const phoneRegex = /^[\d\s\-()]{10,}$/;
            const digits = field.value.replace(/\D/g, '');
            if (!phoneRegex.test(field.value) || digits.length < 10) {
                showError(field, 'Please enter a valid phone number');
                isValid = false;
            }
        }
        
        // Date validation
        if (field.id === 'reservationDate' && field.value) {
            const selectedDate = new Date(field.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                showError(field, 'Please select a future date');
                isValid = false;
            }
        }
    });
    
    // Check terms acceptance
    const termsCheckbox = document.getElementById('terms');
    if (!termsCheckbox.checked) {
        showError(termsCheckbox, 'You must agree to the reservation terms');
        isValid = false;
    }
    
    return isValid;
}

function showError(field, message) {
    field.classList.add('error');
    
    let errorElement = field.parentNode.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        field.parentNode.appendChild(errorElement);
    }
    errorElement.textContent = message;
}

function clearErrors() {
    document.querySelectorAll('.error').forEach(el => {
        el.classList.remove('error');
    });
    
    document.querySelectorAll('.error-message').forEach(el => {
        el.remove();
    });
}

function showLargePartyWarning() {
    const warningModal = document.createElement('div');
    warningModal.className = 'modal active';
    warningModal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Large Party Notice</h3>
                <button class="modal-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p>For parties of 11-14 people, please note:</p>
                <ul>
                    <li>We may need to split your party across adjacent tables</li>
                    <li>A 18% gratuity will be added to parties of 8 or more</li>
                    <li>Please arrive together as seating is coordinated</li>
                    <li>Consider calling us for optimal seating arrangements</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="this.parentElement.parentElement.parentElement.remove()">
                    I Understand
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(warningModal);
}

function initAvailabilityCheck() {
    const dateInput = document.getElementById('reservationDate');
    const timeSelect = document.getElementById('reservationTime');
    const partySizeSelect = document.getElementById('partySize');
    
    function checkAvailability() {
        if (dateInput.value && timeSelect.value && partySizeSelect.value) {
            // Show loading state
            timeSelect.disabled = true;
            const originalText = timeSelect.options[timeSelect.selectedIndex].text;
            timeSelect.options[timeSelect.selectedIndex].text = 'Checking availability...';
            
            // Simulate API call
            setTimeout(() => {
                // In real app, this would check against actual availability
                const isAvailable = Math.random() > 0.3; // 70% chance of availability
                
                timeSelect.disabled = false;
                timeSelect.options[timeSelect.selectedIndex].text = originalText;
                
                if (!isAvailable) {
                    showAvailabilityWarning();
                }
            }, 1000);
        }
    }
    
    dateInput.addEventListener('change', checkAvailability);
    timeSelect.addEventListener('change', checkAvailability);
    partySizeSelect.addEventListener('change', checkAvailability);
}

function showAvailabilityWarning() {
    const warning = document.createElement('div');
    warning.className = 'availability-warning';
    warning.innerHTML = `
        <i class="fas fa-clock"></i>
        <div>
            <strong>Limited Availability</strong>
            <p>This time slot is nearly booked. We recommend selecting an alternative time.</p>
        </div>
    `;
    
    const form = document.getElementById('reservationForm');
    form.parentNode.insertBefore(warning, form);
    
    // Auto-remove after 10 seconds
    setTimeout(() => warning.remove(), 10000);
}

function initCharacterCounter() {
    const textarea = document.getElementById('specialRequests');
    const counter = document.createElement('div');
    counter.className = 'char-counter';
    counter.textContent = '0/500';
    
    textarea.parentNode.appendChild(counter);
    
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        counter.textContent = `${length}/500`;
        
        if (length > 500) {
            counter.style.color = 'var(--danger-color)';
            this.value = this.value.substring(0, 500);
        } else if (length > 450) {
            counter.style.color = 'var(--warning-color)';
        } else {
            counter.style.color = 'var(--text-light)';
        }
    });
}

function sendConfirmationEmail(data, reservationId) {
    // In a real application, this would make an API call to send an email
    console.log('Sending confirmation email:', {
        to: data.email,
        subject: `Jack Fry's Reservation Confirmation #${reservationId}`,
        body: `Dear ${data.firstName},\n\nYour reservation for ${data.partySize} people on ${data.reservationDate} at ${data.reservationTime} has been confirmed.\n\nReservation ID: ${reservationId}\n\nWe look forward to serving you!\n\nJack Fry's Restaurant`
    });
}

function resetForm() {
    const form = document.getElementById('reservationForm');
    const successMessage = document.getElementById('successMessage');
    
    form.reset();
    form.style.display = 'block';
    successMessage.style.display = 'none';
    
    // Reset date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('reservationDate').value = tomorrow.toISOString().split('T')[0];
    
    // Reset time slots
    populateTimeSlots();
    
    // Scroll to form
    form.scrollIntoView({ behavior: 'smooth' });
}

// Export for use in other modules
window.validateReservationForm = validateReservationForm;
window.resetForm = resetForm;
