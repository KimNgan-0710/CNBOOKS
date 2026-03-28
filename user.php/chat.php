<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Bán Sách</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Icon Chat */
        .chatbot-icon {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            animation: float 2s infinite alternate ease-in-out;
        }

        .chatbot-icon img {
            width: 40px;
            height: 40px;
        }

        /* Cửa sổ chat */
        .chatbot-container {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 300px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .chatbot-header {
            background: #0073e6;
            /* Xanh dương đậm hơn */
            color: white;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            white-space: nowrap;
            /* Chữ trên 1 dòng */
        }

        .chatbot-header button {
            background: transparent;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
        }

        .chatbot-body {
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            height: 250px;
            overflow-y: auto;
            border-bottom: 1px solid #ddd;
        }

        .chatbot-footer {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ddd;
        }

        .chatbot-footer input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
        }

        .chatbot-footer button {
            background: rgb(11, 68, 100);
            color: white;
            border: none;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 5px;
        }

        /* Tin nhắn */
        .message {
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 5px;
            max-width: 80%;
        }

        .user-message {
            background: rgb(223, 207, 65);
            align-self: flex-end;
        }

        .bot-message {
            background: rgb(39, 159, 245);
            align-self: flex-start;
        }

        .sticker1 {
            left: -20px;
            top: -10px;
            animation-delay: 0.5s;
            /* Hiệu ứng so le */
        }

        .sticker2 {
            right: -20px;
            bottom: -10px;
            animation-delay: 1s;
            /* Hiệu ứng so le */
        }

        /* Hiệu ứng di chuyển */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            100% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>

<body>

    <!-- Icon Chatbot -->
    <div class="chatbot-icon" id="chatbotIcon">
        <img src="./post/image/chamhoi.jpg" class="sticker sticker1">
        <img src="./post/image/chatbot.jpg" alt="Chat Bot">

        <img src="./post/image/chamthann.jpg" class="sticker sticker2">
    </div>

    <!-- Cửa sổ chatbot -->
    <div class="chatbot-container" id="chatbotContainer">
        <div class="chatbot-header">
            🤖 CNBot - Hỗ Trợ Nhanh!
            <button id="closeChat">❌</button>
        </div>
        <div class="chatbot-body" id="chatbox">
            <div class="bot-message message">Xin chào! Mình có thể giúp gì cho bạn? 😊</div>
        </div>
        <div class="chatbot-footer">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn..." />
            <button id="sendChat">Gửi 💌</button>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        $(document).ready(function() {
            $("#chatbotIcon").click(function() {
                $("#chatbotContainer").fadeIn();
            });

            $("#closeChat").click(function() {
                $("#chatbotContainer").fadeOut();
            });

            function sendMessage() {
                let userMessage = $("#chatInput").val().trim();
                if (userMessage === "") return;

                // Hiển thị tin nhắn người dùng
                $("#chatbox").append("<div class='message user-message'>" + userMessage + "</div>");
                $("#chatInput").val("");

                // Gửi dữ liệu đến chatbot PHP
                $.ajax({
                    url: "chatbot.php",
                    method: "POST",
                    data: {
                        message: userMessage
                    },
                    success: function(response) {
                        $("#chatbox").append("<div class='message bot-message'>" + response + "</div>");
                        $("#chatbox").scrollTop($("#chatbox")[0].scrollHeight);
                    }
                });
            }

            // Nút gửi tin nhắn
            $("#sendChat").click(sendMessage);

            // Nhấn Enter để gửi
            $("#chatInput").keypress(function(event) {
                if (event.which === 13) {
                    event.preventDefault();
                    sendMessage();
                }
            });
        });
    </script>

</body>

</html>