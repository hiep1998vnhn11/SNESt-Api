# Laravel Rest API server for SNESt Social Network!

## Usage

Make sure that your device have make command:

```
make init-app
```

## Routing

```
make route
```

### {BaseURL}/api/auth

    login
    register
    logout
    refresh
    me

### {BaseURL}/api/v1

#### /user

    handle_friend
        $user_url
        $relationship
    send_message
        $content
        $user_url

    Group /post
        create
        {post}/delete
        {post}/update
        Get:: {post}/get
        Get:: /store
        {post}/handle_like
        {post}/create_comment
            $content

        Group /comment
            {comment}/create_sub_comment
            {comment}/delete
            {comment}/update

            Group /sub_comment
                {sub_comment}/update
                {sub_comment}/delete

    Group /room
        {room}/delete

        Group Message
            {message}/delete
            {message}/remove

#### /admin

    https://hocwebchuan.com/tutorial/css3/display-flex.php
