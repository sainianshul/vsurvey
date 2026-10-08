<?php
$file = 'c:/Users/anshu/Desktop/code/schotech/vcancares/resources/views/admin/nurses/edit.blade.php';
$content = file_get_contents($file);

$fields = [
    'name', 'email', 'phone', 'emergency_contact_phone', 'bio', 
    'address', 'city', 'state', 'country', 'pincode', 'latitude', 'longitude',
    'available_from', 'available_to'
];

foreach ($fields as $field) {
    // Add is-invalid class if error exists
    $content = preg_replace(
        '/(name="'.$field.'".*?class="[^"]*)(")/i',
        '$1 @error(\''.$field.'\') is-invalid @enderror$2',
        $content
    );
    
    // Add error message div after the input or its wrapping .position-relative
    $content = preg_replace(
        '/(<input[^>]*name="'.$field.'"[^>]*>)/i',
        '$1' . "\n                                        @error('".$field."')\n                                            <div class=\"invalid-feedback d-block\">{{ \$message }}</div>\n                                        @enderror",
        $content
    );
    
    // for textarea bio
    if ($field == 'bio') {
        $content = preg_replace(
            '/(<textarea[^>]*name="'.$field.'"[^>]*>.*?<\/textarea>)/is',
            '$1' . "\n                                        @error('".$field."')\n                                            <div class=\"invalid-feedback d-block\">{{ \$message }}</div>\n                                        @enderror",
            $content
        );
    }
}

// Add for profile_photo
$content = preg_replace(
    '/(<input type="file" name="profile_photo"[^>]*>)/i',
    '$1' . "\n                                    @error('profile_photo')\n                                        <div class=\"invalid-feedback d-block text-center mt-2\">{{ \$message }}</div>\n                                    @enderror",
    $content
);

file_put_contents($file, $content);
echo "Fixed edit.blade.php\n";
