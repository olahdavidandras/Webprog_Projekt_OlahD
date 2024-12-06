<?php
session_start();
include 'db_connect.php';
include 'Picture.php';
include 'Tags.php';
include 'Comment.php';

if (!isset($conn)) {
    die("Hiba: Nincs adatbázis kapcsolat!");
}

$picture = new Picture($conn);
$tags = new Tags($conn);
$comment = new Comment($conn);

$userId = $_SESSION['user_id'];
$photos = $picture->getPhotosByUser($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['comment_text'], $_POST['photo_id'])
) {
    $commentText = trim($_POST['comment_text']);
    $photoId = intval($_POST['photo_id']);

    if (!empty($commentText) && $photoId > 0) {
        if ($comment->addComment($photoId, $userId, $commentText)) {
            header("Location: gallery.php");
            exit();
        } else {
            echo "Hiba történt a komment mentésekor.";
        }
    } else {
        echo "A komment nem lehet üres!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galéria</title>
</head>
<body>
<h2>Képgaléria</h2>

<form action="feed.php" method="GET" style="display: inline;">
    <button type="submit">Feed</button>
</form>
<form action="upload.php" method="GET" style="display: inline;">
    <button type="submit">Feltöltés</button>
</form>
<form action="logout.php" method="GET" style="display: inline;">
    <button type="submit">Kijelentkezés</button>
</form>
<hr>

<?php if ($photos): ?>
    <?php foreach ($photos as $photo): ?>
        <div style="border: 1px solid #ccc; margin-bottom: 20px; padding: 10px;">
            <img src="data:image/jpeg;base64,<?= base64_encode(
                $photo['photo_data']
            ) ?>" alt="<?= htmlspecialchars($photo['title']) ?>"
                 style="max-width: 200px;">
            <h3><?= htmlspecialchars($photo['title']) ?></h3>
            <p><?= htmlspecialchars($photo['description']) ?></p>
            <p><strong>Feltöltve:</strong> <?= $photo['created_at']; ?></p>

            <form method="GET" action="download_photo.php"
                  style="display: inline;">
                <input type="hidden" name="photo_id"
                       value="<?= $photo['photo_id']; ?>">
                <button type="submit">Letöltés</button>
            </form>

            <form action="share_photo.php" method="POST"
                  style="display: inline;">
                <input type="hidden" name="photo_id"
                       value="<?= $photo['photo_id'] ?>">
                <button type="submit">Megosztás</button>
            </form>

            <h4>Kommentek:</h4>
            <?php
            $comments = $comment->getCommentsByPhoto($photo['photo_id']);
            if ($comments): ?>
                <ul>
                    <?php foreach ($comments as $comm): ?>
                        <li>
                            <strong><?= htmlspecialchars($comm['username']); ?>
                                :</strong>
                            <?= htmlspecialchars($comm['comment_text']); ?>
                            <em>(<?= $comm['created_at']; ?>)</em>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Még nincsenek kommentek.</p>
            <?php endif; ?>

            <form method="POST">
                <textarea name="comment_text" rows="2" cols="50"
                          placeholder="Írj egy kommentet..."
                          required></textarea><br>
                <input type="hidden" name="photo_id"
                       value="<?= $photo['photo_id']; ?>">
                <button type="submit">Hozzáadás</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nincsenek feltöltött képeid.</p>
<?php endif; ?>
</body>
</html>
