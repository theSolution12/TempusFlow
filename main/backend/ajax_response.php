<?php
function sendAjaxResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

function handleError($message) {
    sendAjaxResponse(false, $message);
}

function handleSuccess($message, $data = null) {
    sendAjaxResponse(true, $message, $data);
}
?> 