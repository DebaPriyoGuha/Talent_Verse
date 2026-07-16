<?php
    include 'session-file.php';
    include 'database/classes/User.php';
    include 'database/classes/Post.php';
    include 'database/classes/Message.php';

    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }
    $userLoggedIn = $_SESSION['username'];

    // Handle new post submission
    if (isset($_POST['post'])) {
        $uploadOk  = 1;
        $imageName = '';
        if (!empty($_FILES['fileToUpload']['name'])) {
            $targetDir = "assets/images/posts/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $imageName   = $targetDir . uniqid() . '_' . basename($_FILES['fileToUpload']['name']);
            if (!move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
                $uploadOk = 0;
            }
        }
        if ($uploadOk) {
            $tag  = isset($_POST['post_tag']) ? $_POST['post_tag'] : 'other';
            $post = new Post($con, $userLoggedIn);
            $post->submitPost($_POST['post_text'], $imageName, $tag);
        }
    }

    $user_q   = mysqli_query($con, "SELECT * FROM users WHERE username='" . mysqli_real_escape_string($con, $userLoggedIn) . "'");
    $user_arr = mysqli_fetch_array($user_q);
    $num_friends = max(0, (substr_count($user_arr['friend_array'] ?? '', ',')) - 1);

    // Active filter tag
    $active_tag = isset($_GET['tag']) ? $_GET['tag'] : 'all';

    // Talent categories config
    $categories = [
        'all'         => ['label' => 'All',         'icon' => 'fas fa-th',           'class' => ''],
        'music'       => ['label' => 'Music',        'icon' => 'fas fa-music',         'class' => 'tag-music'],
        'dance'       => ['label' => 'Dance',        'icon' => 'fas fa-person-dancing','class' => 'tag-dance'],
        'art'         => ['label' => 'Art & Drawing','icon' => 'fas fa-palette',       'class' => 'tag-art'],
        'writing'     => ['label' => 'Writing',      'icon' => 'fas fa-pen-nib',       'class' => 'tag-writing'],
        'photography' => ['label' => 'Photography',  'icon' => 'fas fa-camera',        'class' => 'tag-photography'],
        'video'       => ['label' => 'Video',        'icon' => 'fas fa-video',         'class' => 'tag-video'],
        'acting'      => ['label' => 'Acting',       'icon' => 'fas fa-masks-theater', 'class' => 'tag-acting'],
        'cooking'     => ['label' => 'Cooking',      'icon' => 'fas fa-utensils',      'class' => 'tag-cooking'],
        'fashion'     => ['label' => 'Fashion',      'icon' => 'fas fa-shirt',         'class' => 'tag-fashion'],
        'sports'      => ['label' => 'Sports',       'icon' => 'fas fa-trophy',        'class' => 'tag-sports'],
        'gaming'      => ['label' => 'Gaming',       'icon' => 'fas fa-gamepad',       'class' => 'tag-gaming'],
        'spoken'      => ['label' => 'Spoken Word',  'icon' => 'fas fa-microphone',    'class' => 'tag-spoken'],
        'other'       => ['label' => 'Other',        'icon' => 'fas fa-star',          'class' => 'tag-other'],
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentVerse — Share Your Talent</title>
    <link rel="stylesheet" href="style for knackbook.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- TOPBAR -->
<nav class="topbar">
    <a class="topbar-logo" href="index.php">✦ TalentVerse</a>
    <div class="topbar-search">
        <i class="fas fa-search si"></i>
        <input type="text" placeholder="Search talents, people...">
    </div>
    <div class="topbar-nav">
        <a href="index.php" class="active"><i class="fas fa-home"></i></a>
        <a href="messages.php"><i class="fas fa-comment-dots"></i></a>
        <a href="request.php"><i class="fas fa-user-plus"></i></a>
        <a href="profile.php?profile_username=<?php echo htmlspecialchars($userLoggedIn); ?>">
            <img src="<?php echo htmlspecialchars($user_arr['profile_pic'] ?? 'assets/images/default.png'); ?>" class="topbar-avatar" alt="">
        </a>
    </div>
</nav>

<div class="tv-layout">

    <!-- LEFT SIDEBAR -->
    <aside class="tv-left">
        <div class="user-card">
            <div class="user-card-cover"></div>
            <div class="user-card-body">
                <img src="<?php echo htmlspecialchars($user_arr['profile_pic'] ?? 'assets/images/default.png'); ?>"
                     class="user-card-avatar" alt="">
                <div class="user-card-name">
                    <?php echo htmlspecialchars($user_arr['first_name'] . ' ' . $user_arr['last_name']); ?>
                </div>
                <div class="user-card-handle">@<?php echo htmlspecialchars($userLoggedIn); ?></div>
                <div class="user-card-stats">
                    <div class="user-card-stat">
                        <div class="n"><?php echo (int)($user_arr['num_posts'] ?? 0); ?></div>
                        <div class="l">Posts</div>
                    </div>
                    <div class="user-card-stat">
                        <div class="n"><?php echo $num_friends; ?></div>
                        <div class="l">Friends</div>
                    </div>
                </div>
            </div>
        </div>

        <nav class="nav-menu">
            <a href="index.php" class="active"><i class="fas fa-home"></i> Home Feed</a>
            <a href="profile.php?profile_username=<?php echo htmlspecialchars($userLoggedIn); ?>">
                <i class="fas fa-user"></i> My Profile
            </a>
            <a href="messages.php"><i class="fas fa-comment-dots"></i> Messages</a>
            <a href="request.php"><i class="fas fa-user-plus"></i> Requests</a>
            <a href="account_settings.php"><i class="fas fa-gear"></i> Settings</a>
            <a href="logout.php" style="color:#ef4444"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </nav>
    </aside>

    <!-- MAIN FEED -->
    <main class="tv-feed">

        <!-- Filter bar -->
        <div class="filter-bar">
            <span class="fb-label">Browse:</span>
            <?php foreach ($categories as $key => $cat): ?>
                <a href="index.php?tag=<?php echo $key; ?>"
                   class="fb-tag <?php echo $key === $active_tag ? 'active' : ''; ?>">
                    <i class="<?php echo $cat['icon']; ?>"></i>
                    <?php echo $cat['label']; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Create post -->
        <div class="create-post">
            <form action="index.php" method="POST" enctype="multipart/form-data">
                <div class="create-post-top">
                    <img src="<?php echo htmlspecialchars($user_arr['profile_pic'] ?? 'assets/images/default.png'); ?>" alt="">
                    <textarea name="post_text" placeholder="Share your talent today..." rows="1"
                        oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
                </div>
                <div class="post-img-preview" id="imgPreview">
                    <img id="previewImg" src="" alt="">
                    <button type="button" class="rm-img" onclick="clearImage()"><i class="fas fa-times"></i></button>
                </div>
                <div class="post-options">
                    <select name="post_tag" class="post-tag-select">
                        <?php foreach ($categories as $key => $cat): ?>
                            <?php if ($key === 'all') continue; ?>
                            <option value="<?php echo $key; ?>"
                                <?php echo ($active_tag !== 'all' && $active_tag === $key) ? 'selected' : ''; ?>>
                                <?php echo $cat['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="post-img-label">
                        <i class="fas fa-image"></i> Photo
                        <input type="file" name="fileToUpload" accept="image/*" onchange="previewImage(this)">
                    </label>
                    <button type="submit" name="post" class="btn-post">
                        <i class="fas fa-paper-plane"></i> Post
                    </button>
                </div>
            </form>
        </div>

        <!-- Posts feed -->
        <?php
            $post_obj = new Post($con, $userLoggedIn);
            $post_obj->indexPosts($active_tag, $categories);
        ?>

    </main>

    <!-- RIGHT SIDEBAR -->
    <aside class="tv-right">
        <div class="widget">
            <div class="widget-title"><i class="fas fa-fire"></i> Trending Talents</div>
            <?php foreach ($categories as $key => $cat):
                if ($key === 'all') continue;
                $cnt_q   = mysqli_query($con, "SELECT COUNT(*) as c FROM posts WHERE tags='$key' AND deleted='no'");
                $cnt_row = mysqli_fetch_array($cnt_q);
                $cnt     = (int)$cnt_row['c'];
                if ($cnt === 0) continue;
            ?>
            <a href="index.php?tag=<?php echo $key; ?>" class="trending-tag">
                <div class="tt-icon <?php echo $cat['class']; ?>">
                    <i class="<?php echo $cat['icon']; ?>" style="color:#fff"></i>
                </div>
                <div class="tt-info">
                    <div class="tt-name"><?php echo $cat['label']; ?></div>
                    <div class="tt-count"><?php echo $cnt; ?> post<?php echo $cnt !== 1 ? 's' : ''; ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </aside>

</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imgPreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function clearImage() {
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('previewImg').src = '';
    document.querySelector('input[name="fileToUpload"]').value = '';
}
// Toggle comments
function toggleComments(id) {
    const el = document.getElementById('comments_' + id);
    el.classList.toggle('open');
}
</script>

</body>
</html>
