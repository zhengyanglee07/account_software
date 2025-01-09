// tinymce.init({
//     selector: 'textarea.sohai',  // change this value according to your HTML
//     statubar: true,

//     plugin:
//     'lists',


//     a_plugin_option: true,
//     a_configuration_option: 400,
//     toolbar: ['undo redo | styleselect | bold italic |alignleft aligncenter alignright alignjustif  |numlist bullist'],


//   });

tinymce.init({
    selector: "textarea.gg",
    plugins: [
      "advlist autolink lists link image charmap print preview anchor quickbars",
      "searchreplace visualblocks code fullscreen",
      "insertdatetime media table contextmenu paste quickbars",
    ],
    // toolbar: ['undo redo | styleselect | fontselect |  fontsizeselect | bold italic |alignleft aligncenter alignright alignjustif  |numlist bullist |link code charmap'],
    toolbar: ['undo redo | styleselect | fontselect |  fontsizeselect | bold italic underline |alignleft aligncenter alignright alignjustif  |numlist bullist | code charmap'],

  });
