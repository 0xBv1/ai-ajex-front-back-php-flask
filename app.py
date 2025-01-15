import os
import cv2
import numpy as np
import tensorflow as tf
from flask import Flask, request, jsonify
from PIL import Image
import tempfile

app = Flask(__name__)

# Allowed file extensions (add more if necessary)
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif'}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS
def run(id, image_path):
    
    if id == "brain":
        return load_brain_model(image_path)
    elif id =="skin":
        return load_skin_model(image_path)
    else:
        return {"status": "error", "message": "Invalid ID provided"}, 400
def load_skin_model(image_path):
    print(image_path)
    # Load the skin model
    model_skin_cancer = tf.keras.models.load_model('C:\\xampp\\htdocs\\pro\\ai\\Skin_Cancer.keras')
    test_image = tf.keras.preprocessing.image.load_img(image_path, target_size=(32, 32))
    test_image = tf.keras.preprocessing.image.img_to_array(test_image)
    test_image = np.expand_dims(test_image, axis=0)
    result = model_skin_cancer.predict(test_image)
    prediction = 'benign' if result[0][0] == 0 else 'malignant'
    return  prediction

def load_brain_model(image_path):
    try:
        # Load the pre-trained model
        model = tf.keras.models.load_model('C:\\xampp\\htdocs\\pro\\ai\\BrainTumor10EpochsCategorical.h5')

        # Read the image using OpenCV
        image = cv2.imread(image_path)

        if image is None:
            raise ValueError(f"Image not found or path is incorrect: {image_path}")

        # Convert image to PIL format and resize
        img = Image.fromarray(cv2.cvtColor(image, cv2.COLOR_BGR2RGB))
        img = img.resize((64, 64))

        # Convert to numpy array and prepare for prediction
        img = np.array(img)
        input_img = np.expand_dims(img, axis=0)

        # Normalize the image
        input_img = input_img / 255.0

        # Predict the class
        result = np.argmax(model.predict(input_img), axis=-1)
        
        final_result = 'not tumor detected' if result[0][0] == 0 else 'tumor detected'
        # Return the prediction result
        return final_result
    except Exception as e:
        return {"status": "error", "message": str(e)}, 500
@app.route('/one_for_all', methods=['POST'])
def handle_post():
    
    id = request.form.get('id', 'unknown')

    # Ensure 'image' is in request.files
    if 'image' not in request.files:
        return jsonify({"status": "error", "message": "No image file provided"}), 400

    file = request.files['image']

    # Check if the file is allowed
    if not allowed_file(file.filename):
        return jsonify({"status": "error", "message": "Invalid file format"}), 400

    # Save the file to a temporary directory to avoid conflicts
    with tempfile.NamedTemporaryFile(delete=False) as temp_file:
        image_path = temp_file.name
        file.save(image_path)

    # Run the appropriate processing function based on ID
    result = run(id, image_path)
    # print(result)
    return jsonify({"status" : "success", "message" : f"{result}"}), 200
    

if __name__ == '__main__':
    app.run(debug=True)
