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
    
    // ----------------------------------------------
    // ADMIN DASHBOARD FUNCTIONALITY
    // ----------------------------------------------
    
    // Fix for potential infinite scroll issues in admin dashboard
    const adminContent = document.querySelector('main.admin-content');
    if (adminContent) {
        // Ensure admin content doesn't grow indefinitely
        adminContent.style.overflow = 'auto';
        
        // Handle scroll events - prevent excessive DOM operations during scroll
        let scrollTimeout;
        adminContent.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                // Only run scroll-related code after scrolling stops
                console.log('Scroll ended');
            }, 150);
        });
    }
    
    // CAMPAIGN ADMIN ACTIONS
    
    // Delete campaign action
    const deleteCampaignButtons = document.querySelectorAll('.delete-campaign');
    if (deleteCampaignButtons.length > 0) {
        console.log('Found delete campaign buttons:', deleteCampaignButtons.length);
        deleteCampaignButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const campaignId = this.getAttribute('data-id');
                const campaignTitle = this.getAttribute('data-title');
                console.log('Delete campaign clicked for ID:', campaignId);
                
                if (confirm(`Apakah Anda yakin ingin menghapus kampanye "${campaignTitle}"?`)) {
                    // Use form submission instead of direct navigation
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = BASE_URL + '/admin/deleteCampaign';
                    form.style.display = 'none';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'campaign_id';
                    input.value = campaignId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    
                    console.log('Submitting delete form for campaign ID:', campaignId);
                    form.submit();
                }
            });
        });
    } else {
        console.log('No delete campaign buttons found');
    }
    
    // Change campaign status action
    const changeCampaignStatusButtons = document.querySelectorAll('.change-campaign-status');
    if (changeCampaignStatusButtons.length > 0) {
        console.log('Found change status buttons:', changeCampaignStatusButtons.length);
        changeCampaignStatusButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const campaignId = this.getAttribute('data-id');
                const newStatus = this.getAttribute('data-status');
                const statusText = newStatus === 'active' ? 'aktif' : (newStatus === 'pending' ? 'pending' : 'nonaktif');
                console.log('Change status clicked for ID:', campaignId, 'New status:', newStatus);
                
                if (confirm(`Apakah Anda yakin ingin mengubah status kampanye menjadi "${statusText}"?`)) {
                    // Use form submission instead of direct navigation
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = BASE_URL + '/admin/update-campaign-status';
                    form.style.display = 'none';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'campaign_id';
                    idInput.value = campaignId;
                    
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.value = newStatus;
                    
                    form.appendChild(idInput);
                    form.appendChild(statusInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    } else {
        console.log('No change campaign status buttons found');
    }
    
    // DONATION ADMIN ACTIONS
    
    // Delete donation action
    const deleteDonationButtons = document.querySelectorAll('.delete-donation');
    if (deleteDonationButtons.length > 0) {
        console.log('Found delete donation buttons:', deleteDonationButtons.length);
        deleteDonationButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const donationId = this.getAttribute('data-id');
                console.log('Delete donation clicked for ID:', donationId);
                
                if (confirm('Apakah Anda yakin ingin menghapus donasi ini?')) {
                    // Use form submission instead of direct navigation
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = BASE_URL + '/admin/deleteDonation';
                    form.style.display = 'none';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'donation_id';
                    input.value = donationId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    console.log('Submitting delete form for donation ID:', donationId);
                    form.submit();
                }
            });
        });
    } else {
        console.log('No delete donation buttons found');
    }
    
    // Change donation status action
    const changeDonationStatusButtons = document.querySelectorAll('.change-donation-status');
    if (changeDonationStatusButtons.length > 0) {
        changeDonationStatusButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const donationId = this.getAttribute('data-id');
                const newStatus = this.getAttribute('data-status');
                const statusText = newStatus === 'completed' ? 'selesai' : (newStatus === 'pending' ? 'pending' : 'dibatalkan');
                
                if (confirm(`Apakah Anda yakin ingin mengubah status donasi menjadi "${statusText}"?`)) {
                    window.location.href = BASE_URL + '/admin/change-donation-status/' + donationId + '/' + newStatus;
                }
            });
        });
    }
    
    // Add confirmation to all delete actions
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                e.preventDefault();
            }
        });
    });
    
    // Fix for datatable pagination to prevent page growth
    const dataTableWrapper = document.querySelector('.dataTables_wrapper');
    if (dataTableWrapper && adminContent) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Reset main content scroll position if needed
                    if (adminContent.scrollHeight > window.innerHeight * 3) {
                        console.log('Resetting scroll due to excessive page growth');
                        adminContent.scrollTop = 0;
                        
                        // Alert admin of potential issue
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-warning alert-dismissible fade show';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Terjadi masalah dengan tampilan halaman. Silakan refresh browser Anda. <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                        
                        const firstChild = adminContent.firstChild;
                        adminContent.insertBefore(alertDiv, firstChild);
                    }
                }
            });
        });
        
        // Configure the observer to watch for changes
        observer.observe(dataTableWrapper, { childList: true, subtree: true });
        
        // Cleanup to prevent memory leaks
        window.addEventListener('beforeunload', function() {
            observer.disconnect();
        });
    }
    
    // Limit chart resize events to improve performance
    window.addEventListener('resize', function() {
        if (window.resizeTimer) {
            clearTimeout(window.resizeTimer);
        }
        
        window.resizeTimer = setTimeout(function() {
            const chartCanvases = document.querySelectorAll('canvas');
            chartCanvases.forEach(function(canvas) {
                if (canvas.chart) {
                    canvas.chart.resize();
                }
            });
        }, 250);
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