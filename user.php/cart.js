// Khởi tạo giỏ hàng từ localStorage hoặc tạo mới nếu chưa có
let cart = JSON.parse(localStorage.getItem('cart')) || [];
updateCartCount();

// Kiểm tra xem người dùng đã đăng nhập chưa và đồng bộ giỏ hàng
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra đăng nhập
    fetch('check_login.php')
        .then(response => response.json())
        .then(data => {
            if (data.logged_in) {
                // Nếu đã đăng nhập, đồng bộ giỏ hàng
                syncCartWithDatabase();
                
                // Sau đó lấy giỏ hàng từ database
                fetch('get_cart.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.cart && data.cart.length > 0) {
                            // Nếu có dữ liệu từ database, sử dụng nó
                            cart = data.cart;
                            localStorage.setItem('cart', JSON.stringify(cart));
                            updateCartCount();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching cart from database:', error);
                    });
            }
        })
        .catch(error => {
            console.error('Error checking login status:', error);
        });
});

// Lắng nghe sự kiện click vào icon giỏ hàng
document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.getElementById('cart-icon');
    const cartBox = document.getElementById('cart-box');
    
    if (cartIcon && cartBox) {
        // Hiển thị/ẩn giỏ hàng khi click vào icon
        cartIcon.addEventListener('click', function() {
            if (cartBox.style.display === 'none' || cartBox.style.display === '') {
                cartBox.style.display = 'block';
                renderCart();
                // Thêm hiệu ứng khi mở giỏ hàng
                cartIcon.classList.add('bounce');
                setTimeout(() => {
                    cartIcon.classList.remove('bounce');
                }, 500);
            } else {
                cartBox.style.display = 'none';
            }
        });
        
        // Ẩn giỏ hàng khi click ra ngoài
        document.addEventListener('click', function(event) {
            if (!cartIcon.contains(event.target) && !cartBox.contains(event.target)) {
                cartBox.style.display = 'none';
            }
        });
    }
});

// Hàm thêm sản phẩm vào giỏ hàng
function addToCart(productId, productName, productPrice, productImage) {
    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        // Nếu đã có, tăng số lượng
        existingItem.quantity += 1;
    } else {
        // Nếu chưa có, thêm mới vào giỏ hàng
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            image: productImage,
            quantity: 1
        });
    }
    
    // Lưu giỏ hàng vào localStorage
    saveCart();
    
    // Cập nhật số lượng sản phẩm hiển thị trên icon giỏ hàng
    updateCartCount();
    
    // Hiển thị thông báo đã thêm vào giỏ hàng
    showAddToCartNotification(productName);
    
    // Hiệu ứng bounce cho icon giỏ hàng
    const cartIcon = document.getElementById('cart-icon');
    if (cartIcon) {
        cartIcon.classList.add('bounce');
        setTimeout(() => {
            cartIcon.classList.remove('bounce');
        }, 500);
    }
    
    // Render lại giỏ hàng nếu đang mở
    const cartBox = document.getElementById('cart-box');
    if (cartBox && cartBox.style.display === 'block') {
        renderCart();
    }
    
    return true; // Trả về true để biết hàm đã thực hiện thành công
}

// Hàm xóa sản phẩm khỏi giỏ hàng
function removeFromCart(productId) {
    // Tìm vị trí của sản phẩm trong giỏ hàng
    const index = cart.findIndex(item => item.id === productId);
    
    if (index !== -1) {
        // Xóa sản phẩm khỏi giỏ hàng
        cart.splice(index, 1);
        
        // Lưu giỏ hàng vào localStorage
        saveCart();
        
        // Cập nhật số lượng sản phẩm hiển thị trên icon giỏ hàng
        updateCartCount();
        
        // Render lại giỏ hàng
        renderCart();
    }
}

// Hàm thay đổi số lượng sản phẩm trong giỏ hàng
function updateQuantity(productId, newQuantity) {
    // Tìm sản phẩm trong giỏ hàng
    const item = cart.find(item => item.id === productId);
    
    if (item) {
        // Nếu số lượng mới là 0 hoặc nhỏ hơn, xóa sản phẩm khỏi giỏ hàng
        if (newQuantity <= 0) {
            removeFromCart(productId);
        } else {
            // Cập nhật số lượng mới
            item.quantity = newQuantity;
            
            // Lưu giỏ hàng vào localStorage
            saveCart();
            
            // Cập nhật số lượng sản phẩm hiển thị trên icon giỏ hàng
            updateCartCount();
            
            // Render lại giỏ hàng
            renderCart();
        }
    }
}

// Hàm lưu giỏ hàng vào localStorage và đồng bộ với database
function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Đồng bộ với database nếu người dùng đã đăng nhập
    syncCartWithDatabase();
}

