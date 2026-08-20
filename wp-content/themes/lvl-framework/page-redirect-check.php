<?php
/**
 * Page Template Name: Redirect Check
 */

// get all json file in /test/redirect/
$json_files = glob( get_stylesheet_directory() . '/src/test/redirect/*.json' );
$json_files = array_map( function ( $file ) {
	return basename( $file );
}, $json_files );

$selected = $_GET['testFile'] ?? $json_files[0] ?? null;

$test_json = locate_template( 'src/test/redirect/' . $selected, false, false );
$test_json = file_exists( $test_json ) ? file_get_contents( $test_json ) : null;
$test_json = json_decode( $test_json, true );

$list_of_urls = $test_json['urls'] ?? [];

$list_of_urls = array_map( function ( $url ) {
	$url = trim( $url );
	$url = strtolower( $url );
	$url = strtok( $url, '?' );
	$url = strtok( $url, '#' );
//	$url = rtrim( $url, '/' );

	if ( strpos( $url, '/' ) !== 0 ) {
		$url = '/' . $url;
	}

	return $url;
}, $list_of_urls );

$list_of_urls = array_filter( $list_of_urls, function ( $url ) {
	return ! empty( $url );
} );
$list_of_urls = array_unique( $list_of_urls );

$live_url = $test_json['liveDomain'] ?? '';
$dev_url  = $test_json['devDomain'] ?? '';
$username = $test_json['basicAuth']['username'] ?? '';
$password = $test_json['basicAuth']['password'] ?? '';

get_header();

