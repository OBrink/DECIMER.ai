# Initializing and importing necessary libararies

import tensorflow as tf
from rdkit import Chem
import os
import pickle
import re
import helper
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"


# Print tensorflow version
print("Tensorflow version: "+tf.__version__)

# Always select a GPU if available
os.environ["CUDA_VISIBLE_DEVICES"] = "0"

# Scale memory growth as needed
gpus = tf.config.experimental.list_physical_devices("GPU")
for gpu in gpus:
    tf.config.experimental.set_memory_growth(gpu, True)


# Load the packed model forward
# Load important pickle files which consists the tokenizers and the maxlength setting
base = os.path.split(__file__)[0]
inp_lang = pickle.load(open(os.path.join(base, "models/assets/tokenizer_input.pkl"), "rb"))
targ_lang = pickle.load(open(os.path.join(base, "models/assets/tokenizer_target.pkl"), "rb"))
inp_max_length = pickle.load(open(os.path.join(base, "models/assets/max_length_inp.pkl"), "rb"))
reloaded_forward = tf.saved_model.load(os.path.join(base, "models/translator_forward"))

# Not needed in web app
# Load the packed model forward
# reloaded_reverse = tf.saved_model.load("STOUT_Web/models/translator_reverse")


def translate_forward(sentence_input: str) -> str:
    """Takes user input splits them into words and generates tokens.
    Tokens are then passed to the model and the model predicted tokens are retrieved.
    The predicted tokens gets detokenized and the final result is returned in a string format.

    Args:
        sentence_input (str): user input SMILES in string format.

    Returns:
        result (str): The predicted IUPAC names in string format.
    """
    if len(sentence_input) == 0:
        return ''
    sentence_input = Chem.MolToSmiles(Chem.MolFromSmiles(sentence_input),kekuleSmiles=True)
    splitted_list = list(sentence_input)
    Tokenized_SMILES = re.sub(r"\s+(?=[a-z])", "", " ".join(map(str, splitted_list)))
    decoded = helper.tokenize_input(Tokenized_SMILES, inp_lang, inp_max_length)
    result_predited = reloaded_forward(decoded)
    result = helper.detokenize_output(result_predited, targ_lang)
    return result


# def translate_reverse(sentence_input: str) -> str:
#     """Takes user input splits them into words and generates tokens.
#     Tokens are then passed to the model and the model predicted tokens are retrieved.
#     The predicted tokens gets detokenized and the final result is returned in a string format.

#     Args:
#         sentence_input (str): user input IUPAC names in string format.

#     Returns:
#         result (str): The predicted SMILES in string format.
#     """

#     # Load important pickle files which consists the tokenizers and the maxlength setting
#     targ_lang = pickle.load(open("STOUT_Web/models/assets/tokenizer_input.pkl", "rb"))
#     inp_lang = pickle.load(open("STOUT_Web/models/assets/tokenizer_target.pkl", "rb"))
#     inp_max_length = pickle.load(open("STOUT_Web/models/assets/max_length_targ.pkl", "rb"))

#     splitted_list = list(sentence_input)
#     Tokenized_SMILES = " ".join(map(str, splitted_list))
#     decoded = helper.tokenize_input(Tokenized_SMILES, inp_lang, inp_max_length)

#     result_predited = reloaded_reverse(decoded)
#     result = helper.detokenize_output(result_predited, targ_lang)

#     return result
