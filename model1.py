import os
import cv2
import numpy as np
import tensorflow as tf
from flask import Flask, request, jsonify
from PIL import Image
import cv2
import tensorflow 
from PIL import Image
import numpy as np

def load_brain_model(image_path):

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
        print(f"Predicted Class: {result[0]}")
    
load_brain_model('C:\\xampp\\htdocs\\pro\\ai\\img\\WhatsApp Image 2024-12-28 at 16.56.56_482b74b4.jpg')