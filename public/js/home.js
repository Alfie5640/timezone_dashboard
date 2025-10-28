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
