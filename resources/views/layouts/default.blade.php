<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="index" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie-edge">
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
	<meta http-equiv="Pragma" content="no-cache"/>
	<meta http-equiv="Expires" content="0"/>
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
	<link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
	<script src="/js/app.js?1234"></script>
	<script src="/js/jquery.min.js"></script>
	<link rel="icon" href=" {{ asset('DECIMER_favicon.png') }}">
	<title>DECIMER Web Application</title>
	<!--Global site tag (gtag.js) - Google Analytics-->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-VKSWMKC79R"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-VKSWMKC79R');
	</script>
</head>


<body class="white m-0">
	<header class="fixed top-0 left-0 right-0 z-50 bg-gray-100">
		<div class="container mx-auto flex justify-between p-2">
			<div>
				<img src="loading_icon_mini.gif" alt="Loading icon" class="mx-auto align-sub" id="header_loading_icon" style="display: block; visibility: hidden;"/>
				<div class="text-lg text-gray-800 mx-2 align-bottom" id="loading_text" style="display: inline;"></div>
			</div>
			<nav class="navbar navbar-default -mx-2">
				@if (Session::get('smiles_array'))
					<!-- HEADER IUPAC GENERATION BUTTON -->
					<form id="iupac_generation_form" action="{{ route('stout.iupac.post') }}" method="POST" enctype="multipart/form-data">
						@csrf
						<input type="hidden" name="img_paths" value="{{ Session::get('img_paths') }}" />
						<input type="hidden" name="structure_depiction_img_paths" value="{{ Session::get('structure_depiction_img_paths') }}" />
						<input type="hidden" name="iupac_array" value="{{ Session::get("iupac_array") }}" />
						<input type="hidden" id="stout_form_smiles_array" name="smiles_array" value="{{ Session::get('smiles_array') }}" />
						<?php $num_ketcher_frames = count(json_decode(Session::get('smiles_array')))?>
						<button class="px-4 text-lg mx-2 text-gray-800 hover:text-blue-900 transition" 
								onclick="stout_submit('{{ $num_ketcher_frames }}', 'stout_form_smiles_array')">
							Generate IUPAC names
						</button>
					</form>
					<!-- HEADER DOWNLOAD BUTTON -->
					<form id="archive_creation_form" action="{{ route('archive.creation.post') }}" method="POST" enctype="multipart/form-data">
						@csrf
						<input type="hidden" name="img_paths" value="{{ Session::get('img_paths') }}" />
						<input type="hidden" name="structure_depiction_img_paths" value="{{ Session::get('structure_depiction_img_paths') }}" />
						<input type="hidden" name="iupac_array" value="{{ Session::get("iupac_array") }}" />
						<input type="hidden" id="download_form_smiles_array" name="smiles_array" value="{{ Session::get('smiles_array') }}" />
						<?php $num_ketcher_frames = count(json_decode(Session::get('smiles_array')))?>
						<button class="px-4 text-lg mx-2 text-gray-800 hover:text-blue-900 transition" 
								onclick="submit_with_updated_smiles('{{ $num_ketcher_frames }}', 'download_form_smiles_array')">
							Download results   
						</button>
					</form>
				@endif
				<!-- HEADER RELOAD BUTTON -->
				<button class="text-lg mx-2 text-gray-800 hover:text-blue-900 transition" onclick="window.location='{{ route('home') }}'">
					Reload
				</button>
				<!--
				<a href="{{ url('https://cheminf.uni-jena.de/') }}" class="text-lg mx-2 text-gray-800 hover:text-blue-900 transition">About</a>
				-->
			</nav>
		</div>
	</header>
	
	<main>
		@yield('page-content')
		<!-- HOW TO USE THE DECIMER WEB APP -->
		<section class="py-20 text-justify">
			<div class="max-w-screen-lg container mx-auto">
				<h3 class="text-4xl font-bold mb-6"> How to use the DECIMER web app?</h3>
				<p class="mb-6 ">
					Just upload a pdf document or one or multiple images that contain chemical structure
					depictions above. If a pdf document is uploaded, DECIMER Segmentation is used to detect
					and segment all chemical structure depictions. The detected or uploaded chemical structure
					depictions are processed using the powerful OCSR engine of DECIMER V2. The chemical
					structure depictions and the corresponding SMILES representation are presented above. 
					
					You can edit the structures according to your needs in the 
					<a href="{{ url('https://lifescience.opensource.epam.com/ketcher/') }}" target="_blank" class="text-blue-400 hover:text-blue-600 transition">
						Ketcher chemical structure editor
					</a>
					windows before downloading
					the segmented images and the correponding mol files.
					Additionally, the IUPAC names of the chemical structures can be resolved using STOUT V2.

				</p>
				<!-- Logos with links -->
				<div class="grid grid-cols-3 gap-8">
					<div>
						<a href={{ url("https://github.com/Kohulan/DECIMER-Image-Segmentation") }} target="_blank">
							<img src="DECIMER_Segmentation_logo.png" alt="DECIMER Segmentation Logo"/>
						</a>
					</div>
					<div>
						<a href={{ url("https://github.com/Kohulan/Smiles-TO-iUpac-Translator") }} target="_blank">
							<img src="STOUT_logo.png" alt="STOUT Logo"/>
						</a>
					</div>
					<div>
						<a href={{ url("https://github.com/Kohulan/DECIMER-Image_Transformer") }} target="_blank">
							<img src="DECIMER_Transformer_logo.png" alt="DECIMER OCSR Logo"/>
						</a>
					</div>
				</div>
			</div>
		</section>

		<!-- CITE US -->
		<section class="py-20 text-justify">
			<div class="max-w-screen-lg container mx-auto">
				<h3 class="text-4xl font-bold mb-6">Cite us</h3>
				<h4 class="text-xl mb-3 gray-800">If our toolkit helped your work, please cite our publications.</h4> 
				<div class="flex flex-wrap -mx-2">
					<div class="w-full sm:w1/2 mb-3 px-2">
						<div class="p-4 bg-gray-200 h-full">
							<a href="{{ url('https://doi.org/10.1186/s13321-021-00496-1') }}" class="text-lg text-black mb-3" target="_blank">
								Rajan, K., Brinkhaus, H.O., Sorokina, M. et al. 
								<span class="italic">J Cheminform</span>, 
								<span class="font-bold">13</span>, 20 (2021).
							</a>
							</br>
							<a href="{{ url('https://doi.org/10.1186/s13321-021-00496-1') }}" class="text-lg text-black mb-3" target="_blank">
								Rajan, K., Zielesny, A., Steinbeck, C.
								<span class="italic">J Cheminform</span>, 
								<span class="font-bold">13</span>, 61 (2021).
							</a>
						</div>
					</div>	
				</div>
				<div class="flex justify-center">
					<a href="{{ url('https://cheminf.uni-jena.de/research/deep-learning/') }}" target="_blank" class="bg-gray-300 text-black text-center py-2 px-4 rounded hover:bg-blue-100 transition">Learn more</a>
				</div>
			</div>
		</section>
	</main>

	<footer>
		<div class="max-w-screen-lg container mx-auto p-4 text-justify">
			<p>
				Deep Learning for Chemical Image Recognition (DECIMER) is a step towards automated chemical 
				image segmentation and recognition. DECIMER is actively developed and maintained by the
				<a href="{{ url('https://cheminf.uni-jena.de/research/deep-learning/') }}" target="_blank" class="text-blue-400 hover:text-blue-600 transition">Steinbeck group</a> at the
				<a href="{{ url('https://www.uni-jena.de/') }}" target="_blank" class="text-blue-400 hover:text-blue-600 transition">Friedrich Schiller University Jena</a>.
				You need to have the right granted by the publisher of the uploaded documents and images to use them for data mining.
				We do not store or use the data for anything other than automated processing and display of results in the web app. 
				Your documents and images are only saved for one hour unless a problem is reported. If a problem is reported,
				we will use the reported image to analyse errors before deleting them.
				Google Analytics is used to get some basic statistics about the number of visitors.
				You can look up a more detailed description of what happens with your data in our 
				<a href="{{ route('privacy_policy') }}" target="_blank" class="text-blue-400 hover:text-blue-600 transition">privacy policy</a>. 
				German law requires us to provide some information about who we are: 
				<a href="{{ route('impressum') }}" target="_blank" class="text-blue-400 hover:text-blue-600 transition">Impressum - Legal Disclosure</a>.
				The animated loading icon was generated using
				<a href="{{ url('https://loading.io/icon/') }}" target="_blank" class="text-blue-400 hover:text-blue-600 transition">loading.io</a>.
				If you run into problems, please file an issue on 
				<a href="{{ url('https://github.com/OBrink/DECIMER_Web/issues') }}" target="_blank" class="text-blue-400 hover:text-blue-600 transition">Github</a> or 
				<a href= "mailto:otto.brinkhaus@uni-jena.de" class="text-blue-400 hover:text-blue-600 transition">contact us via email</a>.
			</p>
		</div>
	</footer>

</body>


</html>
