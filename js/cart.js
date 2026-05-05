// Hàm cập nhật tổng tiền
function updateTotal() {
    let total = 0;
    const checkboxes = document.querySelectorAll('.item-checkbox');

    checkboxes.forEach(function(checkbox) {
        // Chỉ cập nhật tổng khi checkbox được chọn
        if (checkbox.checked) {
            let itemTotal = parseInt(checkbox.closest('tr').querySelector('.item-total').textContent.replace(/\./g, '').replace(' đ', ''));
            total += itemTotal;
        }
    });

    // Kiểm tra sự tồn tại của phần tử trước khi cập nhật nội dung
    const totalDisplay = document.getElementById('total-price');
    const subtotalDisplay = document.getElementById('subtotal');
    const finalTotalDisplay = document.getElementById('final-total');

    if (totalDisplay && subtotalDisplay && finalTotalDisplay) {
        const formattedTotal = total.toLocaleString('vi-VN') + ' đ';
        totalDisplay.textContent = formattedTotal;
        subtotalDisplay.textContent = formattedTotal;
        finalTotalDisplay.textContent = formattedTotal;
    } else {
        console.warn("Một số phần tử hiển thị không tồn tại trên trang.");
    }
}

// Cập nhật tổng khi checkbox "Chọn tất cả" được tick
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const isChecked = this.checked;

    // Đặt tất cả checkbox cùng trạng thái
    checkboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
    });

    // Cập nhật tổng khi "Chọn tất cả" được click
    updateTotal();
});

// Lắng nghe sự kiện thay đổi checkbox của từng item
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        updateTotal();
    });
});

// Cập nhật tổng khi thay đổi số lượng
document.querySelectorAll('.quantity-btn').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.getAttribute('data-action');
        const index = this.getAttribute('data-index');
        const quantitySpan = document.querySelectorAll('.quantity')[index];
        let quantity = parseInt(quantitySpan.textContent);

        // Tăng hoặc giảm số lượng
        if (action === 'increase') {
            quantity++;
        } else if (action === 'decrease' && quantity > 1) {
            quantity--;
        }

        quantitySpan.textContent = quantity;

        // Cập nhật tổng tiền cho item
        updateItemTotal(index, quantity);
        updateTotal();
    });
});

// Cập nhật tổng cho từng item
function updateItemTotal(index, quantity) {
    const price = parseFloat(document.querySelectorAll('td:nth-child(4)')[index].textContent.replace(' đ', '').replace(',', ''));
    const itemTotal = quantity * price;

    // Cập nhật tổng tiền của item với định dạng tiền tệ
    document.querySelectorAll('.item-total')[index].textContent = itemTotal.toLocaleString('vi-VN') + ' đ';
}

// Chạy hàm một lần khi trang load xong để đảm bảo số liệu khớp
document.addEventListener('DOMContentLoaded', function() {
    updateTotal();
});