// Reservation Form Functionality
document.addEventListener('DOMContentLoaded', function() {
    const reservationForm = document.getElementById('reservation-form');
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    const partySizeSelect = document.getElementById('party-size');
    
    // Set minimum date to today
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        
        // Set default date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.value = tomorrow.toISOString().split('T')[0];
    }
    
    // Time slot management
    const availableTimes = {
        '17:30': '5:30 PM',
        '18:00': '6:00 PM',
        '18:30': '6:30 PM',
        '19:00': '7:00 PM',
        '19:30': '7:30 PM',
        '20:00': '8:00 PM',
        '20:30': '8:30 PM',
        '21:00': '9:00 PM'
    };
    
    // Populate time slots
    if (timeSelect) {
        timeSelect.innerHTML = '<option value="">Select Time</option>';
        Object.entries(availableTimes).forEach(([value, text]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = text;
            timeSelect.appendChild(option);
        });
    }
    
    // Party size limitations
    if (partySizeSelect) {
        partySizeSelect.innerHTML = '<option value="">Number of Guests</option>';
        for (let i = 1; i <= 14; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = i === 1 ? '1 Person' : `${i} People`;
            partySizeSelect.appendChild(option);
        }
    }
    
    // Form submission
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Collect form data
            const formData = {
                date: dateInput.value,
                time: timeSelect.value,
                partySize: partySizeSelect.value,
                specialRequests: document.getElementById('special-requests').value,
                timestamp: new Date().toISOString()
            };
            
            // Validate
            if (!formData.date || !formData.time || !formData.partySize) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Check party size
            if (parseInt(formData.partySize) > 14) {
                alert('For parties larger than 14, please call us at (502) 452-9244');
                return;
            }
            
            // Check if date is in Derby week (example logic)
            const selectedDate = new Date(formData.date);
            const derbyWeekStart = new Date('2024-05-01'); // Example Derby week
            const derbyWeekEnd = new Date('2024-05-07');
            
            if (selectedDate >= derbyWeekStart && selectedDate <= derbyWeekEnd) {
                alert('For Kentucky Derby week reservations, please call us at (502) 452-9244');
                return;
            }
            
            // Simulate API call to OpenTable
            simulateReservation(formData);
        });
    }
    
    function simulateReservation(data) {
        // Show loading state
        const submitBtn = reservationForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Checking Availability...';
        submitBtn.disabled = true;
        
        // Simulate API delay
        setTimeout(() => {
            // Mock response
            const isAvailable = Math.random() > 0.3; // 70% chance of availability
            
            if (isAvailable) {
                // Redirect to OpenTable (or show success modal)
                const openTableUrl = `https://www.opentable.com/restref/client/?rid=123456&date=${data.date}&time=${data.time}&covers=${data.partySize}`;
                window.open(openTableUrl, '_blank');
            } else {
                alert('Sorry, that time is not available. Please try a different time or date, or call us at (502) 452-9244 for assistance.');
            }
            
            // Reset button
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }, 1500);
    }
    
    // Real-time availability indicator
    function updateAvailabilityIndicator() {
        // This would typically call an API
        const indicator = document.createElement('div');
        indicator.className = 'availability-indicator';
        indicator.innerHTML = `
            <i class="fas fa-circle" style="color: #28a745;"></i>
            <span>Good availability for ${partySizeSelect.value} on ${dateInput.value}</span>
        `;
        
        const existingIndicator = document.querySelector('.availability-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }
        
        if (dateInput.value && partySizeSelect.value) {
            reservationForm.parentNode.insertBefore(indicator, reservationForm.nextSibling);
        }
    }
    
    // Add event listeners for real-time updates
    if (dateInput) dateInput.addEventListener('change', updateAvailabilityIndicator);
    if (partySizeSelect) partySizeSelect.addEventListener('change', updateAvailabilityIndicator);
});
