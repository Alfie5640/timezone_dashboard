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

            // Loop through the arrays (theyre parallel)
            for (let i = 0; i < data.timezone.length; i++) {
                const tzName = data.timezone[i];
                const offset = data.offset[i];

                const tzElement = document.createElement("div");
                tzElement.classList.add("timezoneElement");

                tzElement.innerHTML = `
                    <h1>${tzName}</h1>
                    <p>UTC ${offset >= 0 ? "+" : ""}${offset}</p>
                `;

                container.appendChild(tzElement);
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


loadTimezones();
