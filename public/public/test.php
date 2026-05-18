<?php
$hash = '$2y$10$CwTycUXWue0Thq9StjUM0uJ8dXRu1oKp9c5fOQxwYxj6Jm8bGZ3Ue';

var_dump(password_verify('admin123', $hash));