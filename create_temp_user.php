<?php
require_once('../../config.php');
require_login();

require_capability('local/openlrs:view', context_system::instance());

header('Content-Type: application/json');

// Function to create temp user and get LRS URL
function create_temp_user($course_id) {
    // Get settings
    $externalpath = get_config('local_openlrs', 'externalpath');
    $secretkey = get_config('local_openlrs', 'secretkey');
    $consumerid = get_config('local_openlrs', 'consumerid');

    if (empty($externalpath) || empty($secretkey) || empty($consumerid)) {
        throw new moodle_exception('Missing required OpenLRS configuration');
    }

    $message = [
        'courseId' => $course_id,
        'consumerId' => $consumerid,
    ];

    if (empty($message['courseId']) || empty($message['consumerId'])) {
        throw new moodle_exception('Invalid course or consumer data');
    }

    $signature = hash_hmac('sha1', json_encode($message), $secretkey);

    $externalpathtempusercreate = $externalpath . 'lrs/create_temp_user';

    $ch = curl_init($externalpathtempusercreate );
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Signature: ' . $signature
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success'] && isset($data['user']) ) {
            return [
                'user' => $data['user'],
                'lrsUrl' => $externalpath
            ];
        }
    }

    throw new moodle_exception('Failed to create temp user: ' . $response);
}

if (isset($_POST['courseId'])) {
    $course_id = $_POST['courseId'];

    try {
        $result = create_temp_user($course_id);
        echo json_encode([
            'success' => true,
            'user' => $result['user'],
            'lrsUrl' => $result['lrsUrl']
        ]);
    } catch (moodle_exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No courseId provided'
    ]);
}