if ( empty( $list_of_urls ) || empty( $live_url ) || empty( $dev_url ) || empty( $username ) || empty( $password ) ) {
	echo '<div class="container-fluid"><div class="row"><div class="col"><div class="p-3 border shadow-sm bg-white">Error: Missing data</div></div></div></div>';
	get_footer();

	return;
}
?>
    <div class="p-2 bg-primary"></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1>Redirect Checker</h1>
            </div>
            <div class="col-auto">
                <div class="mb-4">
                    <form class="p-4 border border-primary rounded bg-primary-subtle d-inline-block">
                        <label class="d-inline-block me-2" for="testFile">Select Test File:</label>
                        <select id="testFile" name="tesFile" class="d-inline-block form-select w-auto">
							<?php
							foreach ( $json_files as $file ) {
								echo '<option value="' . $file . '"' . ( $file === $selected ? ' selected' : '' ) . '>' . $file . '</option>';
							}
							?>
                        </select>
                        <p class="small mt-3 mb-0">Upload JSON files to <em>/src/test/redirect</em></p>
                    </form>
                </div>
                <script>
                    document.getElementById('testFile').addEventListener('change', function (e) {
                        const file = e.target.value;
                        if (file) {
                            window.location.href = '<?php echo get_permalink(); ?>?testFile=' + file;
                        }
                    });
                </script>
            </div>
            <div class="col">
                <table id="settings" class="table table-sm small align-middle">
                    <tr id="live-url" data-url="<?php echo $live_url; ?>">
                        <th>Live URL</th>
                        <td><a href="<?php echo $live_url; ?>" target="_blank"><?php echo $live_url; ?></a></td>
                    </tr>
                    <tr id="dev-url" data-url="<?php echo $dev_url; ?>">
                        <th>Dev URL</th>
                        <td><a href="<?php echo $dev_url; ?>" target="_blank"><?php echo $dev_url; ?></a></td>
                    </tr>
                    <tr id="username" data-username="<?php echo $username; ?>">
                        <th>Username</th>
                        <td><?php echo $username; ?></td>
                    </tr>
                    <tr id="password" data-password="<?php echo $password; ?>">
                        <th>Password</th>
                        <td><?php echo $password; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="p-3 border shadow-sm bg-white">
                    <div class="py-4">
                        <button id="refresh-all" class="btn btn-primary">Refresh All</button>
                        <button id="refresh-missing" class="btn btn-primary-outline">Refresh Missing</button>
                        <button id="cancel-refresh" class="btn btn-warning">Cancel Refresh</button>
                        <button id="continue-refresh" class="btn btn-outline-success">Continue Refresh</button>"
                    </div>
                    <p>
                        <button id="export-redirects" class="btn btn-primary-outline" onclick="export_redirection_json()">Export Redirects</button>
                        <textarea id="export-redirects-output" class="form-control" style="display: none;"></textarea>
                    </p>
                    <p>Row Count: <?php echo count( $list_of_urls ); ?></p>
                    <style>
                        thead {
                            position: sticky;
                            top: 32px;
                            z-index: 1;
                            background-color: var(--bs-light, #f8f9fa);
                        }

                        thead::after {
                            content: "";
                            position: absolute;
                            width: 100%;
                            border-bottom: 1px solid var(--bs-primary, #000);
                            display: block;
                            box-shadow: 0px 5px 8px -5px rgba(0, 0, 0, .5);
                            height: 100%;
                            top: 0;
                        }

                        .icon-external {
                            color: var(--bs-primary, green);
                            filter: color(var(--bs-primary));
                            display: block;
                            margin: auto;
                            width: 1rem;
                            height: 1rem;
                            /*background-repeat: no-repeat;*/
                            /*background-position: center center;*/
                            /*background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-box-arrow-up-right' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5'/%3E%3Cpath fill-rule='evenodd' d='M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z'/%3E%3C/svg%3E");*/
                            /*background-size: contain;*/
                        }

                        #export-redirects-output {
                            font-size: 12px;
                            background: #efefef;
                            border: 1px solid;
                            font-family: monospace;
                            min-height: 33vh;
                            white-space: pre;
                            margin: 1rem 0;
                        }
                    </style>
                    <table class="table table-striped table-sm small table-hover position-relative align-middle" style="max-height: 50vh; min-height: 500px;" aria-live="polite">
                        <thead class="position-sticky">
                        <tr>
                            <th>URL</th>
                            <th>Live URL</th>
                            <th>Status</th>
                            <th>Dev URL</th>
                            <th>Status</th>
                            <th>New Path</th>
                            <th>New URL</th>
                            <th>Status</th>
                            <th>Freshness</th>
                            <th>Refresh</th>
                        </tr>
                        </thead>
                        <tbody id="results">
						<?php
						// loop through the list of urls and output a row for each
						//                        $transient = get_transient('url_checker_transient') ?? [];

						// get db rows
						global $wpdb;
						$table_name = $wpdb->prefix . 'lvl_301_redirect_checker';
						if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
							create_db_table_301_redirect_checker();
						}
						// if table doesn't have colum suggestion, add_columns_to_db_table_301_redirect_checker()
						$columns = $wpdb->get_results( "SHOW COLUMNS FROM $table_name" );
						$columns = array_map( function ( $column ) {
							return $column->Field;
						}, $columns );

						if ( ! in_array( 'suggestion', $columns ) ) {
							add_columns_to_db_table_301_redirect_checker();
						}
						//                        $rows = $wpdb->prepare("SELECT * FROM $table_name WHERE url IN ('" . implode("','", $list_of_urls) . "')");

						$status_color = [
							'000' => 'light',
							'200' => 'success',
							'301' => 'info',
							'302' => 'info',
							'404' => 'warning',
							'500' => 'danger',
						];

						foreach ( $list_of_urls as $url ) {
							$row    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE url = %s", $url ) );
							$row_id = $row->id ?? null;
							if ( ! $row ) {
								$row_id = $wpdb->insert( $table_name, [ 'url' => $url ] );
								if ( $row_id ) {
									$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $row_id ) );
								} else {
									echo '<tr><td colspan="7">Error: ' . $url . '</td></tr>';
									continue;
								}
							}

//                            var_dumped($row);

