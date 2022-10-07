<?php
/**
 * LianaAutomation Gravity Forms admin panel
 *
 * PHP Version 7.4
 *
 * @package  LianaAutomation
 * @license  https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL-3.0-or-later
 * @link     https://www.lianatech.com
 */

/**
 * LianaAutomation / Gravity Forms options panel class
 *
 * @package  LianaAutomation
 * @license  https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL-3.0-or-later
 * @link     https://www.lianatech.com
 */
class LianaAutomationGravityForms
{
    private $_lianaautomation_gravityforms_options;

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action(
            'admin_menu',
            array( $this, 'lianaAutomationGravityFormsAddPluginPage' )
        );

        add_action(
            'admin_init',
            array( $this, 'lianaAutomationGravityFormsPageInit' )
        );
    }

    /**
     * Add an admin page
     * 
     * @return null
     */
    public function lianaAutomationGravityFormsAddPluginPage()
    {
        global $admin_page_hooks;

        //error_log(print_r($admin_page_hooks, true));

        // Only create the top level menu if it doesn't exist (via another plugin)
        if (!isset($admin_page_hooks['lianaautomation'])) {
            add_menu_page(
                'LianaAutomation', // page_title
                'LianaAutomation', // menu_title
                'manage_options', // capability
                'lianaautomation', // menu_slug
                array( $this, 'lianaAutomationGravityFormsCreateAdminPage' ),
                'dashicons-admin-settings', // icon_url
                65 // position
            );
        }
        add_submenu_page(
            'lianaautomation',
            'Gravity Forms',
            'Gravity Forms',
            'manage_options',
            'lianaautomationgravityforms',
            array( $this, 'lianaAutomationGravityFormsCreateAdminPage' ),
        );

        // Remove the duplicate of the top level menu item from the sub menu
        // to make things pretty.
        remove_submenu_page('lianaautomation', 'lianaautomation');

    }


    /**
     * Construct an admin page
     *
     * @return null
     */
    public function lianaAutomationGravityFormsCreateAdminPage()
    {
        $this->lianaautomation_gravityforms_options
            = get_option('lianaautomation_gravityforms_options'); ?>
        <div class="wrap">
            <h2>LianaAutomation API Options for Gravity Forms Tracking</h2>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('lianaautomation_gravityforms_option_group');
                do_settings_sections('lianaautomation_gravityforms_admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Init a Gravity Forms admin page
     *
     * @return null
     */
    public function lianaAutomationGravityFormsPageInit() 
    {
        register_setting(
            'lianaautomation_gravityforms_option_group', // option_group
            'lianaautomation_gravityforms_options', // option_name
            array(
                $this,
                'lianaAutomationGravityFormsSanitize'
            ) // sanitize_callback
        );

        add_settings_section(
            'lianaautomation_gravityforms_section', // id
            '', // empty section title text
            array( $this, 'lianaAutomationGravityFormsSectionInfo' ), // callback
            'lianaautomation_gravityforms_admin' // page
        );

        add_settings_field(
            'lianaautomation_gravityforms_url', // id
            'Automation API URL', // title
            array( $this, 'lianaAutomationGravityFormsURLCallback' ), // callback
            'lianaautomation_gravityforms_admin', // page
            'lianaautomation_gravityforms_section' // section
        );

        add_settings_field(
            'lianaautomation_gravityforms_realm', // id
            'Automation Realm', // title
            array( $this, 'lianaAutomationGravityFormsRealmCallback' ), // callback
            'lianaautomation_gravityforms_admin', // page
            'lianaautomation_gravityforms_section' // section
        );

        add_settings_field(
            'lianaautomation_gravityforms_user', // id
            'Automation User', // title
            array( $this, 'lianaAutomationGravityFormsUserCallback' ), // callback
            'lianaautomation_gravityforms_admin', // page
            'lianaautomation_gravityforms_section' // section
        );

        add_settings_field(
            'lianaautomation_gravityforms_key', // id
            'Automation Secret Key', // title
            array( $this, 'lianaAutomationGravityFormsKeyCallback' ), // callback
            'lianaautomation_gravityforms_admin', // page
            'lianaautomation_gravityforms_section' // section
        );

        add_settings_field(
            'lianaautomation_gravityforms_channel', // id
            'Automation Channel ID', // title
            array( $this, 'lianaAutomationGravityFormsChannelCallback' ), // callback
            'lianaautomation_gravityforms_admin', // page
            'lianaautomation_gravityforms_section' // section
        );


        // Status check
        add_settings_field(
            'lianaautomation_gravityforms_status_check', // id
            'LianaAutomation Connection Check', // title
            array(
                $this,
                'lianaAutomationGravityFormsConnectionCheckCallback'
            ), // callback
            'lianaautomation_gravityforms_admin', // page
            'lianaautomation_gravityforms_section' // section
        );


    }

    /** 
     * Basic input sanitization function
     * 
     * @param string $input String to be sanitized.
     * 
     * @return null
     */
    public function lianaAutomationGravityFormsSanitize($input)
    {
        $sanitary_values = array();

        if (isset($input['lianaautomation_url'])) {
            $sanitary_values['lianaautomation_url']
                = sanitize_text_field($input['lianaautomation_url']);
        }
        if (isset($input['lianaautomation_realm'])) {
            $sanitary_values['lianaautomation_realm']
                = sanitize_text_field($input['lianaautomation_realm']);
        }
        if (isset($input['lianaautomation_user'])) {
            $sanitary_values['lianaautomation_user']
                = sanitize_text_field($input['lianaautomation_user']);
        }
        if (isset($input['lianaautomation_key'])) {
            $sanitary_values['lianaautomation_key']
                = sanitize_text_field($input['lianaautomation_key']);
        }
        if (isset($input['lianaautomation_channel'])) {
            $sanitary_values['lianaautomation_channel']
                = sanitize_text_field($input['lianaautomation_channel']);
        }
        return $sanitary_values;
    }

    /** 
     * Empty section info
     * 
     * @return null
     */
    public function lianaAutomationGravityFormsSectionInfo()
    {
        // Intentionally empty section here.
        // Could be used to generate info text.
    }

    /** 
     * Automation URL
     * 
     * @return null
     */
    public function lianaAutomationGravityFormsURLCallback()
    {
        printf(
            '<input class="regular-text" type="text" '
            .'name="lianaautomation_gravityforms_options[lianaautomation_url]" '
            .'id="lianaautomation_url" value="%s">',
            isset(
                $this->lianaautomation_gravityforms_options['lianaautomation_url']
            )
            ? esc_attr(
                $this->lianaautomation_gravityforms_options['lianaautomation_url']
            )
            : ''
        );
    }

    /** 
     * Automation Realm
     * 
     * @return null
     */
    public function lianaAutomationGravityFormsRealmCallback()
    {
        printf(
            '<input class="regular-text" type="text" '
            .'name="lianaautomation_gravityforms_options[lianaautomation_realm]" '
            .'id="lianaautomation_realm" value="%s">',
            isset(
                $this->lianaautomation_gravityforms_options['lianaautomation_realm']
            )
            ? esc_attr(
                $this->lianaautomation_gravityforms_options['lianaautomation_realm']
            )
            : ''
        );
    }
    /** 
     * Automation User
     * 
     * @return null
     */
    public function lianaAutomationGravityFormsUserCallback()
    {
        printf(
            '<input class="regular-text" type="text" '
            .'name="lianaautomation_gravityforms_options[lianaautomation_user]" '
            .'id="lianaautomation_user" value="%s">',
            isset(
                $this->lianaautomation_gravityforms_options['lianaautomation_user']
            )
            ? esc_attr(
                $this->lianaautomation_gravityforms_options['lianaautomation_user']
            )
            : ''
        );
    }

    /** 
     * Automation Key
     * 
     * @return null
     */
    public function lianaAutomationGravityFormsKeyCallback()
    {
        printf(
            '<input class="regular-text" type="text" '
            .'name="lianaautomation_gravityforms_options[lianaautomation_key]" '
            .'id="lianaautomation_key" value="%s">',
            isset(
                $this->lianaautomation_gravityforms_options['lianaautomation_key']
            )
            ? esc_attr(
                $this->lianaautomation_gravityforms_options['lianaautomation_key']
            )
            : ''
        );
    }

    /** 
     * Automation Channel
     * 
     * @return null
     */
    public function lianaAutomationGravityFormsChannelCallback()
    {
        printf(
            '<input class="regular-text" type="text" '
            .'name="lianaautomation_gravityforms_options[lianaautomation_channel]" '
            .'id="lianaautomation_channel" value="%s">',
            isset(
                $this->lianaautomation_gravityforms_options[
                    'lianaautomation_channel'
                ]
            )
            ? esc_attr(
                $this->lianaautomation_gravityforms_options[
                    'lianaautomation_channel'
                ]
            )
            : ''
        );  
    }

    /**
     * LianaAutomation Gravity Forms Status check
     *
     * @return null
     */
    public function lianaAutomationGravityFormsConnectionCheckCallback()
    {

        $return = '💥Fail';
        // phpcs:ignore Generic.Files.LineLength.TooLong
        if (empty($this->lianaautomation_gravityforms_options['lianaautomation_user'])) {
            echo $return;
            return null;
        }
        $user
            = $this->lianaautomation_gravityforms_options['lianaautomation_user'];

        // phpcs:ignore Generic.Files.LineLength.TooLong
        if (empty($this->lianaautomation_gravityforms_options['lianaautomation_key'])) {
            echo $return;
            return null;
        }
        $secret
            = $this->lianaautomation_gravityforms_options['lianaautomation_key'];

        // phpcs:ignore Generic.Files.LineLength.TooLong
        if (empty($this->lianaautomation_gravityforms_options['lianaautomation_realm'])) {
            echo $return;
            return null;
        }
        $realm
            = $this->lianaautomation_gravityforms_options['lianaautomation_realm'];
    
        // phpcs:ignore Generic.Files.LineLength.TooLong
        if (empty($this->lianaautomation_gravityforms_options['lianaautomation_url'])) {
            echo $return;
            return null;
        }
        $url
            = $this->lianaautomation_gravityforms_options['lianaautomation_url'];


        // phpcs:ignore Generic.Files.LineLength.TooLong
        if (empty($this->lianaautomation_gravityforms_options['lianaautomation_channel'])) {
            echo $return;
            return null;
        }
        $channel
            = $this->lianaautomation_gravityforms_options['lianaautomation_channel'];

        /**
        * General variables
        */
        $basePath    = 'rest';             // Base path of the api end points
        $contentType = 'application/json'; // Content will be send as json
        $method      = 'POST';             // Method is always POST

        // Import Data
        $path = 'v1/pingpong';
        $data = array(
            "ping" => "pong"
        );

        // Encode our body content data
        $data = json_encode($data);
        // Get the current datetime in ISO 8601
        $date = date('c');
        // md5 hash our body content
        $contentMd5 = md5($data);
        // Create our signature
        $signatureContent = implode(
            "\n",
            [
                $method,
                $contentMd5,
                $contentType,
                $date,
                $data,
                "/{$basePath}/{$path}"
            ],
        );
        $signature = hash_hmac('sha256', $signatureContent, $secret);
        // Create the authorization header value
        $auth = "{$realm} {$user}:" . $signature;

        // Create our full stream context with all required headers
        $ctx = stream_context_create(
            [
            'http' => [
                'method' => $method,
                'header' => implode(
                    "\r\n",
                    [
                    "Authorization: {$auth}",
                    "Date: {$date}",
                    "Content-md5: {$contentMd5}",
                    "Content-Type: {$contentType}"
                    ]
                ),
                'content' => $data
            ]
            ]
        );

        // Build full path, open a data stream, and decode the json response
        $fullPath = "{$url}/{$basePath}/{$path}";
        $fp = fopen($fullPath, 'rb', false, $ctx);

        if (!$fp) {
            // API failed to connect
            echo $return;
            return null;
        }

        $response = stream_get_contents($fp);
        $response = json_decode($response, true);

        if (!empty($response)) {
            // error_log(print_r($response, true));
            if (!empty($response['pong'])) {
                $return = '💚 OK';
            }
        }
        
        echo $return;
    }


}
if (is_admin()) {
    $lianaAutomationGravityForms = new LianaAutomationGravityForms();
}

