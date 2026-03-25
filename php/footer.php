<footer class="footer">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <div class="footer-inner">

        <div class="footer-brand">
            <?php
                $showImage = true;
                $showText  = true;
                include __DIR__ . '/logo.php';
            ?>
            <p>Rawis Detour Road, Brgy. Alang-alang, Borongan City, 
                Eastern Samar, Philippines, 6800</p>

            <div class="maps">
                <ul>
                    <li class="map-link">
                        <a href="https://maps.google.com/?q=Rawis+Resort+Hotel" target="_blank">
                            <i class="fas fa-location-dot"></i> View on GMaps
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-socials">
            <h3>Stay Connected</h3>
            <ul class="social-links">
                <li><a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a></li>
                <li><a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                <li><a href="#" aria-label="X (Twitter)"><i class="fab fa-x-twitter"></i></a></li>
            </ul>
            
            <div class="footer-legal-links">
                <a href="#">Privacy Policy</a> | 
                <a href="#">Terms & Conditions</a> | 
                <a href="#">Safety & Security</a>
            </div>
            
            <p class="copyright">© 2026 Rawis Resort Hotel. All Rights Reserved.</p>
        </div>

        <div class="footer-contact">
            <h3>Contact Us</h3>
            <ul class="contact-list">
                <li>
                    <i class="fas fa-phone"></i>
                    <a href="#">0977 183 7288</a>
                </li>
                <li>
                    <i class="fab fa-facebook-messenger"></i>
                    <span>Rawis Resort Hotel</span>
                </li>
                <li>
                    <i class="fas fa-envelope"></i>
                    <a href="#">rawisresorthotel@gmail.com</a>
                </li>
            </ul>
        </div>
    </div>
</footer>
