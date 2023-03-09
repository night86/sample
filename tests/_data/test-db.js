db.getCollection("task").insert({
    _id: ObjectId("608bf2d65b5ffa4ab51ad963"),
    result_code: "EmailSenderTask",
    status: "new",
    class_name: "console\\models\\tasks\\EmailSenderTask",
    tries: 0,
    priority: 0,
    scheduled_at: NumberLong(1666200506),
    max_retries: 10,
    retry_after: 30,
    updated_at: NumberLong(1666200506),
    created_at: NumberLong(1666200506)
});

db.getCollection("oauth_clients").insert({
    _id: ObjectId("61a0a1bb52b23b64a3246188"),
    client_id: "testclient",
    client_secret: "testpass",
    redirect_uri: "http://fake",
    grant_types:
        "client_credentials authorization_code password implicit refresh_token",
});

db.getCollection("user").insert({
    _id: ObjectId("6183aee15cf084562b3dd450"),
    email: "asdf@asd.asd",
    status: 3,
    password_hash: "toBeUpdated"
});