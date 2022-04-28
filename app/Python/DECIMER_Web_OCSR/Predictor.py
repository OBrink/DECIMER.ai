import config
import os
import sys
import logging
import pickle
import json
import tensorflow as tf

# Silence tensorflow model loading warnings.
logging.getLogger('absl').setLevel('ERROR')

# Silence tensorflow errors. optional not recommened if your model is not working properly.
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"
print(tf.__version__)

# Set the absolute path
HERE = os.path.dirname(os.path.abspath(__file__))

# Set model to run on default GPU and allow memory to grow as much as needed.
# This allows us to run multiple instances of inference in the same GPU.
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
gpus = tf.config.experimental.list_physical_devices("GPU")
for gpu in gpus:
    tf.config.experimental.set_memory_growth(gpu, True)

# Load important pickle files which consists the tokenizers and the maxlength setting

tokenizer = pickle.load(open(os.path.join(HERE, "DECIMER_Packed_model/assets/tokenizer_SMILES.pkl"), "rb"))
# Load DECIMER model_packed
DECIMER_V2 = tf.saved_model.load(os.path.join(HERE,'DECIMER_Packed_model'))

def main():
    """
    This function take the path of the image as user input
    and returns the predicted SMILES as output in CLI.
    
    Agrs:
        str: image_path

    Returns:
        str: predicted SMILES

    """
    if len(sys.argv) != 2:
        print("Usage: {} $image_path".format(sys.argv[0]))
    else:
        path = sys.argv[1]
        # SMILES = predict_SMILES(path)
        # print(SMILES)
        inp = sys.argv[1].replace(',', '","').replace('[', '["').replace(']', '"]')
        SMILES = []
        paths = []
        for path in eval(inp):
            file_dir = '../storage/app/public/media/'
            file_name = os.path.split(path)[1]
            paths.append(os.path.join(file_dir, file_name))
        paths=eval(inp)
        # with Pool(4) as p:
        #     # SMILES = p.map(predict_SMILES, paths)
        # SMILES = map(predict_SMILES, paths)
        for path in paths:
            SMILES.append(predict_SMILES(path))
        print(json.dumps(list(SMILES)))

def detokenize_output(predicted_array: int) -> str:
    """
    This function takes the predited tokens from the DECIMER model
    and returns the decoded SMILES string.

    Args:
        predicted_array (int): Predicted tokens from DECIMER

    Returns:
        (str): SMILES representation of the molecule
    """
    outputs = [tokenizer.index_word[i] for i in predicted_array[0].numpy()]
    prediction = ''.join([str(elem) for elem in outputs]).replace("<start>","").replace("<end>","")
    
    return prediction



def predict_SMILES(image_path: str) -> str:
    """
    This function takes an image path (str) and returns the SMILES 
    representation of the depicted molecule (str).

    Args:
        image_path (str): Path of chemical structure depiction image

    Returns:
        (str): SMILES representation of the molecule in the input image
    """
    chemical_structure = config.decode_image(image_path)
    predicted_tokens = DECIMER_V2(chemical_structure)
    predicted_SMILES = detokenize_output(predicted_tokens)


    return predicted_SMILES


if __name__ == "__main__":
    main()