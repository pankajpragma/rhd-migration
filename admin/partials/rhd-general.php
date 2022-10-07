<?php 
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.pragmasoftwares.com/
 * @since      1.0.0
 *
 * @package    RHD
 * @subpackage RHD/admin/partials
 */ 
?>

<?php
global $rhd_admin;

$rhd_site_url = sanitize_text_field(get_option('rhd_site_url'));
$rhd_shash_key = sanitize_text_field(get_option('rhd_shash_key'));
$rhd_dhash_key = sanitize_text_field(get_option('rhd_dhash_key'));
$rhd_destination_url = sanitize_textarea_field(get_option('rhd_destination_url'));
$rhd_media_exclude = sanitize_text_field(get_option('rhd_media_exclude'));
$rhd_operation = sanitize_text_field(get_option('rhd_operation'));
$rhd_website_name = sanitize_text_field(get_option('rhd_website'));
$rhd_author_selected = absint(get_option('rhd_author'));
$rhd_comment_selected = sanitize_text_field(get_option('rhd_comment'));
$rhd_media_ext = sanitize_text_field(get_option('rhd_media_ext'));
$rhd_operations = $rhd_admin->get_rhd_operations();
$rhd_medias = $rhd_admin->get_rhd_media_exclude();
$rhd_websites = $rhd_admin->get_rhd_websites();
$rhd_authors = $rhd_admin->get_rhd_authors();
?>
<div class="wrap" style=" display: grid;">
<?php echo $rhd_admin->page_tabs('rhd-migration'); ?>

