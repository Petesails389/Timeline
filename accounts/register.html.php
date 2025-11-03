<h2>Register:</h2>
<form action="" method="post">
    <div class="w3-panel">
        <label>Username:</label><br>
        <input type="text"  class="w3-border-theme-select" name="username" placeholder="Username" required>
    </div>
    <div class="w3-panel">
        <label>Password:</label><br>
        <input type="password"  class="w3-border-theme-select" name="password" placeholder="Password" required><br><br>
        <input type="password"  class=" w3-border-theme-select" name="confirmPassword" placeholder="Confirm Password" required>
    </div>
    <div class="w3-panel">
        <input type="submit" name="submit" class="w3-button w3-theme-d2 w3-hover-theme" value="Submit">
    </div>
    <p>Already have an account? <a href="login.php?redirect=<?php echo $endpoint; ?>">Login here</a>.</p>
</form>