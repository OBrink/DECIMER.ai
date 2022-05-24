# DECIMER.AI (Deep Learning for Chemical Image Recognition - WebApp)

[![License](https://img.shields.io/badge/License-MIT%202.0-blue.svg)](https://opensource.org/licenses/MIT)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-blue.svg)](https://GitHub.com/OBrink/DECIMER_Web/graphs/commit-activity)
[![GitHub issues](https://img.shields.io/github/issues/OBrink/DECIMER_Web.svg)](https://GitHub.com/OBrink/DECIMER_Web/issues/)
[![GitHub contributors](https://img.shields.io/github/contributors/OBrink/DECIMER_Web.svg)](https://GitHub.com/OBrink/DECIMER_Web/graphs/contributors/)
[![GitHub release](https://img.shields.io/github/release/OBrink/DECIMER_Web.svg)](https://GitHub.com/OBrink/DECIMER_Web/releases/)


This repository contains the code for [DECIMER.AI](https://decimer.ai)
[![GitHub Logo](https://github.com/Kohulan/DECIMER-Image-to-SMILES/raw/master/assets/DECIMER.gif)](https://decimer.ai)

Deep Learning for Chemical Image Recognition (DECIMER) is a step towards automated chemical image segmentation and recognition. DECIMER is actively developed and maintained by the [Steinbeck group](https://cheminf.uni-jena.de/) at the [Friedrich Schiller University Jena](https://www.uni-jena.de/).

# To run decimer.ai locally
```shell
git clone https://github.com/OBrink/DECIMER_Web.git
sudo chmod -R 777 DECIMER_Web
cd DECIMER_Web/
mv .env.example .env
sed -i '$ d' routes/web.php (Which deletes the last line "URL::forceScheme('https');")
sudo chmod -R 777 storage/
sudo chmod -R 777 bootstrap/cache/
docker-compose up --build -d
```
- Open your chrome browser(DECIMER works better on chrome and chromium based web browsers only) and enter http://localhost:80
- First run you will be asked to generate an app key for the laravel app
- Click on "Generate app key"
- Refresh the webpage. Now you could see decimer.ai running locally on your machine

# DECIMER.AI is powered by
[<img src="https://decimer.ai/DECIMER_Segmentation_logo.png" alt="drawing" width="250"/>](https://github.com/Kohulan/DECIMER-Image-Segmentation)
[<img src="https://decimer.ai/STOUT_logo.png" alt="drawing" width="250"/>](https://github.com/Kohulan/Smiles-TO-iUpac-Translator)
[<img src="https://decimer.ai/DECIMER_Transformer_logo.png" alt="drawing" width="250"/>](https://github.com/Kohulan/DECIMER-Image_Transformer)

## License:
- This project is licensed under the MIT License - see the [LICENSE](https://raw.githubusercontent.com/Kohulan/DECIMER-Image_Transformer/master/LICENSE?token=AHKLIF3EULMCUKCFUHIPBMDARSMDO) file for details

## Citation

- DECIMER: towards deep learning for chemical image recognition: Rajan, K., Zielesny, A., Steinbeck, C. J Cheminform, 12, 65 (2020).
- DECIMER-Segmentation: Automated extraction of chemical structure depictions from scientific literature: Rajan, K., Brinkhaus, H.O., Sorokina, M. et al. J Cheminform, 13, 20 (2021).
- DECIMER 1.0: deep learning for chemical image recognition using transformers: Rajan, K., Zielesny, A., Steinbeck, C. J Cheminform, 13, 61 (2021).
- STOUT: SMILES to IUPAC names using neural machine translation: Rajan, K., Zielesny, A., Steinbeck, C. J Cheminform, 13, 34 (2021).


## Research Group
[![GitHub Logo](https://github.com/Kohulan/DECIMER-Image-to-SMILES/blob/master/assets/CheminfGit.png)](https://cheminf.uni-jena.de)
