<?php

function jsonResponse($data = null, $message = 'Success', $statusCode = 200)
{
    return service('response')
        ->setStatusCode($statusCode)
        ->setContentType('application/json')
        ->setJSON([
            'success' => $statusCode >= 200 && $statusCode < 300,
            'message' => $message,
            'data' => $data,
        ]);
}

function errorResponse($message = 'Error', $statusCode = 400, $errors = null)
{
    $response = service('response')
        ->setStatusCode($statusCode)
        ->setContentType('application/json')
        ->setJSON([
            'success' => false,
            'message' => $message,
        ]);

    if ($errors) {
        $body = json_decode($response->getBody());
        $body->errors = $errors;
        $response->setBody(json_encode($body));
    }

    return $response;
}
