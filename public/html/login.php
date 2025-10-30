<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Quicksand:wght@300..700&family=Racing+Sans+One&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/login.css">

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const loginTab = document.getElementById("loginTab");
            const registerTab = document.getElementById("registerTab");
            const loginForm = document.getElementById("loginForm");
            const registerForm = document.getElementById("registerForm");
            const indicator = document.querySelector(".tab-indicator");

            loginTab.addEventListener("click", () => {
                loginTab.classList.add("active");
                registerTab.classList.remove("active");
                loginForm.classList.add("active");
                registerForm.classList.remove("active");
                indicator.style.transform = "translateX(0%)";
            });

            registerTab.addEventListener("click", () => {
                registerTab.classList.add("active");
                loginTab.classList.remove("active");
                registerForm.classList.add("active");
                loginForm.classList.remove("active");
                indicator.style.transform = "translateX(100%)";
            });
        });

    </script>
</head>

<body>

    <div class="maincontent">
        <img src="../clockImg.png" alt="clock">

        <div class="tab-header">
            <div class="tab-indicator"></div>
            <div class="tab active" id="loginTab">Login</div>
            <div class="tab" id="registerTab">Register</div>
        </div>

        <div class="form-container">
            <form id="loginForm" class="form active">
                <input type="text" id="loginUsername" placeholder="Username:" required>

                <input type="password" id="loginPassword" placeholder="Password:" required>

                <input type="submit" value="Login">
            </form>

            <form id="registerForm" class="form">
                <input type="text" id="regUsername" placeholder="Username:" required>

                <input type="password" id="regPassword" placeholder="Password:" required>

                <input type="submit" value="Register">
            </form>
        </div>

        <div id="error">
        </div>

    </div>

    <script src="../js/login.js"></script>
</body>

</html>
