(function ($) {
  Drupal.behaviors.nodedataupdate = {
    attach: function (context, settings) {
      // Check if user submit form values
      if (typeof settings.user_submit_val !== 'undefined' && typeof settings.user_submit_val.fields !== 'undefined') {
        var progressBar = void 0;
        var batch_id = '';
        function updateCallback(progress, status, pb) {
          $('#updateprogress').html(progress + '%');
          if (progress === '100') {
            pb.stopMonitoring();
            // Call the finishCallback()
            $.ajax({
              url: Drupal.url('batch?id=' + batch_id + '&op=finished'),
              type: 'POST',
              contentType: 'application/json; charset=utf-8',
              dataType: 'json',
              success: function success(val) {
                window.location.reload();
              }
            });
          }
        }

        // Call batch controller by ajax & post method.
        const url = 'update-node-data/ajax';
        $.ajax({
          url: Drupal.url(url),
          type: 'POST',
          data: {
            'fields': settings.user_submit_val.fields,
          },
          dataType: 'json',
          success: function success(value) {
            batch_id = value[0].command;
            // Display batch progress
            progressBar = new Drupal.ProgressBar('updateprogress', updateCallback, 'POST');
            progressBar.setProgress(0, 'Updating Data');
            progressBar.startMonitoring(value[0].data + '&op=do', 10);
          }
        });
      }

    }
  }
})(jQuery);
