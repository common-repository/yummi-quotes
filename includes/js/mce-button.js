(function() {
  tinymce.PluginManager.add('yq_mce_button', function(editor, url) {
    editor.addButton('yq_mce_button', {
      text: 'YQ',
      icon: 'blockquote',
      onclick: function() {
        // change the shortcode as per your requirement
        editor.insertContent('[quotes sort=random number=1]');
      }
    });
  });
})();
