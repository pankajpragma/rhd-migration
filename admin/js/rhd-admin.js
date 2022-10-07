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
		var r = true;
		if (ealert == 1) {
			var r = confirm("Have you saved before proceeding to migrate? If yes then click on Ok else Cancel");
			if (r == true) {
				load_migration_setting(id);
			}
		}
		else {
			load_migration_setting(id);
		}
	});
	
	
	jQuery('body').on('click', '.bulk_rhd_migrate', function (event) {
		event.preventDefault();
		var cnt = jQuery("input[name='post[]']:checked").length; 
		if(!cnt)
		{
			alert("No post/page selected to process.");
			return false;
		}
		jQuery("#rhd_id").val("--"); 
		jQuery("#log-rhd-modal-1").modal(); 
	});

	jQuery('body').on('click', '.rhd-start-migration', function (event) {
		event.preventDefault();
		var cnt = jQuery("input[name='rhd_destination_url']:checked").length; 
		if(!cnt)
		{
			alert("No destination URL selected.");
			return false;
		}
		jQuery(".rhd-tree").html("");
		var id = jQuery("#rhd_id").val(); 
		var media_exclude=0;
		var overwrite_media=0;
		var localhost_migration = 0;
		if (jQuery('#media_exclude').is(":checked"))
			media_exclude  =1;
		if (jQuery('#overwrite_media').is(":checked"))
			overwrite_media  =1;
		if (jQuery('#localhost_migration').is(":checked"))
			localhost_migration  =1;

		//setup an array of AJAX options,
		//each object will specify information for a single AJAX request
		var ajaxes  = [];
		var imagajax = [];
		current = 0;
		if(id == "--")
		{
			jQuery("input[name='post[]']:checked").each(function() {
				id = jQuery(this).val();
				jQuery("input[name='rhd_destination_url']:checked").each(function() { 
					if(jQuery(this).val())
					{
						destination_url = jQuery(this).val();
						var hash = hashRhdCode(destination_url);
						input_data = { id: id, action: "load_rhd_log", image: "0" , destination: destination_url, localhost:localhost_migration, overwrite:overwrite_media, exclude:media_exclude, hash: hash};
						var data = {
							url      : ajaxurl,
							data     : input_data,
							callback : function (data) { }
						};
						ajaxes.push(data);
					}
				});
			});
		}
		else{
			jQuery("input[name='rhd_destination_url']:checked").each(function() {
				if(jQuery(this).val())
				{
					destination_url = jQuery(this).val();
					var hash = hashRhdCode(destination_url);
					input_data = { id: id, action: "load_rhd_log", image: "0" , destination: destination_url, localhost:localhost_migration, overwrite:overwrite_media, exclude:media_exclude, hash: hash};
					var data = {
						url      : ajaxurl,
						data     : input_data,
						callback : function (data) { }
					};
					ajaxes.push(data);
				}
			});
		}

		if(ajaxes.length)
		{
			jQuery("#rhd-migration-heading").html("Migration Started"); 
			jQuery("#log-rhd-modal-2").modal(); 
			jQuery("#rhd-loading-bar").show();
			//run the AJAX function for the first time once 
			process_rhd_content_migration(current, ajaxes, imagajax);
		}
	});
	function load_migration_setting(id) {
		jQuery("#rhd_id").val(id);
		jQuery("#log-rhd-modal-1").modal(); 
	}
	function process_rhd_content_migration(current, ajaxes, imagajax) { 

		if (current < ajaxes.length) {
			var hash = ajaxes[current].data.hash;
			var destination = ajaxes[current].data.destination; 
			if(!jQuery("#"+hash).length)
			{
				jQuery(".rhd-tree").append('<li id="'+hash+'"><a class="rhd-destination-log" >'+destination+'</a></li>');
			}
			jQuery.ajax({
				type: "POST",
				url: ajaxes[current].url,
				dataType: "json",
				data: ajaxes[current].data,
				success  : function (alrt) {
					//once a successful response has been received,
					//no HTTP error or timeout reached,
					//run the callback for this request
					ajaxes[current].callback(alrt);
					jQuery("#"+hash).append(alrt.message);
					if (alrt.image_upload == '1' && !alrt.error) {
						ajaxes[current].data.image = 1;
						var data = {
							url      : ajaxurl,
							data     : ajaxes[current].data,
							callback : function (data) { }
						};
						imagajax.push(data);
					}
				},
				complete : function () {
					//increment the `current` counter
					//and recursively call our do_ajax() function again.
					current++;
					process_rhd_content_migration(current, ajaxes, imagajax);
					//note that the "success" callback will fire
					//before the "complete" callback
				},
				error: function (alrt) {				
					jQuery("#"+hash).append("<div class='rhd-status-msg rhd-error-label' >Error while processing your request, please try again.</div>");
				}
			});
		}
		else
		{
			icurrent = 0;
			process_rhd_image_migration(icurrent, imagajax);
		}

	}

	function process_rhd_image_migration(icurrent, imagajax)
	{
		if (icurrent < imagajax.length) {

			var hash = imagajax[icurrent].data.hash; 
			var id = imagajax[icurrent].data.id;
			jQuery.ajax({
				type: "POST",
				url: imagajax[icurrent].url,
				dataType: "json",
				data: imagajax[icurrent].data,
				complete : function () {
					//increment the `current` counter
					//and recursively call our do_ajax() function again.
					icurrent++;
					process_rhd_image_migration(icurrent, imagajax);
					//note that the "success" callback will fire
					//before the "complete" callback
				},
				success: function (alrt) {
					jQuery("#post_image_"+id+"_"+hash).append(alrt.message);
				},
				error: function (alrt) {
					jQuery("#post_image_"+id+"_"+hash).append("<div class='rhd-status-msg rhd-error-label' >Error while processing your request, please try again.</div>");
				}
			});
		}
		else
		{
			jQuery("#rhd-loading-bar").hide(); 
			jQuery("#rhd-migration-heading").html("Migration Completed"); 
		}

	}

	jQuery(".copy-rhd-element").on('click', function (e) {
		e.preventDefault();
		var ele = jQuery(this).attr('data-value'); 
		if(ele == 'textarea')
		{
			var id = jQuery(this).prev('textarea').attr('id'); 
		}
		else
		{
			var id = jQuery(this).prev('input').attr('id'); 
		}
		jQuery(this).removeClass("copied-hide");
		jQuery(this).removeClass("copied");
		var copyText = document.getElementById(id);
		// Select the text field
		copyText.select();
		copyText.setSelectionRange(0, 99999); // For mobile devices
		// Copy the text inside the text field
		copyToRHDClipboard(copyText.value); 
		jQuery(this).addClass("copied");
		var obj = jQuery(this);
		setTimeout(function () {
			obj.addClass("copied-hide");
		}, 3000);
	});

	jQuery(".rhd_media_exclude").on('click', function (e) {
		var media_exclude=0; 
		if (jQuery('#media_exclude').is(":checked"))
			media_exclude  =1;
		if(media_exclude)
		{
			alert("You have selected the exclude option but still all links would get migrate. You need to move all media manually on destination.");
		}
	});

	if(jQuery("#posts-filter").length)
	{
		jQuery(jQuery(".wrap .page-title-action")[0]).after('<a href="#" class="page-title-action bulk_rhd_migrate">Bulk Migration</a>');
	}

	jQuery(".rhd-tab-wrapper a").on('click', function (e) {
		e.preventDefault();
		var ele = jQuery(this).attr('href'); 
		jQuery(".rhd-tabs").hide();
		jQuery(ele+"-tab").show();
		jQuery(".rhd-tab-wrapper a").removeClass('nav-tab-active');
		jQuery(this).addClass('nav-tab-active');
	});
});
function isUrlValidRHD(url) {
	if (url.match(/\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?/g) != null) {
		return true;
	}
	return false;
}
// return a promise
function copyToRHDClipboard(textToCopy) {
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

function hashRhdCode (str){
    var hash = 0;
    if (str.length == 0) return hash;
    for (i = 0; i < str.length; i++) {
        char = str.charCodeAt(i);
        hash = ((hash<<5)-hash)+char;
        hash = hash & hash; // Convert to 32bit integer
    }
    return hash;
}
