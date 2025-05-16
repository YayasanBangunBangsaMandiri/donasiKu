/**
 * DonateHub - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Donation Amount Selection
    initDonationAmountSelection();
    
    // Flash message auto-dismiss
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible.fade.show');
        alerts.forEach(alert => {
            const alertInstance = new bootstrap.Alert(alert);
            alertInstance.close();
        });
    }, 5000);
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Scroll to top button
    const scrollTopBtn = document.getElementById('scroll-to-top');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.add('show');
            } else {
                scrollTopBtn.classList.remove('show');
            }
        });
        
        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Campaign search filter
    const campaignFilter = document.getElementById('campaign-filter-form');
    if (campaignFilter) {
        const categorySelect = document.getElementById('category_id');
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                campaignFilter.submit();
            });
        }
    }
    
    // Mobile Navigation
    const mobileNavToggle = document.getElementById('mobile-nav-toggle');
    if (mobileNavToggle) {
        mobileNavToggle.addEventListener('click', function() {
            document.body.classList.toggle('mobile-nav-active');
            this.classList.toggle('bi-list');
            this.classList.toggle('bi-x');
        });
    }
    
    // Copy to clipboard functionality
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-copy');
            navigator.clipboard.writeText(textToCopy).then(() => {
                // Change button text temporarily
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> Disalin!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    });
});

/**
 * Initialize donation amount selection
 */
function initDonationAmountSelection() {
    const donationAmountRadios = document.querySelectorAll('input[name="donation_amount_option"]');
    const customAmountContainer = document.querySelector('.custom-amount-container');
    const customAmountInput = document.getElementById('custom_amount');
    const finalAmountInput = document.getElementById('final_amount');
    
    if (donationAmountRadios.length > 0 && finalAmountInput) {
        // Update final amount when amount option is selected
        donationAmountRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customAmountContainer.style.display = 'block';
                    finalAmountInput.value = customAmountInput.value;
                } else {
                    customAmountContainer.style.display = 'none';
                    finalAmountInput.value = this.value;
                }
            });
        });
        
        // Update final amount when custom amount changes
        if (customAmountInput) {
            customAmountInput.addEventListener('input', function() {
                finalAmountInput.value = this.value;
            });
        }
        
        // Initial state
        const checkedAmountOption = document.querySelector('input[name="donation_amount_option"]:checked');
        if (checkedAmountOption) {
            if (checkedAmountOption.value === 'custom' && customAmountContainer) {
                customAmountContainer.style.display = 'block';
                finalAmountInput.value = customAmountInput ? customAmountInput.value : '';
            } else {
                if (customAmountContainer) {
                    customAmountContainer.style.display = 'none';
                }
                finalAmountInput.value = checkedAmountOption.value;
            }
        }
    }
}

/**
 * Format number as currency
 */
function formatCurrency(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number);
}

/**
 * Share campaign on social media
 */
function shareCampaign(platform, url, title) {
    let shareUrl = '';
    
    switch (platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(title + ' ' + url)}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    if (!navigator.clipboard) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            return true;
        } catch (err) {
            console.error('Failed to copy text: ', err);
            return false;
        } finally {
            document.body.removeChild(textArea);
        }
    }
    
    return navigator.clipboard.writeText(text)
        .then(() => true)
        .catch(err => {
            console.error('Failed to copy text: ', err);
            return false;
        });
}