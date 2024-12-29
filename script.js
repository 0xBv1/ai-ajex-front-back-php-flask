function uploadImage() {
    var fileInput = document.getElementById("upload-button");
    if (!fileInput.files.length) {
        alert("Please select an image!");
        return;
    }

    var formData = new FormData();
    formData.append("image", fileInput.files[0]);

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                document.getElementById("response").innerHTML = xhr.responseText;
            } else {
                alert("Error uploading image: " + xhr.status);
            }
        }
    };

    xhr.open("POST", "upload.php", true);
    xhr.send(formData);
}
