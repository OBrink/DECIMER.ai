import sys
import json
from typing import List
from rdkit import Chem


def decode_mol_block_array(str_mol_block_arr: str) -> List[str]:
    """
    This function takes json_encoded array from the StoutController in Laravel
    and returns a list of mol block strings that can be processed with RDKit

    Args:
        str_mol_block_arr (str): stringified array with mol block strings
                                 as returned by the php function json_decode()

    Returns:
        List[str]: List of mol block strings
    """
    str_mol_block_arr = str_mol_block_arr[1:-1]
    str_mol_block_arr = '["' + str_mol_block_arr + '"]'
    str_mol_block_arr = str_mol_block_arr.replace(',', '","')
    mol_block_arr = eval(str_mol_block_arr)
    return mol_block_arr


def main():
    """
    This script takes a stringified array with mol str from sys.argv and
    prints the corresponding stringified array with SMILES representations
    """
    mol_block_arr = decode_mol_block_array(sys.argv[1])
    smiles = []
    
    for mol_block_str in mol_block_arr:
        mol = Chem.MolFromMolBlock(mol_block_str)
        # empty mol block str from Ketcher have length of 102
        if mol and len(mol_block_str) > 102:
            smiles.append(Chem.MolToSmiles(mol, kekuleSmiles=True))
        else:
            smiles.append("invalid")
    print(json.dumps(smiles))


if __name__ == '__main__':
    main()
