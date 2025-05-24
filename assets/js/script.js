// Back to Top Button
document.addEventListener('DOMContentLoaded', function() {
    // Add back to top button
    const backToTopButton = document.createElement('div');
    backToTopButton.classList.add('back-to-top');
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    document.body.appendChild(backToTopButton);

    // Show/hide back to top button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });

    // Scroll to top when button is clicked
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Initialize all Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle quantity input in product detail and cart pages
    const quantityInputs = document.querySelectorAll('.quantity-input');
    if (quantityInputs.length > 0) {
        quantityInputs.forEach(input => {
            const minusBtn = input.previousElementSibling;
            const plusBtn = input.nextElementSibling;
            
            if (minusBtn && minusBtn.classList.contains('quantity-minus')) {
                minusBtn.addEventListener('click', function() {
                    if (input.value > 1) {
                        input.value = parseInt(input.value) - 1;
                        if (input.hasAttribute('data-cart-id')) {
                            updateCartQuantity(input);
                        }
                    }
                });
            }
            
            if (plusBtn && plusBtn.classList.contains('quantity-plus')) {
                plusBtn.addEventListener('click', function() {
                    input.value = parseInt(input.value) + 1;
                    if (input.hasAttribute('data-cart-id')) {
                        updateCartQuantity(input);
                    }
                });
            }
            
            input.addEventListener('change', function() {
                if (parseInt(input.value) < 1) {
                    input.value = 1;
                }
                if (input.hasAttribute('data-cart-id')) {
                    updateCartQuantity(input);
                }
            });
        });
    }

    // Function to update cart quantity via AJAX
    function updateCartQuantity(input) {
        const cartId = input.getAttribute('data-cart-id');
        const quantity = input.value;
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        
        fetch('/ecommerce-platform/cart/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}&quantity=${quantity}&csrf_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update subtotal
                const subtotalElement = document.querySelector(`#subtotal-${cartId}`);
                if (subtotalElement) {
                    subtotalElement.textContent = data.subtotal;
                }
                
                // Update total
                const totalElement = document.querySelector('#cart-total');
                if (totalElement) {
                    totalElement.textContent = data.total;
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart:', error);
        });
    }

    // Image preview for product upload/edit
    const productImageInput = document.getElementById('product-image');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    
    if (productImageInput && imagePreviewContainer) {
        productImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreviewContainer.innerHTML = `
                        <div class="mt-2">
                            <img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Add animation to product cards
    const productCards = document.querySelectorAll('.product-card');
    if (productCards.length > 0) {
        productCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('animate-fade-in');
        });
    }

    // Shipping address toggle in checkout
    const sameAsShipping = document.getElementById('same-as-shipping');
    const billingAddressFields = document.getElementById('billing-address-fields');
    
    if (sameAsShipping && billingAddressFields) {
        sameAsShipping.addEventListener('change', function() {
            if (this.checked) {
                billingAddressFields.style.display = 'none';
            } else {
                billingAddressFields.style.display = 'block';
            }
        });
        
        // Initial state
        if (sameAsShipping.checked) {
            billingAddressFields.style.display = 'none';
        }
    }

    // Payment method toggle in checkout
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const creditCardFields = document.getElementById('credit-card-fields');
    
    if (paymentMethods.length > 0 && creditCardFields) {
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                if (this.value === 'credit_card') {
                    creditCardFields.style.display = 'block';
                } else {
                    creditCardFields.style.display = 'none';
                }
            });
        });
        
        // Initial state
        const checkedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (checkedMethod && checkedMethod.value === 'credit_card') {
            creditCardFields.style.display = 'block';
        } else {
            creditCardFields.style.display = 'none';
        }
    }
});