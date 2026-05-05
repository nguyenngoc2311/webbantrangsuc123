<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Liên hệ - Luxury Jewelry</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link href="https://unpkg.com/leaflet/dist/leaflet.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/lienhe.css">
</head>
<body>
  
    <?php include 'menu.php'; ?>

    <div id="mapid"></div>

    <div class="container my-5">
        <div class="contact-header text-center mb-5">
            <p class="slogan">-- Hãy luôn độc nhất và khác biệt --</p>
            <h2 class="main-title d-inline-block">Luxury Jewelry</h2>
            <div class="contact-info-row mt-3">
                <span><strong>Hotline:</strong> <a href="tel:0368446960">0368446960</a></span>
                <span class="mx-3 d-none d-md-inline">|</span>
                <br class="d-md-none">
                <span><strong>Email:</strong> <a href="mailto:ntkngoc.noliship@gmail.com">ntkngoc.noliship@gmail.com</a></span>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="store-list-full shadow-sm p-4 p-md-5">
                    <h3 class="text-center mb-4 gold-text">HỆ THỐNG 11 CỬA HÀNG TẠI TP. HCM</h3>
                    <div class="list-group list-group-flush">
                        <div class="store-item"><span>01.</span> <b>Luxury Jewelry Q1:</b> 442 Hai Bà Trưng, P. Tân Định, Quận 1</div>
                        <div class="store-item"><span>02.</span> <b>Luxury Jewelry Q10:</b> 275 Tô Hiến Thành, P. 12, Quận 10</div>
                        <div class="store-item"><span>03.</span> <b>Luxury Jewelry Q3:</b> 179A Cách mạng tháng 8, P. 5, Quận 3</div>
                        <div class="store-item"><span>04.</span> <b>Luxury Jewelry Phú Nhuận:</b> 99 Lê Văn Sỹ, P. 14, Q. Phú Nhuận</div>
                        <div class="store-item"><span>05.</span> <b>Luxury Jewelry Q5:</b> 213A Nguyễn Trãi, P. 2, Quận 5</div>
                        <div class="store-item"><span>06.</span> <b>Luxury Jewelry Gò Vấp:</b> 529 Quang Trung, P. 10, Q. Gò Vấp</div>
                        <div class="store-item"><span>07.</span> <b>Luxury Jewelry Tân Phú:</b> 117 Cây Keo, P. Hiệp Tân, Q. Tân Phú</div>
                        <div class="store-item"><span>08.</span> <b>Luxury Jewelry Bình Thạnh:</b> 197 Nguyễn Gia Trí, P. 25, Q. Bình Thạnh</div>
                        <div class="store-item"><span>09.</span> <b>Luxury Jewelry Q7:</b> 391 Nguyễn Thị Thập, P. Tân Phong, Quận 7</div>
                        <div class="store-item"><span>10.</span> <b>Luxury Jewelry Thủ Đức:</b> 215 Võ Văn Ngân, P. Linh Chiểu, Thủ Đức</div>
                        <div class="store-item"><span>11.</span> <b>Luxury Jewelry Q6:</b> 45 Bà Hom, P. 13, Quận 6</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Tạo bản đồ với Leaflet
    var map = L.map('mapid').setView([10.776, 106.67], 12); // Vị trí trung tâm TP.HCM

    // Sử dụng OpenStreetMap làm lớp bản đồ
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const diadiemList = [
        { ten: "Luxury Jewelry Q1", lat: 10.7891, lng: 106.6907, dc: "442 Hai Bà Trưng, Q.1" },
        { ten: "Luxury Jewelry Q10", lat: 10.7780, lng: 106.6670, dc: "275 Tô Hiến Thành, Q.10" },
        { ten: "Luxury Jewelry Q3", lat: 10.7825, lng: 106.6747, dc: "179A Cách mạng tháng 8, Q.3" },
        { ten: "Luxury Jewelry Phú Nhuận", lat: 10.7933, lng: 106.6716, dc: "99 Lê Văn Sỹ, Phú Nhuận" },
        { ten: "Luxury Jewelry Q5", lat: 10.7589, lng: 106.6821, dc: "213A Nguyễn Trãi, Q.5" },
        { ten: "Luxury Jewelry Gò Vấp", lat: 10.8265, lng: 106.6763, dc: "529 Quang Trung, Gò Vấp" },
        { ten: "Luxury Jewelry Tân Phú", lat: 10.7709, lng: 106.6268, dc: "117 Cây Keo, Tân Phú" },
        { ten: "Luxury Jewelry Bình Thạnh", lat: 10.8031, lng: 106.7125, dc: "197 Nguyễn Gia Trí, Bình Thạnh" },
        { ten: "Luxury Jewelry Q7", lat: 10.7406, lng: 106.7020, dc: "391 Nguyễn Thị Thập, Q.7" },
        { ten: "Luxury Jewelry Thủ Đức", lat: 10.8524, lng: 106.7580, dc: "215 Võ Văn Ngân, Thủ Đức" },
        { ten: "Luxury Jewelry Q6", lat: 10.7512, lng: 106.6345, dc: "45 Bà Hom, Q.6" }
    ];

    var bounds = L.latLngBounds(); // Tạo một vùng bao quanh tất cả các điểm

    // Đặt các marker cho từng cửa hàng
    diadiemList.forEach(item => {
        var popupContent = "<b>" + item.ten + "</b><br>" + item.dc;
        var marker = L.marker([item.lat, item.lng]).addTo(map)
            .bindPopup(popupContent);

        // Mở rộng bounds để bao gồm tất cả các điểm
        bounds.extend([item.lat, item.lng]);
    });

    // Tự động zoom và căn chỉnh bản đồ để bao gồm tất cả các điểm
    map.fitBounds(bounds, { padding: [50, 50] });
</script>
    <?php include 'footer.php'; ?>
</body>
</html>