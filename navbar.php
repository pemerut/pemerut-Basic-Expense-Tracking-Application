<nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <span class="navbar-brand">App</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-secondary" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-secondary" href="user_data.php">User's Data</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-secondary" href="login.php">Sign out</a>
                    </li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) : ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-secondary" href="admin_page.php">Admin Page</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
