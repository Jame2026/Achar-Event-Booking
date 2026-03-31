# API Test Template

Use `{{BASE_URL}}` as your hosted backend URL.

Example:

```text
{{BASE_URL}} = https://your-domain.com
Register URL = {{BASE_URL}}/api/register
Login URL = {{BASE_URL}}/api/login
```

Default headers for these APIs:

```http
Content-Type: application/json
Accept: application/json
```

## Blank Template

Copy this section for any API test case.

```text
Test API ID:
API Name:
Method:
Preconditions:
URL:
Header:
Request Body:
Expected Result:
Test Step:
Actual Result:
Status: Pass / Fail / Blocked
```

---

## Register API Test Cases

### REG-001 Register user with email

Test API ID: `REG-001`  
API Name: `Register user with email`  
Method: `POST`  
Preconditions: Hosted backend is running. Test email does not already exist.  
URL: `{{BASE_URL}}/api/register`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "name": "Test User",
  "email": "testuser01@example.com",
  "password": "Password123",
  "password_confirmation": "Password123",
  "role": "user"
}
```

Expected Result: Status code `201 Created`. Response contains `message: "Registration successful."` and a `user` object with `id`, `name`, `email`, `phone`, `location`, `profile_image_url`, and `role`.  
Test Step:
1. Open Postman or another API tool.
2. Select `POST`.
3. Enter the URL.
4. Add the headers.
5. Paste the request body.
6. Click Send.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

### REG-002 Register user with phone

Test API ID: `REG-002`  
API Name: `Register user with phone`  
Method: `POST`  
Preconditions: Hosted backend is running. Test phone number does not already exist.  
URL: `{{BASE_URL}}/api/register`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "name": "Phone User",
  "phone": "+85512345678",
  "password": "Password123",
  "password_confirmation": "Password123",
  "role": "user"
}
```

Expected Result: Status code `201 Created`. Response contains success message and `user.role = "user"`. The `phone` value is saved.  
Test Step:
1. Open an API tool.
2. Select `POST`.
3. Enter the URL.
4. Add the headers.
5. Paste the request body.
6. Click Send.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

### REG-003 Register fails when password confirmation does not match

Test API ID: `REG-003`  
API Name: `Register with wrong password confirmation`  
Method: `POST`  
Preconditions: Hosted backend is running.  
URL: `{{BASE_URL}}/api/register`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "name": "Wrong Confirm",
  "email": "wrongconfirm@example.com",
  "password": "Password123",
  "password_confirmation": "Password999",
  "role": "user"
}
```

Expected Result: Status code `422 Unprocessable Entity`. Response returns a validation error for password confirmation.  
Test Step:
1. Open an API tool.
2. Select `POST`.
3. Enter the URL.
4. Add the headers.
5. Paste the invalid request body.
6. Click Send.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

### REG-004 Register fails when email already exists

Test API ID: `REG-004`  
API Name: `Register duplicate email`  
Method: `POST`  
Preconditions: Hosted backend is running. An account with the same email already exists.  
URL: `{{BASE_URL}}/api/register`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "name": "Duplicate Email",
  "email": "testuser01@example.com",
  "password": "Password123",
  "password_confirmation": "Password123",
  "role": "user"
}
```

Expected Result: Status code `422 Unprocessable Entity`. Response returns a validation error for duplicate email.  
Test Step:
1. Use an email that was already registered.
2. Send the request again.
3. Verify the validation error is returned.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

### REG-005 Register fails when role is invalid

Test API ID: `REG-005`  
API Name: `Register with invalid role`  
Method: `POST`  
Preconditions: Hosted backend is running.  
URL: `{{BASE_URL}}/api/register`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "name": "Bad Role",
  "email": "badrole@example.com",
  "password": "Password123",
  "password_confirmation": "Password123",
  "role": "admin"
}
```

Expected Result: Status code `422 Unprocessable Entity`. Response returns a validation error because allowed roles are only `user` and `vendor`.  
Test Step:
1. Open an API tool.
2. Use role `admin`.
3. Send the request.
4. Verify the validation error.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

---

## Login API Test Cases

### LOG-001 Login success with email

Test API ID: `LOG-001`  
API Name: `Login with email`  
Method: `POST`  
Preconditions: Hosted backend is running. Registered user exists with valid email and password.  
URL: `{{BASE_URL}}/api/login`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "login": "testuser01@example.com",
  "password": "Password123"
}
```

Expected Result: Status code `200 OK`. Response contains `message: "Login successful."` and a `user` object.  
Test Step:
1. Open an API tool.
2. Select `POST`.
3. Enter the login URL.
4. Add the headers.
5. Paste the request body.
6. Click Send.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

### LOG-002 Login success with phone

Test API ID: `LOG-002`  
API Name: `Login with phone`  
Method: `POST`  
Preconditions: Hosted backend is running. Registered user exists with valid phone and password.  
URL: `{{BASE_URL}}/api/login`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "login": "+85512345678",
  "password": "Password123"
}
```

Expected Result: Status code `200 OK`. Response contains a login success message and `user.phone`.  
Test Step:
1. Open an API tool.
2. Select `POST`.
3. Enter the login URL.
4. Add the headers.
5. Paste the phone login body.
6. Click Send.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

### LOG-003 Login fails with wrong password

Test API ID: `LOG-003`  
API Name: `Login with wrong password`  
Method: `POST`  
Preconditions: Hosted backend is running. Registered user exists.  
URL: `{{BASE_URL}}/api/login`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "login": "testuser01@example.com",
  "password": "WrongPassword123"
}
```

Expected Result: Status code `401 Unauthorized`. Response contains `message: "Invalid login credentials."`  
Test Step:
1. Open an API tool.
2. Enter a valid login identifier.
3. Enter the wrong password.
4. Send the request.
5. Verify the unauthorized response.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

### LOG-004 Login fails when login identifier is missing

Test API ID: `LOG-004`  
API Name: `Login without email or phone`  
Method: `POST`  
Preconditions: Hosted backend is running.  
URL: `{{BASE_URL}}/api/login`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "password": "Password123"
}
```

Expected Result: Status code `422 Unprocessable Entity`. Response contains `message: "Email or phone is required."`  
Test Step:
1. Open an API tool.
2. Do not send `login` or `email`.
3. Send the request.
4. Verify the validation message.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

### LOG-005 Login fails when user does not exist

Test API ID: `LOG-005`  
API Name: `Login with unregistered account`  
Method: `POST`  
Preconditions: Hosted backend is running. The email or phone is not registered in the system.  
URL: `{{BASE_URL}}/api/login`  
Header:

```http
Content-Type: application/json
Accept: application/json
```

Request Body:

```json
{
  "login": "notfound@example.com",
  "password": "Password123"
}
```

Expected Result: Status code `401 Unauthorized`. Response contains `message: "Invalid login credentials."`  
Test Step:
1. Open an API tool.
2. Enter an unregistered email or phone.
3. Enter a password.
4. Send the request.
5. Verify the unauthorized response.

Actual Result: ______________________________  
Status: `Pass / Fail / Blocked`

---

## Notes

- Register accepts either `email` or `phone`. One of them is required.
- Register allows only `role: "user"` or `role: "vendor"`.
- Login accepts `login` for either email or phone. It also supports `email`, but `login` is the better field to use in testing.
- You can copy this same template for other APIs such as forgot password, profile, bookings, vendor, and admin endpoints.
