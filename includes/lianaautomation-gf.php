<?php
/**
 * LianaAutomation Gravity Forms handler
 *
 * PHP Version 7.4
 *
 * @category Components
 * @package  WordPress
 * @author   Liana Technologies <websites@lianatech.com>
 * @license  Liana License
 * @link     https://www.lianatech.com
 */

/**
 * Gravity Forms functionality. Sends the information to Automation API.
 *
 * See https://docs.gravityforms.com/gform_after_submission/ for tips.
 *
 * @param $entry Entry Object - The entry that was just created.
 * @param $form  Form Object  - The current form.
 *
 * @return null
 */
function Liana_Automation_gravityforms($entry, $form)
{
    // Gets liana_t tracking cookie if set
    if (isset($_COOKIE['liana_t'])) {
        $liana_t = $_COOKIE['liana_t'];
    } else {
        // We shall send the form even without tracking cookie data
        $liana_t = null;
    }


    // DEBUG: Print out the $entry for inspection
    //error_log("DEBUG: Print out the \$entry for inspection");
    //error_log(print_r($entry, true));

    //error_log("DEBUG: Print out the \$form fields for inspection");
    //error_log(print_r($form['fields'], true));

    // Extract the form data to Automation compatible array
    $gravityFormsArray = array();

    // Try to find an email address from the form data
    $email = null;

    // Try to find an email address from the form data
    $sms = null;


    // Example from https://docs.gravityforms.com/gform_after_submission/#h-4-access-the-entry-by-looping-through-the-form-fields
    // Ideas from https://www.php.net/manual/en/function.array-key-exists.php
    foreach ($form['fields'] as $field) {
        $inputs = $field->get_entry_inputs();
        if (is_array($inputs)) {
            foreach ($inputs as $input) {
                $value = rgar($entry, (string) $input['id']);
                //error_log(print_r($value, true));
                // do something with the value
                if (!empty($value)) {
                    if (empty($email)) {
                        if (preg_match("/email/i", $field->label) || $field->type == 'email') {
                            $email = $value;
                        }
                    }
                    if (empty($sms)) {
                        if (preg_match("/phone/i", $field->label)) {
                            $sms = $value;
                        }
                    }
                    if (!array_key_exists($field->label, $gravityFormsArray)) {
                        $gravityFormsArray[$field->label] = $value;
                    } else {
                        $gravityFormsArray[$field->label] .= ','.$value;
                    }
                }
            }
        } else {
            $value = rgar($entry, (string) $field->id);
            //error_log(print_r($value, true));
            // do something with the value
            if (!empty($value)) {
                // If we still don't have email, try to get it here
                if (empty($email)) {
                    if (preg_match("/email/i", $field->label) || $field->type == 'email') {
                        $email = $value;
                    }
                }
                if (empty($sms)) {
                    if (preg_match("/phone/i", $field->label)) {
                        $sms = $value;
                    }
                }
                // If nonempty multivalue, convert to commaseparated
                if (!array_key_exists($field->label, $gravityFormsArray)) {
                    $gravityFormsArray[$field->label] = $value;
                } else {
                    $gravityFormsArray[$field->label] .= ','.$value;
                }
            }
        }
    }

    // Add Gravity Forms 'magic' values for title and id
    $gravityFormsArray['formtitle'] = $form['title'];
    $gravityFormsArray['formid'] = $form['id'];

    //error_log(print_r($gravityFormsArray, true));

    if (empty($email)) {
        error_log("ERROR: No /email/i found on form data. Bailing out.");
        return false;
    }

    /**
    * Retrieve Liana Options values (Array of All Options)
    */
    $lianaautomation_gravityforms_options
        = get_option('lianaautomation_gravityforms_options');

    if (empty($lianaautomation_gravityforms_options)) {
        error_log("lianaautomation_gravityforms_options was empty");
        return false;
    }

    // The user id, integer
    if (empty($lianaautomation_gravityforms_options['lianaautomation_user'])) {
        error_log("lianaautomation_options lianaautomation_user was empty");
        return false;
    }
    $user   = $lianaautomation_gravityforms_options['lianaautomation_user'];

    // Hexadecimal secret string
    if (empty($lianaautomation_gravityforms_options['lianaautomation_key'])) {
        error_log(
            "lianaautomation_gravityforms_options lianaautomation_key was empty!"
        );
        return false;
    }
    $secret = $lianaautomation_gravityforms_options['lianaautomation_key'];

    // The base url for our API installation
    if (empty($lianaautomation_gravityforms_options['lianaautomation_url'])) {
        error_log(
            "lianaautomation_gravityforms_options lianaautomation_url was empty!"
        );
        return false;
    }
    $url    = $lianaautomation_gravityforms_options['lianaautomation_url'];

    // The realm of our API installation, all caps alphanumeric string
    if (empty($lianaautomation_gravityforms_options['lianaautomation_realm'])) {
        error_log(
            "lianaautomation_gravityforms_options lianaautomation_realm was empty!"
        );
        return false;
    }
    $realm  = $lianaautomation_gravityforms_options['lianaautomation_realm'];

    // The channel ID of our automation
    if (empty($lianaautomation_gravityforms_options['lianaautomation_channel'])) {
        error_log(
            "lianaautomation_gravityforms_options lianaautomation_channel was empty!"
        );
        return false;
    }
    $channel  = $lianaautomation_gravityforms_options['lianaautomation_channel'];

    /**
    * General variables
    */
    $basePath    = 'rest';             // Base path of the api end points
    $contentType = 'application/json'; // Content will be send as json
    $method      = 'POST';             // Method is always POST

    // Build the identity array
    $identity = array();
    if (!empty($email)) {
        $identity["email"] = $email;
    }
    if (!empty($liana_t)) {
        $identity["token"] = $liana_t;
    }
    if (!empty($sms)) {
        $identity["sms"] = $sms;
    }

    // Bail out if no identities found
    if (empty($identity)) {
        return false;
    }

    // Import Data
    $path = 'v1/import';

    $data = array(
        "channel" => $channel,
        "no_duplicates" => false,
        "data" => [
            [
                "identity" => $identity,
                "events" => [
                    [
                        "verb" => "formsend",
                        "items" => $gravityFormsArray,
                    ],
                ]
            ]
        ]
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
	// if LianaAutomation API settings is invalid or endpoint is not working properly, bail out
	if(!$fp) {
		return false;
	}
    $response = stream_get_contents($fp);
    $response = json_decode($response, true);

    //if (!empty($response)) {
    //    error_log(print_r($response, true));
    //}
}

add_action('gform_after_submission', 'Liana_Automation_gravityforms', 10, 2);
