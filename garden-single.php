<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: includes/auth.php');
    exit();
}

include 'config/database.php';

$post_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Post ID not found.');

$query = "SELECT posts.*, categories.name AS category_name, 
          (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.post_id) AS likes_count
          FROM posts 
          JOIN categories ON posts.category_id = categories.category_id 
          WHERE posts.post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result(); 
$post = $result->fetch_assoc();

$liked = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $like_query = "SELECT * FROM likes WHERE post_id = $post_id AND user_id = $user_id";
    $like_result = $conn->query($like_query);
    $liked = $like_result && $like_result->num_rows > 0;
}

$comments_query = "SELECT comments.*, users.username FROM comments 
                   JOIN users ON comments.user_id = users.user_id 
                   WHERE post_id = ? ORDER BY comments.created_at DESC";
$comments_stmt = $conn->prepare($comments_query);
$comments_stmt->bind_param("i", $post_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments_count = $comments_result->num_rows;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    $comment_content = $_POST['comment_content'];
    $user_id = $_SESSION['user_id'];

    $insert_query = "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param('iis', $post_id, $user_id, $comment_content);
    if ($insert_stmt->execute()) {
        ob_start();
        header("Location: garden-single.php?id=$post_id");
        exit;
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

if (!$post) {
    echo "<div>Post not found.</div>";
} else {
    echo "<section class='section wb'>
        <div class='container'>
            <div class='row'>
                <div class='col-lg-9 col-md-12 col-sm-12 col-xs-12'>
                    <div class='page-wrapper'>
                        <div class='blog-title-area'>
                            <span class='color-green'><a href='garden-category.php' title=''>{$post['category_name']}</a></span>
                            <h3>{$post['title']}</h3>
                            <div class='blog-meta big-meta'>
                                <small>Створено: " . date('H:i - d - F - Y', strtotime($post['created_at'])) . "</small>
                                <small>Оновлено: " . date('H:i - d - F - Y', strtotime($post['modified_at'])) . "</small>
                                <small>by admin</small>
                            </div>
                            <div class='post-sharing'>
                                <ul class='list-inline'>
                                    <button class='btn btn-primary' onclick='likePost({$post_id}, this)'>
                                        <i class='fa ".($liked ? "fa-heart" : "fa-heart-o")."'></i> <span class='down-mobile'>".($liked ? "Вподобано" : "Вподобати")."</span>
                                    </button>
                                    <span id='like-count-{$post_id}'>{$post['likes_count']} Лайків</span>
                                </ul>
                            </div>
                        </div>
                        <div class='single-post-media'>
                            <img src='upload/{$post['image']}' alt='' class='img-fluid'>
                        </div>
                        <div class='blog-content'>  
                            <div class='pp'>
                                <p>{$post['description']}</p>
                            </div>
                        </div>
                        <hr class='invis1'>";

    // Відображення форми для додавання коментарів
    echo "<div class='add-comment'>
            <form action='' method='post'>
                <textarea name='comment_content' class='form-control' required placeholder='Напишіть ваш коментар тут...' style='margin-bottom: 20px;'></textarea>
                <button type='submit' name='submit_comment' class='btn btn-primary'>Додати коментар</button>
            </form>
        </div>
        <hr class='invis1'>";
        
    echo "<div class='custombox clearfix'>
            <h4 class='small-title'>Коментарі ({$comments_count})</h4>
            <div class='row'>
                <div class='col-lg-12'>
                    <div class='comments-list'>";
    while ($comment = $comments_result->fetch_assoc()) {
        echo "<div class='media'>
                <a class='media-left' href='#'>
                    <img src='upload/author.jpg' alt='' class='rounded-circle'>
                </a>
                <div class='media-body'>
                    <h4 class='media-heading user_name'>{$comment['username']} <small>".date('d M Y', strtotime($comment['created_at']))."</small></h4>
                    <p>{$comment['content']}</p>
                </div>
              </div>";
    }
    echo        "</div>
                </div>
            </div>
        </div>";

    echo        "<hr class='invis1'>
                        </div>
                    </div>
                </div>
            </div>
        </section>";
}

include 'includes/header.php';
include 'includes/footer.php';
$comments_stmt->close();
?>
