document.addEventListener('DOMContentLoaded', function () {
    const stars = document.querySelectorAll(".star");
    const ratingInput = document.getElementById("rating-value");
    const submitBtn = document.getElementById("submit-rating");

    const userNameInput = document.getElementById("user-name");
    const userCommentInput = document.getElementById("user-comment");
    const reviewsDisplay = document.getElementById("reviews-display");

    // Xử lý khi người dùng click vào sao
    stars.forEach((star) => {
        star.addEventListener("click", function () {
            let rating = this.getAttribute("data-value");
            ratingInput.value = rating;  // Cập nhật giá trị vào input ẩn

            // Thêm màu vàng cho các sao đã chọn
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.add("active");
                } else {
                    s.classList.remove("active");
                }
            });
        });
    });

    // Xử lý nút gửi đánh giá
    if (submitBtn) {
        submitBtn.addEventListener("click", function (e) {
            e.preventDefault(); // Ngừng form gửi tự động

            // Lấy các giá trị tên, sao và nhận xét
            const rating = ratingInput.value;
            const userName = userNameInput.value;
            const userComment = userCommentInput.value;

            if (rating && userName && userComment) {
                // Gửi đánh giá qua AJAX
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "danhgia.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        // Cập nhật phần đánh giá mới mà không cần tải lại trang
                        const reviewItem = document.createElement('div');
                        reviewItem.classList.add('review-item');
                        reviewItem.innerHTML = `
                            <div class="user-name">${userName}</div>
                            <div class="review-stars">${'★'.repeat(rating)}${'☆'.repeat(5 - rating)}</div>
                            <div class="user-comment">${userComment}</div>
                        `;
                        reviewsDisplay.prepend(reviewItem);

                        // Reset form
                        stars.forEach((s) => {
                            s.classList.remove("active");
                        });
                        ratingInput.value = ''; // reset rating
                        userNameInput.value = ''; // reset user name
                        userCommentInput.value = ''; // reset comment
                    } else {
                        alert("Đã xảy ra lỗi khi gửi đánh giá");
                    }
                };

                // Gửi dữ liệu
                xhr.send(`product_id=<?php echo $product_id; ?>&user_name=${userName}&rating=${rating}&comment=${userComment}`);
            } else {
                alert("Vui lòng chọn sao và điền đầy đủ thông tin.");
            }
        });
    }
});