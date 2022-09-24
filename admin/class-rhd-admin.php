<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.pragmasoftwares.com/
 * @since      1.0.0
 *
 * @package    RHD
 * @subpackage RHD/admin
 */
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/wp_remote_class.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/wp_remote_json.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    RHD
 * @subpackage RHD/admin
 * @author     Pankaj Dadure <pankaj.pragma@gmail.com>
 */
class RHD_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $rhd    The ID of this plugin.
     */
    private $rhd;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $rhd       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($rhd, $version)
    {
        $this->rhd = $rhd;
        $this->version       = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles($hook)
    {
        $pos = strpos($hook, 'rhd-migration');
        wp_enqueue_style($this->rhd . '_modal', plugin_dir_url(__FILE__) . 'css/jquery.modal.min.css', array(), $this->version, 'all');
        wp_enqueue_style($this->rhd, plugin_dir_url(__FILE__) . 'css/rhd-admin.css', array(), $this->version, 'all');
        if (is_rtl()) {
            wp_enqueue_style($this->rhd . '_rtl', plugin_dir_url(__FILE__) . 'css/rhd-rtl-admin.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($hook)
    {
        $pos = strpos($hook, 'rhd-migration');
        wp_enqueue_script($this->rhd . '-modal', plugin_dir_url(__FILE__) . 'js/jquery.modal.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->rhd, plugin_dir_url(__FILE__) . 'js/rhd-admin.js', array('jquery'), $this->version, false);
    }

    public function menu()
    {
        add_management_page(__('RHD Migration', 'rhd-migration'), __('RHD Migration', 'rhd-migration'), 'manage_options', 'rhd-migration', 'rhd_migration');
        add_action('admin_init', 'rhd_settings');
    }

    public function load_rhd_log()
    {
        global $wpdb;
        $id    = $this->filterIntData('id');
        $type    = $this->filterTextData('type');
        $image    = $this->filterIntData('image');
        $rhd_hash_key = sanitize_text_field(get_option('rhd_shash_key'));
        $rhd_destination_url = sanitize_text_field(get_option('rhd_destination_url'));
        $rhd_comment = sanitize_text_field(get_option('rhd_comment'));
        if (empty($rhd_destination_url)) {
            $result = array('error' => 0, 'message' => __("Please setup the destination website URL from setting page [ Tools -> RHD Migration ]", 'rhd-migration'));
            $this->json_return($result);
        }
        if (empty($rhd_hash_key)) {
            $result = array('error' => 0, 'message' => __("Hash key can not be left blank. Please check the setting page [Tools -> RHD Migration ]", 'rhd-migration'));
            $this->json_return($result);
        }
        $source_url = site_url();
        $post_data = get_post($id);
        if (empty($id) || empty($post_data)) {
            $result = array('error' => 0, 'message' => __("Invalid request. No data found the post/page", 'rhd-migration'));
            $this->json_return($result);
        }
        $post_meta = get_post_meta($id);
        
        $author_id = $post_data->post_author;
        $display_name = get_the_author_meta('user_nicename', $author_id);
        $post_parent = $post_data->post_parent;
        $parent_post_name = '';
        if (!empty($post_parent)) {
            $parent_post = get_post($post_parent);
            $parent_post_name = $parent_post->post_name;
        }
        $body = array("source_url" => $source_url, "hash_key" => $rhd_hash_key, "user_id" => get_current_user_id(), 'post_data' => $post_data, 'destination' => $rhd_destination_url, 'post_meta' => $post_meta, 'display_name' => $display_name, 'parent_post_name' => $parent_post_name);
        $image_upload = 0;
        if ($image) {
            $attachments = get_children(array(
                'post_parent' => $id,
                'post_type' => 'attachment',
                'order' => 'ASC',
                'orderby' => 'menu_order ID'
            ));
            $images = array();
            $i = 0;
            foreach ($attachments as $att_id => $attachment) {
                $attachment->image_url = wp_get_attachment_url($attachment->ID);
                $attach_meta = get_post_meta($attachment->ID);
                $images[$i]['data'] =  $attachment;
                $images[$i]['meta'] =  $attach_meta;
                $i++;
            }
            $thumbnail_id = get_post_thumbnail_id($id);
            if ($thumbnail_id) {
                $p = get_post($thumbnail_id);
                $p->image_url = wp_get_attachment_url($thumbnail_id);
                $attach_meta = get_post_meta($thumbnail_id);
                $images[$i]['data'] =  $p;
                $images[$i]['meta'] =  $attach_meta;
                $i++;
            }
            $body['images'] = $images;
            $body = json_encode($body);

            # API CALL for Image Upload
            $remote_request = new WordPressRemoteJSON($this->cleanRHDURL($rhd_destination_url) . '/wp-json/rhd/v1/rhd_image_call_request/', array(
                'body' => $body
            ), "post");
            $remote_request->run();
            $response_data = $remote_request->get_body();
        } else {
            # Comments
            $comments = [];
            if ($rhd_comment == 'yes') {
                $pcomments = get_comments(array('post_id' => $id));
                foreach ($pcomments as $k => $comment) {
                    $comments[$k] = (array)$comment;
                }
            }
            $post_taxonomies = array();
            $taxonomies = get_object_taxonomies($post_data->post_type);
            if (!empty($taxonomies) && is_array($taxonomies)) :
                foreach ($taxonomies as $taxonomy) {
                    $post_terms = wp_get_object_terms($id, $taxonomy);
                    $post_terms = array_unique( wp_list_pluck( $post_terms, 'name' ) ); // distinct term names
                    $post_taxonomies[] = array($post_terms, $taxonomy);
                }
            endif;
            $body['taxonomies'] = $post_taxonomies;
            $body['comments'] = $comments;
            $body = json_encode($body);
            # API CALL Post/Page Create & Update
            $remote_request = new WordPressRemoteJSON($this->cleanRHDURL($rhd_destination_url) . '/wp-json/rhd/v1/rhd_init_call_request/', array(
                'body' => $body
            ), "post");
            $remote_request->run();
        }

        if ($remote_request->is_success()) {
            $response_data = $remote_request->get_body();
            $output = json_decode($response_data, true);
            if (!empty($output) && isset($output['message']) && is_array($output['message'])) {
                $response =  "";
                foreach ($output['message'] as $message) {
                    $response .=  $message;
                }
                $error = $output['status'];
                $image_upload = 1;
            } else {
                $response = '<div class="rhd-status-msg rhd-error-label" >' . __("Invalid request. No response returned from destination website.", 'rhd-migration') . '</div>';
                $error = $output['status'];
            }
        } else {
            $msg = $remote_request->get_response_message();
            if ($msg == 'Not Found') {
                $msg = __('Please check the RHD plugin is installed on the destination and no extra authentication is enabled with website.', 'rhd-migration');
            } else if ($msg == 'Unauthorized') {
                $msg = __('No extra authentication should be enabled with destination website.', 'rhd-migration');
            }
            $response = '<div class="rhd-status-msg rhd-error-label" >' . $msg . '</div>';
            $error = 0;
        }
        $result = array('status' => $error, 'message' => $response, 'image_upload' => $image_upload);
        $this->json_return($result);
    }


    function get_api_args()
    {
        $api_args = array(
            'hash_key' => array(
                'default' => "",
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'source_url' => array(
                'default' => "",
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'user_id' => array(
                'default' => 0,
                'sanitize_callback' => 'absint',
            ),
            'display_name' => array(
                'default' => "",
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'destination' => array(
                'default' => "",
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'parent_post_name' => array(
                'default' => "",
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
        return $api_args;
    }

    public function  rhd_init_call_request()
    {
        $api_args = $this->get_api_args();
        register_rest_route(
            'rhd/v1',
            'rhd_init_call_request',
            array(
                'methods' => 'POST',
                'callback' =>  array($this, 'rhd_call_request_callback'),
                'permission_callback' => '__return_true',
                'args'            => $api_args,
            )
        );
    }

    public function  rhd_image_call_request()
    {
        $api_args = $this->get_api_args();
        register_rest_route(
            'rhd/v1',
            'rhd_image_call_request',
            array(
                'methods' => 'POST',
                'callback' =>  array($this, 'rhd_image_request_callback'),
                'permission_callback' => '__return_true',
                'args'            => $api_args,
            )
        );
    }

    public function  rhd_check_call_request()
    {
        $api_args = $this->get_api_args();
        register_rest_route(
            'rhd/v1',
            'rhd_check_call_request',
            array(
                'methods' => 'GET',
                'callback' =>  array($this, 'rhd_check_request_callback'),
                'permission_callback' => '__return_true',
                'args'            => $api_args,
            )
        );
    }

    function rhd_check_request_callback($request)
    {
        global $wp;
        $response = array();
        $response['status'] = true;
        $response['message'] = "Ok";
        return $response;
    }

    function rhd_image_request_callback($request)
    {
        $response = array();
        //Do logic here
        $status = $this->isValidRequest($request);
        if ($status !== true) {
            $response['status'] = false;
            $response['message'] = array('<div class="rhd-status-msg rhd-error-label" >' . $status . '</div>');
        } else {
            $body = json_decode($request->get_body(), true);
            $post_data = isset($body['post_data']) ? $body['post_data'] : '';
            $post_metas  = isset($body['post_meta']) ? $body['post_meta'] : '';
            $images  = isset($body['images']) ? $body['images'] : '';
            $post_name = sanitize_text_field($post_data['post_name']);
            $old_post_id = absint($post_data['ID']);
            $post_type = sanitize_text_field($post_data['post_type']);
            $operation = sanitize_text_field(get_option('rhd_operation'));
            $media = sanitize_text_field(get_option('rhd_media_exclude'));

            if ($media == "yes") {
                $response['status'] = false;
                $response['message'] = array('<div class="rhd-status-msg rhd-error-label" >' . __("Oops! Post/Page media synchronization is disabled on destination website.", "rhd") . '</div>');
            } else {
                $rhd_author = absint(get_option('rhd_author'));
                $display_name = sanitize_text_field($body['display_name']);
                $the_user = get_user_by('login', $display_name);
                if (!empty($the_user)) {
                    $rhd_author = absint($the_user->ID);
                }

                $message = [];
                $found_images = array();
                $found_post_id = $this->getPostId($post_name, $post_type, $old_post_id);

                if (!function_exists('wp_read_video_metadata')) {
                    require_once(ABSPATH . 'wp-admin/includes/media.php');
                }
                if (!function_exists('wp_crop_image')) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                }
                # Add Media like Featured Image etc..
                $this->uploadAllMedia($found_post_id, $images, $message, $rhd_author);

                foreach ($post_data as $post_key => $post_value) {
                    $this->getResourceUrls($post_value, $found_images);
                }
                foreach ($post_metas as $meta_key => $meta_value) {
                    if (is_array($meta_value)) {
                        foreach ($meta_value as $value) {
                            $this->getResourceUrls($value, $found_images);
                        }
                    } else {
                        $value = $this->getResourceUrls($value, $found_images);
                    }
                }
                $found_images = array_filter(array_unique($found_images));

                # Add Media Within Content
                $this->uploadAllContentMedia($found_post_id, $found_images, $message, $rhd_author);

                $this->uploadThumbnail($found_post_id, $post_metas, $message);


                $response['status'] = false;
                if(count($found_images) || count($images))
                {
                    $message[] = '<div class="rhd-status-msg rhd-success-label" >' . __("Page/Post media synchronization is completed.", "rhd") . '</div>';
                }
                $response['message'] = $message;
            }
        }

        return $response;
    }

    function getResourceExtensions()
    {
        $rhd_ext = sanitize_text_field(get_option('rhd_media_ext'));
        $exts = explode(",", $rhd_ext);
        $exts = array_map('trim', $exts);
        return $exts;
    }

    function rhd_call_request_callback($request)
    {
        $response = array();
        //Do logic here
        $status = $this->isValidRequest($request);
        if ($status !== true) {
            $response['status'] = false;
            $response['message'] = array('<div class="rhd-status-msg rhd-error-label" >' . $status . '</div>');
        } else {
            $rhd_author = absint(get_option('rhd_author'));
            $body = json_decode($request->get_body(), true);
            $post_data = isset($body['post_data']) ? $body['post_data'] : '';
            $post_meta  = isset($body['post_meta']) ? $body['post_meta'] : '';
            $taxonomies  = isset($body['taxonomies']) ? $body['taxonomies'] : '';
            $comments  = isset($body['comments']) ? $body['comments'] : '';
            $post_name = sanitize_text_field($post_data['post_name']);
            $old_post_id = absint($post_data['ID']);
            $post_type = sanitize_text_field($post_data['post_type']);
            $display_name = sanitize_text_field($body['display_name']);
            $parent_post_name = sanitize_text_field($body['parent_post_name']);
            $rhd_ext = sanitize_text_field(get_option('rhd_media_ext'));
            $operation = sanitize_text_field(get_option('rhd_operation'));
            $media = sanitize_text_field(get_option('rhd_media_exclude'));
            $the_user = get_user_by('login', $display_name);
            if (!empty($the_user)) {
                $rhd_author = absint($the_user->ID);
            }

            $found_post_id = $this->getPostId($post_name, $post_type, $old_post_id);

            $post_args = array();
            $post_args['ID'] = $found_post_id;
            foreach ($post_data as $post_key => $post_value) {
                if (in_array($post_key, array('guid'))) {
                    continue;
                }
                $post_args[$post_key] = $post_value;
            }
            $post_args['post_content'] = $this->replaceUrlContent($post_args['post_content']);
            $post_args['post_title'] = $this->replaceUrlContent($post_args['post_title']);
            $post_args['post_excerpt'] = $this->replaceUrlContent($post_args['post_excerpt']);
            $post_args['to_ping'] = $this->replaceUrlContent($post_args['to_ping']);
            $post_args['pinged'] = $this->replaceUrlContent($post_args['pinged']);
            $post_args['post_content_filtered'] = $this->replaceUrlContent($post_args['post_content_filtered']);
            $post_args['post_parent'] = 0;
            if (!empty($parent_post_name)) {
                $parent_post = get_page_by_path($parent_post_name, OBJECT, $post_type);
                $post_args['post_parent'] = isset($parent_post->ID) ? $parent_post->ID : 0;
            }
            $meta_inputs = array();
            foreach ($post_meta as $meta_key => $meta_value) {
                $meta_inputs[$meta_key] = $meta_value;
            }
            if (FALSE === get_post_status($found_post_id) && in_array($operation, array('add', 'add_update'))) {
                unset($post_args['ID']);
                # Default author
                $post_args['post_author'] = $rhd_author;
                $postId = wp_insert_post($post_args, true);
                if (is_wp_error($postId)) {
                    $response['status'] = false;
                    $response['message'] = array($postId->get_error_message());
                } else {

                    $message = array('<div class="rhd-status-msg rhd-success-label" >' . __('Post/Page is created successfully!', 'rhd-migration') . '</div>');
                    $this->addPostMeta($postId, $meta_inputs, $message, $old_post_id, $post_type);
                    $this->addTaxonomies($postId, $taxonomies, $message);
                    $this->addComments($postId, $comments, $message);
                    $message[] = '<div class="rhd-status-msg rhd-success-label" >' . __('Migration completed successfully!', 'rhd-migration') . '</div>';
                    $response['status'] = true;
                    $response['message'] = $message;
                }
            } else if (in_array($operation, array('update', 'add_update'))) {
                $post_args['ID'] = $found_post_id;
                unset($post_args['post_author']);
                ## Update the post into the database  
                $post_id = wp_update_post($post_args, true);
                if (is_wp_error($post_id)) {
                    $response['status'] = false;
                    $response['message'] = array($post_id->get_error_message());
                } else {
                    $postId = $post_args['ID'];
                    # Add comments
                    $message = array('<div class="rhd-status-msg rhd-success-label" >' . __('Post/Page is updated successfully!', 'rhd-migration') . '</div>');
                    $this->addPostMeta($postId, $meta_inputs, $message, $old_post_id, $post_type);
                    $this->addTaxonomies($postId, $taxonomies, $message);
                    $message[] = '<div class="rhd-status-msg rhd-success-label" >' . __('Migration completed successfully!', 'rhd-migration') . '</div>';
                    $response['status'] = true;
                    $response['message'] = $message;
                }
            } else {
                $message[] = '<div class="rhd-status-msg rhd-success-label" >' . __('Oops! Nothing is added/updated', 'rhd-migration') . '</div>';
                $response['status'] = true;
                $response['message'] = $message;
            }
        }

        return $response;
    }

    function getPostId($post_name, $post_type, $old_post_id)
    {
        /* Attempt to find post id by post name if it exists */
        $post = get_page_by_path($post_name, OBJECT, $post_type);
        $found_post_id = isset($post->ID) ? $post->ID : '';
        if (empty($found_post_id)) {
            $data = json_decode(get_option("rhd_posts"), true);
            if (!empty($data) && isset($data[$post_type . $old_post_id])) {
                $found_post_id = $data[$post_type . $old_post_id];
            }
            if (!empty($found_post_id)) {
                $post   = get_post($found_post_id);
                if (empty($post)) {
                    $found_post_id = '';
                }
            }
        }
        return $found_post_id;
    }

    function update_rhd_posts_meta($post_type, $old_post_id, $postId)
    {
        $data = get_option("rhd_posts");
        if (!empty($data)) {
            $data = json_decode($data, true);
            if (empty($data))
                $data = [];
        } else {
            $data = [];
        }
        $data[$post_type . $old_post_id] = $postId;
        update_option("rhd_posts", json_encode($data));
    }

    function addPostMeta($postId, $post_metas, &$message, $old_post_id, $post_type)
    {
        global $wpdb;

        $this->update_rhd_posts_meta($post_type, $old_post_id, $postId);

        if (empty($post_metas) || empty($postId)) {
            return false;
        }
        $table = $wpdb->prefix . 'postmeta';
        $wpdb->delete($table, array('post_id' => $postId));
        foreach ($post_metas as $meta_key => $meta_value) {
            if (in_array($meta_key, array('_thumbnail_id'))) {
                continue;
            }

            if (is_array($meta_value)) {
                foreach ($meta_value as $value) {
                    $value = $this->replaceUrlContent($value);
                    $post_meta_id = add_post_meta($postId, $meta_key, maybe_unserialize($value));
                    if (is_wp_error($post_meta_id)) {
                        $message[] = '<div class="rhd-status-msg rhd-error-label" >' . $post_meta_id->get_error_message() . '</div>';
                    }
                }
            } else {
                $value = $this->replaceUrlContent($value);
                $post_meta_id = add_post_meta($postId, $meta_key, maybe_unserialize($meta_value));
                if (is_wp_error($post_meta_id)) {
                    $message[] = '<div class="rhd-status-msg rhd-error-label" >' . $post_meta_id->get_error_message() . '</div>';
                }
            }
        }
    }

    function addTaxonomies($postId, $taxonomies, &$message)
    {
        if (empty($taxonomies) || empty($postId)) {
            return false;
        }

        foreach ($taxonomies as $taxonomie) {
            $term_taxonomy_ids = wp_set_object_terms($postId, $taxonomie[0], $taxonomie[1], false);
            if (is_wp_error($term_taxonomy_ids)) {
                $message[] = '<div class="rhd-status-msg rhd-error-label" >' . $term_taxonomy_ids->get_error_message() . '</div>';
            } else {
                // Success! These taxonomies were added to the post.

            }
        }
        $message[] =  '<div class="rhd-status-msg rhd-success-label" >' . __('Taxonomies migration is completed.', 'rhd-migration') . '</div>';
    }

    function addComments($postId, $taxonomies, &$message)
    {
        if (empty($comments) || empty($postId)) {
            return false;
        }
        foreach ($comments as $comment) {
            $data = array(
                'comment_post_ID' => $postId,
                'comment_author' => $comment['comment_author'],
                'comment_author_email' => $comment['comment_author_email'],
                'comment_author_url' => $comment['comment_author_url'],
                'comment_author_IP' => $comment['comment_author_IP'],
                'comment_date' => $comment['comment_date'],
                'comment_date_gmt' => $comment['comment_date_gmt'],
                'comment_content' => $comment['comment_content'],
                'comment_karma' => $comment['comment_karma'],
                'comment_approved' => $comment['comment_approved'],
                'comment_agent' => $comment['comment_agent'],
                'comment_type' => $comment['comment_type'],
                'comment_parent' => $comment['comment_parent'],
                'user_id' => $comment['user_id']
            );
            $comment_id = wp_insert_comment($data);
            if (is_wp_error($comment_id)) {
                $message[] = '<div class="rhd-status-msg rhd-error-label" >' . $comment_id->get_error_message() . '</div>';
            } else {
                // Success! These comments were added to the post.

            }
        }
        $message[] =  '<div class="rhd-status-msg rhd-success-label" >' . __('Comment migration is completed.', 'rhd-migration') . '</div>';
    }

    function uploadThumbnail($postId, $post_metas, &$message)
    {
        if (empty($post_metas) || empty($postId)) {
            return false;
        }
        
        if (isset($post_metas['_thumbnail_id'])) {
            if (is_array($post_metas['_thumbnail_id'])) {
                $_thumbnail_id = current($post_metas['_thumbnail_id']);
            } else {
                $_thumbnail_id = $post_metas['_thumbnail_id'];
            }
            if ($_thumbnail_id) {
                $post_type = 'attachment';
                $old_post_id = $_thumbnail_id;
                $found_post_id = '';
                /* Attempt to find post id by post name if it exists */
                $data = json_decode(get_option("rhd_posts"), true);
                if (!empty($data) && isset($data[$post_type . $old_post_id])) {
                    $found_post_id = $data[$post_type . $old_post_id];
                }
                if (!empty($found_post_id)) {
                    $post   = get_post($found_post_id);
                    if (empty($post)) {
                        $found_post_id = '';
                    }
                }
                if ($found_post_id) {
                    set_post_thumbnail($postId, $found_post_id);
                    $message['image_error'] = '<div class="rhd-status-msg rhd-success-label" >' . __('Featured image set successfully!', 'rhd-migration') . '</div>';
                } else {
                    $message['image_error'] = '<div class="rhd-status-msg rhd-error-label" >' . __('Unable to set the featured image.', 'rhd-migration') . '</div>';
                }
            }
        }
    }

    function uploadAllMedia($postId, $images, &$message, $rhd_author)
    {
        if (empty($images) || empty($postId)) {
            return false;
        }
        $is_error = 0;
        $exts = $this->getResourceExtensions();
        // Get upload dir
        $upload_dir    = wp_upload_dir(); 
        $parent_id = $postId;
        $attachment_meta = array();
        $post_type = 'attachment';
        foreach ($images as $image_data) {
            $image = $image_data['data'];
            $attachment_meta = $image_data['meta'];

            // Set filename, incl path
            $image_url = $image['image_url'];
            $path_info = pathinfo($image_url);
            $extension = isset($path_info['extension']) ? $path_info['extension'] : '';
            if (!in_array($extension, $exts)) {
                continue;
            }
            $r_image_url = $this->replaceUrlImageContent($image_url);
            $p = parse_url($r_image_url);
            $image_path = $this->cleanRHDURL(ABSPATH) . $p['path'];
            $filename = basename($image_url);
            $upload_dir = dirname($image_path);
            if (wp_mkdir_p($upload_dir)) {
                // Prepare an array of post data for the attachment.
                $attachment_data = array();
                foreach ($image as $img_key => $img_val) {
                    if (in_array($img_key, array('post_author', 'post_parent', 'image_url'))) {
                        continue;
                    }
                    $img_val = $this->replaceUrlContent($img_val);
                    $attachment_data[$img_key] = $img_val;
                }
                $old_post_id = $attachment_data["ID"];
                /* Attempt to find post id by post name if it exists */
                $found_post_id = $this->getPostId($attachment_data["post_name"], $post_type, $old_post_id);
                $attachment_data['ID'] = $found_post_id;
                $meta_inputs = array();
                foreach ($attachment_meta as $meta_key => $meta_value) {
                    $meta_inputs[$meta_key] = $meta_value;
                }
                $attachment_data['meta_input'] = $meta_inputs;
                if (!$found_post_id) {
                    unset($attachment_data['ID']);
                    $attachment_data['post_author'] = $rhd_author;
                } else {
                    $attachment_data['ID'] = $found_post_id;
                    unset($attachment_data['post_author']);
                }
                $file = $upload_dir . '/' . $filename;
                $image_data = $this->getResourceContent($image_url);
                if (empty($image_data)) {
                    $message['image_error'] = '<div class="rhd-status-msg rhd-error-label" >' . __('Unable to download the image from source website.', 'rhd-migration') . '</div>';
                    $is_error = 1;
                    continue;
                }
                file_put_contents($file, $image_data);
                if (!file_exists($file)) {
                    $message['image_error'] = '<div class="rhd-status-msg rhd-error-label" >' . __('Unable to write the image on destination website.', 'rhd-migration') . '</div>';
                    $is_error = 1;
                }
                // Insert the attachment.
                $attach_id = wp_insert_attachment($attachment_data, $file, $parent_id);
                if (is_wp_error($attach_id)) {
                    $message['attachment_error'] = '<div class="rhd-status-msg rhd-error-label" >' . $attach_id->get_error_message() . '</div>';
                    $is_error = 1;
                } else {
                    $this->update_rhd_posts_meta($post_type, $old_post_id, $attach_id);
                    if ($file && file_exists($file)) {
                        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                    }
                }
            } else {
                $is_error = 1;
                $message['attachment_folder'] =  '<div class="rhd-status-msg rhd-success-label" >' . __('Unable to create folder path permission issue.', 'rhd-migration') . '</div>';
            }
        }

        if (!$is_error) {
            $message['attachment_success'] =  '<div class="rhd-status-msg rhd-success-label" >' . __('All post/page media copied successfully!', 'rhd-migration') . '</div>';
        }
    }

    function uploadAllContentMedia($postId, $images, &$message, $rhd_author)
    {
        if (empty($images) || empty($postId)) {
            return false;
        }
        $exts = $this->getResourceExtensions();
        $is_error = 0;
        // Get upload dir
        $root_path = ABSPATH;
        foreach ($images as $image_url) {
            $path_info = pathinfo($image_url);
            $extension = isset($path_info['extension']) ? $path_info['extension'] : '';
            $r_image_url = $this->replaceUrlContent($image_url);
            if (!in_array($extension, $exts) || !$this->isDestinationURL($r_image_url)) {
                continue;
            }
            $r_image_url = $this->replaceUrlImageContent($image_url);
            $p = parse_url($r_image_url);
            $image_path = $this->cleanRHDURL(ABSPATH) . $p['path'];
            $filename = basename($image_url);
            $upload_dir = dirname($image_path);
            if (wp_mkdir_p($upload_dir)) {
                $file = $upload_dir . '/' . $filename;
                if (file_exists($file)) {
                    continue;
                }

                $image_data = $this->getResourceContent($image_url);
                if (empty($image_data)) {
                    $message['image_error'] = '<div class="rhd-status-msg rhd-error-label" >' . __('Unable to download the image from source website.', 'rhd-migration') . '</div>';
                    $is_error = 1;
                    continue;
                }
                file_put_contents($file, $image_data);
                if (!file_exists($file)) {
                    $message['image_error'] = '<div class="rhd-status-msg rhd-error-label" >' . __('Unable to write the image on destination website.', 'rhd-migration') . '</div>';
                    $is_error = 1;
                }
            } else {
                $is_error = 1;
                $message['attachment_folder'] =  '<div class="rhd-status-msg rhd-success-label" >' . __('Unable to create folder path permission issue.', 'rhd-migration') . '</div>';
            }
        }
        if (!$is_error) {
            $message['attachment_success'] =  '<div class="rhd-status-msg rhd-success-label" >' . __('All page/post media copied successfully!', 'rhd-migration') . '</div>';
        }
    }

    function isValidRequest($request)
    {
        global $wp;
        $current = $this->getCurrentUrl();
        $rhd_site_url = sanitize_text_field(get_option('rhd_site_url'));
        $rhd_hash_key = sanitize_text_field(get_option('rhd_dhash_key'));
        $rhd_website = sanitize_text_field(get_option('rhd_website'));
        $rhd_author = absint(get_option('rhd_author'));

        $body = json_decode($request->get_body(), true);
        $hash_key = sanitize_text_field($body['hash_key']);
        $source_url = sanitize_text_field($body['source_url']);
        $rhd_destination_url = sanitize_text_field($body['destination']);
        $site_url = site_url();
        $post_data = $body['post_data'];
        $post_name = sanitize_text_field($post_data['post_name']);
        $post_type = sanitize_text_field($post_data['post_type']);

        if ($request->get_route() != '/rhd/v1/rhd_init_call_request' && $request->get_route() != '/rhd/v1/rhd_image_call_request') {
            return __('Invalid request. Please check the configration', 'rhd-migration');
        } else if ($rhd_website != 'destination') {
            return __('Invalid request. Please check the destination website configration', 'rhd-migration');
        } else if (empty($rhd_site_url) || empty($rhd_hash_key)) {
            return __('Invalid request. Please check the configration', 'rhd-migration');
        } else if (empty($post_data) || empty($post_name) || empty($post_type)) {
            return __("Invalid request. No data found the post/page", 'rhd-migration');
        } else if (empty($rhd_author)) {
            return __("Invalid request. Author is not assigned in the destination website configration.", 'rhd-migration');
        } else if ($current != $this->cleanRHDURL($site_url) . '/wp-json/rhd/v1/rhd_init_call_request/' && $current != $this->cleanRHDURL($site_url) . '/wp-json/rhd/v1/rhd_image_call_request/') {
            return __('Invalid request. Please check the configration', 'rhd-migration');
        } else if ($rhd_hash_key != $hash_key) {
            return __("Invalid request. Authentication failure. Please check the hash key provided in source and destination should be same", 'rhd-migration');
        } else if ($this->cleanRHDURL($rhd_site_url) == $this->cleanRHDURL($rhd_destination_url)) {
            return __("Invalid request. Source & Destination URL can not be same in configration.", 'rhd-migration');
        }

        return true;
    }

    function getCurrentUrl()
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && sanitize_text_field($_SERVER["HTTPS"]) == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= sanitize_text_field($_SERVER["SERVER_NAME"]) . ":" . sanitize_text_field($_SERVER["SERVER_PORT"]) . sanitize_text_field($_SERVER["REQUEST_URI"]);
        } else {
            $pageURL .= sanitize_text_field($_SERVER["SERVER_NAME"]) . sanitize_text_field($_SERVER["REQUEST_URI"]);
        }
        return $pageURL;
    }
    public function json_return($result)
    {
        echo json_encode($result);
        exit;
    }

    public function filterTextData($key, $default = '')
    {
        $v = isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : sanitize_text_field($default);
        return $v;
    }

    public function filterIntData($key, $default = '')
    {
        $v = isset($_POST[$key]) ? intval($_POST[$key]) : intval($default);
        return $v;
    }

    public function filterHtmlData($key, $default = '')
    {
        $v = isset($_POST[$key]) ? wp_kses_post($_POST[$key]) : wp_kses_post($default);
        return $v;
    }

    function rhd_footer()
    {
        echo wp_kses_post('<div id="log-rhd-modal" class="modal "><div class="log-rhd-header"><h2>' . __('RHD Migration', 'rhd-migration') . '</h2></div><div id="log-rhd-row" ></div><div class="log-rhd-footer"><a href="#close" class="button button-primary button-large rhd-pull-right"  rel="modal:close">' . __('Close', 'rhd-migration') . '</a></div></div>');
    }

    public function get_rhd_media_exclude()
    {
        $data = array(
            'yes'     => __('Yes', 'rhd-migration'),
            'no'   => __('No', 'rhd-migration'),
        );
        return $data;
    }

    public function get_rhd_operations()
    {
        $data = array(
            'add_update'   => __('Add/Update', 'rhd-migration'),
            'add'     => __('Add', 'rhd-migration'),
            'update'   => __('Update', 'rhd-migration'),
        );
        return $data;
    }

    public function get_rhd_websites()
    {
        $data = array(
            'source'   => __('Source', 'rhd-migration'),
            'destination'     => __('Destination', 'rhd-migration')
        );
        return $data;
    }

    public function get_rhd_authors()
    {
        $users = get_users();
        $data = array();
        foreach ($users as $user) {
            $data[$user->ID] = $user->display_name;
        }
        return $data;
    }

    function page_tabs($current = 'rhd-migration')
    {
        $tabs = array(
            'rhd-migration'   => __('Settings', 'rhd-migration')
        );
        $html = '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? 'nav-tab-active' : '';
            $html .= '<a class="nav-tab ' . $class . '" href="?page=' . $tab . '">' . $name . '</a>';
        }
        $html .= '</h2><div class="rhd-loader"></div>';
        return $html;
    }

    function cleanRHDURL($url)
    {
        return rtrim($url, "/");
    }

    function isDestinationURL($url)
    {
        $site_url = sanitize_text_field(site_url());
        $destination = $this->cleanRHDURL($site_url);
        $pos = strpos($url, $destination);
        if ($pos === false) {
            return false;
        } else {
            return true;
        }
    }

    function replaceUrlContent($content)
    {
        $rhd_site_url = sanitize_text_field(get_option('rhd_site_url'));
        $site_url = sanitize_text_field(site_url());
        $source = $this->cleanRHDURL($rhd_site_url);
        $destination = $this->cleanRHDURL($site_url);
        $_html = str_replace($source, $destination, $content);
        return $_html;
    }

    function replaceUrlImageContent($content)
    {
        $rhd_site_url = sanitize_text_field(get_option('rhd_site_url'));
        $site_url = sanitize_text_field(site_url());
        $source = $this->cleanRHDURL($rhd_site_url); 
        $_html = str_replace($source, "", $content);
        return $_html;
    }

    function notification_rhd()
    {
        $json_url = site_url('wp-json');
        $remote_request = new WordPressRemoteJSON($json_url, array(), "get");
        $remote_request->run();
        if (!$remote_request->is_success()) {
            $response = '<div class="rhd-status-msg rhd-error" >' . __('To Run an extension, REST-API needs to be enabled. Please make sure that Permalinks should be enable by default. Go to the <b>Options page</b>, click the <b>Permalinks</b> subtab.This will take you to the page where you can customize how WordPress generates permalinks for blog posts. If its enabled still getting an error then please check an error:', 'rhd-migration') . ' "' . $remote_request->get_response_message() . '".</div>';
            return $response;
        }
    }

    function rhd_page_custom_button_classic()
    {
        global $post;
        $rhd_website_name = get_option('rhd_website');
        if ($rhd_website_name == 'source') {
            $html = '<div id="major-publishing-actions">';
            $html .= '<div id="export-action">';
            $html .= '<a href="#" class="rhd-start-migrate button button-primary button-large  " data-id="' . $post->ID . '" data-type="' . $post->post_type . '" data-alert="1" rel="permalink">' . __('RHD Migrate', 'rhd-migration') . '</a>';
            $html .= '</div>';
            $html .= '</div>';
            echo wp_kses_post($html);
        }
    }

    function add_rhd_post_action_menu($actions, $post)
    {
        $rhd_website_name = get_option('rhd_website');
        if ($rhd_website_name == 'source') {
            $actions['rhd-migration'] = '<a href="#" class="rhd-start-migrate" data-alert="0" data-id="' . $post->ID . '" data-type="' . $post->post_type . '"  >' . __('RHD Migrate', 'rhd-migration') . '</a>';
        }
        return $actions;
    }

    function getResourceUrls($content, &$found_images)
    {
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        if (preg_match_all("/$regexp/siU", $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $found_images[] = $match[2];
            }
        }
        preg_match_all('~<img.*?src=["\']+(.*?)["\']+~', $content, $match);
        $srcs = array_pop($match);
        foreach ($srcs as $src) {
            $found_images[] = $src;
        }
    }


    function getResourceContent($url)
    {
        if(!$url)
             return "";

        try {
            $response = wp_remote_get( $url, array( 'sslverify' => false ) );
            if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
                return "";
            }
            // Note that we decode the body's response since it's the actual data
            $data = $response['body'];
 
        } catch ( Exception $ex ) {
            $data = "";
        } // end try/catch

        return $data;
    }


    function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

    function rhd_settings_link($links)
    {
        // Build and escape the URL.
        $url = esc_url(add_query_arg(
            'page',
            'rhd-migration',
            get_admin_url() . 'tools.php'
        ));
        // Create the link.
        $settings_link = "<a href='$url'>" . __('Settings') . '</a>';
        // Adds the link to the end of the array.
        array_push(
            $links,
            $settings_link
        );
        return $links;
    }


    function rhd_site_url_validation($url)
    {
        if (!empty($url)) {
            if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
                add_settings_error('rhd_option_notice', 'invalid_rhd_site_url', 'Source URL is not valid.');
                $url = get_option('rhd_site_url'); // ignore the user's changes and use the old database value 
            } else {
                $remote_request = new WordPressRemoteJSON($url, array(), "get");
                $remote_request->run();
                if (!$remote_request->is_success()) {
                    add_settings_error('rhd_option_notice', 'invalid_rhd_site_url', 'Destination URL returing false status. Please check URL exists.');
                    $url = get_option('rhd_site_url'); // ignore the user's changes and use the old database value 
                }
            }
        }
        return $this->cleanRHDURL($url);
    }

    function rhd_destination_url_validation($url)
    {
        if (!empty($url)) {
            if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
                add_settings_error('rhd_option_notice', 'invalid_rhd_destination_url', 'Destination URL is not valid.');
                $url = get_option('rhd_destination_url'); // ignore the user's changes and use the old database value 
            }
            else if ($this->isDestinationURL($url)) {  
                add_settings_error('rhd_option_notice', 'invalid_rhd_destination_url', 'Destination URL can not same as current url.');
                $url = get_option('rhd_destination_url'); // ignore the user's changes and use the old database value 
            }
            else {
                $remote_request = new WordPressRemoteJSON($url, array(), "get");
                $remote_request->run();
                if (!$remote_request->is_success()) {
                    add_settings_error('rhd_option_notice', 'invalid_rhd_destination_url', 'Destination URL returing false status. Please check URL exists.');
                    $url = get_option('rhd_destination_url'); // ignore the user's changes and use the old database value 
                }
                else
                {
                    # API CALL for Check
                    $remote_request = new WordPressRemoteJSON($this->cleanRHDURL($url) . '/wp-json/rhd/v1/rhd_check_call_request/', array(), "get");
                    $remote_request->run(); 
                    if (!$remote_request->is_success()) {
                        add_settings_error('rhd_option_notice', 'invalid_rhd_destination_url', 'Looks like destination website is not enabled with RHD plugin or path is incorrect. Please configure it correctly.');
                    }
                } 
            }
        }       
        return $this->cleanRHDURL($url);
    }
}
