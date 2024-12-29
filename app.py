from flask import Flask, request, jsonify
from PIL import Image
import io
import base64
import numpy as np
import cv2
from keras.models import load_model

app = Flask(__name__)

# Load the model
model = load_model('BrainTumor10EpochsCategorical.h5')

# AI model function to predict the class of the image
def predict_image(image_data):
    img = np.array(image_data)
    img = cv2.resize(img, (64, 64))
    img = img / 255.0
    input_img = np.expand_dims(img, axis=0)
    # Make prediction
    prediction = model.predict(input_img)
    # Get the predicted class (e.g., 0 or 1 for binary classification)
    predicted_class = np.argmax(prediction, axis=1)
    return predicted_class[0] 
@app.route('/process_image', methods=['POST'])
def process_image():
    # Get the base64-encoded image from the request
    data = request.form.get('image')  
    
    if data:
        # Decode the base64 image data
        image_data = base64.b64decode(data)
        image = Image.open(io.BytesIO(image_data))
        # Process the image with the AI model
        result = predict_image(image)
        # Return the AI result
        return jsonify({'prediction': result})
    return jsonify({'error': 'No image data provided'}), 400

if __name__ == '__main__':
    # Run the Flask app locally
    app.run(debug=True, host='0.0.0.0', port=5000)
