<?php
include '../db.php';
$departments = mysqli_query($conn, "SELECT * FROM departments");
if (isset($_POST['add'])) {
    $section = $_POST['section_name'];
    $dept_id = $_POST['department_id'];
    mysqli_query($conn, "INSERT INTO sections (section_name, department_id) VALUES ('$section', $dept_id)");
}
$sections = mysqli_query($conn, "SELECT sections.*, departments.department_name FROM sections 
    JOIN departments ON sections.department_id = departments.id");
?>
<h2>Sections</h2>
<form method="POST">
    <input type="text" name="section_name" required>
    <select name="department_id" required>
        <option value="">Select Department</option>
        <?php while($dept = mysqli_fetch_assoc($departments)): ?>
        <option value="<?= $dept['id'] ?>"><?= $dept['department_name'] ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit" name="add">Add Section</button>
</form>
<table border="1">
    <tr><th>No</th><th>Section</th><th>Department</th></tr>
    <?php $no=1; while($row = mysqli_fetch_assoc($sections)): ?>
    <tr><td><?= $no++ ?></td><td><?= $row['section_name'] ?></td><td><?= $row['department_name'] ?></td></tr>
    <?php endwhile; ?>
</table>
