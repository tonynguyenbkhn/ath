<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BPG_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function add_plugin_page() {
        add_management_page(
            'Bulk Post Generator', 
            'Bulk Post Generator', 
            'manage_options', 
            'bulk-post-generator', 
            array( $this, 'create_admin_page' )
        );
    }

    public function enqueue_scripts( $hook ) {
        if ( $hook !== 'tools_page_bulk-post-generator' ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style( 'bpg-admin-css', BPG_PLUGIN_URL . 'assets/css/admin.css', array(), BPG_VERSION );
        wp_enqueue_script( 'bpg-admin-js', BPG_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), BPG_VERSION, true );

        wp_localize_script( 'bpg-admin-js', 'bpgData', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'bpg_generate_nonce' ),
        ) );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap bpg-wrap">
            <h1>Bulk Post Generator</h1>
            <div id="bpg-message"></div>
            
            <form id="bpg-form" method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="bpg_num_posts">Number of Posts</label></th>
                        <td><input type="number" name="bpg_num_posts" id="bpg_num_posts" value="10" min="1" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bpg_post_status">Post Status</label></th>
                        <td>
                            <select name="bpg_post_status" id="bpg_post_status">
                                <option value="publish">Publish</option>
                                <option value="draft">Draft</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bpg_category">Category</label></th>
                        <td>
                            <?php wp_dropdown_categories( array( 'name' => 'bpg_category', 'hide_empty' => 0 ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bpg_title_template">Post Title Template</label></th>
                        <td>
                            <input type="text" name="bpg_title_template" id="bpg_title_template" value="Sample Post {index}" class="regular-text" required>
                            <p class="description">Use <code>{index}</code> to insert the post number.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bpg_content_template">Content Template</label></th>
                        <td>
                            <textarea name="bpg_content_template" id="bpg_content_template" rows="10" class="large-text" required>&lt;p&gt;This is a sample paragraph.&lt;/p&gt;
{image}
&lt;p&gt;Here is some more generated content.&lt;/p&gt;
{image}
&lt;p&gt;Conclusion of {spin:this post|the article|the story}.&lt;/p&gt;</textarea>
                            <p class="description">Use <code>{image}</code> to randomly insert one of the selected images. Use <code>{spin:word1|word2}</code> to spin text.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Images</label></th>
                        <td>
                            <button type="button" class="button bpg-select-images">Select / Upload Images</button>
                            <input type="hidden" name="bpg_image_ids" id="bpg_image_ids" value="">
                            <div id="bpg-image-preview" class="bpg-image-preview"></div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bpg_image_option">Image Behavior</label></th>
                        <td>
                            <select name="bpg_image_option" id="bpg_image_option">
                                <option value="random">Randomize out of selected</option>
                                <option value="reuse">Cycle through selected</option>
                            </select>
                            <p class="description">First image inserted into post will also be set as Featured Image.</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" id="bpg-generate-btn">Generate Posts</button>
                </p>
            </form>

            <div id="bpg-progress-wrapper" style="display:none;">
                <p>Generating posts: <span id="bpg-progress-text">0/0</span></p>
                <div class="bpg-progress-bar">
                    <div class="bpg-progress-fill" id="bpg-progress-fill"></div>
                </div>
            </div>
        </div>
        <?php
    }
}
