<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function create_db_table_301_redirect_checker() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lvl_301_redirect_checker';

	// if table exists return
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) {
		return;
	}

	$charset_collate = $wpdb->get_charset_collate();
	$sql             = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        url varchar(255) NOT NULL,
        live_url varchar(255) NOT NULL,
        live_url_status_code varchar(255) NOT NULL,
        dev_url varchar(255) NOT NULL,
        dev_url_status_code varchar(255) NOT NULL,
        suggestion varchar(255) NOT NULL,
        suggested_url_status_code varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

// table update
function add_columns_to_db_table_301_redirect_checker() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lvl_301_redirect_checker';

	$wpdb->query( "ALTER TABLE $table_name ADD COLUMN suggestion varchar(255) NOT NULL" );
	$wpdb->query( "ALTER TABLE $table_name ADD COLUMN suggested_url_status_code varchar(255) NOT NULL" );
}


add_action( 'wp_ajax_check_single_url_status_code', 'check_single_url_status_code' );
add_action( 'wp_ajax_nopriv_check_single_url_status_code', 'check_single_url_status_code' );

function check_single_url_status_code() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lvl_301_redirect_checker';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
		create_db_table_301_redirect_checker();
	}

	$id = $_POST['id'];
	if ( ! $id ) {
		echo json_encode( [ 'error' => 'id not set' ] );
		die();
	}

	$path            = $_POST['path'];
	$live_url       = $_POST['live_url'];
	$dev_url        = $_POST['dev_url'];
	$suggestion_path = $_POST['suggestion_path'];
	$suggestion_url = $_POST['suggestion_url'];
	$username       = $_POST['username'];
	$password       = $_POST['password'];
	$options        = json_decode( stripslashes( $_POST['options'] ), true );

	$refresh = $options['refresh'] ?? false;

	if ( ! $refresh ) {
		//get row from db
		$row = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $id" );
		if ( $row ) {
			echo json_encode( $row );
			die();
		}

		echo json_encode( [
			'id'                        => $id,
			'path'                       => $path,
			'live_url'                  => $live_url,
			'live_url_status_code'      => '...',
			'dev_url'                   => $dev_url,
			'dev_url_status_code'       => '...',
			'suggestion_url'            => $suggestion_url,
			'suggested_url_status_code' => '...',
			'status'                    => 'loading',
		] );
		die();
	}


//    $target = $options['target'] ?? 'single';

//    if ($refresh) {
//        // update statuses in db
//        $wpdb->update(
//            $table_name,
//            [
//                'live_url_status_code' => get_http_response_code($live_url),
//                'dev_url_status_code'  => get_http_response_code($dev_url),
//            ],
//            ['url' => $url]
//        );
//
////        $transient = get_transient('url_checker_transient') ?? [];
////        unset($transient[md5($url)]);
////        set_transient('url_checker_transient', $transient, 0);
//    }


//    $transient = get_transient('url_checker_transient') ?? [];
//    if ($transient[md5($url)] ?? false) {
//        echo json_encode($transient[md5($url)]);
//        die();
//    }

//    if (!$refresh) {
//        echo json_encode([
//            'url'                  => $url,
//            'live_url'             => $live_url,
//            'live_url_status_code' => '...',
//            'dev_url'              => $dev_url,
//            'dev_url_status_code'  => '...',
//            'status'               => 'loading',
//        ]);
//    }


	$live_url_status_code = get_http_response_code( $live_url );
	// use basic auth on dev urls and no cache and follow redirects
	$ch = curl_init( $dev_url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_USERPWD, $username . ":" . $password );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Cache-Control: no-cache' ) );
	curl_exec( $ch );
	$dev_url_status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );

	$suggestion_url_status_code = '--';
	if($suggestion_url) {
		$ch = curl_init( $suggestion_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_USERPWD, $username . ":" . $password );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Cache-Control: no-cache' ) );
		curl_exec( $ch );
		$suggestion_url_status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );
	} else {
		$suggestion_url_status_code = '--';
	}

	$response = [
		'ID'                   => $id,
		'path'                  => $path,
		'live_url'             => $live_url,
		'live_url_status_code' => $live_url_status_code,
		'dev_url'              => $dev_url,
		'dev_url_status_code'  => $dev_url_status_code,
		'suggestion_url'       => $suggestion_url,
		'suggested_url_status_code' => $suggestion_url_status_code,
		'status'               => 'fresh',
	];
//
//    $transient = get_transient('url_checker_transient') ?? [];
//    $transient[md5($url)] = $response;
//    $transient[md5($url)]['status'] = 'cached';
//    set_transient('url_checker_transient', $transient, 0);

	// update or insert row in db
	$row = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $id" );
	if ( $row ) {
		$status = $wpdb->update(
			$table_name,
			[
				'url'                  => $path,
				'live_url'             => $live_url,
				'live_url_status_code' => $live_url_status_code,
				'dev_url'              => $dev_url,
				'dev_url_status_code'  => $dev_url_status_code,
				'suggestion'           => $suggestion_path,
				'suggested_url_status_code' => $suggestion_url_status_code,
			],
			[ 'id' => $id ]
		);
	} else {
		$status = $wpdb->insert(
			$table_name,
			[
				'id'                   => $id,
				'url'                  => $path,
				'live_url'             => $live_url,
				'live_url_status_code' => $live_url_status_code,
				'dev_url'              => $dev_url,
				'dev_url_status_code'  => $dev_url_status_code,
				'suggestion'           => $suggestion_path,
				'suggested_url_status_code' => $suggestion_url_status_code,
			]
		);
	}



	$response['status'] = ( $status !== false ) ? 'fresh' : 'db error';

