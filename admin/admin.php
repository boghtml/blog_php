<?php
session_start();
include '../admin_includes/header.php';
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';

if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="alert alert-success">Новий пост успішно додано!</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $category = $_POST['category'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = '' . $image;
    move_uploaded_file($image_tmp, $image_path);

    $sql = "INSERT INTO posts (category_id, title, description, image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $category, $title, $description, $image_path);
    if ($stmt->execute()) {
        header("Location: admin.php?success=1");
        exit;
    } else {
        $message = 'Помилка при додаванні поста: ' . $conn->error;
    }
    $stmt->close();
}
?>

<body>
    <div id="wrapper">
    <div class="container mt-5">
        <h1>Додати новий пост</h1>
        <?php if ($message): ?>
        <div class="alert alert-success">
            <?php
             echo $message; 
            ?>
        </div>
        <?php endif; ?>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category">Категорія:</label>
                <select name="category" id="category" class="form-control" required>
                    <option value="">Оберіть категорію</option>
                    <?php
                    $sql = "SELECT category_id, name FROM categories";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['category_id'] . "'>" . $row['name'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Зображення:</label>
                <input type="file" name="image" id="image" class="form-control-file" required>
            </div>
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="description">Опис:</label>
                <textarea name="description" id="description" class="form-control" required></textarea>
            </div>
            <button type="submit" name="add_post" class="btn btn-primary">Опублікувати</button>
        </form>
    </div>
    <hr class="invis">

<?php

$sql = "SELECT p.post_id AS id, c.name AS category, p.title, p.description, p.image 
    FROM posts p
    JOIN categories c ON p.category_id = c.category_id";

$result = $conn->query($sql);
?>

<h2>Список постів</h2>
<table class="table">
    <thead>
        <tr>
            <th>Категорія</th>
            <th>Заголовок</th>
            <th>Опис</th>
            <th>Зображення</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><img src="../upload/<?php echo $row['image']; ?>" alt="Post Image" width="100"></td>

            <td>
            <a href="#" onclick='openEditModal(<?php echo json_encode($row); ?>); return false;'>Редагувати</a> |
            <a href="#" onclick="deletePost(<?php echo $row['id']; ?>); return false;">Видалити</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div class="modal fade" id="editPostModal" tabindex="-1" role="dialog" aria-labelledby="editPostModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPostModalLabel">Редагувати пост</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
                <form id="editForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="form-group">
                        <label for="editCategory">Категорія:</label>
                        <select id="editCategory" name="category" class="form-control">
                            <?php
                            $sql = "SELECT category_id, name FROM categories";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['category_id'] . "'>" . $row['name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editTitle">Заголовок:</label>
                        <input type="text" id="editTitle" name="title" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="editImage">Зображення:</label>
                        <input type="file" id="editImage" name="image" class="form-control-file">
                    </div>

                    <div class="form-group">
                        <label for="editDescription">Опис:</label>
                        <textarea id="editDescription" name="description" class="form-control"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" onclick="submitEditForm()">Підтвердити</button>
            </div>
        </div>
    </div>
</div>


<script>
function openEditModal(post) {

    document.getElementById('editId').value = post.id;
    document.getElementById('editCategory').value = post.category_id;
    document.getElementById('editTitle').value = post.title;
    document.getElementById('editDescription').textContent = post.description;

    $('#editPostModal').modal('show');
}

function submitEditForm() {
    var formData = new FormData(document.getElementById('editForm'));
    fetch('update_post.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#editPostModal').modal('hide');
            alert('Пост успішно оновлено!');
            location.reload();
        } else {
            alert('Помилка: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function deletePost(postId) {
    if (confirm('Ви впевнені, що хочете видалити цей пост?')) {
        fetch('delete_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Пост було успішно видалено!');
                location.reload();
            } else {
                alert('Помилка: ' + data.error);
            }
        });
    }
}

</script>


<?php include '../admin_includes/footer.php'; ?>


