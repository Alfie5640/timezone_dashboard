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
            alert("Timezone added!");

            // Clear the form
            document.getElementById("timezoneDescription").value = "";
            document.getElementById("hiddenForm").style.display = "none";

            // Clear existing timezones
            const container = document.querySelector(".maincontent");
            const timezoneElements = Array.from(container.querySelectorAll(".timezoneElement")).slice(2); // skip first two
            timezoneElements.forEach(el => el.remove());

            await loadTimezones();
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

                let description = document.createElement("p");
                description.textContent = desc;

                const delButton = document.createElement("button");
                delButton.textContent = 'x';
                delButton.addEventListener("click", () => deleteTimezone(tzName));

                const updateButton = document.createElement("button");
                updateButton.textContent = "🖊️";
                updateButton.addEventListener("click", () => {
                    const input = document.createElement("input");
                    input.type = "text";
                    input.value = description.textContent;
                    input.placeholder = "Enter new description";
                    input.classList.add("edit-input");

                    // Replace the description with the input temporarily
                    description.replaceWith(input);
                    input.focus();

                    // When user presses Enter , update description
                    input.addEventListener("keydown", async (e) => {
                        if (e.key === "Enter") {
                            const newDescription = input.value.trim();

                            const success = await updateDescription(tzName, newDescription);
                            if (success) {
                                // Replace input back with updated text
                                const newDesc = document.createElement("p");
                                newDesc.textContent = newDescription;
                                input.replaceWith(newDesc);
                                description.textContent = newDescription;
                                description = newDesc;
                            } else {
                                // If failed, revert to old description
                                input.replaceWith(description);
                            }
                        }
                    });
                });

                tzElement.appendChild(title);
                tzElement.appendChild(utcOffset);
                tzElement.appendChild(localTime);
                tzElement.appendChild(description);
                tzElement.appendChild(delButton);
                tzElement.appendChild(updateButton);
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


async function deleteTimezone(tzName) {
    try {
        const token = localStorage.getItem("jwt");

        const response = await fetch(`/api/timezone/${encodeURIComponent(tzName)}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            }
        });

        const data = await response.json();

        if (data.success) {
            alert("Successfully deleted timezone");

            // Remove the matching timezone element from the DOM
            document.querySelectorAll(".timezoneElement h1").forEach(h1 => {
                if (h1.textContent === tzName) {
                    h1.parentElement.remove();
                }
            });
        } else {
            console.log(data.message);
        }

    } catch (err) {
        console.error(err);
    }
}

async function updateDescription(tzName, desc) {
    try {
        const token = localStorage.getItem("jwt");
        
        const response = await fetch(`/api/timezone/${encodeURIComponent(tzName)}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            },
            body: JSON.stringify({
                desc: desc
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log(data.message);
            return true;
        } else {
            return false;
        }
        
    } catch(err) {
        console.log(err);
        return false;
    }
}




document.getElementById("logoutButton").addEventListener("click", () => {
    localStorage.removeItem("jwt");
    window.location.href = "/html/login.php";
});

loadTimezones();
