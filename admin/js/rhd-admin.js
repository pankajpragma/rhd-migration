jQuery(document).ready(function () {
	jQuery('#rhd_general_form').on('submit', function () {
		var website = jQuery("#rhd_website").val();
		
		if (website == 'source') {
			if (!isUrlValidRHD(jQuery('#rhd_destination_url').val())) {
				alert("Please provide the valid destination url");
				return false;
			}
			if (jQuery('#rhd_shash_key').val().length <= 10) {
				alert("Hash Key should be more than 10 characters");
				return false;
			}
		}
		else {
			if (!isUrlValidRHD(jQuery('#rhd_site_url').val())) {
				alert("Please provide the valid source url");
				return false;
			}
			if (jQuery('#rhd_dhash_key').val().length <= 10) {
				alert("Hash Key should be more than 10 characters");
				return false;
			}
		}
	});

	jQuery("#rhd_website").on('change', function () {
		var website = jQuery(this).val();
		if (website == 'source') {
			jQuery(".rhd_source_section").show();
			jQuery(".rhd_destination_section").hide();
		}
		else {
			jQuery(".rhd_source_section").hide();
			jQuery(".rhd_destination_section").show();
		}
	});
	// Open modal in AJAX callback
	jQuery('body').on('click', '.rhd-start-migrate', function (event) {
		event.preventDefault();
		ealert = jQuery(this).attr('data-alert');
		id = jQuery(this).attr('data-id');
		etype = jQuery(this).attr('data-type');
		var r = true;
		if (ealert == 1) {
			var r = confirm("Have you saved before proceeding to migrate? If yes then click on Ok else Cancel");
			if (r == true) {
				load_generator_log(id, etype);
			}
		}
		else {
			load_generator_log(id, etype);
		}
	});

	function load_generator_log(id, etype) {
		jQuery("#log-rhd-modal").modal();
		jQuery("#log-rhd-row").html('<div id="rhd-loading-bar" >Please wait, Loading....</div>');
		input_data = { id: id, etype: etype, action: "load_rhd_log", image: "0" };
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			data: input_data,
			success: function (alrt) {
				jQuery("#rhd-loading-bar").hide();
				jQuery("#log-rhd-row").append(alrt.message);
				if (alrt.image_upload == '1' && alrt.status) {
					jQuery("#rhd-loading-bar").show();
					input_data = { id: id, etype: etype, action: "load_rhd_log", image: "1" };
					jQuery.ajax({
						type: "POST",
						url: ajaxurl,
						dataType: "json",
						data: input_data,
						success: function (alrt) {
							jQuery("#rhd-loading-bar").hide();
							jQuery("#log-rhd-row").append(alrt.message);
						},
						error: function (alrt) {
							jQuery("#rhd-loading-bar").hide();
							jQuery("#log-rhd-row").append("<div class='rhd-status-msg rhd-error-label' >Error while processing your request, please try again.</div>");
						}
					});
				}
			},
			error: function (alrt) {
				jQuery("#rhd-loading-bar").hide();
				jQuery("#log-rhd-row").append("<div class='rhd-status-msg rhd-error-label' >Error while processing your request, please try again.</div>");
			}
		});
	}


	jQuery(".copy-rhd-element").on('click', function (e) {
		e.preventDefault();
		var id = jQuery(this).prev('input').attr('id'); 
		jQuery(this).removeClass("copied-hide");
		jQuery(this).removeClass("copied");
		var copyText = document.getElementById(id);
		// Select the text field
		copyText.select();
		copyText.setSelectionRange(0, 99999); // For mobile devices
		// Copy the text inside the text field
		copyToClipboard(copyText.value); 
		jQuery(this).addClass("copied");
		var obj = jQuery(this);
		setTimeout(function () {
			obj.addClass("copied-hide");
		}, 3000);
	});
});
function isUrlValidRHD(url) {
	if (url.match(/\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?/g) != null) {
		return true;
	}
	return false;
}
// return a promise
function copyToClipboard(textToCopy) {
    // navigator clipboard api needs a secure context (https)
    if (navigator.clipboard && window.isSecureContext) {
        // navigator clipboard api method'
        return navigator.clipboard.writeText(textToCopy);
    } else {
        // text area method
        let textArea = document.createElement("textarea");
        textArea.value = textToCopy;
        // make the textarea out of viewport
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        return new Promise((res, rej) => {
            // here the magic happens
            document.execCommand('copy') ? res() : rej();
            textArea.remove();
        });
    }
}