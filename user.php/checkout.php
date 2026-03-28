<?php
session_start();
include 'header.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
    header("Location: login.php?redirect=checkout.php");
    exit();
}

// Lấy thông tin người dùng từ database
require 'connect.php';
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Kiểm tra xem có lỗi thanh toán không
$paymentError = '';
if (isset($_GET['error']) && $_GET['error'] == 'payment_failed') {
    $paymentError = 'Thanh toán không thành công. Vui lòng thử lại hoặc chọn phương thức thanh toán khác.';
}

// Lấy thông tin lỗi từ session nếu có
if (isset($_SESSION['payment_error'])) {
    $paymentError = $_SESSION['payment_error']['message'];
    unset($_SESSION['payment_error']); // Xóa thông tin lỗi sau khi hiển thị
}
?>

<div style="margin-top: 100px; padding: 20px; max-width: 800px; margin-left: auto; margin-right: auto;">
    <h1 style="color: #1e90ff; margin-bottom: 30px; text-align: center;">Thanh toán</h1>

    <?php if (!empty($paymentError)): ?>
        <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #c62828;">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($paymentError); ?>
        </div>
    <?php endif; ?>

    <div class="checkout-container" style="display: flex; flex-wrap: wrap; gap: 30px;">
        <!-- Thông tin giỏ hàng -->
        <div class="cart-summary" style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h2 style="color: #1e90ff; margin-bottom: 20px; font-size: 20px;">Giỏ hàng của bạn</h2>
            <div id="checkout-items">
                <!-- Các sản phẩm sẽ được hiển thị ở đây bằng JavaScript -->
            </div>
            <div class="checkout-total" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-weight: bold; display: flex; justify-content: space-between;">
                <span>Tổng cộng:</span>
                <span id="checkout-total">0 ₫</span>
            </div>
        </div>

        <!-- Form thông tin thanh toán -->
        <div class="checkout-form" style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h2 style="color: #1e90ff; margin-bottom: 20px; font-size: 20px;">Thông tin thanh toán</h2>
            <form id="payment-form">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="name" style="display: block; margin-bottom: 5px; color: #555;">Họ và tên</label>
                    <input type="text" id="name" name="name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="email" style="display: block; margin-bottom: 5px; color: #555;">Email</label>
                    <input type="email" id="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="phone" style="display: block; margin-bottom: 5px; color: #555;">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="address" style="display: block; margin-bottom: 5px; color: #555;">Địa chỉ</label>
                    <textarea id="address" name="address" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 80px;"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="city" style="display: block; margin-bottom: 5px; color: #555;">Tỉnh/Thành phố</label>
                    <select id="city" name="city" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></select>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="district" style="display: block; margin-bottom: 5px; color: #555;">Quận/Huyện</label>
                    <select id="district" name="district" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></select>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="ward" style="display: block; margin-bottom: 5px; color: #555;">Phường/Xã</label>
                    <select id="ward" name="ward" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="payment-method" style="display: block; margin-bottom: 5px; color: #555;">Phương thức thanh toán</label>
                    <select id="payment-method" name="payment-method" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">-- Chọn phương thức thanh toán --</option>
                        <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                        <option value="vnpay">Thanh toán qua VNPAY</option>
                        <option value="momo">Thanh toán qua MoMo</option>
                        <option value="credit">Thanh toán qua thẻ tín dụng/ghi nợ</option>
                    </select>
                </div>

                <div id="payment-details" style="margin-top: 15px; display: none;">
                    <!-- Chi tiết thanh toán sẽ được hiển thị dựa trên phương thức thanh toán -->
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: #1e90ff; border: none; color: white; font-size: 16px; border-radius: 5px; cursor: pointer; transition: 0.3s; margin-top: 10px;">
                    Hoàn tất đơn hàng
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lấy giỏ hàng từ localStorage và database
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const checkoutItemsElement = document.getElementById('checkout-items');
        const checkoutTotalElement = document.getElementById('checkout-total');
        const paymentMethodSelect = document.getElementById('payment-method');
        const paymentDetailsDiv = document.getElementById('payment-details');

        // Biến lưu tổng tiền
        let totalPrice = 0;

        // Kiểm tra xem người dùng đã đăng nhập chưa và lấy giỏ hàng từ database
        fetch('get_cart.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Nếu có dữ liệu từ database, sử dụng nó
                    if (data.cart && data.cart.length > 0) {
                        // Đồng bộ giỏ hàng từ database với localStorage
                        cart = data.cart;
                        localStorage.setItem('cart', JSON.stringify(cart));
                    }

                    // Hiển thị các sản phẩm trong giỏ hàng
                    renderCheckoutItems(cart);
                } else {
                    // Nếu có lỗi, vẫn hiển thị giỏ hàng từ localStorage
                    renderCheckoutItems(cart);
                }
            })
            .catch(error => {
                console.error('Error fetching cart from database:', error);
                // Nếu có lỗi, vẫn hiển thị giỏ hàng từ localStorage
                renderCheckoutItems(cart);
            });

        // Hàm hiển thị các sản phẩm trong giỏ hàng
        function renderCheckoutItems(cart) {
            if (cart.length === 0) {
                checkoutItemsElement.innerHTML = '<p>Giỏ hàng trống</p>';
                checkoutTotalElement.textContent = '0 ₫';
            } else {
                let checkoutHTML = '';
                totalPrice = 0;
                cart.forEach((item, idx) => {
                    const itemTotal = item.price * item.quantity;
                    totalPrice += itemTotal;
                    checkoutHTML += `
                    <div class="checkout-item" style="display: flex; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                        <div class="checkout-item-image" style="width: 60px; margin-right: 10px;">
                            <img src="${item.image}" alt="${item.name}" style="width: 100%; border-radius: 5px;">
                        </div>
                        <div class="checkout-item-details" style="flex-grow: 1;">
                            <div class="checkout-item-name" style="font-weight: bold;">${item.name}</div>
                            <div class="checkout-item-price">${formatCurrency(item.price)}</div>
                            <div class="checkout-item-qty-row" style="display: flex; align-items: center; gap: 12px; margin-top: 8px;">
                                <button class="checkout-qty-btn" data-id="${item.id}" data-action="decrease" style="width:32px; height:32px; border-radius:50%; background:#1e90ff; color:#fff; border:2px solid #1e90ff; font-size:20px; font-weight:bold; display:flex; align-items:center; justify-content:center; cursor:pointer;">-</button>
                                <span class="checkout-item-qty" style="min-width:32px; text-align:center; font-weight:bold; font-size:20px; color:#222; background:#f0f8ff; border-radius:6px; padding:4px 0; border:1px solid #1e90ff; margin:0 2px; display:inline-block;">${item.quantity}</span>
                                <button class="checkout-qty-btn" data-id="${item.id}" data-action="increase" style="width:32px; height:32px; border-radius:50%; background:#1e90ff; color:#fff; border:2px solid #1e90ff; font-size:20px; font-weight:bold; display:flex; align-items:center; justify-content:center; cursor:pointer;">+</button>
                                <button class="checkout-remove-btn" data-id="${item.id}" style="background: #ff6b6b; color: white; border: none; margin-left: 10px; padding: 6px 14px; border-radius: 5px; cursor: pointer; font-size: 15px;">Xóa</button>
                            </div>
                        </div>
                        <div class="checkout-item-total" style="font-weight: bold; color: #1e90ff; min-width: 80px; text-align: right;">
                            ${formatCurrency(itemTotal)}
                        </div>
                    </div>
                    `;
                });
                checkoutItemsElement.innerHTML = checkoutHTML;
                checkoutTotalElement.textContent = formatCurrency(totalPrice);

                // Gắn sự kiện cho nút +, -
                const qtyBtns = checkoutItemsElement.querySelectorAll('.checkout-qty-btn');
                qtyBtns.forEach(btn => {
                    btn.addEventListener('click', function(e) {
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
                                        cart = cart.filter(i => i.id != id);
                                    } else {
                                        item.quantity = 1;
                                    }
                                }
                            }
                            localStorage.setItem('cart', JSON.stringify(cart));
                            renderCheckoutItems(cart);
                        }
                    });
                });
                // Gắn sự kiện cho nút Xóa
                const removeBtns = checkoutItemsElement.querySelectorAll('.checkout-remove-btn');
                removeBtns.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.getAttribute('data-id');
                        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                            cart = cart.filter(i => i.id != id);
                            localStorage.setItem('cart', JSON.stringify(cart));
                            renderCheckoutItems(cart);
                        }
                    });
                });
            }
        }

        // Xử lý thay đổi phương thức thanh toán
        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function() {
                const selectedMethod = this.value;

                // Hiển thị chi tiết thanh toán dựa trên phương thức được chọn
                if (selectedMethod) {
                    paymentDetailsDiv.style.display = 'block';

                    switch (selectedMethod) {
                        case 'cod':
                            paymentDetailsDiv.innerHTML = `
                                <div class="payment-info" style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                    <h4 style="margin-top: 0; color: #1e90ff;">Thanh toán khi nhận hàng</h4>
                                    <p>Bạn sẽ thanh toán khi nhận được hàng.</p>
                                </div>
                            `;
                            break;

                        case 'vnpay':
                            paymentDetailsDiv.innerHTML = `
                                <div class="payment-info" style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                    <h4 style="margin-top: 0; color: #1e90ff;">Thanh toán qua VNPAY</h4>
                                    <p>Bạn sẽ được chuyển đến cổng thanh toán VNPAY để hoàn tất giao dịch.</p>
                                    <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-VNPAY-QR-1.png" alt="VNPAY" style="max-width: 150px; margin-top: 10px;">
                                </div>
                            `;
                            break;

                        case 'momo':
                            paymentDetailsDiv.innerHTML = `
                                <div class="payment-info" style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                    <h4 style="margin-top: 0; color: #1e90ff;">Thanh toán qua MoMo</h4>
                                    <p>Bạn sẽ được chuyển đến cổng thanh toán MoMo để hoàn tất giao dịch.</p>
                                    <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo" style="max-width: 100px; margin-top: 10px;">
                                </div>
                            `;
                            break;

                        case 'credit':
                            paymentDetailsDiv.innerHTML = `
                                <div class="payment-info" style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                    <h4 style="margin-top: 0; color: #1e90ff;">Thanh toán qua thẻ tín dụng/ghi nợ</h4>
                                    <div class="card-icons" style="display: flex; gap: 10px; margin: 10px 0;">
                                        <img src="https://cdn.iconscout.com/icon/free/png-256/free-visa-3-226460.png" alt="Visa" style="height: 30px;">
                                        <img src="https://cdn.iconscout.com/icon/free/png-256/free-mastercard-3521564-2944982.png" alt="MasterCard" style="height: 30px;">
                                        <img src="https://cdn.iconscout.com/icon/free/png-256/free-jcb-credit-card-3521563-2944981.png" alt="JCB" style="height: 30px;">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label for="card-number" style="display: block; margin-bottom: 5px;">Số thẻ</label>
                                        <input type="text" id="card-number" placeholder="XXXX XXXX XXXX XXXX" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                    </div>
                                    <div style="display: flex; gap: 10px;">
                                        <div class="form-group" style="flex: 1;">
                                            <label for="expiry-date" style="display: block; margin-bottom: 5px;">Ngày hết hạn</label>
                                            <input type="text" id="expiry-date" placeholder="MM/YY" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                        </div>
                                        <div class="form-group" style="flex: 1;">
                                            <label for="cvv" style="display: block; margin-bottom: 5px;">CVV</label>
                                            <input type="text" id="cvv" placeholder="XXX" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                        </div>
                                    </div>
                                </div>
                            `;
                            break;
                    }
                } else {
                    paymentDetailsDiv.style.display = 'none';
                }
            });
        }

        // Xử lý form thanh toán
        const paymentForm = document.getElementById('payment-form');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(event) {
                event.preventDefault();

                // Kiểm tra xem giỏ hàng có trống không
                if (cart.length === 0) {
                    alert('Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm vào giỏ hàng trước khi thanh toán.');
                    return;
                }

                // Lấy phương thức thanh toán
                const paymentMethod = paymentMethodSelect.value;

                if (!paymentMethod) {
                    alert('Vui lòng chọn phương thức thanh toán.');
                    return;
                }

                // Lấy thông tin từ form
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const phone = document.getElementById('phone').value;
                const address = document.getElementById('address').value;

                // Tạo ID đơn hàng
                const orderId = 'ORD' + Date.now();

                // Xử lý theo phương thức thanh toán
                switch (paymentMethod) {
                    case 'cod':
                        // Xử lý thanh toán COD
                        processCodPayment(orderId, name, email, phone, address, totalPrice, cart);
                        break;

                    case 'vnpay':
                        // Xử lý thanh toán VNPAY
                        processVnpayPayment(orderId, name, email, phone, address, totalPrice, cart);
                        break;

                    case 'momo':
                        // Xử lý thanh toán MoMo
                        processMomoPayment(orderId, name, email, phone, address, totalPrice, cart);
                        break;

                    case 'credit':
                        // Xử lý thanh toán thẻ tín dụng
                        processCreditCardPayment(orderId, name, email, phone, address, totalPrice, cart);
                        break;
                }
            });
        }
    });

    // Hàm xử lý thanh toán COD
    function processCodPayment(orderId, name, email, phone, address, totalPrice, cart) {
        // Lưu thông tin đơn hàng vào localStorage để xử lý sau
        const orderData = {
            orderId: orderId,
            name: name,
            email: email,
            phone: phone,
            address: address,
            totalPrice: totalPrice,
            items: cart,
            paymentMethod: 'cod',
            status: 'pending'
        };

        localStorage.setItem('lastOrder', JSON.stringify(orderData));

        // Gửi dữ liệu đơn hàng đến server
        saveOrderToServer(orderData, function() {
            // Hiển thị thông báo đặt hàng thành công
            alert('Đặt hàng thành công! Cảm ơn bạn đã mua sắm tại CNBooks.');

            // Xóa giỏ hàng
            localStorage.removeItem('cart');

            // Chuyển hướng đến trang xác nhận đơn hàng
            window.location.href = 'order_confirmation.php?id=' + orderId;
        });
    }

    // Hàm xử lý thanh toán VNPAY
    function processVnpayPayment(orderId, name, email, phone, address, totalPrice, cart) {
        // Lưu thông tin đơn hàng vào localStorage để xử lý sau
        const orderData = {
            orderId: orderId,
            name: name,
            email: email,
            phone: phone,
            address: address,
            totalPrice: totalPrice,
            items: cart,
            paymentMethod: 'vnpay',
            status: 'pending'
        };

        localStorage.setItem('lastOrder', JSON.stringify(orderData));

        // Tạo form để gửi dữ liệu đến VNPAY
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'vnpay_php/vnpay_create_payment.php';

        // Tạo chuỗi JSON chứa thông tin sản phẩm
        const cartItems = JSON.stringify(cart.map(item => ({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: item.quantity
        })));

        // Thêm các trường dữ liệu
        const fields = {
            'id_don_hang': orderId,
            'total_price': totalPrice,
            'ho_ten': name,
            'phone': phone,
            'dia_chi_giao': address,
            'note': 'Đơn hàng từ website',
            'id_kh': '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : ""; ?>',
            'so_luong': cart.reduce((total, item) => total + item.quantity, 0)
        };

        for (const key in fields) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key];
            form.appendChild(input);
        }

        // Thêm form vào body và submit
        document.body.appendChild(form);
        form.submit();
    }

    // Hàm xử lý thanh toán MoMo
    function processMomoPayment(orderId, name, email, phone, address, totalPrice, cart) {
        // Lưu thông tin đơn hàng vào localStorage để xử lý sau
        const orderData = {
            orderId: orderId,
            name: name,
            email: email,
            phone: phone,
            address: address,
            totalPrice: totalPrice,
            items: cart,
            paymentMethod: 'momo',
            status: 'pending'
        };
        localStorage.setItem('lastOrder', JSON.stringify(orderData));

        // Gọi API lưu đơn hàng về database
        saveOrderToServer(orderData, function() {
            alert('Đặt hàng thành công! Cảm ơn bạn đã mua sắm tại CNBooks.');
            localStorage.removeItem('cart');
            window.location.href = 'order_confirmation.php?id=' + orderId;
        });
    }

    // Hàm xử lý thanh toán thẻ tín dụng
    function processCreditCardPayment(orderId, name, email, phone, address, totalPrice, cart) {
        // Kiểm tra thông tin thẻ
        const cardNumber = document.getElementById('card-number').value;
        const expiryDate = document.getElementById('expiry-date').value;
        const cvv = document.getElementById('cvv').value;

        if (!cardNumber || !expiryDate || !cvv) {
            alert('Vui lòng nhập đầy đủ thông tin thẻ.');
            return;
        }

        // Lưu thông tin đơn hàng vào localStorage để xử lý sau
        const orderData = {
            orderId: orderId,
            name: name,
            email: email,
            phone: phone,
            address: address,
            totalPrice: totalPrice,
            items: cart,
            paymentMethod: 'credit',
            status: 'pending'
        };

        localStorage.setItem('lastOrder', JSON.stringify(orderData));

        // Giả lập xử lý thanh toán thẻ (trong thực tế sẽ tích hợp cổng thanh toán)
        alert('Đang xử lý thanh toán...');

        // Giả lập chuyển hướng đến trang xác nhận đơn hàng sau khi thanh toán thành công
        setTimeout(function() {
            // Xóa giỏ hàng
            localStorage.removeItem('cart');

            // Chuyển hướng đến trang xác nhận đơn hàng
            window.location.href = 'order_confirmation.php?id=' + orderId;
        }, 2000);
    }

    // Hàm lưu đơn hàng vào server
    function saveOrderToServer(orderData, callback) {
        // Gửi dữ liệu đến server qua AJAX
        fetch('process_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Đơn hàng đã được lưu:', data);

                    // Gọi callback sau khi lưu thành công
                    if (typeof callback === 'function') {
                        callback();
                    }
                } else {
                    alert('Lỗi khi lưu đơn hàng: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Có lỗi xảy ra khi xử lý đơn hàng. Vui lòng thử lại sau.');
            });
    }

    // Hàm định dạng tiền tệ
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    function callApiAddress() {
        const host = "https://provinces.open-api.vn/api/";
        var callAPI = (api) => {
            return axios.get(api).then((response) => {
                renderData(response.data, "city");
            });
        };
        callAPI(host + "?depth=1");
        var callApiDistrict = (api) => {
            return axios.get(api).then((response) => {
                renderData(response.data.districts, "district");
            });
        };
        var callApiWard = (api) => {
            return axios.get(api).then((response) => {
                renderData(response.data.wards, "ward");
            });
        };

        var renderData = (array, select) => {
            let row = '<option disable value="">Chọn</option>';
            array.forEach((element) => {
                row += `<option data-id="${element.code}" value="${element.name}">${element.name}</option>`;
            });
            document.querySelector("#" + select).innerHTML = row;
        };

        document.getElementById("city").addEventListener('change', function() {
            callApiDistrict(
                host + "p/" + document.getElementById("city").selectedOptions[0].getAttribute('data-id') + "?depth=2"
            );
        });
        document.getElementById("district").addEventListener('change', function() {
            callApiWard(
                host + "d/" + document.getElementById("district").selectedOptions[0].getAttribute('data-id') + "?depth=2"
            );
        });
        document.getElementById("ward").addEventListener('change', function() {
            // Khi chọn đủ 3 trường, tự động ghép địa chỉ vào textarea
            const city = document.getElementById('city').value;
            const district = document.getElementById('district').value;
            const ward = document.getElementById('ward').value;
            if (city && district && ward) {
                let diaChi = ward + ", " + district + ", " + city;
                document.getElementById('address').value = diaChi;
            }
        });
    }
    callApiAddress();
</script>

<?php
include 'footer.php';
?>