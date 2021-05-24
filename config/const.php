<?php
return [
    'DEFAULT_PER_PAGE'              => 10,
    'DEFAULT_FRIEND_PER_PAGE'       => 8,
    'DEFAULT_URL_LENGTH'            => 15,

    // response code
    'STATUS_CODE_SUCCESS'           => 200,
    'STATUS_CODE_CREATED'           => 201,
    'STATUS_CODE_ACCEPTED'          => 202,

    'STATUS_CODE_BAD_REQUEST'       => 400,
    'STATUS_CODE_UNAUTHORIZED'      => 401,
    'STATUS_CODE_FORBIDDEN'         => 403,
    'STATUS_CODE_NOT_FOUND'         => 404,
    'STATUS_CODE_UN_PROCESSABLE'    => 422,

    'STATUS_CODE_SERVER_ERROR'      => 500,

    'FRIEND_STATUS_PENDING'         => '0',   //don't know yet
    'FRIEND_STATUS_FRIEND'          => '1',   // is friend
    'FRIEND_STATUS_NONE'            => '2',   // You block
    'FRIEND_STATUS_BLOCK'           => '3',   // your are blocked
    'FRIEND_STATUS_YOU_SENT'        => '4',   // You sent request friend
    'FRIEND_STATUS_THEY_SENT'       => '5',   //they sent
];
