<?php
session_start();
include 'config/database.php';
include 'includes/header.php';
include 'includes/bodybeforeposts.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$conditions = []; // Містить окремі умови SQL запиту
$params = []; //значення для підстановки в підготовлений запит.

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $conditions[] = "posts.category_id = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['from_date']) && !empty($_GET['from_date'])) {
    $conditions[] = "posts.created_at >= ?";
    $params[] = $_GET['from_date'];
}

if (isset($_GET['to_date']) && !empty($_GET['to_date'])) {
    $conditions[] = "posts.created_at <= ?";
    $params[] = $_GET['to_date'];
}

if (isset($_GET['search_title']) && !empty($_GET['search_title'])) {
    $conditions[] = "posts.title LIKE ?";
    $params[] = '%' . $_GET['search_title'] . '%';
}

$query = "SELECT posts.*, categories.name AS category_name, 
                 (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.post_id) AS like_count
          FROM posts 
          JOIN categories ON posts.category_id = categories.category_id";

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY posts.created_at DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $types = str_repeat('s', count($params)); // Створює рядок із заданим символом s (string) стільки разів, скільки елементів у масиві $params.
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

?>


<form method="get" action="garden-index.php" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
    <div class="form-group" style="flex: 1;">
        <label for="category">Категорія:</label>
        <select name="category" id="category" class="form-control">
            <option value="">Всі категорії</option>
            <?php
            $category_query = "SELECT category_id, name FROM categories";
            $category_result = $conn->query($category_query);
            while ($category_row = $category_result->fetch_assoc()) {
                $selected = (isset($_GET['category']) && $_GET['category'] == $category_row['category_id']) ? 'selected' : '';
                echo "<option value='" . $category_row['category_id'] . "' $selected>" . $category_row['name'] . "</option>";
            }
            ?>
        </select>

    </div>
    <div class="form-group" style="flex: 1;">
        <label for="from_date">З:</label>
        <input type="date" name="from_date" id="from_date" class="form-control" value="<?php echo $_GET['from_date'] ?? ''; ?>">
    </div>
    <div class="form-group" style="flex: 1;">
        <label for="to_date">По:</label>
        <input type="date" name="to_date" id="to_date" class="form-control" value="<?php echo $_GET['to_date'] ?? ''; ?>">
    </div>
    <div class="form-group" style="flex: 1;">
        <label for="search_title">Заголовок:</label>
        <input type="text" name="search_title" id="search_title" class="form-control" value="<?php echo $_GET['search_title'] ?? ''; ?>">
    </div>
    <button type="submit" class="btn btn-primary">Застосувати фільтри</button>
</form>

<section class="section wb">
    <div class="container">
        <div class="row">
            <div class="col-lg-9 col-md-12 col-sm-12 col-xs-12">
                <div class="page-wrapper">
                    <div class="blog-list clearfix">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                $post_id = $row['post_id'];
                                $liked = false;

                                if (isset($_SESSION['user_id'])) {
                                    $user_id = $_SESSION['user_id'];
                                    $like_query = "SELECT * FROM likes WHERE post_id = $post_id AND user_id = $user_id";
                                    $like_result = $conn->query($like_query); // виконує запит
                                    if ($like_result && $like_result->num_rows > 0) {
                                        $liked = true;
                                    }
                                }
                                ?>
                                <div class="blog-box row">
                                    <div class="col-md-4">
                                        <div class="post-media">

                                            <a href="garden-single.php?id=<?php echo $post_id; ?>" title="">
                                                <img src="upload/<?php echo $row['image']; ?>" alt="" class="img-fluid">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="blog-meta big-meta col-md-8">
                                        <h4><a href="garden-single.php?id=<?php echo $post_id; ?>"> <?php echo htmlspecialchars($row['title']); ?></a></h4>
                                        <small><a href="garden-single.php?id=<?php echo $row['post_id']; ?>" title=""><?php echo date('d M, Y', strtotime($row['created_at'])); ?></a></small>
                                        <small>by admin</small>
                                        <button class="btn btn-primary" onclick="likePost(<?php echo $post_id; ?>, this)">
                                            <?php echo $liked ? 'Вподобано' : 'Вподобати'; ?>
                                        </button>
                                        <span class="like-count"><?php echo $row['like_count']; ?> лайків</span>
                                    </div>
                                </div>
                                <hr class="invis">

                                <?php endwhile; ?>
                        <?php else: ?>
                            <p>No posts found.</p>
                        <?php endif; ?>
                        <?php $conn->close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function likePost(postId, button) {
    fetch('like_handler.php', {
        method: 'POST',
        body: JSON.stringify({ post_id: postId }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeCountSpan = button.nextElementSibling; // наступний ел після sp
            let likes = parseInt(likeCountSpan.textContent); // перетворює текстове значення кількості лайків
            button.textContent = button.textContent === 'Вподобати' ? 'Вподобано' : 'Вподобати';
            likeCountSpan.textContent = (button.textContent === 'Вподобано' ? --likes : ++likes) + ' лайків';
        }
    })
    .catch(error => console.error('Error:', error));
}

$(document).ready(function() {
    $('#comment-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var postId = <?php echo $post_id; ?>;

        $.ajax({
            url: 'add_comment.php',
            type: 'POST',
            data: formData + '&post_id=' + postId,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.error);
                }
            },
            error: function() {
                alert('Щось пішло не так. Спробуйте ще раз.');
            }
        });
    });
});

</script>

<?php include 'includes/footer.php'; ?>