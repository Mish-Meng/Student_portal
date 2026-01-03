<?php
include 'connect.php';
if ($conn) {
  echo "✅ Database connected successfully!";
} else {
  echo "❌ Failed to connect!";
}
?>
