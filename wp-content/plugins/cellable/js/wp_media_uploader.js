jQuery(document).ready(function() {
  var $ = jQuery;
  if ($(".set_brand_images").length > 0) {
    if (typeof wp !== "undefined" && wp.media && wp.media.editor) {
      $(".set_brand_images").on("click", function(e) {
        e.preventDefault();
        var button = $(this);
        wp.media.editor.send.attachment = function(props, attachment) {
          const post_id = attachment.id;
          console.log("!!!!:", post_id);
          const phone_id = button.attr("id");
          $.ajax({
            type: "POST",
            url: cellable_admin_url.ajax_url,
            data: {
              post_id: post_id,
              phone_id: phone_id.replace("upbtn-", "")
            },
            success: function(res) {
              location.reload(true);
            }
          });
        };
        wp.media.editor.open(button);
      });
    }
  }

  // Phone Versions Image
  if ($(".set_version_images").length > 0) {
    if (typeof wp !== "undefined" && wp.media && wp.media.editor) {
      $(".set_version_images").on("click", function(e) {
        e.preventDefault();
        var button = $(this);
        wp.media.editor.send.attachment = function(props, attachment) {
          const post_id = attachment.id;
          console.log("!!!!:", post_id);
          const version_id = button.attr("id");
          $.ajax({
            type: "POST",
            url: cellable_admin_url.ajax_url,
            data: {
              post_id: post_id,
              version_id: version_id.replace("upbtn-", "")
            },
            success: function(res) {
              location.reload(true);
            }
          });
        };
        wp.media.editor.open(button);
      });
    }
  }

  // Carrier Image
  if ($(".set_carrier_images").length > 0) {
    if (typeof wp !== "undefined" && wp.media && wp.media.editor) {
      $(".set_carrier_images").on("click", function(e) {
        e.preventDefault();
        var button = $(this);
        wp.media.editor.send.attachment = function(props, attachment) {
          const post_id = attachment.id;
          console.log("!!!!:", post_id);
          const version_id = button.attr("id");
          $.ajax({
            type: "POST",
            url: cellable_admin_url.ajax_url,
            data: {
              post_id: post_id,
              carrier_id: version_id.replace("upbtn-", "")
            },
            success: function(res) {
              location.reload(true);
            }
          });
        };
        wp.media.editor.open(button);
      });
    }
  }
});



  