<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_set_cookie_params(15*60, "/", "waph-team27.minifacebook.com", TRUE, TRUE);
session_start();

if(isset($_POST["username"]) and isset($_POST["password"])) {
    if (checklogin_mysql($_POST["username"], $_POST["password"]) || checklogin_email($_POST["username"], $_POST["password"]) || checklogin_superusers($_POST["username"], $_POST["password"])) {
        $_SESSION["authenticated"] = TRUE;
        $_SESSION["username"] = $_POST["username"];
        $_SESSION["browser"] = $_SERVER["HTTP_USER_AGENT"];
        $_SESSION["is_superuser"] = checklogin_superusers($_POST["username"], $_POST["password"]);
    } else {
        session_destroy();
        echo "<script>alert('Invalid username/password');window.location='form.php';</script>";
        die();
    }
}

if(!$_SESSION["authenticated"] or $_SESSION["authenticated"] != TRUE){
    session_destroy();
    echo "<script>alert('You have not Login. Please login first ');</script>";
    header("Refresh:0; url=form.php");
    die();
}

if($_SESSION["browser"] != $_SERVER["HTTP_USER_AGENT"]){
    session_destroy();
    echo "<script>alert('Session Hijacking attack is detected!');</script>";
    header("Refresh:0; url=form.php");
    die();
}

function checklogin_mysql($username, $password) {
    $mysqli = new mysqli('localhost','waphteam27','waphteam27@UC','waph_team');
    if($mysqli->connect_errno){
        printf("Database connection failed: %s\n", $mysqli->connect_error);
        exit();
    }

    $sql = "SELECT * FROM users WHERE username=?  AND password = md5(?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 1)
        return TRUE;
    return FALSE;
}

function checklogin_email($email, $password) {
    $mysqli = new mysqli('localhost','waphteam27','waphteam27@UC','waph_team');
    if($mysqli->connect_errno){
        printf("Database connection failed: %s\n", $mysqli->connect_error);
        exit();
    }

    $sql = "SELECT * FROM users WHERE email=?  AND password = md5(?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 1)
        return TRUE;
    return FALSE;
}

function checklogin_superusers($username, $password) {
    $mysqli = new mysqli('localhost','waphteam27','waphteam27@UC','waph_team');
    if($mysqli->connect_errno){
        printf("Database connection failed: %s\n", $mysqli->connect_error);
        exit();
    }

    $sql = "SELECT * FROM superusers WHERE username=? AND password = md5(?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 1)
        return TRUE;
    return FALSE;
}

function getUserProfile($username) {
    $mysqli = new mysqli('localhost','waphteam27','waphteam27@UC','waph_team');
    if($mysqli->connect_errno){
        printf("Database connection failed: %s\n", $mysqli->connect_error);
        exit();
    }

    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $userProfile = $result->fetch_assoc();
    return $userProfile;
}

$userProfile = getUserProfile($_SESSION['username']);

if ($_SESSION["is_superuser"]) {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["disable_user"])) {
        $username = $_POST["disable_user"];
        if (disableUser($username)) {
            echo "<script>alert('User disabled successfully!');</script>";
        } else {
            echo "<script>alert('Failed to disable user.');</script>";
        }
    } elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["enable_user"])) {
        $username = $_POST["enable_user"];
        if (enableUser($username)) {
            echo "<script>alert('User enabled successfully!');</script>";
        } else {
            echo "<script>alert('Failed to enable user.');</script>";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["post_title"]) && isset($_POST["post_content"])) {
    $post_title = $_POST["post_title"];
    $post_content = $_POST["post_content"];

    if (!empty($post_title) && !empty($post_content)) {
        if (addNewPost($_SESSION['username'], $post_title, $post_content)) {
            echo "<script>alert('Post created successfully!');</script>";
        } else {
            echo "<script>alert('Failed to create post.');</script>";
        }
    } else {
        echo "<script>alert('Please provide both post title and content.');</script>";
    }
}

