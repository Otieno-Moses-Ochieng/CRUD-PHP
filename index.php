<?php
include('db/conn.php');

// Handle Create/Insert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $photo = file_get_contents($_FILES['photo']['tmp_name']);

    $sql = "INSERT INTO people (name, email, photo) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $photo);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Person added successfully!');</script>";
    } else {
        echo "<script>alert('Failed to add person: " . mysqli_error($conn) . "');</script>";
    }
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $photo = !empty($_FILES['photo']['tmp_name']) ? file_get_contents($_FILES['photo']['tmp_name']) : null;

    if ($photo) {
        $sql = "UPDATE people SET name = ?, email = ?, photo = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $photo, $id);
    } else {
        $sql = "UPDATE people SET name = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Person updated successfully!');</script>";
    } else {
        echo "<script>alert('Failed to update person: " . mysqli_error($conn) . "');</script>";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM people WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Person deleted successfully!');</script>";
    } else {
        echo "<script>alert('Failed to delete person: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch Data
$sql = "SELECT id, name, email, photo FROM people";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD with Photo as BLOB</title>
    <style>
        th, td {
            padding: 10px 20px;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            margin: 20px auto;
            width: 80%;
        }
        th {
            background-color: blue;
            color: white;
        }
        td img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center; background-color: blue; color: white;">CRUD Operations </h1>

    <!-- Form for Adding/Updating -->
    <form method="POST" enctype="multipart/form-data" style="width: 50%; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px;">
        <h3>Add/Update Person</h3>
        <input type="hidden" name="id" id="id">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required><br><br>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br><br>
        <label for="photo">Photo:</label>
        <input type="file" name="photo" id="photo" accept="image/*"><br><br>
        <button type="submit" name="add">Add</button>
        <button type="submit" name="update">Update</button>
    </form>

    <!-- Table for Displaying Data -->
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <?php if ($row['photo']): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['photo']); ?>" alt="Photo">
                            <?php else: ?>
                                No Photo
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="javascript:void(0);" onclick="editPerson('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['email']); ?>')">Edit</a> |
                            <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this person?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No data found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // JavaScript for populating the form for editing
        function editPerson(id, name, email) {
            document.getElementById('id').value = id;
            document.getElementById('name').value = name;
            document.getElementById('email').value = email;
            document.getElementsByName('photo')[0].value = ''; // Clear file input
        }
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