<?php echo $rhd_admin->notification_rhd(); ?>
<div class="rhd-grid rhd-top-margin rhd-action-form">
<div class="rhd-notice-container">
<?php settings_errors(); ?>
</div>
  <div class="rhd-col-8-12 rhd-segment">  
  <div class="rhd-section rhd-group rhd-tabs" id="rhd-migration-tab" >
  <div class="rhd-col rhd_span_1_of_2">
      <form method='post' action='options.php' name="rhd_general_form" id="rhd_general_form">
                <?php settings_fields( 'rhd-settings-group' ); ?>
                <?php do_settings_sections( 'rhd-settings-group' ); ?>

                
                <h3 ><span><?php  esc_html_e( "Settings:", 'rhd-migration' ); ?></span></h3>
                <table class='form-table'>   
                     <tr valign='top'>
                        <th scope='row'><?php esc_html_e( 'Website', 'rhd-migration' ); ?></th>
                        <td>
                            <select name="rhd_website" id="rhd_website" size="1" required class="regular-select" >
                                <?php
                                if ( is_array( $rhd_websites ) && count( $rhd_websites ) > 0 ) {
                                  foreach ( $rhd_websites as $rhd_website => $label ) {
                                    ?>
                                    <option value="<?php echo esc_attr($rhd_website); ?>"<?php selected( $rhd_website, $rhd_website_name ); ?>>
                                      <?php echo esc_html($label); ?>
                                    </option>
                                    <?php
                                  }
                                }
                                ?>
                              </select>
                              <div class="rhd-help-text"><?php esc_html_e( 'Select the source or destination operation that wants to migrate data', 'rhd-migration' ); ?></div>
                        </td>
                    </tr>
                    <tr valign='top' class=" rhd_destination_section" >
                        <th scope='row'><?php esc_html_e( 'Source URL', 'rhd-migration' ); ?></th>
                        <td>
                            <input type="text" class="large-text regular-text" value="<?php echo esc_html($rhd_site_url); ?>" id="rhd_site_url"   name="rhd_site_url"  >
                            <div class="copy-rhd-element" ><a href="#"><?php esc_html_e( 'Copy', 'rhd-migration' ); ?></a><span class="rhd-copied" ><?php esc_html_e( 'Copied!', 'rhd-migration' ); ?></span></div>    
                            <div class="rhd-help-text"><?php esc_html_e( 'Enter the site URL of the source website in a format like https://www.example.com', 'rhd-migration' ); ?></div>
                        </td>
                    </tr>  
                    <tr valign='top'  class=" rhd_source_section" >
                        <th scope='row'><?php esc_html_e( 'Source URL', 'rhd-migration' ); ?></th>
                        <td> 
                            <input type="text" class="large-text regular-text" value="<?php echo esc_html(get_site_url()); ?>" id="get_site_url"   name="get_site_url" readonly />
                            <div class="copy-rhd-element" ><a href="#"><?php esc_html_e( 'Copy', 'rhd-migration' ); ?></a><span class="rhd-copied" ><?php esc_html_e( 'Copied!', 'rhd-migration' ); ?></span></div>    
                            <div class="rhd-help-text"><?php esc_html_e( 'Use a URL in the destination website in a format like https://www.example.com', 'rhd-migration' ); ?></div>
                        </td>
                    </tr>
                    <tr valign='top'  class=" rhd_source_section" >
                        <th scope='row'><?php esc_html_e( 'Destination URL', 'rhd-migration' ); ?></th>
                        <td>
                            <textarea  class="large-text regular-textarea"  id="rhd_destination_url"   name="rhd_destination_url" rows="5" cols="10" ><?php echo esc_html($rhd_destination_url); ?></textarea>
                            <div class="copy-rhd-element"  data-value="textarea" ><a href="#"><?php esc_html_e( 'Copy', 'rhd-migration' ); ?></a><span class="rhd-copied" ><?php esc_html_e( 'Copied!', 'rhd-migration' ); ?></span></div>   
                            <div class="rhd-help-text"><?php esc_html_e( 'Enter a URL of the destination website in a format like https://www.example.com, if you have multiple destination URL then add in the new line.', 'rhd-migration' ); ?></div>
                        </td>
                    </tr>
                    <tr valign='top'  class=" rhd_source_section" >
                        <th scope='row'><?php esc_html_e( 'Hash Key' , 'rhd-migration'); ?></th>
                        <td>
                            <input type="text" class="large-text regular-text" value="<?php echo esc_html($rhd_shash_key); ?>" id="rhd_shash_key"   name="rhd_shash_key"  >
                            <div class="copy-rhd-element" ><a href="#" ><?php esc_html_e( 'Copy', 'rhd-migration' ); ?></a><span class="rhd-copied" ><?php esc_html_e( 'Copied!', 'rhd-migration' ); ?></span></div>
                            <div class="rhd-help-text"><?php esc_html_e( "It's the key used to authenticate call requests between the source and the destination website. Please copy it from source website." , 'rhd-migration' ); ?></div>
                        </td>
                    </tr>
                    <tr valign='top'  class=" rhd_destination_section" >
                        <th scope='row'><?php esc_html_e( 'Hash Key' , 'rhd-migration'); ?></th>
                        <td>
                            <input type="text" class="large-text regular-text" value="<?php echo esc_html($rhd_dhash_key); ?>" id="rhd_dhash_key"   name="rhd_dhash_key"  >
                            <div class="copy-rhd-element" ><a href="#"><?php esc_html_e( 'Copy', 'rhd-migration' ); ?></a><span class="rhd-copied" ><?php esc_html_e( 'Copied!', 'rhd-migration' ); ?></span></div>
                            <div class="rhd-help-text"><?php esc_html_e( "It's the key used to authenticate call requests between the source and the destination website. Please copy it from source website." , 'rhd-migration' ); ?></div>
                        </td>
                    </tr>
                    <tr valign='top'  class=" rhd_destination_section" >
                        <th scope='row'><?php esc_html_e( 'Default Author' , 'rhd-migration'); ?></th>
                        <td>
                            <select name="rhd_author" size="1"  id="rhd_author" class="regular-select" >
                                <?php
                                if ( is_array( $rhd_authors ) && count( $rhd_authors ) > 0 ) {
                                  foreach ( $rhd_authors as $rhd_author => $label ) {
                                    ?>
                                    <option value="<?php echo esc_attr($rhd_author); ?>"<?php selected( $rhd_author, $rhd_author_selected ); ?>>
                                      <?php echo esc_html($label); ?>
                                    </option>
                                    <?php
                                  }
                                }
                                ?>
                              </select>
                              <div class="rhd-help-text"><?php esc_html_e( "Default author get assigned for post, page etc..", 'rhd-migration' ); ?></div>
                        </td>
                    </tr>
                    <tr valign='top'  class=" rhd_destination_section" >
                        <th scope='row'><?php esc_html_e( 'Media Exclude' , 'rhd-migration'); ?></th>
                        <td>
                            <select name="rhd_media_exclude" size="1"  id="rhd_media_exclude" class="regular-select" >
                                <?php
                                if ( is_array( $rhd_medias ) && count( $rhd_medias ) > 0 ) {
                                  foreach ( $rhd_medias as $rhd_media => $label ) {
                                    ?>
                                    <option value="<?php echo esc_attr($rhd_media); ?>"<?php selected( $rhd_media, $rhd_media_exclude ); ?>>
                                      <?php echo esc_html($label); ?>
                                    </option>
                                    <?php
                                  }
                                }
                                ?>
                              </select>
                              <div class="rhd-help-text"><?php esc_html_e( "If you want to exclude images or pdfs etc.. during migration. Means you will manually move media on destination website.", 'rhd-migration' ); ?></div>
                        </td>
                    </tr>
                    <tr valign='top'  class=" rhd_destination_section" >
                        <th scope='row'><?php esc_html_e( 'Media Extensions Allowed' , 'rhd-migration'); ?></th>
                        <td>
                            <input type="text" class="large-text regular-text" value="<?php echo esc_html($rhd_media_ext); ?>" id="rhd_media_ext"   name="rhd_media_ext"  />
                            <div class="copy-rhd-element" ><a href="#"><?php esc_html_e( 'Copy', 'rhd-migration' ); ?></a><span class="rhd-copied" ><?php esc_html_e( 'Copied!', 'rhd-migration' ); ?></span></div>
                            <div class="rhd-help-text"><?php esc_html_e( "There will be only allowed file extensions migrated from the source to the destination website. Multiple values must be separated by commas. Other resources would need to migrate manually and also absolute media path is required to migrate." , 'rhd-migration' ); ?></div>
                        </td>
                    </tr>                    
                    <tr valign='top'  class=" rhd_source_section" >
                        <th scope='row'><?php esc_html_e( 'Comments' , 'rhd-migration'); ?></th>
                        <td>
                            <select name="rhd_comment" size="1"  id="rhd_comment" class="regular-select">
                                <?php
                                if ( is_array( $rhd_medias ) && count( $rhd_medias ) > 0 ) {
                                  foreach ( $rhd_medias as $rhd_media => $label ) {
                                    ?>
                                    <option value="<?php echo esc_attr($rhd_media); ?>"<?php selected( $rhd_media, $rhd_comment_selected ); ?>>
                                      <?php echo esc_html($label); ?>
                                    </option>
                                    <?php
                                  }
                                }
                                ?>
                              </select>
                              <div class="rhd-help-text"><?php esc_html_e( 'If you want to migrate comments with post/page, select "Yes" otherwise "No".', 'rhd-migration' ); ?></div>
                        </td>
                    </tr>
                    <tr valign='top'  class=" rhd_destination_section" >
                        <th scope='row'><?php esc_html_e( 'Operation' , 'rhd-migration'); ?></th>
                        <td>
                            <select name="rhd_operation" size="1"   id="rhd_operation" class="regular-select" >
                                <?php
                                if ( is_array( $rhd_operations ) && count( $rhd_operations ) > 0 ) {
                                  foreach ( $rhd_operations as $operation => $label ) {
                                    ?>
                                    <option value="<?php echo esc_attr($operation); ?>"<?php selected( $operation, $rhd_operation ); ?>>
                                      <?php echo esc_html($label); ?>
                                    </option>
                                    <?php
                                  }
                                }
                                ?>
                              </select>
                              <div class="rhd-help-text"><?php esc_html_e( "Add/Update: If it's not exist then it create and update if exists.", 'rhd-migration' ); ?></div>
                              <div class="rhd-help-text"><?php esc_html_e( "Add: If it's not exist then it'll create else ignore it.", 'rhd-migration' ); ?></div>
                              <div class="rhd-help-text"><?php esc_html_e( "Update: If it exists then update else ignore it.", 'rhd-migration' ); ?></div>
                        </td>
                    </tr>
                </table>
                <?php submit_button(null, 'rhd-button button-primary rhd-button-lg'); ?>
            </form>
  </div>
  <div class="rhd-col rhd_span_1_of_2 rhd-about-us">
      <h3 ><span><?php  esc_html_e( "About this Plugin:", 'rhd-migration' ); ?></span></h3>
      <p><?php  esc_html_e( "Migrate the content of pages, posts, or custom post types from one WordPress to another WordPress.", 'rhd-migration' ); ?></p>
            <p><?php  esc_html_e( "If you want to migrate post/page content from the stage to the multiple production server, this plugin will be your best choice. ", 'rhd-migration' ); ?></p>
            <p><?php  esc_html_e( "If your client wants to prepare the page in a staging environment and wants to migrate to production with less efforts then the plugin will do it easily for you. It automatically downloads media based on configuration.", 'rhd-migration' ); ?></p>
            <h3><?php  esc_html_e( "Special Features:", 'rhd-migration' ); ?> </h3>
            <ul class="rhd-help-doc">
              <li><?php  esc_html_e( "Communicate with WordPress default API. So no special configuration is needed.", 'rhd-migration' ); ?></li>
              <li><?php  esc_html_e( "Download Media Automatically.", 'rhd-migration' ); ?></li>
              <li><?php esc_html_e( "Multiple Destination URL Support.", 'rhd-migration' ); ?></li>
              <li><?php esc_html_e( "Add/Update post/page data based on configuration", 'rhd-migration' ); ?></li>
              <li><?php  esc_html_e( "RTL Supported", 'rhd-migration' ); ?></li>        
              <li><?php  esc_html_e( "Comment Migration Supported", 'rhd-migration' ); ?></li>    
              <li><?php  esc_html_e( "Media Exclude Supported", 'rhd-migration' ); ?></li>  
              <li><?php  esc_html_e( "Multiple Destination Supported", 'rhd-migration' ); ?></li>  
              <li><?php  esc_html_e( "Overwrite the default setting during the migration", 'rhd-migration' ); ?></li>  
            </ul>
            <p><strong><?php esc_html_e( "More features will be added soon...", 'rhd-migration' ); ?></strong></p> 
  </div>
