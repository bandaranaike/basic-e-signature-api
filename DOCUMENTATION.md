------------------------------------------------------------------------------------------

#### User Registration

<details>
 <summary><code>POST</code> <code><b>/register</b></code> <code>(registers a new user)</code></summary>

##### Parameters

> | name              |  type     | data type | description                    |
> |-------------------|-----------|-----------|--------------------------------|
> | name              | required  | string    | Full name of the user          |
> | email             | required  | string    | Email address of the user      |
> | password          | required  | string    | Password for the account       |
> | password_confirmation | required  | string    | Password confirmation          |

##### Responses

> | http code | content-type              | response                                               |
> |-----------|---------------------------|--------------------------------------------------------|
> | `201`     | `application/json`        | `{"message":"User registered successfully"}`           |
> | `400`     | `application/json`        | `{"message":"Bad Request"}`                            |

##### Example cURL

> ```bash
> curl -X POST -H "Content-Type: application/json" -d '{"name":"John Doe","email":"john@example.com","password":"password","password_confirmation":"password"}' http://localhost:8000/api/register
> ```

</details>

------------------------------------------------------------------------------------------

#### User Login

<details>
 <summary><code>POST</code> <code><b>/login</b></code> <code>(logs in an existing user)</code></summary>

##### Parameters

> | name      |  type     | data type | description                    |
> |-----------|-----------|-----------|--------------------------------|
> | email     | required  | string    | Email address of the user      |
> | password  | required  | string    | Password for the account       |

##### Responses

> | http code | content-type              | response                                               |
> |-----------|---------------------------|--------------------------------------------------------|
> | `200`     | `application/json`        | `{"message":"Login successful", "token":"JWT_TOKEN"}`  |
> | `401`     | `application/json`        | `{"message":"Invalid credentials"}`                    |

##### Example cURL

> ```bash
> curl -X POST -H "Content-Type: application/json" -d '{"email":"john@example.com","password":"password"}' http://localhost:8000/api/login
> ```

</details>

------------------------------------------------------------------------------------------

#### User Information

