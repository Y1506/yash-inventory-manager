document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });
    }

    const lowStockBadge = document.getElementById('low-stock-badge');

    function updateLowStockBadge() {
        fetch('php/api.php?action=get_low_stock_count')
            .then(response => response.json())
            .then(data => {
                lowStockBadge.textContent = data.count;
            });
    }

    // Update the badge every 30 seconds
    setInterval(updateLowStockBadge, 30000);

    const itemSearchInput = document.getElementById('item-search-input');
    const itemSearchResults = document.getElementById('item-search-results');
    const cartItemsContainer = document.getElementById('cart-items');
    const checkoutForm = document.querySelector('.checkout-cart form');
    let cart = [];

    if (itemSearchInput) {
        itemSearchInput.addEventListener('keyup', function() {
            const searchTerm = this.value;
            if (searchTerm.length > 2) {
                fetch(`php/api.php?action=search_items&term=${searchTerm}`)
                    .then(response => response.json())
                    .then(data => {
                        itemSearchResults.innerHTML = '';
                        data.forEach(item => {
                            const itemDiv = document.createElement('div');
                            itemDiv.classList.add('search-result-item');
                            itemDiv.innerHTML = `<span>${item.name} (${item.sku})</span>`;
                            itemDiv.addEventListener('click', function() {
                                addToCart(item);
                            });
                            itemSearchResults.appendChild(itemDiv);
                        });
                    });
            } else {
                itemSearchResults.innerHTML = '';
            }
        });
    }

    function addToCart(item) {
        const existingItem = cart.find(cartItem => cartItem.id === item.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({
                id: item.id,
                name: item.name,
                sku: item.sku,
                price: item.price,
                quantity: 1
            });
        }
        renderCart();
    }

    function renderCart() {
        cartItemsContainer.innerHTML = '';
        let total = 0;
        cart.forEach((item, index) => {
            const cartItemDiv = document.createElement('div');
            cartItemDiv.classList.add('cart-item');
            cartItemDiv.innerHTML = `
                <span>${item.name}</span>
                <input type="number" value="${item.quantity}" min="1" data-index="${index}">
                <span>₹${(item.price * item.quantity).toFixed(2)}</span>
                <button class="remove-from-cart" data-index="${index}">Remove</button>
            `;
            cartItemsContainer.appendChild(cartItemDiv);
            total += item.price * item.quantity;
        });

        const totalDiv = document.createElement('div');
        totalDiv.innerHTML = `<strong>Total: ₹${total.toFixed(2)}</strong>`;
        cartItemsContainer.appendChild(totalDiv);

        // Add event listeners for quantity changes and remove buttons
        document.querySelectorAll('.cart-item input').forEach(input => {
            input.addEventListener('change', function() {
                const index = this.dataset.index;
                cart[index].quantity = parseInt(this.value);
                renderCart();
            });
        });

        document.querySelectorAll('.remove-from-cart').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.dataset.index;
                cart.splice(index, 1);
                renderCart();
            });
        });
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const cartItemsInput = document.createElement('input');
            cartItemsInput.type = 'hidden';
            cartItemsInput.name = 'cart_items';
            cartItemsInput.value = JSON.stringify(cart);
            this.appendChild(cartItemsInput);
        });
    }
});