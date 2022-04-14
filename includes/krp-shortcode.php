<?php

function krp_add_shortcode( $atts ) {
  extract( shortcode_atts( array(
    'file_id' => null,
    'file_path' => null,
    'file_path_type' => 'normal',
  ), $atts) );

  $get_file_id = isset($_GET['krp_file_id']) ? $_GET['krp_file_id'] : null;
  $get_file_path = isset($_GET['krp_file_path']) ? $_GET['krp_file_path'] : null;
  $get_file_path_type = isset($_GET['krp_file_path_type']) ? $_GET['krp_file_path_type'] : null;

  if ( ! is_null($file_id) ) {

    $built_file_path = wp_get_attachment_url($file_id);

  } else if ( ! is_null($file_path) ) {

    $built_file_path = $file_path;

    if ( $file_path_type === 'secret' ) {
      $wp_upload_dir = wp_upload_dir();
      $built_file_path = site_url() . '/wp-content/uploads/' . $file_path . '.pdf';
    }

  } else if ( ! is_null($get_file_path) ) {

    $wp_upload_dir = wp_upload_dir();
    $built_file_path = site_url() . '/wp-content/uploads/' . $get_file_path . '.pdf';

    if ( $get_file_path_type === 'normal' ) {
      $built_file_path = $get_file_path;
    }

  } else if ( ! is_null($get_file_id) ) {

    $built_file_path = wp_get_attachment_url($get_file_id);

  } else {

    $html = __('You need to set attribute with PDF file path OR file ID such as following<br>[krp_show_pdf file_path="path/to/file.pdf"]<br>[krp_show_pdf file_id="0"]', 'keima-restricted-pdf');
    return $html;

  }

  $html = <<<EOT
<style>
  #krp_loading {
    margin-top: 1em;
    margin-bottom: 1em;
    padding: .25em .5em;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 2px;
  }
  #krp_loading:empty {
    display: none;
  }
  #krp_canvas_container {
    margin-left: auto;
    margin-right: auto;
    background-color: #fff;
    border: 1px solid #ccc;
  }
  #krp_canvas_container canvas {
    max-width: 100%;
  }
  @media print {
    #krp_canvas_container {
      display: none;
    }
  }
</style>

<script src="//mozilla.github.io/pdf.js/build/pdf.js"></script>
<script>
  // Canceling right click.
  document.getElementsByTagName('html')[0].oncontextmenu = function () {return false;}
  document.body.oncontextmenu = function () {return false;}

  // Display PDF file.
  var queryString = window.location.search;
  var queryObject = new Object();
  if(queryString){
    queryString = queryString.substring(1);
    var parameters = queryString.split('&');

    for (var i = 0; i < parameters.length; i++) {
      var element = parameters[i].split('=');

      var paramName = decodeURIComponent(element[0]);
      var paramValue = decodeURIComponent(element[1]);

      queryObject[paramName] = paramValue;
    }
  }
  var url = '$built_file_path';

  // Loaded via <script> tag, create shortcut to access PDF.js exports.
  var pdfjsLib = window['pdfjs-dist/build/pdf'];

  // The workerSrc property shall be specified.
  pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';

  // Using DocumentInitParameters object to load binary data.
  var loadingTask = pdfjsLib.getDocument(url);
  loadingTask.onProgress = function ( data ) {
    //console.log( "loaded : " + data.loaded);
    //console.log( "total : " + data.total);
    if ( data.loaded < data.total ) {
      document.getElementById('krp_loading').innerHTML = '<div class="__loading-inner">loading... (' + Math.round( data.loaded / data.total * 100 ) + '%)</div>';
    }
  }
  loadingTask.promise.then(function(pdf) {
    // Fetch the first page
    var pageNumber = 1;

    renderPages(pdf);
    
    setTimeout(function () {
      document.getElementById('krp_loading').innerHTML = '';
    }, 100);

  }, function (reason) {
    // PDF loading error
    console.error(reason);
  });
  function renderPage(page) {
    var viewport = page.getViewport({scale: 1.25});
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');
    var renderContext = {
      canvasContext: ctx,
      viewport: viewport
    };

    canvas.height = viewport.height;
    canvas.width = viewport.width;

    var kpv_canvas_container = document.getElementById('krp_canvas_container');
    kpv_canvas_container.appendChild(canvas);

    page.render(renderContext);
  }

  function renderPages(pdfDoc) {
    for(var num = 1; num <= pdfDoc.numPages; num++) {
      pdfDoc.getPage(num).then(renderPage);
    }
  }
</script>

<div class="keima-restricted-pdf">
  <div id="krp_loading"></div>
  <div id="krp_canvas_container"></div>
</div>
EOT;

  return $html;
}
add_shortcode('krp_show_pdf', 'krp_add_shortcode');