<details>
 <summary><code>GET</code> <code><b>/user</b></code> <code>(retrieves the authenticated user's information)</code></summary>

##### Parameters

> | name      |  type     | data type | description                   |
> |-----------|-----------|-----------|-------------------------------|
> | None      | required  | None      | N/A                           |

##### Responses

> | http code | content-type              | response                    |
> |-----------|---------------------------|-----------------------------|
> | `200`     | `application/json`        | User object                 |
> | `401`     | `application/json`        | `{"message":"Unauthenticated"}` |

##### Example cURL

> ```bash
> curl -X GET -H "Authorization: Bearer {token}" http://localhost:8000/api/user
> ```

</details>

------------------------------------------------------------------------------------------

#### User Logout

<details>
 <summary><code>POST</code> <code><b>/logout</b></code> <code>(logs out the authenticated user)</code></summary>

##### Parameters

> | name      |  type     | data type | description                    |
> |-----------|-----------|-----------|--------------------------------|
> | None      | required  | None      | N/A                            |

##### Responses

> | http code | content-type              | response                                               |
> |-----------|---------------------------|--------------------------------------------------------|
> | `200`     | `application/json`        | `{"message":"Logout successful"}`                      |
> | `401`     | `application/json`        | `{"message":"Unauthenticated"}`                        |

##### Example cURL

> ```bash
> curl -X POST -H "Authorization: Bearer {token}" http://localhost:8000/api/logout
> ```

</details>

------------------------------------------------------------------------------------------



------------------------------------------------------------------------------------------

#### Upload Document

<details>
 <summary><code>POST</code> <code><b>/documents</b></code> <code>(uploads a document)</code></summary>

##### Parameters

> | name        |  type     | data type   | description                       |
> |-------------|-----------|-------------|-----------------------------------|
> | file        | required  | file        | The document file (PDF)           |

##### Responses

> | http code | content-type              | response                                           |
> |-----------|---------------------------|----------------------------------------------------|
> | `201`     | `application/json`        | `{"message":"Document uploaded successfully"}`     |
> | `400`     | `application/json`        | `{"message":"Bad Request"}`                        |
> | `401`     | `application/json`        | `{"message":"Unauthenticated"}`                    |

##### Example cURL

> ```bash
> curl -X POST -H "Authorization: Bearer {token}" -F "file=@path_to_pdf_file" http://localhost:8000/api/documents
> ```

</details>

------------------------------------------------------------------------------------------

#### Get User Documents

<details>
 <summary><code>GET</code> <code><b>/documents</b></code> <code>(retrieves documents uploaded by the authenticated user)</code></summary>

##### Parameters

> | name      |  type     | data type | description                   |
> |-----------|-----------|-----------|-------------------------------|
> | None      | required  | None      | N/A                           |

##### Responses

> | http code | content-type              | response                                           |
> |-----------|---------------------------|----------------------------------------------------|
> | `200`     | `application/json`        | List of user's documents                           |
> | `401`     | `application/json`        | `{"message":"Unauthenticated"}`                    |

##### Example cURL

> ```bash
> curl -X GET -H "Authorization: Bearer {token}" http://localhost:8000/api/documents
> ```

</details>

------------------------------------------------------------------------------------------

#### Create Signature Request

<details>
 <summary><code>POST</code> <code><b>/document-signatures/create-requests</b></code> <code>(creates a signature request for a document)</code></summary>

##### Parameters

> | name             |  type     | data type | description                       |
> |------------------|-----------|-----------|-----------------------------------|
> | document_id      | required  | integer   | The ID of the document            |
> | recipient_email  | required  | string    | Email of the recipient            |

##### Responses

> | http code | content-type              | response                                               |
> |-----------|---------------------------|--------------------------------------------------------|
> | `201`     | `application/json`        | `{"message":"Signature request created successfully"}` |
> | `400`     | `application/json`        | `{"message":"Bad Request"}`                            |
> | `401`     | `application/json`        | `{"message":"Unauthenticated"}`                        |

##### Example cURL

> ```bash
> curl -X POST -H "Authorization: Bearer {token}" -H "Content-Type: application/json" -d '{"document_id":1,"recipient_email":"example@example.com"}' http://localhost:8000/api/document-signatures/create-requests
> ```

</details>

------------------------------------------------------------------------------------------

#### Get Sign Requested Documents

<details>
 <summary><code>GET</code> <code><b>/documents/sign-requested</b></code> <code>(retrieves documents where signature is requested from the authenticated user)</code></summary>

##### Parameters

> | name      |  type     | data type | description                   |
> |-----------|-----------|-----------|-------------------------------|
> | None      | required  | None      | N/A                           |

##### Responses

> | http code | content-type              | response                                           |
> |-----------|---------------------------|----------------------------------------------------|
> | `200`     | `application/json`        | List of documents with pending signature requests  |
> | `401`     | `application/json`        | `{"message":"Unauthenticated"}`                    |

##### Example cURL

> ```bash
> curl -X GET -H "Authorization: Bearer {token}" http://localhost:8000/api/documents/sign-requested
> ```

</details>

------------------------------------------------------------------------------------------

#### Sign Document

<details>
 <summary><code>POST</code> <code><b>/documents/{documentId}/sign</b></code> <code>(signs a document)</code></summary>

##### Parameters

> | name        |  type     | data type | description                       |
> |-------------|-----------|-----------|-----------------------------------|
> | documentId  | required  | integer   | The ID of the document            |
> | signature   | required  | string    | The signature content             |

##### Responses

> | http code | content-type              | response                                           |
> |-----------|---------------------------|----------------------------------------------------|
> | `200`     | `application/json`        | `{"message":"Document signed successfully"}`       |
> | `400`     | `application/json`        | `{"message":"Bad Request"}`                        |
> | `401`     | `application/json`        | `{"message":"Unauthenticated"}`                    |
> | `404`     | `application/json`        | `{"message":"Document not found"}`                 |

##### Example cURL

> ```bash
> curl -X POST -H "Authorization: Bearer {token}" -H "Content-Type: application/json" -d '{"signature":"example_signature"}' http://localhost:8000/api/documents/{documentId}/sign
> ```

</details>

------------------------------------------------------------------------------------------

#### Verify Signature

<details>
 <summary><code>GET</code> <code><b>/documents/{documentId}/verify-signature</b></code> <code>(verifies the signature of a document)</code></summary>

##### Parameters

> | name        |  type     | data type | description                       |
> |-------------|-----------|-----------|-----------------------------------|
> | documentId  | required  | integer   | The ID of the document            |

##### Responses

> | http code | content-type              | response                                           |
> |-----------|---------------------------|----------------------------------------------------|
> | `200`     | `application/json`        | `{"message":"Signature is valid"}`                 |
> | `400`     | `application/json`        | `{"message":"Bad Request"}`                        |
> | `401`     | `application/json`        | `{"message":"Unauthenticated"}`                    |
> | `404`     | `application/json`        | `{"message":"Document not found"}`                 |

##### Example cURL

> ```bash
> curl -X GET -H "Authorization: Bearer {token}" http://localhost:8000/api/documents/{documentId}/verify-signature
> ```

</details>

------------------------------------------------------------------------------------------
