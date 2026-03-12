<?php
// index.php - Main page: display feedback form and all submissions

require_once 'db.php';

$successMsg = '';
$errorMsg   = '';

// ── Handle form submission ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $rating  = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($name === '' || $email === '' || $rating < 1 || $rating > 5) {
        $errorMsg = 'Please fill in all required fields and choose a rating (1–5).';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare(
            "INSERT INTO feedback (name, email, rating, comment) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssis", $name, $email, $rating, $comment);
        if ($stmt->execute()) {
            $successMsg = 'Thank you, ' . htmlspecialchars($name) . '! Your feedback was saved.';
        } else {
            $errorMsg = 'Database error: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
        $conn->close();
    }
}

// ── Fetch all feedback ────────────────────────────────────────────────────────
$conn    = getConnection();
$result  = $conn->query("SELECT * FROM feedback ORDER BY submitted_at DESC");
$rows    = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Feedback App</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

  <header>
    <h1>📋 Student Feedback</h1>
    <p>Share your thoughts about the course!</p>
  </header>

  <!-- ── Feedback Form ─────────────────────────────────────── -->
  <section class="card form-card">
    <h2>Submit Feedback</h2>

    <?php if ($successMsg): ?>
      <div class="alert success"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
      <div class="alert error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
      <div class="form-group">
        <label for="name">Name <span class="req">*</span></label>
        <input type="text" id="name" name="name" placeholder="Your full name"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="email">Email <span class="req">*</span></label>
        <input type="email" id="email" name="email" placeholder="you@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>Rating <span class="req">*</span></label>
        <div class="star-row">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <label class="star-label">
              <input type="radio" name="rating" value="<?= $i ?>"
                <?= (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'checked' : '' ?>>
              <span class="star">&#9733;</span>
            </label>
          <?php endfor; ?>
        </div>
      </div>

      <div class="form-group">
        <label for="comment">Comment</label>
        <textarea id="comment" name="comment" rows="4"
                  placeholder="Tell us what you think..."><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn-submit">Submit Feedback</button>
    </form>
  </section>

  <!-- ── Submissions Table ──────────────────────────────────── -->
  <section class="card table-card">
    <h2>All Feedback (<?= count($rows) ?> entries)</h2>

    <?php if (empty($rows)): ?>
      <p class="no-data">No feedback submitted yet. Be the first!</p>
    <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Rating</th>
              <th>Comment</th>
              <th>Submitted</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= (int)$row['id'] ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td class="rating">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                  <span class="<?= $s <= $row['rating'] ? 'star-on' : 'star-off' ?>">&#9733;</span>
                <?php endfor; ?>
              </td>
              <td><?= nl2br(htmlspecialchars($row['comment'])) ?></td>
              <td><?= htmlspecialchars($row['submitted_at']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

</div><!-- /container -->
</body>
</html>
