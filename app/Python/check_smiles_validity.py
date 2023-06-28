import sys
import os
import json
from typing import List
from jpype import startJVM, getDefaultJVMPath
from jpype import JClass, JVMNotFoundException, isJVMStarted

# Start the JVM to access Java classes
try:
    jvmPath = getDefaultJVMPath()
except JVMNotFoundException:
    print(
        "If you see this message, for some reason JPype",
        "cannot find jvm.dll.",
        "This indicates that the environment varibale JAVA_HOME",
        "is not set properly.",
    )
    jvmPath = "Define/path/or/set/JAVA_HOME/variable/properly"
if not isJVMStarted():
    here = os.path.split(__file__)[0]
    jar_path = os.path.join(here, "cdk-2.8.jar")
    startJVM(jvmPath, "-ea", "-Djava.class.path=" + str(jar_path))


def decode_smiles_array(str_smiles_arr: str) -> List[str]:
    """
    This function takes json_encoded array of SMILES str from the
    DECIMERController and returns a list of SMILES strings

    Args:
        str_smiles_arr (str): stringified array with SMILES strings
                              as returned by the php function json_decode()

    Returns:
        List[str]: List of SMILES strings
    """
    str_smiles_arr = str_smiles_arr[1:-1]
    str_smiles_arr = '["' + str_smiles_arr + '"]'
    str_smiles_arr = str_smiles_arr.replace(',', '","')
    str_smiles_arr = str_smiles_arr.replace("\\\\", "\\")
    str_smiles_arr = str_smiles_arr.replace("\\/", "/")
    str_smiles_arr = str_smiles_arr.replace("\\N", "\\\\N")
    smiles_arr = eval(str_smiles_arr)
    return smiles_arr


def cdk_smiles_to_IAtomContainer(smiles: str):
    """
    This function takes a SMILES representation of a molecule and
    returns the corresponding IAtomContainer object.

    Args:
        smiles (str): SMILES representation of the molecule

    Returns:
        IAtomContainer: CDK IAtomContainer object that represents the molecule
    """
    cdk_base = "org.openscience.cdk"
    SCOB = JClass(cdk_base + ".silent.SilentChemObjectBuilder")
    SmilesParser = JClass(
        cdk_base + ".smiles.SmilesParser")(SCOB.getInstance())
    molecule = SmilesParser.parseSmiles(smiles)
    # Instantiate StructureDiagramGenerator, determine coordinates
    sdg = JClass(cdk_base + ".layout.StructureDiagramGenerator")()
    sdg.setMolecule(molecule)
    sdg.generateCoordinates(molecule)
    molecule = sdg.getMolecule()
    return molecule


def smiles_to_mol_str(smiles: str) -> str:
    """
    This function takes a SMILES representation of a molecule and returns
    the content of the corresponding SD file using the CDK.
    ___
    The SMILES parser of the CDK is much more tolerant than the parsers of
    RDKit and Indigo.
    ___

    Args:
        smiles (str): SMILES representation of a molecule

    Returns:
        str: content of SD file of input molecule
    """
    i_atom_container = cdk_smiles_to_IAtomContainer(smiles)
    mol_str = cdk_IAtomContainer_to_mol_str(i_atom_container)
    return mol_str


def cdk_IAtomContainer_to_mol_str(i_atom_container) -> str:
    """
    This function takes an IAtomContainer object and returns the content
    of the corresponding MDL MOL file as a string.

    Args:
        i_atom_container (CDK IAtomContainer)

    Returns:
        str: string content of MDL MOL file
    """
    string_writer = JClass("java.io.StringWriter")()
    mol_writer = JClass("org.openscience.cdk.io.MDLV2000Writer")(string_writer)
    mol_writer.write(i_atom_container)
    mol_writer.close()
    mol_str = string_writer.toString()
    return str(mol_str)



def main():
    """
    This script takes a stringified array with SMILES str from sys.argv and
    prints a stringified list of strings ("valid" or "invalid") that indicate
    whether or not the given SMILES represent valid molecules
    """
    smiles_arr = decode_smiles_array(sys.argv[1])
    mol_block_arr = []
    for smiles in smiles_arr:
        try:
            mol_block = smiles_to_mol_str(smiles)
            mol_block_arr.append(mol_block)
        except Exception as e:
            print(e)
            mol_block_arr.append("invalid")
    print(json.dumps(mol_block_arr))


if __name__ == '__main__':
    main()
