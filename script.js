function uploadImage(model) {
    var fileInput = document.getElementById("upload-button");
    var id = model;

    if (!fileInput.files.length) {
        alert("Please select an image!");
        return;
    }

    var formData = new FormData();
    formData.append("image", fileInput.files[0]); // Add the file
    formData.append("id", id); // Add the ID to the FormData object

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    // Parse the JSON response from PHP (which was forwarded from Flask)
                    var response = xhr.responseText;

                    // Display the message based on response
                    document.getElementById("response").innerHTML = response.message;
                } catch (error) {
                    console.error("Error parsing response:", error);
                    alert("An error occurred while processing the response.");
                }
            } else {
                alert("Error uploading image: " + xhr.status);
            }
        }
    };

    // Send the data to upload.php which will forward it to Flask
    xhr.open("POST", "upload.php", true); // Send to PHP script
    xhr.send(formData); // Send the FormData object
}
