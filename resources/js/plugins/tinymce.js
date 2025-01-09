// Import TinyMCE
import tinymce from 'tinymce/tinymce';

// A theme is also required
import 'tinymce/themes/silver';

// Any plugins you want to use has to be imported
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/autoresize';
import 'tinymce/plugins/paste'
import 'tinymce/plugins/quickbars'
import 'tinymce/plugins/image'
import 'tinymce/plugins/imagetools'

// Just a placeholder, the real init func is located
// at tinymce <editor> Vue component separately
tinymce.init({});

// default tinymce for sharing between different editor
export const defaultTinymceInitConfig = {
    menubar: '',
    min_height: 150,
    max_height: 500,
    height: 380,
    toolbar_mode: 'wrap',
    icons: 'small',
    branding: false,
    skin: 'small',
    content_style: '.mce-content-body {font-size:13px;margin:8px}',
    paste_as_text: true,
    plugins: ['autolink link lists paste'],

    // unused configs
    toolbar_items_size: 'small',
    // this largely references MS Word default font size
    // fontsize_formats:
    //     '8px 9px 10px 11px 12px 14px 16px 18px 20px 22px 24px 26px 28px 36px 48px 72px',

    // init_instance_callback: function (editor) {
    //     editor.on('click', function (e) {
    //         console.log('Element clicked:', e.target.nodeName);

    //     });
    // },
};
