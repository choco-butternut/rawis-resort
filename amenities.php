<?php
require_once "php/config.php";


$sql = "SELECT * FROM amenities ORDER BY amenity_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Amenities</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    
</head>
<body>
    <?php require_once __DIR__ . '/php/header.php'; ?>

<h2 style="text-align:center;">Our Amenities</h2>

<div>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div>
                <div>
                    <img  src="<?php echo $row["image_path"] ?>" width="200" height="200"/>
                </div>
                
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