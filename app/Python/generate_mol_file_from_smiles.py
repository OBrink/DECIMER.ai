import sys
import os
from indigo import Indigo


def main():
    """
    This function grabs a SMILES str and a name from sys.argv and saves the
    corresponding molecule in a mol file as f"{$file_name}.mol"
    """
    with open('../storage/app/public/media/mol_file_writer.log', "a") as output:
        output.write(f'{sys.argv}\n')
    smiles = sys.argv[1]
    file_name = sys.argv[2]
    indigo = Indigo()
    molecule = indigo.loadMolecule(smiles)
    save_path = os.path.join('../storage/app/public/media/', f"{file_name}.mol")
    molecule.saveMolfile(save_path)
    
    print(f"{file_name}.mol")


if __name__ == '__main__':
    main()
