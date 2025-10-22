document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const username = document.getElementById("loginUsername").value;
    const password = document.getElementById("loginPassword").value;

    try {
        const response = await fetch("/api/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                username: username,
                password: password,
            })
        });

        const data = await response.json();

        const resultDiv = document.getElementById("error");
        if (data.success && data.token) {

            //Store JWT. [NOTE dont store in local storage normally as vulnerable to xss]
            localStorage.setItem("jwt", data.token);

            // Redirect to corresponding home page
            window.location.href="../html/timeHome.php";

        } else {
            resultDiv.textContent = data.message;
            resultDiv.style.color = "red";
        }

    } catch (err) {
        console.error("Fetch error:", err);
        document.getElementById("error").textContent = "Error contacting server.";
    }
});

document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const username = document.getElementById("regUsername").value;
    const password = document.getElementById("regPassword").value;

    try {
        const response = await fetch("/api/register", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                username: username,
                password: password,
            })
        });

        const data = await response.json();

        const resultDiv = document.getElementById("error");

        if (data.success) {
            resultDiv.textContent = data.message;
            resultDiv.style.color = 'green';
            // Redirect to home page

        } else {
            resultDiv.textContent = data.message;
            resultDiv.style.color = 'red';
        }

    } catch (err) {
        console.error("Fetch error:", err);
        document.getElementById("error").textContent = "Error contacting server.";
    }
});