</div>

<div>
  <div class="rhd-col rhd_span_2_of_3 rhd-hide rhd-section rhd-group rhd-tabs" id="rhd-help-tab" >
          <h3><span><?php  esc_html_e( 'Help & Troubleshooting', 'rhd-migration' ); ?></span></h3>
          <form method='post' action='admin-post.php' name="rhd_general_form" id="rhd_general_form">
              <p>             
                <?php  esc_html_e( "Following information would help to troubleshoot.", 'rhd-migration' ); ?>
              </p> 
              <!--System Info-->      
              <label><strong><?php  esc_html_e( 'System Info', 'rhd-migration' ); ?></strong></label><br>
              <textarea readonly="readonly"   onclick="this.focus(); this.select()" name='rhd-sysinfo'><?php echo RHD_Compatibility::get_sysinfo(); ?></textarea>
              <br><br><br>                      
              <!--Submit Button-->
              <input type="hidden" name="action" value="rhd_download_sysinfo" />
              <?php wp_nonce_field( 'rhd_download_sysinfo', 'rhd_sysinfo_nonce' ); ?>
              <input type="submit" name="rhd-download-sysinfo" id="rhd-download-sysinfo" class="rhd-button button-primary rhd-button-lg" value="<?php esc_html_e( 'Download System Info', 'rhd-migration' ); ?>">
          </form>
  </div>
</div>

</div>
</div>
<script>
jQuery(document).ready(function() { 
    jQuery("#rhd_website").trigger("change");
});
</script>
</div>