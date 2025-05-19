<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        :root {
            --primary: #e8c07d;
            --secondary: #9b5de5;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --accent: #ff6b6b;
        }

        body {
            font-family: 'Montserrat', sans-serif;
        }

        .luxury-footer {
            background: black;
            color: white;
            border-top: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
        }

        .luxury-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 70% 30%, rgba(155, 93, 229, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .footer-column h4 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 2rem;
            position: relative;
            color: var(--primary);
            letter-spacing: 1px;
        }

        .footer-column h4::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 3px;
        }

        .footer-about p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            display: inline-block;
            position: relative;
            padding-left: 20px;
            font-weight: 300;
        }

        .footer-links a::before {
            content: '→';
            position: absolute;
            left: -15px;
            opacity: 0;
            color: var(--primary);
            transition: all 0.4s ease;
        }

        .footer-links a:hover {
            color: white;
            transform: translateX(15px);
            padding-left: 25px;
        }

        .footer-links a:hover::before {
            left: 0;
            opacity: 1;
        }

        .contact-info {
            list-style: none;
            padding: 0;
        }

        .contact-info li {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            position: relative;
            padding-left: 35px;
        }

        .contact-info i {
            position: absolute;
            left: 0;
            top: 5px;
            color: var(--primary);
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .contact-info span {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 300;
        }

        .contact-info li:hover i {
            transform: scale(1.2);
            color: var(--accent);
        }

        .contact-info li:hover span {
            color: white;
        }

        .social-link {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            color: white;
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .social-link::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: all 0.4s ease;
        }

        .social-link i {
            position: relative;
            z-index: 1;
        }

        .social-link:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .social-link:hover::after {
            opacity: 1;
        }

        .footer-bottom {
            background: rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .footer-bottom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        }

        .copyright {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 300;
        }

        .copyright strong {
            color: var(--primary);
            font-weight: 500;
        }

        .business-info {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
            font-weight: 300;
        }
    </style>
</head>

<body>
    <footer class="luxury-footer pt-5">
        <div class="container position-relative">
            <div class="row g-4">
                <div class="col-lg- col-md-6">
                    <div class="footer-column">
                        <h4>Về chúng tôi</h4>
                        <div class="footer-about">
                            <p>LuxeStay là đội ngũ đầy nhiệt huyết, cam kết mang đến những sản phẩm và dịch vụ tốt
                                nhất, đồng hành và phát triển cùng khách hàng qua từng trải nghiệm mua sắm chất lượng.
                            </p>
                            <div class="d-flex gap-3">
                                <a href="https://www.facebook.com" class="social-link"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.twitter.com" class="social-link"><i class="fab fa-twitter"></i></a>
                                <a href="https://www.instargram.com" class="social-link"><i class="fab fa-instagram"></i></a>
                                <a href="https://www.youtube.com" class="social-link"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="footer-column">
                        <h4>Liên kết nhanh</h4>
                        <ul class="footer-links">
                            <li><a href="#home">Trang chủ</a></li>
                            <li><a href="#hotels">Khách sạn</a></li>
                            <li><a href="#services">Dịch vụ</a></li>
                            <li><a href="#about">Về chúng tôi</a></li>
                            <li><a href="#contact">Liên hệ</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="footer-column">
                        <h4>Liên hệ</h4>
                        <ul class="contact-info">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>19 Đường Nguyễn Hữu Thọ, Phường Tân Phong, Quận 7, TP.HCM</span>
                            </li>
                            <li>
                                <i class="fas fa-phone-alt"></i>
                                <span>(+84) 0912345678</span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span>LuxeStay@gmail.com</span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span>8:00 - 22:00, T2 - CN</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Map -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-column">
                        <div id="map" style="height: 200px; border-radius: 10px; overflow: hidden; margin-top: 1rem;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom py-4 mt-4">
            <div class="container text-center">
                <p class="copyright mb-1">© 2025 - Bản quyền thuộc về <strong>LuxeStay</strong></p>
                <p class="business-info mb-0">Giấy chứng nhận ĐKKD số: 0123456789 do Sở KHĐT TP.HCM cấp</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map("map").setView([10.731929, 106.699189], 16);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© LuxeStay"
        }).addTo(map);

        L.marker([10.731929, 106.699189]).addTo(map)
            .bindPopup("Our Location")
            .openPopup();
    });
</script>

</html>