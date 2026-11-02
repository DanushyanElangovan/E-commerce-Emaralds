document.addEventListener('DOMContentLoaded', function () {
    // Update cart count
    updateCartCount();

    // Quantity buttons
    document.querySelectorAll('.minus-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.nextElementSibling;
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });

    document.querySelectorAll('.plus-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const max = parseInt(input.max) || 999;
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        });
    });

    // Add to cart
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const quantity = this.querySelector('.quantity-input').value;

            fetch('/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    productId: productId,
                    quantity: quantity
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount();
                        showAlert('Product added to cart!', 'success');
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred', 'danger');
                });
        });
    });

    // Add to wishlist
    document.querySelectorAll('.add-to-wishlist').forEach(btn => {
        btn.addEventListener('click', function () {
            const productId = this.dataset.productId;

            fetch('/add_to_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    productId: productId
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Product added to wishlist!', 'success');
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred', 'danger');
                });
        });
    });
});

function updateCartCount() {
    fetch('/get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.count !== undefined) {
                document.getElementById('cart-count').textContent = data.count;
            }
        })
        .catch(error => console.error('Error:', error));
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    const container = document.querySelector('.container');
    container.prepend(alertDiv);

    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    }, 3000);
}