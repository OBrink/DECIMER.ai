@extends('layouts.default')

@section('page-content')
    <section class="max-w-screen-lg container mx-auto flex-grow">
        <div class="pt-8">
            <img src="DECIMER.gif" alt="DECIMER Logo" id="decimer_logo_gif" style="display: none;" />
            <img src="DECIMER.png" alt="DECIMER Logo" id="decimer_logo" style="display: none;" />
            <img src="loading_icon.gif" alt="Loading icon" class="mx-auto" id="loading_icon" style="display: none;" />
            <!-- DECIMER LOGO (Animated gif is only shown the first time we are sent to index view) -->
            @if (!Session::get('img_paths'))
                <script>
                    document.getElementById("decimer_logo_gif").style = "display: centered;"
                </script>
                <!-- UPLOAD BUTTON -->
                <form id="upload_form" action="{{ route('file.upload.post') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="container d-flex justify-content-center">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mx-auto bg-gray-300 text-center p-4 rounded hover:bg-blue-100 transition">
                                    <span>
                                        Drop PDF document or chemical structure images here, or click to select files
                                    </span>
                                    <input class="file-input" type="file" name="file[]" multiple>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Warn Safari users that the app my not work -->
                <div class="col-md-12" role="alert" id='alert-if-safari'></div>
                <script>
                    var is_safari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
                    if (is_safari) {
                        const alert_div = document.getElementById('alert-if-safari')
                        alert_div.className = 'alert alert-danger'
                        alert_div.innerHTML = 'Safari is currently not supported! Please use a different browser!'
                    }
                </script>

                <script async type="module">
                    $(document).on('change', '.file-input', function() {
                        document.getElementById("loading_icon").style = "display: centered;";
                        document.getElementById("header_loading_icon").style = "display: block; visibility: visible;";
                        document.getElementById("loading_text").innerHTML = "Uploading files..."
                        document.getElementById('upload_form').submit();
                    });
                </script>
            @elseif (Session::get('img_paths') == '[]')
                <script>
                    document.getElementById("decimer_logo").style = "display: centered;";
                </script>
                @if (!Session::get('smiles_array'))
                    <script>
                        document.getElementById("loading_icon").style = "display: centered;";
                        document.getElementById("header_loading_icon").style = "display: block; visibility: visible;";
                        document.getElementById("loading_text").innerHTML = "Interpreting structure images..."
                    </script>
                    <p style="text-align:center">
                        The uploaded images are presented below.</br>
                        The DECIMER OCSR engine is running.</br>
                        This may take a few minutes.
                    </p>
                @endif
            @else
                <script>
                    document.getElementById("decimer_logo").style = "display: centered;"
                </script>
                @if (!Session::get('structure_depiction_img_paths'))
                    <script>
                        document.getElementById("loading_icon").style = "display: centered;";
                        document.getElementById("header_loading_icon").style = "display: block; visibility: visible;";
                        document.getElementById("loading_text").innerHTML = "Searching for chemical structures..."
                    </script>
                    <p style="text-align:center">
                        The document has been uploaded and converted.</br>
                        Detecting chemical structures.</br>
                        This may take a few minutes.
                    </p>
                @elseif (!Session::get('smiles_array'))
                    @if (Session::get('structure_depiction_img_paths') == '[]')
                        <p style="text-align:center">
                            No structures were detected in the uploaded document.
                        </p>
                    @else
                        <script>
                            document.getElementById("loading_icon").style = "display: centered;";
                            document.getElementById("header_loading_icon").style = "display: block; visibility: visible;";
                            document.getElementById("loading_text").innerHTML = "Interpreting structure images..."
                        </script>
                        <p style="text-align:center">
                            The segmented chemical structures are presented below.</br>
                            The DECIMER OCSR engine is running.</br>
                            This may take a few minutes.
                        </p>
                    @endif
                @endif
            @endif
            @if (Session::get('smiles_array'))
                <p style="text-align:center" id="main_loading_text">
                    The chemical structure depictions have been processed.</br>
                    The results are presented below.</br></br></br>
                </p>
                <span id="smiles_error_span"></span>
                <!-- DOWNLOAD BUTTON -->
                <form id="archive_creation_form" action="{{ route('archive.creation.post') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="container d-flex justify-content-center">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mx-auto bg-gray-300 text-center p-4 rounded hover:bg-blue-100 transition">
                                    <span>
                                        Download results
                                    </span>
                                    <input type="hidden" name="img_paths" value="{{ Session::get('img_paths') }}" />
                                    <input type="hidden" name="structure_depiction_img_paths"
                                        value="{{ Session::get('structure_depiction_img_paths') }}" />
                                    <input type="hidden" name="iupac_array" value="{{ Session::get('iupac_array') }}" />
                                    <input type="hidden" id="download_form_smiles_array" name="smiles_array"
                                        value="{{ Session::get('smiles_array') }}" />
                                    <?php $num_ketcher_frames = count(json_decode(Session::get('smiles_array'))); ?>
                                    <button class="file-input"
                                        onclick="submit_with_updated_smiles('{{ $num_ketcher_frames }}', 'download_form_smiles_array')">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                @if ($download_link = Session::get('download_link'))
                    <script async type="module">
                        downloadURI("{{ $download_link }}", "{{ basename($download_link) }}");
                    </script>
                @endif
            @endif
            </br></br></br>
        </div>

        
        <!-- If a file was loaded, display page images -->
        @if ($img_paths = Session::get('img_paths'))
            @if ($img_paths != '[]')
                <div class="text-xl mb-3 gray-800">
                    Display uploaded document
                    <input type="checkbox" id="page_image_checkbox"
                        onclick="display_or_not('page_image_checkbox', 'page_images')">
                </div>
                <?php $img_paths_array = json_decode($img_paths); ?>
                @if (count($img_paths_array) == 10)
                    <div class="text-xl mb-3 text-red-800">
                        <strong>Warning:</strong> If you upload a pdf document with more than 10 pages,
                        only the first 10 pages are processed. Please host your own version of this
                        application if you want to process large amounts of data.
                    </div>
                @endif
                <div id="page_images" class="flex overflow-auto max-h-screen" style="display:none">
                    @foreach ($img_paths_array as $img_path)
                        <img src="{{ asset('storage/' . $img_path) }}" alt="page image" class="w-7/12">
                    @endforeach
                </div>
            @endif

            <!-- Handle data about uploaded/segmented structures and their SMILES/IUPAC representations -->
            @if ($structure_depiction_img_paths = Session::get('structure_depiction_img_paths'))
                <?php $structure_img_paths_array = json_decode($structure_depiction_img_paths); ?>
                @if (count($structure_img_paths_array) > 20)
                    <div class="text-xl mb-3 text-red-800">
                        <strong>Warning:</strong> It appears like you uploaded more than 20 chemical
                        structure depictions (or we detected more than 20 structures in your uploaded
                        document). Only the first 20 structures are processed. Please host your own
                        version of this application if you want to process a large amounts of data.
                    </div>
                @endif
                @if ($smiles_array_str = Session::get('smiles_array'))
                    <?php $smiles_array = json_decode($smiles_array_str); ?>
                @endif
                @if ($iupac_array_str = Session::get('iupac_array'))
                    <?php $iupac_array = json_decode($iupac_array_str); ?>
                @endif

                <div class="grid grid-cols-3 gap-4">
                    @foreach ($structure_img_paths_array as $key => $struc_img_path)
                        <!-- Present IUPAC name -->
                        <div class="col-span-3">
                            @if (Session::get('iupac_array'))
                                <strong>IUPAC name</strong> </br>
                                <a class="break-words"> {{ $iupac_array[$key] }} </a></br>
                            @endif
                        </div>
                        <div class="frame border-b">
                            <!-- Display uploaded or segmented chemical structure depiction -->
                            <img src="{{ URL::asset($struc_img_path) }}" alt="extracted structure depiction"
                                class="chemical_structure_img">
                            <!-- Display corresponding SMILES str -->
                            @if (Session::get('smiles_array'))
                                <strong>Resolved SMILES representation</strong> </br>
                                <a class="break-words"> {{ $smiles_array[$key] }} </a> </br>

                                <!-- Problem report form (no redirection)-->
                                <iframe name="dummyframe" id="dummyframe" style="display: none;"></iframe>
                                <form id="problem_report_form" target="dummyframe"
                                    action="{{ route('problem.report.post') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="structure_depiction_img_path"
                                        value="{{ $struc_img_path }}" />
                                    <input type="hidden" name="smiles" value="{{ $smiles_array[$key] }}" />
                                </form>
                                <!-- Problem report button (no redirection)-->
                                <a href="" target="_blank" id="problem_report_link"
                                    class="text-blue-400 hover:text-blue-600 transition absolute bottom-0"
                                    onclick="handle_problem_report()">
                                    Report a problem with this result
                                </a>
                            @endif
                        </div>
                        <!-- Present DECIMER OCSR results in Ketcher (if it has already run) -->
                        <div class="col-span-2 border-b">
                            @if ($smiles_array_str = Session::get('smiles_array'))
                                <iframe onload="loadMol('{{ str_replace('\\', '\\\\', $smiles_array[$key]) }}', '{{ $key * 2 + 1 }}')"
                                    id='{{ $key * 2 + 1 }}' name='{{ $key * 2 + 1 }}'
                                    src="ketcher_standalone/ketcher_index.html" width="100%" height="420px">
                                </iframe>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Execution of DECIMER OCSR when segmented structures are available -->
                @if (!Session::get('smiles_array'))
                    @if (Session::get('structure_depiction_img_paths') != '[]')
                        <form id="OCSR_form" action="{{ route('decimer.ocsr.post') }}" method="POST">
                            @csrf
                            <input type="hidden" name="img_paths" value="{{ $img_paths }}" />
                            <input type="hidden" name="structure_depiction_img_paths"
                                value="{{ $structure_depiction_img_paths }}" />
                        </form>
                        <script async type="module">
                            document.getElementById('OCSR_form').submit();
                        </script>
                    @endif
                @endif

                <!-- Execution of DECIMER Segmentation when PDF has been loaded -->
            @else
                <form id="segmentation_form" action="{{ route('decimer.segmentation.post') }}" method="POST">
                    @csrf
                    <input type="hidden" name="img_paths" value="{{ $img_paths }}" />
                </form>
                <script async type="module">
                    document.getElementById('segmentation_form').submit();
                </script>
            @endif
        @else
            <!-- If input file could not be read, display error -->
            @if (count($errors) > 0)
                <div class="alert alert-danger container mx-auto flex justify-between py-24">
                    <ul>
                        @foreach ($errors as $error)
                            <li><strong>{{ $error }}</li></strong>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
    </section>
@endsection
