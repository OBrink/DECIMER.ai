import sys
import json
from typing import List
from rdkit import Chem


def decode_smiles_array(str_smiles_arr: str) -> List[str]:
    """
    This function takes json_encoded array of SMILES str from the
    DECIMERController and returns a list of SMILES strings

    Args:
        str_smiles_arr (str): stringified array with SMILES strings
                              as returned by the php function json_decode()

    Returns:
        List[str]: List of mol block strings
    """
    str_smiles_arr = str_smiles_arr[1:-1]
    str_smiles_arr = '["' + str_smiles_arr + '"]'
    str_smiles_arr = str_smiles_arr.replace(',', '","')
    str_smiles_arr = str_smiles_arr.replace("\\\\", "\\")
    str_smiles_arr = str_smiles_arr.replace("\\/", "/")
    smiles_arr = eval(str_smiles_arr)
    return smiles_arr


def main():
    """
    This script takes a stringified array with mol str from sys.argv and
    prints a stringified list of strings ("valid" or "invalid") that indicate
    whether or not the given SMILES represent valid molecules
    """
    smiles_arr = decode_smiles_array(sys.argv[1])
    validity_arr = []
    for smiles in smiles_arr:            
        mol = Chem.MolFromSmiles(smiles)
        if mol:
            validity_arr.append("valid")
        else:
            validity_arr.append("invalid")
    print(json.dumps(validity_arr))


if __name__ == '__main__':
    main()
