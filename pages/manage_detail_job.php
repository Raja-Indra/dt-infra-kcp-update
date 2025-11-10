<?php
include '../db.php';
include '../includes/session.php';
include '../nav.php';

$issues = mysqli_query($conn, "SELECT * FROM issues ORDER BY issue_name");
$sub_issues = mysqli_query($conn, "SELECT sub_issues.id, sub_issues.sub_issue_name, issues.issue_name FROM sub_issues JOIN issues ON sub_issues.issue_id = issues.id ORDER BY issues.issue_name, sub_issues.sub_issue_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Detail Job</title>
</head>
<body>
    <h2>Issue Management</h2>
    <form action="issue_add.php" method="POST">
        <input type="text" name="issue_name" placeholder="Issue name" required>
        <button type="submit">Add Issue</button>
    </form>
    <ul>
        <?php while ($issue = mysqli_fetch_assoc($issues)) : ?>
            <li><?= $issue['issue_name'] ?> 
                <a href="issue_delete.php?id=<?= $issue['id'] ?>">Delete</a>
            </li>
        <?php endwhile; ?>
    </ul>

    <h2>Sub Issue Management</h2>
    <form action="sub_issue_add.php" method="POST">
        <input type="text" name="sub_issue_name" placeholder="Sub Issue name" required>
        <select name="issue_id" required>
            <option value="">Select Issue</option>
            <?php mysqli_data_seek($issues, 0); while ($i = mysqli_fetch_assoc($issues)) : ?>
                <option value="<?= $i['id'] ?>"><?= $i['issue_name'] ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Add Sub Issue</button>
    </form>
    <table border="1">
        <tr><th>#</th><th>Sub Issue</th><th>Issue</th><th>Action</th></tr>
        <?php $no = 1; while ($sub = mysqli_fetch_assoc($sub_issues)) : ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $sub['sub_issue_name'] ?></td>
                <td><?= $sub['issue_name'] ?></td>
                <td><a href="sub_issue_delete.php?id=<?= $sub['id'] ?>">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
