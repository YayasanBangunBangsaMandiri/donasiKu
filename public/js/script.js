/**
 * DonateHub - Custom JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Animasi counter untuk statistik
    animateCounters();
    
    // Inisialisasi tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-dismiss untuk alert
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Form validasi
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Smooth scroll untuk anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Donasi custom amount toggle
    const customAmountCheckbox = document.getElementById('customAmount');
    const customAmountInput = document.getElementById('customAmountInput');
    const predefinedAmounts = document.querySelectorAll('input[name="amount"]');
    
    if (customAmountCheckbox && customAmountInput) {
        customAmountCheckbox.addEventListener('change', function() {
            if (this.checked) {
                customAmountInput.disabled = false;
                customAmountInput.focus();
                predefinedAmounts.forEach(input => {
                    input.checked = false;
                });
            } else {
                customAmountInput.disabled = true;
                customAmountInput.value = '';
            }
        });
    }
    
    // Validasi jumlah donasi
    const donationForm = document.getElementById('donationForm');
    if (donationForm) {
        donationForm.addEventListener('submit', function(e) {
            const minAmount = parseInt(donationForm.getAttribute('data-min-amount')) || 10000;
            const maxAmount = parseInt(donationForm.getAttribute('data-max-amount')) || 100000000;
            let amount = 0;
            
            if (customAmountCheckbox && customAmountCheckbox.checked) {
                amount = parseInt(customAmountInput.value.replace(/\D/g, '')) || 0;
            } else {
                const checkedAmount = document.querySelector('input[name="amount"]:checked');
                amount = checkedAmount ? parseInt(checkedAmount.value) : 0;
            }
            
            if (amount < minAmount) {
                e.preventDefault();
                alert(`Minimal donasi adalah Rp ${minAmount.toLocaleString('id-ID')}`);
                return false;
            }
            
            if (amount > maxAmount) {
                e.preventDefault();
                alert(`Maksimal donasi adalah Rp ${maxAmount.toLocaleString('id-ID')}`);
                return false;
            }
        });
    }
});

// Fungsi untuk animasi counter
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const value = counter.innerText;
        counter.innerText = '0';
        
        const updateCounter = () => {
            const target = parseInt(value.replace(/\D/g, ''));
            const c = parseInt(counter.innerText.replace(/\D/g, ''));
            const increment = target / 100;
            
            if (c < target) {
                counter.innerText = value.replace(/\d+/, Math.ceil(c + increment));
                setTimeout(updateCounter, 10);
            } else {
                counter.innerText = value;
            }
        };
        
        updateCounter();
    });
} 