//                            $transient_url = $transient[md5($url)] ?? [];
							$url           = $row->url;
							$live_url_full = $live_url . $url;
							$live_code     = $row->live_url_status_code;
							$dev_url_full  = $dev_url . $url;
							$dev_code      = $row->dev_url_status_code;
							$live_color    = $status_color[ $row->live_url_status_code ] ?? 'light';
							$dev_color     = $status_color[ $row->dev_url_status_code ] ?? 'light';

							$suggestion          = $row->suggestion ?? '';
							$suggestion_code     = $row->suggested_url_status_code ?? '';
							$suggestion_color    = $status_color[ $row->suggested_url_status_code ] ?? 'light';
							$suggestion_url_full = $dev_url . $suggestion;


							echo '<tr data-id="' . $row_id . '">';
							echo '<td data-url="' . $url . '">' . $url . '</td>';

							echo '<td data-live-url="true"><a class="btn btn-outline-secondary btn-sm" href="' . $live_url_full . '" target="_blank">View</a></td>';
							echo '<td class="text-center live-status-code table-' . $live_color . '">' . ( $live_code ?: '--' ) . '</td>';

							echo '<td data-dev-url="true"><a class="btn btn-outline-secondary btn-sm" href="' . $dev_url_full . '" target="_blank">View</a></td>';
							echo '<td class="text-center dev-status-code table-' . $dev_color . '">' . ( $dev_code ?: '--' ) . '</td>';

							echo '<td><input name="suggestion" placeholder="... new path" type="text" class="form-control" value="' . $suggestion . '"></td>';
							echo '<td data-suggestion-url="true"><a class="btn btn-outline-secondary btn-sm" href="' . $suggestion_url_full . '" target="_blank">View</a></td>';
							echo '<td class="text-center suggestion-status-code table-' . $suggestion_color . '">' . ( $suggestion_code ?: '--' ) . '</td>';


							echo '<td class="text-center freshness" data-freshness="' . ( $dev_code ? 'Stored' : 'Pending' ) . '">' . ( $dev_code ? 'Stored' : 'Pending' ) . '</td>';
							echo '<td><button class="btn btn-sm btn-primary-outline refresh" data-url="' . $url . '">Refresh</button></td>';
							echo '</tr>';
						}
						?>
                        </tbody>
                    </table>

                    <script>
                        function initStatusCheckTable() {
                            let interval;
                            let interval_delay = 250;

                            //let urls = <?php //echo json_encode($list_of_urls); ?>//;
                            const refreshAllButton = document.getElementById('refresh-all');
                            refreshAllButton.addEventListener('click', function (e) {
                                const refreshButtons = document.querySelectorAll('.refresh');
                                //throttle
                                let i = 0;
                                interval = setInterval(() => {
                                    if (i >= refreshButtons.length) {
                                        clearInterval(interval);
                                        return;
                                    }

                                    checkSingleProcess(refreshButtons[i]);
                                    i++;

                                }, interval_delay);
                            });

                            const refreshMissingButton = document.getElementById('refresh-missing');
                            refreshMissingButton.addEventListener('click', function (e) {
                                const refreshButtons = document.querySelectorAll('[data-freshness="Pending"] + td > .btn');
                                //throttle
                                let i = 0;
                                interval = setInterval(() => {
                                    if (i >= refreshButtons.length) {
                                        clearInterval(interval);
                                        return;
                                    }

                                    checkSingleProcess(refreshButtons[i]);
                                    i++;
                                }, interval_delay);
                            });

                            const cancelRefreshButton = document.getElementById('cancel-refresh');
                            cancelRefreshButton.addEventListener('click', function (e) {
                                clearInterval(interval);
                                interval = null;
                            });

                            const continueRefreshButton = document.getElementById('continue-refresh');
                            continueRefreshButton.addEventListener('click', function (e) {
                                const refreshButtons = document.querySelectorAll('.refresh:not(.--refreshed)');
                                //throttle
                                let i = 0;
                                interval = setInterval(() => {
                                    if (i >= refreshButtons.length) {
                                        clearInterval(interval);
                                        return;
                                    }

                                    checkSingleProcess(refreshButtons[i]);
                                    i++;
                                }, interval_delay);
                            });

                            let results = document.getElementById('results');

                            // event listener for refresh buttons
                            results.addEventListener('click', function (e) {

                                if (e.target.classList.contains('refresh')) {
                                    checkSingleProcess(e.target);
                                }
                            });

                        }

                        function checkSingleProcess(el) {
                            let id = el.closest('tr').getAttribute('data-id');
                            let path = el.getAttribute('data-url');

                            let live_url = document.getElementById('live-url').getAttribute('data-url') + path;
                            let dev_base_url = document.getElementById('dev-url').getAttribute('data-url');
                            let dev_url = dev_base_url + path;
                            let suggestion_path = el.closest('tr').querySelector('input[name="suggestion"]').value;
                            let suggestion_url = dev_base_url + suggestion_path;
                            let username = document.getElementById('username').getAttribute('data-username');
                            let password = document.getElementById('password').getAttribute('data-password');

                            const statusCodeLiveElement = el.closest('tr').querySelector('.live-status-code');
                            const statusCodeDevElement = el.closest('tr').querySelector('.dev-status-code');
                            const statusCodeSuggestionElement = el.closest('tr').querySelector('.suggestion-status-code');
                            const freshnessElement = el.closest('tr').querySelector('.freshness');

                            statusCodeLiveElement.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';
                            statusCodeDevElement.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';
                            statusCodeSuggestionElement.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';
                            freshnessElement.innerText = 'Refreshing...';

                            const statusCheck = new Promise((resolve, reject) => {
                                resolve(checkSingleStatusCode(id, path, live_url, dev_url, suggestion_path, suggestion_url, username, password, {refresh: true}));
                            });

                            statusCheck.then((response) => {
                                statusCodeLiveElement.innerText = response?.live_url_status_code;
                                statusCodeDevElement.innerText = response?.dev_url_status_code;
                                statusCodeSuggestionElement.innerText = response?.suggested_url_status_code;
                                freshnessElement.innerText = response?.status === 'fresh' ? 'Fresh' : response?.status;

                                if (response?.status === 'fresh') {
                                    freshnessElement.classList.add('text-success');
                                    freshnessElement.dataset.freshness = 'Stored';
                                } else {
                                    freshnessElement.classList.remove('text-success');
                                    freshnessElement.dataset.freshness = 'Pending';
                                }

                                for (let statusColorsKey in statusColors) {
                                    statusCodeLiveElement.classList.remove('table-' + statusColors[statusColorsKey]);
                                    statusCodeDevElement.classList.remove('table-' + statusColors[statusColorsKey]);
                                    statusCodeSuggestionElement.classList.remove('table-' + statusColors[statusColorsKey]);
                                }

                                const status = response?.live_url_status_code;
                                const statusClass = statusColors[status] || 'secondary';
                                statusCodeLiveElement.classList.add('table-' + statusClass);

                                const devStatus = response?.dev_url_status_code;
                                const devStatusClass = statusColors[devStatus] || 'secondary';
                                statusCodeDevElement.classList.add('table-' + devStatusClass);

                                const suggestionStatus = response?.suggested_url_status_code;
                                const suggestionStatusClass = statusColors[suggestionStatus] || 'secondary';
                                statusCodeSuggestionElement.classList.add('table-' + suggestionStatusClass);

                                el.classList.add('--refreshed');
                            });
                        }

                        function checkSingleStatusCode(id, path, live_url, dev_url, suggestion_path, suggestion_url, username = '', password = '', options = {refresh: false}) {
                            let data = new FormData();
                            data.append('action', 'check_single_url_status_code');
                            data.append('id', id);
                            data.append('path', path);
                            data.append('live_url', live_url);
                            data.append('dev_url', dev_url);
                            data.append('suggestion_path', suggestion_path);
                            data.append('suggestion_url', suggestion_url);
                            data.append('username', username);
                            data.append('password', password);
                            data.append('options', JSON.stringify(options));

                            console.log('checking status for ' + path);

                            return fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                                method: 'POST',
                                body: data
                            })
                                .then(response => response.json())
                                .then(data => {
                                    return data;
                                })
                                .catch((error) => {
                                    console.error('Error:', error);
                                });
                        }

                        function storeSuggestion(id, suggestion) {
                            let data = new FormData();
                            data.append('action', 'store_suggestion');
                            data.append('id', id);
                            data.append('suggestion', JSON.stringify(suggestion));

                            return fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                                method: 'POST',
                                body: data
                            })
                                .then(response => response.json())
                                .then(data => {
                                    return data;
                                })
                                .catch((error) => {
                                    console.error('Error:', error);
                                });
                        }

                        const statusColors = {
                            '000': 'light',
                            '200': 'success',
                            '301': 'info',
                            '302': 'info',
                            '404': 'warning',
                            '500': 'danger',
                        };

                        initStatusCheckTable();


                        function get_formatted_data_response(redirects) {
                            let data = new FormData();
                            data.append('action', 'get_formatted_data_response');
                            data.append('message', 'Given the following list of redirect pairs, return the formatted data response. First look for items that can be combined into a single redirect using a regular expression. Then, look for items that can be combined into a single group. Finally, return the formatted data response.');
                            data.append('data', JSON.stringify(redirects))

                            // console.log(data);return;

                            return fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                                method: 'POST',
                                body: data
                            })
                                .then(response => response.json())
                                .then(data => {
                                    return data;
                                })
                                .catch((error) => {
                                    console.error('Error:', error);
                                });
                        }

                        function export_redirection_json() {
                            let dev_base_url = document.getElementById('dev-url').getAttribute('data-url');

                            // build redirection import file base on 'input[name="suggestion"]' values
                            let redirects = [];

                            let rows = document.querySelectorAll('input[name="suggestion"]');
                            rows.forEach((row, index) => {
                                let suggestion = row.value;
                                if (suggestion) {
                                    let redirect = {
                                        url: row.closest('tr').querySelector('td').innerText,
                                        newUrl: suggestion
                                    };

                                    // let redirect = {
                                    //     id: index + 1,
                                    //     url: row.closest('tr').querySelector('td').innerText,
                                    //     match_url: 'regex',
                                    //     match_data: {
                                    //         source: {
                                    //             flag_query: 'exact',
                                    //             flag_case: true,
                                    //             flag_trailing: true,
                                    //             flag_regex: true
                                    //         }
                                    //     },
                                    //     action_code: 301,
                                    //     action_type: 'url',
                                    //     action_data: {
                                    //         url: dev_base_url + suggestion
                                    //     },
                                    //     match_type: 'url',
                                    //     title: '',
                                    //     hits: 0,
                                    //     regex: true,
                                    //     group_id: group_id,
                                    //     position: index,
                                    //     last_access: date,
                                    //     enabled: true
                                    // };
                                    redirects.push(redirect);
                                }
                            });

                            const exportRedirectsOutput = document.getElementById('export-redirects-output');

                            // console.log(get_formatted_data_response(redirects));

                            const formatted_data_response = new Promise((resolve, reject) => {
                                resolve(get_formatted_data_response(redirects));
                            });


                            formatted_data_response.then((response) => {
                                console.log(response);
                                exportRedirectsOutput.value = response;
                                exportRedirectsOutput.style.display = 'block';
                            });

                            return;

                            // let redirection_import_file = {
                            //     plugin: {
                            //         version: version,
                            //         date: date
                            //     },
                            //     groups: [
                            //         {
                            //             id: group_id,
                            //             name: 'Redirections',
                            //             redirects: redirects.length,
                            //             module_id: module_id,
                            //             moduleName: moduleName,
                            //             enabled: enabled
                            //         }
                            //     ],
                            //     redirects: redirects
                            // };

                            // let blob = new Blob([JSON.stringify(redirection_import_file)], {type: 'application/json'});
                            // let url = URL.createObjectURL(blob);
                            // let a = document.createElement('a');
                            // a.href = url;
                            // a.download = 'redirection-import.json';
                            // a.click();
                            //
                            // // clean up
                            // URL.revokeObjectURL(url);
                            //
                            // return redirection_import_file;
                        }

                    </script>
                </div>
            </div>
        </div>
    </div>
<?php
get_footer();