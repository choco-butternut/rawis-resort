<?php
require_once "php/config.php";


$sql = "SELECT * FROM amenities ORDER BY amenity_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Our Amenities</title>
    <link rel="stylesheet" href="css/style.css">
    
</head>
<body>
    <?php require_once __DIR__ . '/php/header.php'; ?>

<h2 style="text-align:center;">Our Amenities</h2>

<div>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div>
                <div class="amenity-title">
                    <?php echo htmlspecialchars($row['amenity_name']); ?>
                </div>
                <div class="amenity-description">
                    <?php echo htmlspecialchars($row['description']); ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No amenities available at the moment.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/php/footer.php'; ?>

</body>
</html>