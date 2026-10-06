document.addEventListener("DOMContentLoaded", function () {
    fetch("footer.html")
        .then(response => {
            if (!response.ok) {
                throw new Error("No se pudo cargar el footer");
            }
            return response.text();
        })
        .then(data => {
            document.getElementById("footer").innerHTML = data;
        })
        .catch(error => {
            console.error("Error al cargar el footer:", error);
        });
});