function addNewPost($username, $title, $content) {
    $mysqli = new mysqli('localhost','waphteam27','waphteam27@UC','waph_team');
    if($mysqli->connect_errno){
        printf("Database connection failed: %s\n", $mysqli->connect_error);
        exit();
    }

    $sql = "INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sss", $username, $title, $content);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function disableUser($username) {
    $mysqli = new mysqli('localhost','waphteam27','waphteam27@UC','waph_team');
    if($mysqli->connect_errno){
        printf("Database connection failed: %s\n", $mysqli->connect_error);
        exit();
    }
    
    $sql = "UPDATE users SET status = 0 WHERE username=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function enableUser($username) {
    $mysqli = new mysqli('localhost','waphteam27','waphteam27@UC','waph_team');
    if($mysqli->connect_errno){
        printf("Database connection failed: %s\n", $mysqli->connect_error);
        exit();
    }
    
    $sql = "UPDATE users SET status = 1 WHERE username=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function getAllUsers() {
    $mysqli = new mysqli('localhost','waphteam27','waphteam27@UC','waph_team');
    if($mysqli->connect_errno){
        printf("Database connection failed: %s\n", $mysqli->connect_error);
        exit();
    }

    $sql = "SELECT * FROM users";
    $result = $mysqli->query($sql);
    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>User Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 50px auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    h2, p {
      text-align: center;
    }
    .button {
      display: block;
      width: 100px;
      margin: 0 auto;
      padding: 10px;
      background-color: #4CAF50;
      color: white;
      text-align: center;
      text-decoration: none;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .button:hover {
      background-color: #45a049;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #f2f2f2;
    }
    .superuser {
      background-color: #f44336;
      color: white;
      padding: 10px;
      text-align: center;
      margin-bottom: 10px;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <div class="container">
    <?php if ($_SESSION["is_superuser"]) { ?>
      <div class="superuser">You are a superuser!</div>
      <h3>All Users</h3>
      <table>
        <tr>
          <th>Username</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone Number</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php
          $users = getAllUsers();
          foreach ($users as $user) {
              echo "<tr>";
              echo "<td>" . htmlentities($user['username']) . "</td>";
              echo "<td>" . htmlentities($user['name']) . "</td>";
              echo "<td>" . htmlentities($user['email']) . "</td>";
              echo "<td>" . htmlentities($user['mobile_number']) . "</td>";
              echo "<td>" . ($user['status'] ? 'Enabled' : 'Disabled') . "</td>";
              if ($user['status']) {
                  echo "<td>";
                  echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                  echo "<input type='hidden' name='disable_user' value='" . $user['username'] . "'>";
                  echo "<button type='submit'>Disable</button>";
                  echo "</form>";
                  echo "</td>";
              } else {
                  echo "<td>";
                  echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                  echo "<input type='hidden' name='enable_user' value='" . $user['username'] . "'>";
                  echo "<button type='submit'>Enable</button>";
                  echo "</form>";
                  echo "</td>";
              }
              echo "</tr>";
          }
        ?>
      </table>
    <?php } else { ?>
      <?php if (!$userProfile['status']) {
        session_destroy();
        echo "<script>alert('Your account has been disabled. Please contact a superuser to enable your account.');</script>";
        header("Refresh:0; url=form.php");
        die();
      } ?>
      <h2>Welcome <?php echo htmlentities($userProfile['username']); ?>!</h2>
      <table>
        <tr>
          <th>Username</th>
          <td><?php echo htmlentities($userProfile['username']); ?></td>
        </tr>
        <tr>
          <th>Name</th>
          <td><?php echo htmlentities($userProfile['name']); ?></td>
        </tr>
        <tr>
          <th>Email</th>
          <td><?php echo htmlentities($userProfile['email']); ?></td>
        </tr>
        <tr>
          <th>Phone Number</th>
          <td><?php echo htmlentities($userProfile['mobile_number']); ?></td>
        </tr>
      </table>
      <a href="homepage.php" class="button">Go to all Posts and Comments</a>
      <a href="updatedetails.php" class="button">Update Details</a>
      <a href="changepassword.php" class="button">Change Password</a>
      <a href="logout.php" class="button">Logout</a>

      <!-- Add new section for creating a post -->
      <h3>Create a Post</h3>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="post_title">Post Title:</label><br>
        <input type="text" id="post_title" name="post_title" required><br>
        <label for="post_content">Post Content:</label><br>
        <textarea id="post_content" name="post_content" rows="4" required></textarea><br>
        <button type="submit">Create Post</button>
      </form>
    <?php } ?>
  </div>
</body>
</html>