// Hàm đồng bộ giỏ hàng với database
function syncCartWithDatabase() {
    // Kiểm tra xem người dùng đã đăng nhập chưa
    fetch('check_login.php')
        .then(response => response.json())
        .then(data => {
            if (data.logged_in) {
                // Nếu đã đăng nhập, đồng bộ giỏ hàng
                fetch('sync_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ cart: cart })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Giỏ hàng đã được đồng bộ với database!');
                    } else {
                        console.error('Lỗi khi đồng bộ giỏ hàng:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        })
        .catch(error => {
            console.error('Error checking login status:', error);
        });
}

// Hàm cập nhật số lượng sản phẩm hiển thị trên icon giỏ hàng
function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        // Tính tổng số lượng sản phẩm trong giỏ hàng
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartCountElement.textContent = totalItems;
    }
}

// Hàm hiển thị giỏ hàng
function renderCart() {
    const cartItemsElement = document.getElementById('cart-items');
    
    if (cartItemsElement) {
        // Xóa nội dung cũ
        cartItemsElement.innerHTML = '';
        
        if (cart.length === 0) {
            // Nếu giỏ hàng trống
            cartItemsElement.innerHTML = '<p>Giỏ hàng trống</p>';
        } else {
            // Tạo HTML cho từng sản phẩm trong giỏ hàng
            let cartHTML = '';
            let totalPrice = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                totalPrice += itemTotal;
                
                cartHTML += `
                <div class="cart-item" style="display: flex; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                    <div class="cart-item-image" style="width: 60px; margin-right: 10px;">
                        <img src="${item.image}" alt="${item.name}" style="width: 100%; border-radius: 5px;">
                    </div>
                    <div class="cart-item-details" style="flex-grow: 1;">
                        <div class="cart-item-name" style="font-weight: bold;">${item.name}</div>
                        <div class="cart-item-price">${formatCurrency(item.price)}</div>
                        <div class="cart-item-qty-row" style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                            <button class="cart-qty-btn" data-id="${item.id}" data-action="decrease" style="width:28px; height:28px; border-radius:50%; background:#1e90ff; color:#fff; border:none; font-size:18px; font-weight:bold; display:flex; align-items:center; justify-content:center; cursor:pointer;">-</button>
                            <span class="cart-item-qty" style="min-width:32px; text-align:center; font-weight:bold; font-size:20px; color:#222; background:#f0f8ff; border-radius:6px; padding:4px 0; border:1px solid #1e90ff; margin:0 2px; display:inline-block;">${item.quantity}</span>
                            <button class="cart-qty-btn" data-id="${item.id}" data-action="increase" style="width:28px; height:28px; border-radius:50%; background:#1e90ff; color:#fff; border:none; font-size:18px; font-weight:bold; display:flex; align-items:center; justify-content:center; cursor:pointer;">+</button>
                            <button onclick="removeFromCart('${item.id}'); event.stopPropagation(); return false;" style="background: #ff6b6b; color: white; border: none; margin-left: 10px; padding: 3px 8px; border-radius: 5px; cursor: pointer;">Xóa</button>
                        </div>
                    </div>
                </div>
                `;
            });
            
            // Thêm tổng tiền và nút thanh toán
            cartHTML += `
            <div class="cart-total" style="margin-top: 10px; font-weight: bold; display: flex; justify-content: space-between; font-size: 15px;">
                <span>Tổng cộng:</span>
                <span>${formatCurrency(totalPrice)}</span>
            </div>
            <div class="cart-actions" style="margin-top: 10px; display: flex; justify-content: space-between; gap: 8px;">
                <button onclick="clearCart(); event.stopPropagation(); return false;" style="background: #ff6b6b; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 14px;">Xóa tất cả</button>
                <button onclick="checkout(); event.stopPropagation(); return false;" style="background: #1e90ff; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 14px;">Thanh toán</button>
            </div>
            `;
            
            cartItemsElement.innerHTML = cartHTML;
            
            // Gắn sự kiện cho nút + và -
            const qtyBtns = cartItemsElement.querySelectorAll('.cart-qty-btn');
            qtyBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Ngăn đóng popup
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const action = this.getAttribute('data-action');
                    const item = cart.find(i => i.id == id);
                    if (item) {
                        if (action === 'increase') {
                            item.quantity++;
                        } else if (action === 'decrease') {
                            item.quantity--;
                            if (item.quantity <= 0) {
                                if (confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                                    removeFromCart(id);
                                    return;
                                } else {
                                    item.quantity = 1;
                                }
                            }
                        }
                        saveCart();
                        updateCartCount();
                        renderCart();
                    }
                });
            });
        }
    }
}

// Hàm xóa toàn bộ giỏ hàng
function clearCart() {
    cart = [];
    saveCart();
    updateCartCount();
    renderCart();
}

// Hàm thanh toán
function checkout() {
    // Kiểm tra xem người dùng đã đăng nhập chưa
    fetch('check_login.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.logged_in) {
                // Nếu đã đăng nhập, chuyển đến trang thanh toán
                window.location.href = 'checkout.php';
            } else {
                // Nếu chưa đăng nhập, hiển thị thông báo và chuyển đến trang đăng nhập
                alert('Bạn cần đăng nhập để tiếp tục thanh toán!');
                window.location.href = 'login.php?redirect=checkout.php';
            }
        })
        .catch(error => {
            console.error('Error during checkout:', error);
            alert('Có lỗi xảy ra. Vui lòng thử lại sau!');
        });
}

// Hàm hiển thị thông báo đã thêm vào giỏ hàng
function showAddToCartNotification(productName) {
    // Kiểm tra xem đã có thông báo nào chưa
    let notification = document.getElementById('add-to-cart-notification');
    
    // Nếu chưa có, tạo mới
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'add-to-cart-notification';
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.background = '#1e90ff';
        notification.style.color = 'white';
        notification.style.padding = '10px 20px';
        notification.style.borderRadius = '5px';
        notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
        notification.style.zIndex = '9999';
        notification.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
        notification.style.transform = 'translateY(100px)';
        notification.style.opacity = '0';
        document.body.appendChild(notification);
    }
    
    // Cập nhật nội dung thông báo
    notification.innerHTML = `<i class="fas fa-check-circle"></i> Đã thêm "${productName}" vào giỏ hàng`;
    
    // Hiển thị thông báo
    setTimeout(() => {
        notification.style.transform = 'translateY(0)';
        notification.style.opacity = '1';
    }, 10);
    
    // Ẩn thông báo sau 3 giây
    setTimeout(() => {
        notification.style.transform = 'translateY(100px)';
        notification.style.opacity = '0';
    }, 3000);
}

// Hàm định dạng tiền tệ
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}