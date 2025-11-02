<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Quicksand:wght@300..700&family=Racing+Sans+One&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/home.css">
</head>

<body>
    <div id="header">
        <div id="username">
            <h1 id="name"></h1>

            <h1 id="time">
            </h1>
        </div>

        <div id="logoutContainer">
            <button id="logoutButton">Logout</button>
        </div>
    </div>

    <div class="maincontent">
        <!-- Added timezone elements go here -->

        <div class="timezoneElement">
            <button id="addButton"> + </button>
        </div>

        <div class="timezoneElement" id="hiddenForm" style="display:none;">
            <h1>ADD</h1>

            <div class="formElement">
                <label>*Timezone: </label>
                <select id="timezoneSelect">
                </select>
            </div>
            <div class="formElement">
                <label>Description: </label>
                <textarea id="timezoneDescription"></textarea>
            </div>
            <input type="submit" value="SUBMIT" id="submitButton" onclick="addTimezone()">
        </div>

    </div>

    <script>
        {
            const token = localStorage.getItem("jwt");

            fetch("/api/decode", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": `Bearer ${token}`
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('name').textContent = data.username;
                    } else {
                        document.getElementById('name').textContent = data.message;
                    }
                })
                .catch(err => console.error("Decode error:", err));

            // --- Clock updater ---
            function updateClock() {
                const now = new Date();
                const formatted = now.toLocaleTimeString('en-GB', {
                    hour12: false
                });

                const mediaQuery = window.matchMedia("(max-width: 700px)");

                if (!mediaQuery.matches) {
                    document.getElementById('time').textContent = formatted;
                }
            }

            // Call once immediately, then every second
            updateClock();
            setInterval(updateClock, 1000);

            // --- Populate timezone dropdown once ---
            const timezoneSelect = document.getElementById("timezoneSelect");

            if (typeof Intl.supportedValuesOf === "function") {
                const timezones = Intl.supportedValuesOf("timeZone");
                timezones.forEach(tz => {
                    const option = document.createElement("option");
                    option.value = tz;
                    option.textContent = tz;
                    timezoneSelect.appendChild(option);
                });
            }
        }

    </script>

    <script src="/js/home.js"></script>
</body>

</html>
