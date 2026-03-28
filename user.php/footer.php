<footer>
    <div class="footer-container">
        <div class="footer-logo">
            <i class="fas fa-book-reader"></i> CNBooks 💙✨
        </div>
        
        <div class="footer-socials">
            <a href="https://www.facebook.com/cinngnn/" target="_blank" class="fb-link">
                <i class="fab fa-facebook-f"></i> Facebook 🩵
            </a>
            <a href="https://www.instagram.com/cinn.ngn/" target="_blank" class="ig-link">
                <i class="fab fa-instagram"></i> Instagram 🤍
            </a>
        </div>

        <p class="footer-text">© 2025 CNBooks - Chúc quý khách đọc sách vui vẻ ạaa! 📖✨🐰</p>
    </div>

    <img src="/post/image/motsach.png" class="sticker sticker1">
    <img src="/post/image/motsach.png" class="sticker sticker2">
</footer>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@400;700&display=swap');

    footer {
        background: url('/post/image/bgr.jpg') no-repeat center center/cover;
        text-align: center;
        padding: 40px 0;
        position: relative;
        overflow: hidden;
        color: white;
        font-family: 'Quicksand', sans-serif;
    }

    .footer-container {
        max-width: 800px;
        margin: auto;
    }

    .footer-logo {
        font-size: 26px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-shadow: 2px 2px 5px rgba(255, 255, 255, 0.8);
    }

    .footer-socials {
        margin: 15px 0;
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .footer-socials a {
        font-size: 20px;
        color: white;
        text-decoration: none;
        padding: 10px;
        border-radius: 15px;
        transition: 0.3s;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .footer-socials a:hover {
        transform: scale(1.1);
        background: rgba(255, 255, 255, 0.4);
    }

    .footer-text {
        font-size: 16px;
        margin-top: 10px;
        font-weight: bold;
        text-shadow: 1px 1px 4px rgba(255, 255, 255, 0.7);
    }

    /* Sticker đáng yêu */
    .sticker {
        position: absolute;
        width: 100px;
        animation: float 1.5s infinite alternate ease-in-out;
    }

    .sticker1 {
        left: 30%;
        bottom: 30px;
        transform: rotate(-10deg);
    }

    .sticker2 {
        right: 30%;
        bottom: 30px;
        transform: rotate(10deg);
    }

    @keyframes float {
        0% {
            transform: translateY(0px);
        }
        100% {
            transform: translateY(12px);
        }
    }
</style>
