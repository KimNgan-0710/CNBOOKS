document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".like-btn").forEach(button => {
        button.addEventListener("click", function () {
            let postId = this.dataset.id;
            let countSpan = this.querySelector(".like-count");
            let heartIcon = this.querySelector(".heart-icon");
            
            fetch("like.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật số lượt thích
                    countSpan.textContent = data.likes;
                    
                    // Thay đổi trạng thái nút like
                    if (data.action === 'liked') {
                        button.classList.add('liked');
                    } else {
                        button.classList.remove('liked');
                    }
                    
                    // Hiệu ứng nhảy
                    button.classList.add('pulse');
                    setTimeout(() => {
                        button.classList.remove('pulse');
                    }, 300);
                }
            })
            .catch(error => console.error("Lỗi:", error));
        });
    });

    document.querySelectorAll(".comment-form").forEach(form => {
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            let postId = this.dataset.id;
            let formData = new FormData(this);
            formData.append("post_id", postId);

            fetch("comment.php", { method: "POST", body: formData })
            .then(response => response.json())
            .then(() => location.reload());
        });
    });

    document.querySelectorAll(".load-more").forEach(button => {
        button.addEventListener("click", function () {
            let postId = this.dataset.id;
            let commentList = document.getElementById("comments-" + postId);

            fetch("load_comments.php?post_id=" + postId)
            .then(response => response.json())
            .then(data => {
                commentList.innerHTML = "";
                data.forEach(comment => {
                    let li = document.createElement("li");
                    li.innerHTML = `<strong>${comment.user_name}:</strong> ${comment.comment}`;
                    commentList.appendChild(li);
                });
                this.style.display = "none"; // Ẩn nút "Xem thêm" sau khi tải hết bình luận
            });
        });
    });
});