//	$wpdb_error = $wpdb->last_error;
//	if ( $wpdb_error ) {
//		$response['status'] = $wpdb_error;
//	}

	echo json_encode( $response );
	die();
}

function get_http_response_code( $url ): string {
	$headers = get_headers( $url );

	return substr( $headers[0], 9, 3 );
}


function lvl_301_redirect_suggestion() {

}

// add_action ajax
add_action( 'wp_ajax_get_formatted_data_response', 'get_formatted_data_response' );
add_action( 'wp_ajax_nopriv_get_formatted_data_response', 'get_formatted_data_response' );
function get_formatted_data_response() {
	$message = $_POST['message'] ?? '';
	$data = $_POST['data'] ?? '';
//	$data = json_decode($data, true);

	$instructions = 'Redirection can import and export data in the following JSON format. This is a JSON object that contains an array of groups and redirects.

The main required data in a redirect is:

    url – the source URL
    match_data – the flags used when matching the source URL
    action_code – the HTTP code used
    action_type – the type of action performed when redirecting (url, error, nothing, pass)
    action_data – the data used by the action_type. Typically contains the target URL
    match_type – the type of match performed (url, server, ip, referrer, useragent)
    enabled – whether the redirect is enabled or not

It also contains other information:

    id – database ID
    title – optional title for the redirect
    hits – number of times this redirect has been hit
    regex – deprecated regular expression option. Use match_data instead
    group_id – ID of the group this redirect belongs to
    position – position within the list of redirects
    last_access – date when the redirect was last hit

Note that importing from JSON may not use the same IDs.
EXAMPLE of OUTPUT:
```{
  "groups": [
    {
      "id": 1,
      "name": "Redirections",
      "redirects": 0,
      "module_id": 1,
      "enabled": true
    }
  ],
  "redirects": [
        {
            "id": 1,
            "url": "/source",
            "match_url": "/source",
            "match_data": {
                "source": {
                    "flag_query": "exact",
                    "flag_case": false,
                    "flag_trailing": false,
                    "flag_regex": false
                }
            },
            "action_code": 301,
            "action_type": "url",
            "action_data": {
                "url": "/target"
            },
            "match_type": "url",
            "title": "",
            "hits": 15,
            "regex": false,
            "group_id": 9,
            "position": 0,
            "last_access": "May 18, 2019",
            "enabled": true
        }
  ]
}```
';

//	$instructions = '"redirects": [
//        {
//            "id": 1,
//            "url": "/source",
//            "match_url": "/source",
//            "match_data": {
//                "source": {
//                    "flag_query": "exact",
//                    "flag_case": false,
//                    "flag_trailing": false,
//                    "flag_regex": false
//                }
//            },
//            "action_code": 301,
//            "action_type": "url",
//            "action_data": {
//                "url": "/target"
//            },
//            "match_type": "url",
//            "title": "",
//            "hits": 15,
//            "regex": false,
//            "group_id": 9,
//            "position": 0,
//            "last_access": "May 18, 2019",
//            "enabled": true
//        }
//  ]';


	$message = esc_js(_wp_specialchars(wp_strip_all_tags($message), ENT_QUOTES, 'UTF-8', true));
	$model = $args['model'] ?? 'gpt-3.5-turbo';
	$system_content = $args['system_content'] ?? 'You are a 3 data formatting experts. 
		Your reply will only be JSON formatted data.  
		Review solutions and output the best one. 
		Look for patterns in the URLs and use regular expressions to combine items where possible. 
		Instructions: ' . $instructions;
	$user_content = $args['user_content'] ?? $message . $data;


	$response = wp_remote_request(
		'http://api.ai.lvltools.com/v1/chat/completions',
		[
			'method'  => 'POST',
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode('dev:webmechanix'),
			],
			'timeout' => 60,
			'body'    =>
				json_encode(
					[
						'model'    => $model,
						'messages' => [
							[
								'role'    => 'system',
								'content' => $system_content,
							],
							[
								'role'    => 'user',
								'content' => $user_content,
							],
						],
					]
				),
		]);

	$body = wp_remote_retrieve_body($response);
	$body = json_decode($body);
	if ($body->choices[0]->message->content ?? false)
		$body = $body->choices[0]->message->content;
	//else $body = '<pre><span class="fs-3" aria-hidden="true">(╯°□°)╯︵ ┻━┻</span> ... unable to get response from AI</pre>';

	echo json_encode($body);
	die();
}