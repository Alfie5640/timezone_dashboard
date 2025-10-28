document.getElementById("addButton").addEventListener("click", openForm);

function openForm() {
    const form = document.getElementById("hiddenForm");

    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "flex";
    } else {
        form.style.display = "none";
    }
}

async function addTimezone() {

    try {
        const token = localStorage.getItem("jwt");

        const timezone = document.getElementById("timezoneSelect").value;
        const description = document.getElementById("timezoneDescription").value;

        const response = await fetch("/api/timezone", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            },
            body: JSON.stringify({
                timezone: timezone,
                description: description
            })
        });

        const data = await response.json();

        if (data.success) {
            alert("timezone added");
        } else {
            alert(data.message);
        }

    } catch (err) {
        console.log(err);
    }
}

async function loadTimezones() {
    try {
        const token = localStorage.getItem("jwt");
        const response = await fetch("/api/timezone", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            }
        });

        const data = await response.json();

        if (data.success) {
            const container = document.querySelector(".maincontent");

            for (let i = 0; i < data.timezone.length; i++) {
                const tzName = data.timezone[i];
                const offset = data.offset[i];
                const desc = data.descriptions ? data.descriptions[i] : "";

                const tzElement = document.createElement("div");
                tzElement.classList.add("timezoneElement");

                // Create child elements separately for live updates
                const title = document.createElement("h1");
                title.textContent = tzName;

                const utcOffset = document.createElement("h2");
                utcOffset.textContent = `UTC ${offset}`;

                const localTime = document.createElement("h2");
                localTime.classList.add("localTime");

                const description = document.createElement("p");
                description.textContent = desc;

                tzElement.appendChild(title);
                tzElement.appendChild(utcOffset);
                tzElement.appendChild(localTime);
                tzElement.appendChild(description);
                container.appendChild(tzElement);

                // Initialize and keep updating the displayed local time
                updateLocalTime(localTime, tzName);
                setInterval(() => updateLocalTime(localTime, tzName), 1000);
            }
        } else {
            console.error("Failed to load timezones:", data.message);
            alert("Could not load your timezones. Please try again.");
        }
    } catch (err) {
        console.error("Error loading timezones:", err);
        alert("An unexpected error occurred.");
    }
}

// Helper: display current local time for a timezone
function updateLocalTime(element, tzName) {
    const now = new Date();
    try {
        const formatted = now.toLocaleTimeString("en-GB", {
            hour12: false,
            timeZone: tzName
        });
        element.textContent = `Current time: ${formatted}`;
    } catch (err) {
        element.textContent = "Invalid timezone";
    }
}


loadTimezones();
