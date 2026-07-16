<?php

class Post {
    private $user_obj;
    private $con;

    public function __construct($con, $user) {
        $this->con      = $con;
        $this->user_obj = new User($con, $user);
    }

    public function submitPost($body, $imageName, $tag = 'other') {
        $body = strip_tags($body);
        $body = mysqli_real_escape_string($this->con, $body);
        $tag  = mysqli_real_escape_string($this->con, $tag);
        $body = implode(' ', preg_split('/\s+/', $body));

        if (trim($body) === '' && $imageName === '') return;

        $date_added = date('Y-m-d H:i:s');
        $added_by   = $this->user_obj->getUsername();

        mysqli_query($this->con,
            "INSERT INTO posts (body, added_by, date_added, user_closed, deleted, likes, image, tags)
             VALUES('$body', '$added_by', '$date_added', 'no', 'no', '0', '$imageName', '$tag')"
        );

        $id          = mysqli_insert_id($this->con);
        $num_posts   = $this->user_obj->getNumPosts() + 1;
        mysqli_query($this->con,
            "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'"
        );
        return $id;
    }

    public function indexPosts($filter = 'all', $categories = []) {
        $where = "WHERE p.deleted='no' AND p.user_closed='no'";
        if ($filter !== 'all') {
            $f     = mysqli_real_escape_string($this->con, $filter);
            $where .= " AND p.tags='$f'";
        }

        $query = mysqli_query($this->con,
            "SELECT p.*, u.first_name, u.last_name, u.profile_pic
             FROM posts p
             JOIN users u ON u.username = p.added_by
             $where
             ORDER BY p.id DESC"
        );

        if (mysqli_num_rows($query) === 0) {
            echo "<div style='text-align:center;padding:40px;color:#6b7280;'>
                    <i class='fas fa-star fa-2x' style='margin-bottom:12px;color:#c4b5fd'></i><br>
                    No posts here yet. Be the first to share your talent!
                  </div>";
            return;
        }

        $now = new DateTime();

        while ($row = mysqli_fetch_array($query)) {
            $id          = (int) $row['id'];
            $body        = htmlspecialchars($row['body']);
            $added_by    = $row['added_by'];
            $first_name  = htmlspecialchars($row['first_name']);
            $last_name   = htmlspecialchars($row['last_name']);
            $profile_pic = htmlspecialchars($row['profile_pic'] ?: 'assets/images/default.png');
            $imagePath   = htmlspecialchars($row['image']);
            $tag         = $row['tags'] ?? 'other';
            $tag_class   = isset($categories[$tag]) ? $categories[$tag]['class'] : 'tag-other';
            $tag_label   = isset($categories[$tag]) ? $categories[$tag]['label'] : ucfirst($tag);
            $tag_icon    = isset($categories[$tag]) ? $categories[$tag]['icon']  : 'fas fa-star';

            // Time ago
            $post_time = new DateTime($row['date_added']);
            $diff      = $now->diff($post_time);
            if ($diff->y >= 1)      $time_msg = $diff->y . 'y ago';
            elseif ($diff->m >= 1)  $time_msg = $diff->m . 'mo ago';
            elseif ($diff->d >= 1)  $time_msg = $diff->d . 'd ago';
            elseif ($diff->h >= 1)  $time_msg = $diff->h . 'h ago';
            elseif ($diff->i >= 1)  $time_msg = $diff->i . 'min ago';
            else                    $time_msg = 'Just now';

            // Comment count
            $c_q   = mysqli_query($this->con, "SELECT COUNT(*) AS c FROM comments WHERE post_id='$id'");
            $c_row = mysqli_fetch_array($c_q);
            $c_cnt = (int) $c_row['c'];

            // Like count
            $likes = (int)($row['likes'] ?? 0);

            $img_html = '';
            if ($imagePath) {
                $img_html = "<img src='$imagePath' class='post-image' alt='post image'>";
            }

            echo "
            <div class='post-card' id='post_$id'>
                <div class='post-header'>
                    <img src='$profile_pic' class='post-avatar' alt=''>
                    <div class='post-meta'>
                        <div class='post-author'><a href='profile.php?profile_username=" . htmlspecialchars($added_by) . "'>$first_name $last_name</a></div>
                        <div class='post-time'>$time_msg</div>
                    </div>
                    <a href='index.php?tag=$tag' class='post-tag-badge $tag_class'>
                        <i class='$tag_icon'></i> $tag_label
                    </a>
                </div>
                " . ($body ? "<div class='post-body'>$body</div>" : '') . "
                $img_html
                <div class='post-footer'>
                    <iframe src='like.php?post_id=$id' style='border:0;height:30px;width:110px' scrolling='no'></iframe>
                    <button class='post-action' onclick='toggleComments($id)'>
                        <i class='fas fa-comment'></i> $c_cnt
                    </button>
                </div>
                <div class='post-comments' id='comments_$id'>
                    <iframe src='comment_frame.php?post_id=$id' frameborder='0'
                            style='width:100%;min-height:180px;display:block'></iframe>
                </div>
            </div>";
        }
    }
}
?>
