<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>

    <div class="maincontent">
        <div class="formRow">
            <h1>LOGIN</h1>
            <form id="loginForm">
                <label>Username: </label> <input type="text" placeholder="Enter username..." required id="loginUsername">
                <label>Password: </label> <input type="password" placeholder="Enter password..." required id="loginPassword">
                <input type="submit" value="Login" id="loginSubmit">
            </form>
        </div>

        <div class="formRow">
            <h1>REGISTER</h1>
            <form id="registerForm">
                <label>Username: </label> <input type="text" placeholder="Enter username..." required id="regUsername">
                <label>Password: </label> <input type="password" placeholder="Enter password..." required id="regPassword">
                <input type="submit" value="Register" id="registerSubmit">
            </form>
        </div>
    </div>
    
    <div id="error">
    </div>
    <script src="../js/login.js"></script>
</body>

</html>
